<?php


class _ActorInfo{
	public $time;
	public $Found = false;
	public $imdb;
	public $Name;
	public $Image;
	public $PlaceOfBirth;
	public $YearOfBirth;
	public $YearOfDeath;
	public $Biography;
	public $Credits = [];
	public function __construct($time){ $this->time=$time; }
}

/**
 * @property-read boolean    Found
 * @property-read XDateTIme  time
 * @property-read string     imdb
 * @property-read string     Name
 * @property-read string     Image
 * @property-read string     PlaceOfBirth
 * @property-read int        YearOfBirth
 * @property-read int        YearOfDeath
 * @property-read string     Biography
 * @property-read array      Credits
 */
class Actor {

	public $iid;
	/** @var _ActorInfo */ private $_info;
	private function __construct($iid){ $this->iid = $iid; }


	public function __get($key) {
		if ($this->_info === null) throw new Exception('Actor has not been loaded.');
		return $this->_info->$key;
	}
	private function set_info($time,$info) {
		$this->_info = new _ActorInfo($time);
		$this->_info->Found = !empty($info);
		$this->_info->Name = @$info[0] ?: null;
		$this->_info->imdb = @$info[1] ?: null;
		$this->_info->YearOfBirth = strlen($d=@$info[2])<4 ? null : intval(substr($d,0,4));
		$this->_info->YearOfDeath = strlen($d=@$info[3])<4 ? null : intval(substr($d,0,4));
		$this->_info->PlaceOfBirth = @$info[4] ?: null;
		$this->_info->Image = @$info[5] ?: null;
		$this->_info->Biography = @$info[6] ?: null;
	}

	public function GetCaption() { return $this->Name; }
	public function GetImage(){ return $this->Image === null ? null : TMDb::GetImageSrc($this->Image,TMDb::PROFILE_W185); }
	public function ToJson() { return json_encode($this->ToArray(),JSON_UNESCAPED_UNICODE); }
	public function ToArray() {
		$r = [];
		$r['iid'] = $this->iid;
		$r['Caption'] = $this->GetCaption();
		$r['imdb'] = $this->imdb;
		$r['Name'] = $this->Name;
		$r['Image'] = $this->GetImage();
		$r['PlaceOfBirth'] = $this->PlaceOfBirth;
		$r['YearOfBirth'] = $this->YearOfBirth;
		$r['YearOfDeath'] = $this->YearOfDeath;
		$r['Biography'] = $this->Biography;
		return $r;
	}






	private static $cache = [];
	/**
	 * @param $iid_or_actor int|self
	 * @return self
	 */
	public static function Pick($iid_or_actor) {
		$iid = $iid_or_actor instanceof self ? $iid_or_actor->iid : $iid_or_actor;
		if (!isset(self::$cache[$iid])) self::$cache[$iid] = new self($iid);
		return self::$cache[$iid];
	}

	/**
	 * @param $iids_or_actors array
	 * @return array
	 */
	public static function Mass($iids_or_actors = array()) {
		$r = [];
		$to_be_loaded = [];
		foreach ($iids_or_actors as $iid) {
			$x = self::Pick($iid);
			$r[$x->iid] = $x;
			if ($x->_info === null) $to_be_loaded[$x->iid] = $x->iid;
		}
		if (!empty($to_be_loaded)) {
			$a = $to_be_loaded; // copy
			while (!empty($a)) {
				$dr = Database::Execute('SELECT `iid`,`Info`,`Time` FROM `mmx_actor` WHERE `iid` IN '.new Sql($a));
				while($dr->Read()) {
					/** @var $x self */
					$x = $r[$dr['iid']->AsInteger()];
					$x->set_info($dr['Time']->AsDateTime(),json_decode($dr['Info']->AsString(),JSON_OBJECT_AS_ARRAY));
					unset($a[$x->iid]);
				}
				$dr->Close();
				foreach ($a as $iid) self::ImportFromTMDb($iid,$to_be_loaded,[]);
			}
			$dr = Database::Execute('SELECT `iidActor`,`iidMovie`,`Info`,`Time` FROM `mmx_credit` WHERE `iidActor` IN '.new Sql($to_be_loaded).' ORDER BY `Date` DESC');
			while($dr->Read()) {
				/** @var $x self */
				$x = $r[$dr['iidActor']->AsInteger()];
				$x->_info->Credits[] = new Credit( $x , Movie::Pick($dr['iidMovie']->AsInteger()) , json_decode($dr['Info']->AsString(),JSON_OBJECT_AS_ARRAY) , $dr['Time']->AsDateTime() );
			}
		}
		return $r;
	}











