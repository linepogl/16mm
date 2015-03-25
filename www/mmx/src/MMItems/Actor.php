<?php

/**
 * @property-read string  imdb
 * @property-read string  Name
 * @property-read string  Image
 * @property-read string  PlaceOfBirth
 * @property-read int     YearOfBirth
 * @property-read int     YearOfDeath
 * @property-read string  Biography
 * @property-read array   Credits
 */
class Actor extends MMItem {

	public $iid;
	/** @var string  */ protected $_imdb;
	/** @var string  */ protected $_Name;
	/** @var string  */ protected $_Image;
	/** @var string  */ protected $_PlaceOfBirth;
	/** @var int     */ protected $_YearOfBirth;
	/** @var int     */ protected $_YearOfDeath;
	/** @var string  */ protected $_Biography;
	/** @var array   */ protected $_Credits = [];
	/** @var array   */ protected $_Pictures = [];

	private function __construct($iid) {
		$this->iid = $iid;
	}

	public function GetKey(){ return 'A'.$this->iid; }
	public function GetCaption() { $this->Load(); return $this->Name; }
	public function GetText() { $this->Load(); return $this->_Biography; }
	public function GetImage(){ $this->Load(); return $this->_Image === null ? null : TMDb::GetImageSrc($this->_Image,TMDb::PROFILE_W185); }


	public function ToArray() {
		$this->Load();
		$r = [];
		$r['iid'] = $this->iid;
		$r['Caption'] = $this->GetCaption();
		$r['imdb'] = $this->_imdb;
		$r['Name'] = $this->_Name;
		$r['Image'] = $this->GetImage();
		$r['PlaceOfBirth'] = $this->_PlaceOfBirth;
		$r['YearOfBirth'] = $this->_YearOfBirth;
		$r['YearOfDeath'] = $this->_YearOfDeath;
		$r['Biography'] = $this->_Biography;
		return $r;
	}

	private static function HealCharacter($s){ return str_replace(['himself','herself'],['Himself','Herself'],$s); }
	private static function HealBiography($s){ return empty($s) ? null :
		preg_replace('/\s*From Wikipedia, the free encyclopedia.\s*/','',
		preg_replace('/\s*Description above from the Wikipedia article .*, licensed under CC-BY-SA, full list of contributors on Wikipedia.\s*/','',
		$s));
	}
	protected function LoadFromTMDb() {
		$info = TMDb::GetActorInfo($this->iid);
		if ($info === null) return false;
		$this->_Timestamp = intval(@$info['timestamp']) ?: null;
		$this->_imdb = @$info['imdb_id'];
		$this->_Name = @$info['name'];
		$this->_Biography = self::HealBiography(@$info['biography']);
		$this->_Image = @$info['profile_path'];
		$this->_YearOfBirth = strlen($d = @$info['birthday'])<4 ? null : intval(substr($d,0,4));
		$this->_YearOfDeath = strlen($d = @$info['deathday'])<4 ? null : intval(substr($d,0,4));
		$this->_PlaceOfBirth = @$info['place_of_birth'];
		$this->LoadCreditsFromTMDb($info);
		$this->LoadPicturesFromTMDb($info);
		return true;
	}
	protected final function LoadCreditsFromTMDb($info) {
		$this->_Credits = [];
		$a = [];
		$c = [];
		$b = @$info['combined_credits']['cast']; if (is_array($b)) foreach ($b as $bb){ $bb['cast']=true; $a[] = $bb; $c[] = (@$bb['release_date'] ?: @$bb['first_air_date']) ?: ''; }
		$b = @$info['combined_credits']['crew']; if (is_array($b)) foreach ($b as $bb){ $bb['crew']=true; $a[] = $bb; $c[] = (@$bb['release_date'] ?: @$bb['first_air_date']) ?: ''; }
		array_multisort($c, SORT_DESC, $a);
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
			$credit->IsCast = isset($aa['cast']);
			$credit->IsCrew = isset($aa['crew']);
			$s = @$aa['character']; if ($s !== null && $s !== '') $credit->Characters[] = new Character(self::HealCharacter($s),@$aa['episode_count']);
			$s = @$aa['job']; if ($s !== null && $s !== '') $credit->Jobs[] = new Job($s,@$aa['episode_count']);
		}
	}
	protected final function LoadPicturesFromTMDb($info) {
		$this->_Pictures = [];
		$a = @$info['images']['profiles']; if (is_array($a)) foreach ($a as $aa){
			$x = new Picture();
			$x->Type = 'profile';
			$x->Path = @$aa['file_path'];
			$x->Width = @$aa['width'] ?: null;
			$x->Height = @$aa['height'] ?: null;
			$this->_Pictures[] = $x;
		}
	}

	public function GetDataPath(){ return sprintf('../dat/actor/%03d/%03d/%03d.dat',$this->iid/1000000%1000,$this->iid/1000%1000,$this->iid%1000); }
	public function SaveIntoFile(){
		$this->Load();
		$path = $this->GetDataPath();
		Fs::Ensure(dirname($path));
		if (($f = @fopen($path,'w')) === false) return;
		fprintf($f,'%X',$this->_Timestamp);
		$k = 'n'; $s = $this->_Name;             if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'i'; $s = $this->_imdb;             if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'j'; $s = $this->_Image;            if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'p'; $s = $this->_PlaceOfBirth;     if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'b'; $s = $this->_YearOfBirth;      if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'd'; $s = $this->_YearOfDeath;      if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 't'; $s = $this->_Biography;        if ($s!==null) fprintf($f,"\n%s%s",$k,str_replace(["\\","\n"],["\\\\","\\n"],$s));
		/** @var $x Credit  */ foreach ($this->_Credits  as $x) fprintf($f,"\n%s",$x->PackForActor());
		/** @var $x Picture */ foreach ($this->_Pictures as $x) fprintf($f,"\n%s",$x->Pack());
		fclose($f);
	}
	public function LoadFromFile(){
		$path = $this->GetDataPath();
		if (($f = @fopen($path,'r')) === false) return false;
		$this->_Timestamp = intval(fgets($f),16);
		$this->_Credits = [];
		$this->_Pictures = [];
		while (($line = fgets($f)) !== false) {
			$line = rtrim($line);
			if ($line === '') continue;
			switch($line[0]) {
				case 'n': $s = substr($line,1); $this->_Name         = $s === '' ? null : $s; break;
				case 'i': $s = substr($line,1); $this->_imdb         = $s === '' ? null : $s; break;
				case 'j': $s = substr($line,1); $this->_Image        = $s === '' ? null : $s; break;
				case 'p': $s = substr($line,1); $this->_PlaceOfBirth = $s === '' ? null : $s; break;
				case 'b': $s = substr($line,1); $this->_YearOfBirth  = $s === '' ? null : intval($s); break;
				case 'd': $s = substr($line,1); $this->_YearOfDeath  = $s === '' ? null : intval($s); break;
				case 't': $s = substr($line,1); $this->_Biography    = $s === '' ? null : str_replace(["\\n","\\\\"],["\n","\\",],$s); break;
				case 'A':
				case 'B':
				case 'C':
				case 'D':
				case 'E':
				case 'F':
					$x = Credit::UnpackForActor($this,$line);
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
			if (!$x->Found) continue;
			$r[] = $x;
		}
		return $r;
	}

}

