<?php

/**
 * @property-read string     imdb
 * @property-read string     Title
 * @property-read int        Year
 * @property-read string     OriginalTitle
 * @property-read XTimeSpan  Runtime
 * @property-read string     Backdrop
 * @property-read string     Poster
 * @property-read string     Overview
 * @property-read array      Countries
 * @property-read array      Languages
 * @property-read array      Keywords
 * @property-read array      Genres
 * @property-read array      Credits
 * @property-read array      Pictures
 */

class Movie extends MMItem {

	public $iid;
	public $Type;
	/** @var string     */ protected $_imdb;
	/** @var string     */ protected $_Title;
	/** @var string     */ protected $_Overview;
	/** @var string     */ protected $_OriginalTitle;
	/** @var string     */ protected $_Backdrop;
	/** @var string     */ protected $_Poster;
	/** @var int        */ protected $_Year;
	/** @var XTimeSpan  */ protected $_Runtime;
	/** @var array      */ protected $_Countries = [];
	/** @var array      */ protected $_Languages = [];
	/** @var array      */ protected $_Genres = [];
	/** @var array      */ protected $_Keywords = [];
	/** @var array      */ protected $_Credits = [];
	/** @var array      */ protected $_Pictures = [];

	const TMDb_TYPE = 'movie';
	private function __construct($iid) {
		$this->iid = $iid;
		$this->Type = self::TMDb_TYPE;
	}



	public function GetKey(){ return 'M'.$this->iid; }
	public function GetCaption() { $this->Load(); return $this->_Title . ($this->_Year === null ? '' : ' ('.$this->_Year.')'); }
	public function GetImage(){ $this->Load(); return $this->_Backdrop === null ? null : TMDb::GetImageSrc($this->_Backdrop,TMDb::BACKDROP_W300); }
	public function GetLanguagesTranslated() { $this->Load(); return implode(', ',array_map(function($x){ return mmx::FormatLanguage($x); }, $this->Languages)); }
	public function GetCountriesTranslated() { $this->Load(); return implode(', ',array_map(function($x){ return mmx::FormatCountry($x); }, $this->Countries)); }


	public function ToArray() {
		$this->Load();
		$r = [];
		$r['iid'] = $this->iid;
		$r['Type'] = $this->Type;
		$r['imdb'] = $this->_imdb;
		$r['Caption'] = $this->GetCaption();
		$r['Title'] = $this->_Title;
		$r['Overview'] = $this->_Overview;
		$r['OriginalTitle'] = $this->_OriginalTitle;
		$r['Image'] = $this->GetImage();
		$r['CountryCodes'] = $this->_Countries;
		$r['Countries'] = array_map(function($x){return mmx::FormatCountry($x);},$this->_Countries);
		$r['LanguageCodes'] = $this->Languages;
		$r['Languages'] = array_map(function($x){return mmx::FormatLanguage($x);},$this->_Languages);
		$r['GenreCodes'] = array_keys($this->_Genres);
		$r['Genres'] = array_values($this->_Genres);
		$r['KeywordCodes'] = array_keys($this->_Keywords);
		$r['Keywords'] = array_values($this->_Keywords);
		$r['Runtime'] = mmx::FormatTimeSpan($this->_Runtime);
		$r['RuntimeInMinutes'] = $this->_Runtime === null ? null : $this->_Runtime->GetTotalMinutes();
		$r['Year'] = $this->_Year;
		return $r;
	}






