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
 * @property-read array   Pictures
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
		$r['Pictures'] = array_map(function(Picture $x){ return $x->Path; },$this->_Pictures);
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
		if (@$info['deathday'] === '') unset($info['deathday']);
		if (isset($info['biography'])) {
			$info['biography'] =
				preg_replace('/\s*From Wikipedia, the free encyclopedia.\s*/','',
				preg_replace('/\s*Description above from the Wikipedia article .*, licensed under CC-BY-SA, full list of contributors on Wikipedia.\s*/',''
				,$info['biography']));
		}

		if (isset($info['combined_credits']['cast'])) { $info['cast'] = []; foreach ($info['combined_credits']['cast'] as $info2) {
			$aa = [];
			if (isset($info2['id'])) $aa[ $info2['media_type']==='tv' ? 'chain' : 'movie' ] = $info2['id'];
			if (isset($info2['release_date'])) $aa['date'] = $info2['release_date'];
			if (isset($info2['first_air_date'])) $aa['date'] = $info2['first_air_date'];
			if (isset($info2['character'])) $aa['character'] = str_replace(['himself','herself'],['Himself','Herself'],$info2['character']);
			if (isset($info2['episode_count']) && intval($info2['episode_count']) > 0) $aa['episodes'] = intval($info2['episode_count']);
			$info['cast'][ $info2['credit_id'] ] = $aa;
		}}
		if (isset($info['combined_credits']['crew'])) { $info['crew'] = []; foreach ($info['combined_credits']['crew'] as $info2) {
			$aa = [];
			if (isset($info2['id'])) $aa[ $info2['media_type']==='tv' ? 'chain' : 'movie' ] = $info2['id'];
			if (isset($info2['release_date'])) $aa['date'] = $info2['release_date'];
			if (isset($info2['first_air_date'])) $aa['date'] = $info2['first_air_date'];
			if (isset($info2['department'])) $aa['department'] = $info2['department'];
			if (isset($info2['job'])) $aa['job'] = $info2['job'];
			if (isset($info2['episode_count']) && intval($info2['episode_count']) > 0) $aa['episodes'] = intval($info2['episode_count']);
			$info['crew'][ $info2['credit_id'] ] = $aa;
		}}
		unset($info['combined_credits']);
		if (isset($info['profile_path'])) { $info['profile'] = $info['profile_path']; unset($info['profile_path']); }
		if (isset($info['images']['profiles'])) { $info['profiles'] = []; foreach ($info['images']['profiles'] as $info2) if(isset($info2['file_path'])) $info['profiles'][$info2['file_path']] = [@$info2['width'],@$info2['height']]; }
		unset($info['images']);
		ksort($info);
		return $info;
	}
	protected function LoadInfo($info){
		if ($info === null) return false;
		$this->_Timestamp = intval(@$info['timestamp']) ?: null;
		$this->_imdb = @$info['imdb_id'];
		$this->_Name = @$info['name'];
		$this->_Biography = @$info['biography'];
		$this->_Image = @$info['profile'];
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
		$b = @$info['cast']; if (is_array($b)) foreach ($b as $bb){ $a[] = $bb; $c[] = @$bb['date'] ?: ''; }
		$b = @$info['crew']; if (is_array($b)) foreach ($b as $bb){ $a[] = $bb; $c[] = @$bb['date'] ?: ''; }
		array_multisort($c, SORT_DESC, $a);
		foreach ($a as $aa){
			$iid = @$aa['movie'];
			if ($iid !== null) {
				$movie = Movie::Find($iid);
			}
			else {
				$iid = @$aa['chain'];
				if ($iid !== null) {
					$movie = Chain::Find($iid);
				}
				else {
					continue;
				}
			}
			/** @var $credit Credit */
			$credit = null; foreach ($this->_Credits as $credit) { if ($credit->movie===$movie) break; else $credit = null; }
			if ($credit === null) {
				$credit = new Credit($movie,$this);
				$this->_Credits[] = $credit;
			}
			$s = @$aa['character']; if ($s !== null && $s !== '') $credit->Cast[] = new Cast($s,@$aa['episodes']);
			$s = @$aa['job']; if ($s !== null && $s !== '') { $credit->Crew[] = new Crew($s,@$aa['episodes']);  if ($s==='Director') $credit->IsDirector = true; }
		}
	}
	protected final function LoadPictures($info) {
		$this->_Pictures = [];
		$a = @$info['profiles']; if (is_array($a)) foreach ($a as $k=>$aa){
			$x = new Picture();
			$x->Type = 'profile';
			$x->Path = $k;
			$x->Width = @$aa[0] ?: null;
			$x->Height = @$aa[1] ?: null;
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

