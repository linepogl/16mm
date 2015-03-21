<?php


abstract class Title {
	const MOVIE = 'movie';
	const CHAIN = 'tv';

	public $id;
	public $Type;
	public $Title;
	public $Description;
	public $OriginalTitle;
	public $Image;
	public $Countries = [];
	public $Languages = [];
	public $Genres = [];

	public function GetLanguagesTranslated() { return implode(', ',array_map(function($x){ return mmx::FormatLanguage($x); }, $this->Languages)); }
	public function GetCountriesTranslated() { return implode(', ',array_map(function($x){ return mmx::FormatCountry($x); }, $this->Countries)); }

	public function __construct(){}
	public abstract function GetTitle();

	public function GetImage(){
		return $this->Image === null ? null : TMDb::GetImageSrc($this->Image,TMDb::BACKDROP_W300);
	}

	public final  function ToJson() { return json_encode($this->ToArray()); }
	public function ToArray() {
		$r = [];
		$r['id'] = $this->id;
		$r['Type'] = $this->Type;
		$r['Title'] = $this->GetTitle();
		$r['Description'] = $this->Description;
		$r['OriginalTitle'] = $this->OriginalTitle;
		$r['Image'] = $this->GetImage();
		$r['CountryCodes'] = $this->Countries;
		$r['Countries'] = array_map(function($x){return mmx::FormatCountry($x);},$this->Countries);
		$r['LanguageCodes'] = $this->Languages;
		$r['Languages'] = array_map(function($x){return mmx::FormatLanguage($x);},$this->Languages);
		$r['GenreCodes'] = array_keys($this->Genres);
		$r['Genres'] = array_values($this->Genres);
		return $r;
	}



}
