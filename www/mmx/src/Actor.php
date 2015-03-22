<?php


class Actor {

	public $iid;
	public $imdb;
	public $Name;
	public $Image;
	public $Countries = [];
	public $YearOfBirth;
	public $YearOfDeath;
	public $Biography;

	public function GetKey(){ return 'A'.$this->iid; }
	public function GetCaption() { return $this->Name; }
	public function GetText() { return $this->Biography; }
	public function GetImage(){ return $this->Image === null ? null : TMDb::GetImageSrc($this->Image,TMDb::PROFILE_W185); }
	public function GetCountriesTranslated() { return implode(', ',array_map(function($x){ return mmx::FormatCountry($x); }, $this->Countries)); }

	public final  function ToJson() { return json_encode($this->ToArray()); }
	public function ToArray() {
		$r = [];
		$r['iid'] = $this->iid;
		$r['imdb'] = $this->imdb;
		$r['Caption'] = $this->GetCaption();
		$r['Name'] = $this->Name;
		$r['Biography'] = $this->Biography;
		$r['Image'] = $this->GetImage();
		$r['CountryCodes'] = $this->Countries;
		$r['Countries'] = array_map(function($x){return mmx::FormatCountry($x);},$this->Countries);
		$r['YearOfBirth'] = $this->YearOfBirth;
		$r['YearOfDeath'] = $this->YearOfDeath;
		return $r;
	}


	public static function Find($iid) {
		$info = TMDb::GetActorInfo($iid);
		if ($info === null) return null;
		$r = new self();
		$r->iid = $iid;
		$r->imdb = @$info['imdb_id'];
		$r->Name = @$info['name'];
		$r->Biography = @$info['biography'];
		$r->Image = @$info['profile_path'];
		$date = @$info['birthday']; if ($date !== null && strlen($date)>=4) $r->YearOfBirth = intval(substr($date,0,4));
		$date = @$info['deathday']; if ($date !== null && strlen($date)>=4) $r->YearOfDeath = intval(substr($date,0,4));
		return $r;
	}

	public static function Search($search_string,$page=1){
		$r = [];
		$c = TMDb::SearchActor($search_string,$page);
		$a = @$c['results'];
		if (is_array($a)) foreach ($a as $aa) {
			$iid = @$aa['id'];
			$x = Actor::Find($iid);
			if ($x === null) continue;
			$r[] = $x;
		}
		return $r;
	}

}