	public static function ImportFromTMDb($iid,$actors_to_skip=[],$movies_to_skip=[]) {
		Debug::Write("Importing actor $iid.");
		$tmdb = TMDb::GetActorInfo($iid);
		if ($tmdb === null) return;
		$info = [];
		$crdt = [];
		$dddd = [];
		$time = new XDateTime($tmdb['timestamp']);


		//
		// Clean up input
		//
		if (!isset($tmdb['empty'])) {
			$info[] = @$tmdb['name'] ?: null;
			$info[] = @$tmdb['imdb_id'] ?: null;
			$info[] = @$tmdb['birthday'] ?: null;
			$info[] = @$tmdb['deathday'] ?: null;
			$info[] = @$tmdb['place_of_birth'] ?: null;
			$info[] = @$tmdb['profile_path'] ?: null;
			$info[] = @$tmdb['biography']
					? preg_replace('/\s*From Wikipedia, the free encyclopedia.\s*/','',
						preg_replace('/\s*Description above from the Wikipedia article .*, licensed under CC-BY-SA, full list of contributors on Wikipedia.\s*/',''
						,$tmdb['biography']))
					: null;
			if (isset($tmdb['combined_credits']['cast'])) foreach ($tmdb['combined_credits']['cast'] as $tmdb2) if ($iid2 = @$tmdb2['id']) {
				if (@$tmdb2['media_type'] === 'tv') $iid2 = -$iid2;
				if (!array_key_exists($iid2,$crdt)) {
					$crdt[$iid2] = [];
					$dddd[$iid2] = @$tmdb2['release_date']?:@$tmdb2['first_air_date']?:'';
				}
				$crdt[$iid2][] = Credit::EncodeJob(@$tmdb2['character']?:'Cast');
			}
			if (isset($tmdb['combined_credits']['crew'])) foreach ($tmdb['combined_credits']['crew'] as $tmdb2) if ($iid2 = @$tmdb2['id']) {
				if (@$tmdb2['media_type'] === 'tv') $iid2 = -$iid2;
				if (!array_key_exists($iid2,$crdt)) {
					$crdt[$iid2] = [];
					$dddd[$iid2] = @$tmdb2['release_date']?:@$tmdb2['first_air_date']?:'';
				}
				$crdt[$iid2][] = Credit::EncodeJob(@$tmdb2['job']?:'Crew');
			}
		}

		//
		// Update mmx_actor
		//
		$json_new = json_encode($info,JSON_UNESCAPED_UNICODE);
		$json_old = Database::ExecuteScalar('SELECT `Info` FROM `mmx_actor` WHERE `iid`=?',$iid)->AsStringOrNull();
		if ($json_old === null )
			Database::Execute('INSERT INTO `mmx_actor`(`iid`,`Info`,`Time`) VALUES (?,?,?)',$iid,$json_new,$time);
		elseif ($json_old !== $json_new)
			Database::Execute('UPDATE `mmx_actor` SET `Info`=?, `Time`=? WHERE `iid`=?',$json_new,$time,$iid);


		//
		// Update mmx_credit
		//
		foreach ($crdt as &$v) $v = json_encode($v,JSON_UNESCAPED_UNICODE);

		$seen = [];
		$to_kill = [];
		$to_look = [];
		$to_save = [];
		$to_make = [];

		$dr = Database::Execute('SELECT `iidMovie`,`Info`,`Rank`,`Date` FROM `mmx_credit` WHERE `iidActor`=?',$iid);
		while($dr->Read()) {
			$iidMovie = $dr['iidMovie']->AsInteger();
			$old_info = $dr['Info']->AsString();
			$old_rank = $dr['Rank']->AsIntegerOrNull();
			$old_date = ($d = $dr['Date']->AsDate()) === null ? '' : $d->Format('Y-m-d');
			$seen[$iidMovie] = $iidMovie;


			if (!array_key_exists($iidMovie,$crdt)) {
				// We found a movie that is not in the actor credits

				if ($old_rank === null) 	// The actor is not in the movie credits anyway.
					$to_kill[] = $iidMovie;

				elseif ($old_rank >= 0)   // The actor really belongs to the movie. Maybe there has been a change.
					$to_look[] = $iidMovie;

			}
			else {
				// We found a movie that still exists in the actor credits

				if ($old_rank === null || $old_rank < 0)  // this was a light credit (and still is)
					if ($old_info !== $crdt[$iidMovie] || $old_date !== $dddd[$iidMovie])  // there has been a change
						$to_save[] = $iidMovie;

			}
		}
		$dr->Close();

		foreach ($crdt as $iidMovie=>$_)
			if (!array_key_exists($iidMovie,$seen))
				$to_make[] = $iidMovie;

		if (!empty($to_kill))
			Database::Execute('DELETE FROM `mmx_credit` WHERE `iidActor`=? AND `iidMovie` IN '.new Sql($to_kill),$iid);
		foreach ($to_save as $iidMovie)
			Database::Execute('UPDATE `mmx_credit` SET `Info`=?,`Date`=?,`Time`=? WHERE `iidActor`=? AND `iidMovie`=?',$crdt[$iidMovie],$dddd[$iidMovie] === ''?null:XDate::Parse($dddd[$iidMovie],'Y-m-d'),$time,$iid,$iidMovie);
		foreach ($to_make as $iidMovie)
			Database::Execute('INSERT INTO `mmx_credit`(`iidActor`,`iidMovie`,`Info`,`Date`,`Time`) VALUES (?,?,?,?,?)',$iid,$iidMovie,$crdt[$iidMovie],$dddd[$iidMovie]===''?null:XDate::Parse($dddd[$iidMovie],'Y-m-d'),$time);


		//
		// Propagate changes
		//
		$a = array_diff($to_look,$movies_to_skip);
		$b = array_merge($a,$movies_to_skip);
		$c = array_merge($actors_to_skip,[$iid]);
		foreach ($a as $iidMovie) Movie::ImportFromTMDb($iidMovie,$b,$c);


	}
}