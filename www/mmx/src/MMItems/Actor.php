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




	protected function GetCouchUrl(){ return "/actor/$this->iid"; }


	/** @return array */
	protected function GetTMDbInfo() {
		$info = TMDb::GetActorInfo($this->iid);
		if ($info === null) return null;
		unset($info['also_known_as']);
		unset($info['homepage']);
		unset($info['popularity']);
	   if (isset($info['biography'])) {
			$info['biography'] =
				preg_replace('/\s*From Wikipedia, the free encyclopedia.\s*/','',
				preg_replace('/\s*Description above from the Wikipedia article .*, licensed under CC-BY-SA, full list of contributors on Wikipedia.\s*/',''
				,$info['biography']));
		}
		Arr::UnsetPath($info,['combined_credits','cast',null,'original_title']);
		Arr::UnsetPath($info,['combined_credits','cast',null,'poster_path']);
		Arr::UnsetPath($info,['combined_credits','cast',null,'title']);
		Arr::UnsetPath($info,['combined_credits','cast',null,'name']);
		Arr::UnsetPath($info,['combined_credits','cast',null,'original_name']);
		Arr::UnsetPath($info,['images',null,null,'aspect_ratio']);
		Arr::UnsetPath($info,['images',null,null,'iso_639_1']);
		Arr::UnsetPath($info,['images',null,null,'vote_average']);
		Arr::UnsetPath($info,['images',null,null,'vote_count']);
		Arr::UnsetPath($info,['images',null,null,'id']);
		return $info;
	}
	protected function LoadInfo($info){
		if ($info === null) return false;
		$this->_Timestamp = intval(@$info['timestamp']) ?: null;
		$this->_imdb = @$info['imdb_id'];
		$this->_Name = @$info['name'];
		$this->_Biography = @$info['biography'];
		$this->_Image = @$info['profile_path'];
		$this->_YearOfBirth = strlen($d = @$info['birthday'])<4 ? null : intval(substr($d,0,4));
		$this->_YearOfDeath = strlen($d = @$info['deathday'])<4 ? null : intval(substr($d,0,4));
		$this->_PlaceOfBirth = @$info['place_of_birth'];
		$this->LoadCredits($info);
		$this->LoadPictures($info);
		return true;
	}
	protected final function LoadCredits($info) {
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
			$s = @$aa['character']; if ($s !== null && $s !== '') $credit->Cast[] = new Cast(str_replace(['himself','herself'],['Himself','Herself'],$s),@$aa['episode_count']);
			$s = @$aa['job']; if ($s !== null && $s !== '') $credit->Crew[] = new Crew($s,@$aa['episode_count']);
		}
	}
	protected final function LoadPictures($info) {
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

