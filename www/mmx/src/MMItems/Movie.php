<?php

/**
 * @property-read string     Title
 * @property-read string     Overview
 * @property-read string     OriginalTitle
 * @property-read string     Image
 * @property-read array      Countries
 * @property-read array      Languages
 * @property-read array      Genres
 * @property-read array      Keywords
 * @property-read int        Year
 * @property-read XTimeSpan  Runtime
 * @property-read array      Credits
 *
 */

class Movie extends MMItem {

	public $iid;
	public $Type;
	/** @var string     */ protected $_Title;
	/** @var string     */ protected $_Overview;
	/** @var string     */ protected $_OriginalTitle;
	/** @var string     */ protected $_Image;
	/** @var int        */ protected $_Year;
	/** @var XTimeSpan  */ protected $_Runtime;
	/** @var array      */ protected $_Countries = [];
	/** @var array      */ protected $_Languages = [];
	/** @var array      */ protected $_Genres = [];
	/** @var array      */ protected $_Keywords = [];
	/** @var array      */ protected $_Credits = [];

	const TMDb_TYPE = 'movie';
	private function __construct($iid) {
		$this->iid = $iid;
		$this->Type = self::TMDb_TYPE;
	}



	public function GetKey(){ return 'M'.$this->iid; }
	public function GetCaption() { $this->Load(); return $this->_Title . ($this->_Year === null ? '' : ' ('.$this->_Year.')'); }
	public function GetImage(){ $this->Load(); return $this->_Image === null ? null : TMDb::GetImageSrc($this->Image,TMDb::BACKDROP_W300); }
	public function GetLanguagesTranslated() { $this->Load(); return implode(', ',array_map(function($x){ return mmx::FormatLanguage($x); }, $this->Languages)); }
	public function GetCountriesTranslated() { $this->Load(); return implode(', ',array_map(function($x){ return mmx::FormatCountry($x); }, $this->Countries)); }


	public function ToArray() {
		$this->Load();
		$r = [];
		$r['iid'] = $this->iid;
		$r['Type'] = $this->Type;
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






	protected function OnLoad() {
		$info = TMDb::GetMovieInfo($this->iid);
		if ($info === null) return false;
		$this->_Title = @$info['title'];
		$this->_OriginalTitle = @$info['original_title']; if ($this->_OriginalTitle === $this->_Title) $this->_OriginalTitle = null;
		$this->_Overview = @$info['overview'];
		$this->_Image = @$info['backdrop_path'];
		$this->_Countries = []; $a = @$info['production_countries']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['iso_3166_1']; if ($key !== null) $this->_Countries[] = $key; }
		$this->_Languages = []; $a = @$info['spoken_languages']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['iso_639_1']; if ($key !== null) $this->_Languages[] = $key; }
		$this->_Genres = []; $a = @$info['genres']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['id']; $name = @$aa['name']; if ($key !== null && $name !== null) $this->_Genres[$key] = $name; }
		$this->_Keywords = []; $a = @$info['keywords']['keywords']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['id']; $name = @$aa['name']; if ($key !== null && $name !== null) $this->_Keywords[$key] = $name; }
		$this->_Runtime = ($m = intval(@$info['runtime'])) <= 0 ? null : XTimeSpan::Make(0, 0, $m);
		$this->_Year = strlen($d = @$info['release_date'])<4 ? null : intval(substr($d,0,4));
		$this->LoadCredits($info);
		return true;
	}
	protected final function LoadCredits($info) {
		$this->_Credits = [];
		$a = [];
		$b = @$info['credits']['cast']; if (is_array($b)) $a = array_merge($a,$b);
		$b = @$info['credits']['crew']; if (is_array($b)) $a = array_merge($a,$b);
		foreach ($a as $aa){
			$actor_iid = @$aa['id']; if ($actor_iid === null) continue;
			$actor = Actor::Find($actor_iid);
			/** @var $credit Credit */
			$credit = null; foreach ($this->_Credits as $credit) { if ($credit->actor===$actor) break; else $credit = null; }
			if ($credit === null) {
				$credit = new Credit($this,$actor);
				$this->_Credits[] = $credit;
			}
			$s = @$aa['character']; if ($s !== null && $s !== '') $credit->Characters[] = str_replace(['himself','herself'],['Himself','Herself'],$s);
			$s = @$aa['job']; if ($s !== null && $s !== '') $credit->Jobs[] = $s;
		}
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
			if ($x->NotFound()) continue;
			$r[] = $x;
		}
		$pages = intval(@$c['total_pages']);
		$count = intval(@$c['total_results']);
		return $r;
	}

}

