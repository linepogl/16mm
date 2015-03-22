<?php


class Movie {

	public $iid;
	public $Type;
	public $Title;
	public $Overview;
	public $OriginalTitle;
	public $Image;
	public $Countries = [];
	public $Languages = [];
	public $Genres = [];
	public $Year;
	/** @var XTimeSpan */ public $Runtime;

	const TMDb_TYPE = 'movie';
	public final function GetTMDbType(){ return static::TMDb_TYPE; }

	public function GetKey(){ return 'M'.$this->iid; }
	public function GetCaption() { return $this->Title . ($this->Year === null ? '' : ' ('.$this->Year.')'); }
	public function GetImage(){ return $this->Image === null ? null : TMDb::GetImageSrc($this->Image,TMDb::BACKDROP_W300); }
	public function GetLanguagesTranslated() { return implode(', ',array_map(function($x){ return mmx::FormatLanguage($x); }, $this->Languages)); }
	public function GetCountriesTranslated() { return implode(', ',array_map(function($x){ return mmx::FormatCountry($x); }, $this->Countries)); }

	public static function FindGeneric($iid,$tmdb_type=null){
		switch($tmdb_type){
			case Chain::TMDb_TYPE: return Chain::Find($iid);
			default: return Movie::Find($iid);
		}
	}

	public final  function ToJson() { return json_encode($this->ToArray()); }
	public function ToArray() {
		$r = [];
		$r['iid'] = $this->iid;
		$r['Type'] = $this->GetTMDbType();
		$r['Caption'] = $this->GetCaption();
		$r['Title'] = $this->Title;
		$r['Overview'] = $this->Overview;
		$r['OriginalTitle'] = $this->OriginalTitle;
		$r['Image'] = $this->GetImage();
		$r['CountryCodes'] = $this->Countries;
		$r['Countries'] = array_map(function($x){return mmx::FormatCountry($x);},$this->Countries);
		$r['LanguageCodes'] = $this->Languages;
		$r['Languages'] = array_map(function($x){return mmx::FormatLanguage($x);},$this->Languages);
		$r['GenreCodes'] = array_keys($this->Genres);
		$r['Genres'] = array_values($this->Genres);
		$r['Runtime'] = mmx::FormatTimeSpan($this->Runtime);
		$r['RuntimeInMinutes'] = $this->Runtime === null ? null : $this->Runtime->GetTotalMinutes();
		$r['Year'] = $this->Year;
		return $r;
	}




	public static function Search($search_string,$page=1){
		$r = [];
		$c = TMDb::SearchMovie($search_string,$page);
		$a = @$c['results'];
		if (is_array($a)) foreach ($a as $aa) {
			$iid = @$aa['id'];
			$x = Movie::Find($iid);
			if ($x === null) continue;
			$r[] = $x;
		}
		return $r;
	}






	public static function Find($iid) {
		$info = TMDb::GetMovieInfo($iid);
		if ($info === null) return null;
		$r = new self();
		$r->iid = $iid;
		$r->Title = @$info['title'];
		$r->OriginalTitle = @$info['original_title'];
		if ($r->OriginalTitle === $r->Title) $r->OriginalTitle = null;
		$r->Overview = @$info['overview'];
		$r->Image = @$info['backdrop_path'];
		$a = @$info['production_countries'];
		if (is_array($a)) foreach ($a as $aa) {
			$key = @$aa['iso_3166_1'];
			if ($key !== null) $r->Countries[] = $key;
		}
		$a = @$info['spoken_languages'];
		if (is_array($a)) foreach ($a as $aa) {
			$key = @$aa['iso_639_1'];
			if ($key !== null) $r->Languages[] = $key;
		}
		$a = @$info['genres'];
		if (is_array($a)) foreach ($a as $aa) {
			$key = @$aa['id'];
			$name = @$aa['name'];
			if ($key !== null && $name !== null) $r->Genres[$key] = $name;
		}
		$minutes = intval(@$info['runtime']);
		if ($minutes > 0) {
			$r->Runtime = XTimeSpan::Make(0, 0, $minutes);
		}
		$date = @$info['release_date'];
		if ($date !== null && strlen($date)>=4) {
			$r->Year = intval(substr($date,0,4));
		}
		return $r;
	}

}