	private static function HealCharacter($s){ return str_replace(['himself','herself'],['Himself','Herself'],$s); }
	protected function LoadFromTMDb() {
		$info = TMDb::GetMovieInfo($this->iid);
		if ($info === null) return false;
		$this->_Timestamp = intval(@$info['timestamp']) ?: null;
		$this->_imdb = @$info['imdb_id'];
		$this->_Title = @$info['title'];
		$this->_OriginalTitle = @$info['original_title']; if ($this->_OriginalTitle === $this->_Title) $this->_OriginalTitle = null;
		$this->_Overview = @$info['overview'];
		$this->_Backdrop = @$info['backdrop_path'];
		$this->_Poster = @$info['poster_path'];
		$this->_Countries = []; $a = @$info['production_countries']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['iso_3166_1']; if ($key !== null) $this->_Countries[] = $key; }
		$this->_Languages = []; $a = @$info['spoken_languages']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['iso_639_1']; if ($key !== null) $this->_Languages[] = $key; }
		$this->_Genres = []; $a = @$info['genres']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['id']; $name = @$aa['name']; if ($key !== null && $name !== null) $this->_Genres[$key] = $name; }
		$this->_Keywords = []; $a = @$info['keywords']['keywords']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['id']; $name = @$aa['name']; if ($key !== null && $name !== null) $this->_Keywords[$key] = $name; }
		$this->_Runtime = ($m = intval(@$info['runtime'])) <= 0 ? null : XTimeSpan::Make(0, 0, $m);
		$this->_Year = strlen($d = @$info['release_date'])<4 ? null : intval(substr($d,0,4));
		$this->LoadCreditsFromTMDb($info);
		$this->LoadPicturesFromTMDb($info);
		return true;
	}
	protected final function LoadCreditsFromTMDb($info) {
		$this->_Credits = [];
		$a = [];
		$c = [];
		$b = @$info['credits']['cast']; if (is_array($b)) foreach ($b as $bb){ $bb['cast']=true; $a[] = $bb; }
		$b = @$info['credits']['crew']; if (is_array($b)) foreach ($b as $bb){ $bb['crew']=true; $a[] = $bb; }
		foreach ($a as $aa){
			$actor_iid = @$aa['id']; if ($actor_iid === null) continue;
			$actor = Actor::Find($actor_iid);
			/** @var $credit Credit */
			$credit = null; foreach ($this->_Credits as $credit) { if ($credit->actor===$actor) break; else $credit = null; }
			if ($credit === null) {
				$credit = new Credit($this,$actor);
				$this->_Credits[] = $credit;
			}
			$credit->IsCast = isset($aa['cast']);
			$credit->IsCrew = isset($aa['crew']);
			$s = @$aa['character']; if ($s !== null && $s !== '') $credit->Characters[] = new Character(self::HealCharacter($s),@$aa['episode_count']);
			$s = @$aa['job']; if ($s !== null && $s !== '') $credit->Jobs[] = new Job($s,@$aa['episode_count']);
		}
	}
	protected final function LoadPicturesFromTMDb($info) {
		$this->_Pictures = [];
		$a = @$info['images']['backdrops']; if (is_array($a)) foreach ($a as $aa){
			$x = new Picture();
			$x->Type = 'backdrop';
			$x->Path = @$aa['file_path'];
			$x->Width = @$aa['width'] ?: null;
			$x->Height = @$aa['height'] ?: null;
			$this->_Pictures[] = $x;
		}
		$a = @$info['images']['posters']; if (is_array($a)) foreach ($a as $aa){
			$x = new Picture();
			$x->Type = 'poster';
			$x->Path = @$aa['file_path'];
			$x->Width = @$aa['width'] ?: null;
			$x->Height = @$aa['height'] ?: null;
			$this->_Pictures[] = $x;
		}
	}


	public function GetDataPath(){ return sprintf('../dat/movie/%03d/%03d/%03d.dat',$this->iid/1000000%1000,$this->iid/1000%1000,$this->iid%1000); }
	public function SaveIntoFile(){
		$this->Load();
		$path = $this->GetDataPath();
		Fs::Ensure(dirname($path));
		if (($f = @fopen($path,'w')) === false) return;
		fprintf($f,'%X',$this->_Timestamp);
		$k = 'n'; $s = $this->_Title;            if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'i'; $s = $this->_imdb;             if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'y'; $s = $this->_Year;             if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'd'; $s = $this->_Runtime;          if ($s!==null) fprintf($f,"\n%s%X",$k,$s->GetTotalMinutes());
		$k = 'b'; $s = $this->_Backdrop;         if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'p'; $s = $this->_Poster;           if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'o'; $s = $this->_OriginalTitle;    if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 't'; $s = $this->_Overview;         if ($s!==null) fprintf($f,"\n%s%s",$k,str_replace(["\\","\n"],["\\\\","\\n"],$s));
		$k = 'c'; $s = $this->_Countries;        if (!empty($s)) fprintf($f,"\n%s%s",$k,implode($s));
		$k = 'l'; $s = $this->_Languages;        if (!empty($s)) fprintf($f,"\n%s%s",$k,implode($s));
		$k = 'g'; $s = $this->_Genres;           if (!empty($s)) fprintf($f,"\n%s%s",$k,implode(',',$s));
		$k = 'k'; $s = $this->_Keywords;         if (!empty($s)) fprintf($f,"\n%s%s",$k,implode(',',$s));
		/** @var $x Credit  */ foreach ($this->_Credits  as $x) fprintf($f,"\n%s",$x->PackForMovie());
		/** @var $x Picture */ foreach ($this->_Pictures as $x) fprintf($f,"\n%s",$x->Pack());
		fclose($f);
	}
	public function LoadFromFile(){
		$path = $this->GetDataPath();
		if (($f = @fopen($path,'r')) === false) return false;
		$this->_Timestamp = intval(fgets($f),16);
		$this->_Credits = [];
		$this->_Countries = [];
		$this->_Languages = [];
		$this->_Genres = [];
		$this->_Keywords = [];
		$this->_Pictures = [];
		while (($line = fgets($f)) !== false) {
			$line = rtrim($line);
			if ($line === '') continue;
			switch($line[0]) {
				case 'n': $s = substr($line,1); $this->_Title         = $s === '' ? null : $s; break;
				case 'i': $s = substr($line,1); $this->_imdb          = $s === '' ? null : $s; break;
				case 'y': $s = substr($line,1); $this->_Year          = $s === '' ? null : intval($s); break;
				case 'd': $s = substr($line,1); $this->_Runtime       = $s === '' ? null : XTimeSpan::Make(0,0,intval($s,16)); break;
				case 'b': $s = substr($line,1); $this->_Backdrop      = $s === '' ? null : $s; break;
				case 'p': $s = substr($line,1); $this->_Poster        = $s === '' ? null : $s; break;
				case 'o': $s = substr($line,1); $this->_OriginalTitle = $s === '' ? null : $s; break;
				case 't': $s = substr($line,1); $this->_Overview      = $s === '' ? null : str_replace(["\\n","\\\\"],["\n","\\",],$s); break;
				case 'c': $s = substr($line,1); $this->_Countries     = $s === '' ? [] : str_split($s,2); break;
				case 'l': $s = substr($line,1); $this->_Languages     = $s === '' ? [] : str_split($s,2); break;
				case 'g': $s = substr($line,1); $this->_Genres        = $s === '' ? [] : explode(',',$s); break;
				case 'k': $s = substr($line,1); $this->_Keywords      = $s === '' ? [] : explode(',',$s); break;
				case 'A':
				case 'B':
				case 'C':
					$x = Credit::UnpackForMovie($this,$line);
					if ($x !== null) $this->_Credits[] = $x;
					break;
				case 'P':
					$x = Picture::Unpack($line);
					if ($x !== null) $this->_Pictures[] = $x;
					break;
			}
		}
		fclose($f);
		return true;
	}



	private static $cache = [];
	/** @return Movie */
	public static function Find($iid) {
		if (!array_key_exists($iid, self::$cache)) self::$cache[$iid] = new self($iid);
		return self::$cache[$iid];
	}

	public static final function FindGeneric($iid,$tmdb_type=null){
		switch($tmdb_type){
			case Chain::TMDb_TYPE: return Chain::Find($iid);
			default: return Movie::Find($iid);
		}
	}

	public static function Search($search_string,$page=1,&$pages=1,&$count=0){
		$r = [];
		$c = TMDb::SearchMovie($search_string,$page);
		$a = @$c['results'];
		if (is_array($a)) foreach ($a as $aa) {
			$iid = @$aa['id'];
			$x = Movie::Find($iid);
			if (!$x->Found) continue;
			$r[] = $x;
		}
		$pages = intval(@$c['total_pages']);
		$count = intval(@$c['total_results']);
		return $r;
	}

}

