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





	protected function GetCouchUrl(){ return "/movie/$this->iid"; }

	protected function GetTMDbInfo() {
		$info = TMDb::GetMovieInfo($this->iid);
		if ($info === null) return null;
		unset($info['budget']);
		unset($info['homepage']);
		unset($info['original_language']);
		unset($info['popularity']);
		unset($info['production_companies']);
		unset($info['revenue']);
		unset($info['video']);
		unset($info['vote_average']);
		unset($info['vote_count']);
		if (isset($info['release_date'])) { $info['date'] = $info['release_date']; unset($info['release_date']); }
		if (isset($info['spoken_languages'])) {
			$a = []; foreach ($info['spoken_languages'] as $aa) if (isset($aa['iso_639_1'])) $a[] = $aa['iso_639_1'];
			$info['languages'] = $a;
			unset($info['spoken_languages']);
		}
		if (isset($info['production_countries'])) {
			$a = []; foreach ($info['production_countries'] as $aa) if (isset($aa['iso_3166_1'])) $a[] = $aa['iso_3166_1'];
			$info['countries'] = $a;
			unset($info['production_countries']);
		}
		if (isset($info['genres'])) {
			$a = []; foreach ($info['genres'] as $aa) if (isset($aa['id'])&&isset($aa['name'])) $a[$aa['id']] = $aa['name'];
			$info['genres'] = $a;
		}
		if (isset($info['original_title'])&&isset($info['title'])&&$info['original_title']===$info['title']) unset($info['original_title']);
		if (isset($info['credits']['cast'])) { $info['cast'] = []; foreach ($info['credits']['cast'] as $info2) {
			$aa = [];
			if (isset($info2['id'])) $aa['actor'] = $info2['id'];
			if (isset($info2['character'])) $aa['character'] = str_replace(['himself','herself'],['Himself','Herself'],$info2['character']);
			$info['cast'][ $info2['credit_id'] ] = $aa;
		}}
		if (isset($info['credits']['crew'])) { $info['crew'] = []; foreach ($info['credits']['crew'] as $info2) {
			$aa = [];
			if (isset($info2['id'])) $aa['actor'] = $info2['id'];
			if (isset($info2['department'])) $aa['department'] = $info2['department'];
			if (isset($info2['job'])) $aa['job'] = $info2['job'];
			$info['crew'][ $info2['credit_id'] ] = $aa;
		}}
		unset($info['credits']);
		if (isset($info['keywords']['keywords'])) {
			$a = []; foreach ($info['keywords']['keywords'] as $aa) if (isset($aa['id'])&&isset($aa['name'])) $a[$aa['id']] = $aa['name'];
			$info['keywords'] = $a;
		}
		if (isset($info['backdrop_path'])) { $info['backdrop'] = $info['backdrop_path']; unset($info['backdrop_path']); }
		if (isset($info['images']['backdrops'])) { $info['backdrops'] = []; foreach ($info['images']['backdrops'] as $info2) if(isset($info2['file_path'])) $info['backdrops'][$info2['file_path']] = [@$info2['width'],@$info2['height']]; }
		if (isset($info['poster_path'])) { $info['poster'] = $info['poster_path']; unset($info['poster_path']); }
		if (isset($info['images']['posters'])) { $info['posters'] = []; foreach ($info['images']['posters'] as $info2) if(isset($info2['file_path'])) $info['posters'][$info2['file_path']] = [@$info2['width'],@$info2['height']]; }
		unset($info['images']);
		ksort($info);
		return $info;
	}
	protected function LoadInfo($info){
		if ($info === null) return false;
		$this->_Timestamp = intval(@$info['timestamp']) ?: null;
		$this->_imdb = @$info['imdb_id'];
		$this->_Title = @$info['title'];
		$this->_OriginalTitle = @$info['original_title'];
		$this->_Overview = @$info['overview'];
		$this->_Backdrop = @$info['backdrop'];
		$this->_Poster = @$info['poster'];
		$this->_Countries = @$info['countries'] ?: [];
		$this->_Languages = @$info['languages'] ?: [];
		$this->_Genres = @$info['genres'] ?: [];
		$this->_Keywords = @$info['keywords'] ?: [];
		$this->_Runtime = ($m = intval(@$info['runtime'])) <= 0 ? null : XTimeSpan::Make(0, 0, $m);
		$this->_Year = strlen($d = @$info['date'])<4 ? null : intval(substr($d,0,4));
		$this->LoadCredits($info);
		$this->LoadPictures($info);
		return true;
	}
	protected final function LoadCredits($info) {
		$this->_Credits = [];
		$a = [];
		$a = $a + @$info['cast'] ?: [];
		$a = $a + @$info['crew'] ?: [];
		foreach ($a as $aa){
			$actor_iid = @$aa['actor']; if ($actor_iid === null) continue;
			$actor = Actor::Find($actor_iid);
			/** @var $credit Credit */
			$credit = null; foreach ($this->_Credits as $credit) { if ($credit->actor===$actor) break; else $credit = null; }
			if ($credit === null) {
				$credit = new Credit($this,$actor);
				$this->_Credits[] = $credit;
			}
			$s = @$aa['character']; if ($s !== null && $s !== '') $credit->Cast[] = new Cast(str_replace(['himself','herself'],['Himself','Herself'],$s),@$aa['episodes']);
			$s = @$aa['job']; if ($s !== null && $s !== '') $credit->Crew[] = new Crew($s,@$aa['episodes']);
		}
	}
	protected final function LoadPictures($info) {
		$this->_Pictures = [];
		$a = @$info['backdrops']; if (is_array($a)) foreach ($a as $k=>$aa){
			$x = new Picture();
			$x->Type = 'backdrop';
			$x->Path = $k;
			$x->Width = @$aa[0] ?: null;
			$x->Height = @$aa[1] ?: null;
			$this->_Pictures[] = $x;
		}
		$a = @$info['posters']; if (is_array($a)) foreach ($a as $k=>$aa){
			$x = new Picture();
			$x->Type = 'poster';
			$x->Path = $k;
			$x->Width = @$aa[0] ?: null;
			$x->Height = @$aa[1] ?: null;
			$this->_Pictures[] = $x;
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
			if (!$x->Found) continue;
			$r[] = $x;
		}
		$pages = intval(@$c['total_pages']);
		$count = intval(@$c['total_results']);
		return $r;
	}

}

