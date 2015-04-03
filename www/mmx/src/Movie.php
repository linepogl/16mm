<?php

/**
 *
 * @property-read string     imdb
 * @property-read string     Title
 * @property-read int        Year
 * @property-read int        YearTill
 * @property-read string     OriginalTitle
 * @property-read XTimeSpan  Runtime
 * @property-read string     Backdrop
 * @property-read string     Poster
 * @property-read int        Seasons
 * @property-read int        Episodes
 * @property-read string     Status
 * @property-read string     Overview
 * @property-read array      Countries
 * @property-read array      Languages
 * @property-read array      Keywords
 * @property-read array      Genres
 * @property-read array      Credits
 *
 */
class Movie {

	public $iid;
	public $time;
	private $_imdb;
	private $_Title;
	private $_Year;
	private $_YearTill;
	private $_OriginalTitle;
	private $_Runtime;
	private $_Backdrop;
	private $_Poster;
	private $_Seasons;
	private $_Episodes;
	private $_Status;
	private $_Overview;
	private $_Countries;
	private $_Languages;
	private $_Keywords;
	private $_Genres;
	private $_Credits;

	private function __construct($iid){ $this->iid = $iid; }

	public function __get($key) {
		if ($this->time === null) throw new Exception('Movie has not been loaded.');
		return $this->{'_'.$key};
	}
	private function set_info($info) {
		$this->_Title = @$info[0] ?: null;
		$this->_imdb = @$info[1] ?: null;
		$this->_Year = strlen($d=@$info[2])<4 ? null : intval(substr($d,0,4));
		$this->_YearTill = strlen($d=@$info[3])<4 ? null : intval(substr($d,0,4));
		$this->_OriginalTitle = @$info[4] ?: null;
		//$this->_collection = @$info[5] ?: null;
		$this->_Backdrop = @$info[6] ?: null;
		$this->_Poster = @$info[7] ?: null;
		$this->_Runtime = ($m=@$info[8]) ? XTimeSpan::Make(0,0,$m) : null;
		$this->_Overview = @$info[9] ?: null;
		$this->_Status = @$info[10] ?: null;
		$this->_Seasons = @$info[11] ?: null;
		$this->_Episodes = @$info[12] ?: null;
		$this->_Languages = @$info[13] ?: [];
		$this->_Countries = @$info[14] ?: [];
		$this->_Keywords = @$info[15] ?: [];
		$this->_Genres = @$info[16] ?: [];
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
	 * @param $iids_or_movies array
	 * @return array
	 */
	public static function Mass($iids_or_movies = array()) {
		$r = [];
		$info_to_be_loaded = [];
		$crdt_to_be_loaded = [];
		foreach ($iids_or_movies as $iid) {
			$x = self::Pick($iid);
			$r[$x->iid] = $x;
			if ($x->time === null) {
				$info_to_be_loaded[$x->iid] = $x->iid;
				$crdt_to_be_loaded[$x->iid] = $x->iid;
				$x->_Credits = [];
			}
		}
		while (!empty($info_to_be_loaded)) {
			$dr = Database::Execute('SELECT `iid`,`Info`,`Time` FROM `mmx_movie` WHERE `iid` IN '.new Sql($info_to_be_loaded));
			while($dr->Read()) {
				/** @var $x self */
				$x = $r[$dr['iid']->AsInteger()];
				$x->time = $dr['Time']->AsDateTime();
				$x->set_info(json_decode($dr['Info']->AsString(),JSON_OBJECT_AS_ARRAY));
				unset($info_to_be_loaded[$x->iid]);
			}
			$dr->Close();
			foreach ($info_to_be_loaded as $iid) self::ImportFromTMDb($iid,$info_to_be_loaded,[]);
		}
		if (!empty($crdt_to_be_loaded)) {
			$dr = Database::Execute('SELECT `iidActor`,`iidMovie`,`Info`,`Time` FROM `mmx_credit` WHERE `iidMovie` IN '.new Sql($crdt_to_be_loaded));
			while($dr->Read()) {
				/** @var $x self */
				$x = $r[$dr['iidMovie']->AsInteger()];
				$x->_Credits[] = new Credit( Actor::Pick($dr['iidActor']->AsInteger()) , $x , json_decode($dr['Info']->AsString(),JSON_OBJECT_AS_ARRAY) , $dr['Time']->AsDateTime() );
			}
		}
		return $r;
	}

















	public static function ImportFromTMDb($iid,$actors_to_skip=[],$movies_to_skip=[]) {
		Debug::Write("Importing movie $iid.");
		$tmdb = $iid < 0 ? TMDb::GetChainInfo(-$iid) : TMDb::GetMovieInfo($iid);
		if ($tmdb === null) return;
		$info = [];
		$crdt = [];
		$dddd = '';
		$time = new XDateTime($tmdb['timestamp']);


		//
		// Clean up input
		//
		if (!isset($tmdb['empty'])) {
			if ($iid < 0) {
				$dddd =  @$tmdb['first_air_date'] ?: '';
				$info[] = @$tmdb['name'] ?: null;
				$info[] = @$tmdb['imdb_id'] ?: null;
				$info[] = @$tmdb['first_air_date'] ?: null;
				$info[] = @$tmdb['last_air_date'] ?: null;
				$info[] = @$tmdb['original_name']===$tmdb['name'] ? null : (@$tmdb['original_name'] ?: null);
				$info[] = null; // collection
				$info[] = @$tmdb['backdrop_path'] ?: null;
				$info[] = @$tmdb['poster_path'] ?: null;
				$info[] = null; // runtime
				$info[] = @$tmdb['overview'] ?: null;
				$info[] = @$tmdb['status'] ?: null;
				$info[] = @$tmdb['number_of_seasons'] ?: null;
				$info[] = @$tmdb['number_of_episodes'] ?: null;
				if (isset($tmdb['languages'])) { $info[] = $tmdb['languages']; } else $info[] = [];
				if (isset($tmdb['origin_country'])) { $info[] = $tmdb['origin_country']; } else $info[] = [];
				if (isset($tmdb['genres'])) { $a = []; foreach ($tmdb['genres'] as $aa) if (isset($aa['id'])&&isset($aa['name'])) $a[$aa['id']] = $aa['name']; $info[] = $a; } else $info[] = [];
				if (isset($tmdb['keywords']['results'])) { $a = []; foreach ($tmdb['keywords']['results'] as $aa) if (isset($aa['id'])&&isset($aa['name'])) $a[$aa['id']] = $aa['name']; $info[] = $a; } else $info[] = [];
			}
			else {
				$dddd =  @$tmdb['release_date'] ?: '';
				$info[] = @$tmdb['title'] ?: null;
				$info[] = @$tmdb['imdb_id'] ?: null;
				$info[] = @$tmdb['release_date'] ?: null;
				$info[] = null; // date till
				$info[] = @$tmdb['original_title']===$tmdb['title'] ? null : (@$tmdb['original_title'] ?: null);
				$info[] = @$tmdb['belongs_to_collection']['id'] ?: null;
				$info[] = @$tmdb['backdrop_path'] ?: null;
				$info[] = @$tmdb['poster_path'] ?: null;
				$info[] = @$tmdb['runtime'] ?: null;
				$info[] = @$tmdb['overview'] ?: null;
				$info[] = @$tmdb['status'] ?: null;
				$info[] = null; // seasons
				$info[] = null; // episodes
				if (isset($tmdb['spoken_languages'])) {	$a = []; foreach ($tmdb['spoken_languages'] as $aa) if (isset($aa['iso_639_1'])) $a[] = $aa['iso_639_1']; $info[] = $a; } else $info[] = [];
				if (isset($tmdb['production_countries'])) { $a = []; foreach ($tmdb['production_countries'] as $aa) if (isset($aa['iso_3166_1'])) $a[] = $aa['iso_3166_1']; $info[] = $a; } else $info[] = [];
				if (isset($tmdb['genres'])) { $a = []; foreach ($tmdb['genres'] as $aa) if (isset($aa['id'])&&isset($aa['name'])) $a[$aa['id']] = $aa['name']; $info[] = $a; } else $info[] = [];
				if (isset($tmdb['keywords']['keywords'])) { $a = []; foreach ($tmdb['keywords']['keywords'] as $aa) if (isset($aa['id'])&&isset($aa['name'])) $a[$aa['id']] = $aa['name']; $info[] = $a; } else $info[] = [];
			}


			if (isset($tmdb['credits']['cast'])) foreach ($tmdb['credits']['cast'] as $tmdb2) if ($iid2 = @$tmdb2['id']) {
				if (!array_key_exists($iid2,$crdt)) $crdt[$iid2] = [];
				$crdt[$iid2][ Credit::EncodeJob(@$tmdb2['character']?:'Cast') ] = @$tmdb2['episode_count'] ?: 0;
			}
			if (isset($tmdb['created_by'])) foreach ($tmdb['created_by'] as $tmdb2) if ($iid2 = @$tmdb2['id']) {
				if (!array_key_exists($iid2,$crdt)) $crdt[$iid2] = [];
				$crdt[$iid2][ Credit::EncodeJob('Created by') ] = @$tmdb['episode_count'] ?: 0;
			}
			if (isset($tmdb['credits']['crew'])) foreach ($tmdb['credits']['crew'] as $tmdb2) if ($iid2 = @$tmdb2['id']) {
				if (!array_key_exists($iid2,$crdt)) $crdt[$iid2] = [];
				$crdt[$iid2][ Credit::EncodeJob(@$tmdb2['job']?:'Crew') ] = @$tmdb2['episode_count'] ?: 0;
			}

		}

		//
		// Update mmx_movie
		//
		$json_new = json_encode($info,JSON_UNESCAPED_UNICODE);
		$json_old = Database::ExecuteScalar('SELECT `Info` FROM `mmx_movie` WHERE `iid`=?',$iid)->AsStringOrNull();
		if ($json_old === null )
			Database::Execute('INSERT INTO `mmx_movie`(`iid`,`Info`,`Time`) VALUES (?,?,?)',$iid,$json_new,$time);
		elseif ($json_old !== $json_new)
			Database::Execute('UPDATE `mmx_movie` SET `Info`=?, `Time`=? WHERE `iid`=?',$json_new,$time,$iid);


		//
		// Update mmx_credit
		//
		foreach ($crdt as &$v) $v = json_encode($v,JSON_UNESCAPED_UNICODE);
		$rrrr = []; $i = 0; foreach ($crdt as $iidActor=>$_) $rrrr[$iidActor] = $i++;

		$seen = [];
		$to_hide = [];
		$to_kill = [];
		$to_look = [];
		$to_save = [];
		$to_make = [];

		$dr = Database::Execute('SELECT `iidActor`,`Info`,`Rank` FROM `mmx_credit` WHERE `iidMovie`=?',$iid);
		while($dr->Read()) {
			$iidActor = $dr['iidActor']->AsInteger();
			$seen[$iidActor] = $iidActor;
			$old_rank = $dr['Rank']->AsIntegerOrNull();

			if (!array_key_exists($iidActor,$crdt)) {
				if($old_rank !== null && $old_rank >= 0) {
					$to_hide[] = $iidActor;
					//$to_look[] = $iidActor;
				}
			}
			else {
				$old_json = $dr['Info']->AsString();
				if ($old_json !== $crdt[$iidActor] || $old_rank !== $rrrr[$iidActor]) {
					$to_save[] = $iidActor;
					//$to_look[] = $iidActor;
				}
			}
		}
		$dr->Close();

		foreach ($crdt as $iidActor=>$_)
			if (!array_key_exists($iidActor,$seen))
				$to_make[] = $iidActor;

		$d = $dddd===''?null:XDate::Parse($dddd,'Y-m-d');

		if (!empty($to_hide))
			Database::Execute('UPDATE `mmx_credit` SET `Rank`=? WHERE `iidMovie`=? AND `iidActor` IN '.new Sql($to_hide),-1,$iid);
		if (!empty($to_kill))
			Database::Execute('DELETE FROM `mmx_credit` WHERE `iidMovie`=? AND `iidActor` IN '.new Sql($to_kill),$iid);
		foreach ($to_save as $iidActor)
			Database::Execute('UPDATE `mmx_credit` SET `Info`=?,`Rank`=?,`Time`=? WHERE `iidMovie`=? AND `iidActor`=?',$crdt[$iidActor],$rrrr[$iidActor],$time,$iid,$iidActor);
		foreach ($to_make as $iidActor)
			Database::Execute('INSERT INTO `mmx_credit`(`iidMovie`,`iidActor`,`Info`,`Rank`,`Date`,`Time`) VALUES (?,?,?,?,?,?)',$iid,$iidActor,$crdt[$iidActor],$rrrr[$iidActor],$d,$time);


		$a = array_diff($to_look,$actors_to_skip);
		$b = array_merge($a,$actors_to_skip);
		$c = array_merge($movies_to_skip,[$iid]);
		foreach ($a as $iidActor) Actor::ImportFromTMDb($iidActor,$b,$c);


	}
}