<?php

/**
 * @property-read string  imdb
 * @property-read string  Name
 * @property-read string  Image
 * @property-read int     YearOfBirth
 * @property-read int     YearOfDeath
 * @property-read string  Biography
 * @property-read array   Countries
 * @property-read array   Credits
 */
class Actor extends MMItem {

	public $iid;
	/** @var string  */ public $_imdb;
	/** @var string  */ public $_Name;
	/** @var string  */ public $_Image;
	/** @var int     */ public $_YearOfBirth;
	/** @var int     */ public $_YearOfDeath;
	/** @var string  */ public $_Biography;
	/** @var array   */ public $_Countries = [];
	/** @var array   */ public $_Credits = [];

	private function __construct($iid) {
		$this->iid = $iid;
	}

	public function GetKey(){ return 'A'.$this->iid; }
	public function GetCaption() { $this->Load(); return $this->Name; }
	public function GetText() { $this->Load(); return $this->_Biography; }
	public function GetImage(){ $this->Load(); return $this->_Image === null ? null : TMDb::GetImageSrc($this->_Image,TMDb::PROFILE_W185); }
	public function GetCountriesTranslated() { $this->Load(); return implode(', ',array_map(function($x){ return mmx::FormatCountry($x); }, $this->_Countries)); }

	public function ToArray() {
		$this->Load();
		$r = [];
		$r['iid'] = $this->iid;
		$r['imdb'] = $this->_imdb;
		$r['Caption'] = $this->GetCaption();
		$r['Name'] = $this->_Name;
		$r['Biography'] = $this->_Biography;
		$r['Image'] = $this->GetImage();
		$r['CountryCodes'] = $this->_Countries;
		$r['Countries'] = array_map(function($x){return mmx::FormatCountry($x);},$this->_Countries);
		$r['YearOfBirth'] = $this->_YearOfBirth;
		$r['YearOfDeath'] = $this->_YearOfDeath;
		return $r;
	}

	protected function OnLoad() {
		$info = TMDb::GetActorInfo($this->iid);
		if ($info === null) return false;
		$this->_imdb = @$info['imdb_id'];
		$this->_Name = @$info['name'];
		$this->_Biography = @$info['biography'];
		$this->_Image = @$info['profile_path'];
		$this->_YearOfBirth = strlen($d = @$info['birthday'])<4 ? null : intval(substr($d,0,4));
		$this->_YearOfDeath = strlen($d = @$info['deathday'])<4 ? null : intval(substr($d,0,4));
		$this->_Countries = [];
		$this->LoadCredits($info);
		return true;
	}
	protected final function LoadCredits($info) {
		$this->_Credits = [];
		$a = [];
		$b = @$info['combined_credits']['cast']; if (is_array($b)) $a = array_merge($a,$b);
		$b = @$info['combined_credits']['crew']; if (is_array($b)) $a = array_merge($a,$b);
		foreach ($a as $aa){
			$movie_type = @$aa['media_type']; if ($movie_type === null) continue;
			$movie_iid = @$aa['id']; if ($movie_iid === null) continue;
			$movie = Movie::FindGeneric($movie_iid,$movie_type);

			/** @var $credit Credit */
			$credit = null; foreach ($this->_Credits as $credit) { if ($credit->movie===$movie) break; else $credit = null; }
			if ($credit === null) {
				$credit = new Credit($movie,$this);
				$this->_Credits[] = $credit;
			}
			$s = @$aa['character']; if ($s !== null && $s !== '') $credit->Characters[] = str_replace(['himself','herself'],['Himself','Herself'],$s);
			$s = @$aa['job']; if ($s !== null && $s !== '') $credit->Jobs[] = $s;
		}
	}

	public function GetCreditsSorted() {
		$this->Load();
		foreach ($this->Credits as $credit) $credit->movie->Load();
		usort( $this->_Credits , function(Credit $c1,Credit $c2){ return $c2->movie->Year - $c1->movie->Year; } );
		return $this->_Credits;
	}

	private static $cache = [];
	/** @return Actor */
	public static function Find($iid) {
		if (!array_key_exists($iid, self::$cache)) self::$cache[$iid] = new self($iid);
		return self::$cache[$iid];
	}

	public static function Search($search_string,$page=1){
		$r = [];
		$c = TMDb::SearchActor($search_string,$page);
		$a = @$c['results'];
		if (is_array($a)) foreach ($a as $aa) {
			$iid = @$aa['id'];
			$x = Actor::Find($iid);
			if ($x->NotFound()) continue;
			$r[] = $x;
		}
		return $r;
	}

}

