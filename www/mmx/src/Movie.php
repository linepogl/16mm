<?php

class _MovieInfo{
	public $time;
	public $Found = false;
	public $imdb;
	public $Title;
	public $Year;
	public $YearTill;
	public $OriginalTitle;
	public $Runtime;
	public $Backdrop;
	public $Poster;
	public $Seasons;
	public $Episodes;
	public $Status;
	public $Overview;
	public $Countries;
	public $Languages;
	public $Keywords;
	public $Genres;
	public $Credits = [];
	public function __construct($time){ $this->time=$time; }
}


/**
 *
 * @property-read boolean    Found
 * @property-read XDateTIme  time
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
	/** @var _MovieInfo */ private $_info;
	private function __construct($iid){ $this->iid = $iid; }

	public function __get($key) {
		if ($this->_info === null) throw new Exception('Movie has not been loaded.');
		return $this->_info->$key;
	}
	private function set_info($time,$info) {
		$this->_info = new _MovieInfo($time);
		$this->_info->Found = !empty($info);
		$this->Title = @$info[0] ?: null;
		$this->imdb = @$info[1] ?: null;
		$this->Year = strlen($d=@$info[2])<4 ? null : intval(substr($d,0,4));
		$this->YearTill = strlen($d=@$info[3])<4 ? null : intval(substr($d,0,4));
		$this->OriginalTitle = @$info[4] ?: null;
		//$this->collection = @$info[5] ?: null;
		$this->Backdrop = @$info[6] ?: null;
		$this->Poster = @$info[7] ?: null;
		$this->Runtime = ($m=@$info[8]) ? XTimeSpan::Make(0,0,$m) : null;
		$this->Overview = @$info[9] ?: null;
		$this->Status = @$info[10] ?: null;
		$this->Seasons = @$info[11] ?: null;
		$this->Episodes = @$info[12] ?: null;
		$this->Languages = @$info[13] ?: [];
		$this->Countries = @$info[14] ?: [];
		$this->Genres = @$info[15] ?: [];
		$this->Keywords = @$info[16] ?: [];
	}

	public function GetCaption() { return $this->Title . ($this->Year === null ? '' : ' ('.$this->Year.($this->YearTill === null ? '' : '-'.$this->YearTill).')'); }
	public function GetImage(){ return $this->Backdrop === null ? null : TMDb::GetImageSrc($this->Backdrop,TMDb::BACKDROP_W300); }
	public function ToJson() { return json_encode($this->ToArray(),JSON_UNESCAPED_UNICODE); }
	public function ToArray() {
		$r = [];
		$r['iid'] = $this->iid;
		$r['Type'] = $this->iid < 0 ? 'chain' : 'movie';
		$r['imdb'] = $this->imdb;
		$r['Caption'] = $this->GetCaption();
		$r['Title'] = $this->Title;
		$r['Overview'] = $this->Overview;
		$r['OriginalTitle'] = $this->OriginalTitle;
		$r['Image'] = $this->GetImage();
		$r['Countries'] = array_map(function($x){return mmx::FormatCountry($x);},$this->Countries);
		$r['Languages'] = array_map(function($x){return mmx::FormatLanguage($x);},$this->Languages);
		$r['Genres'] = array_values($this->Genres);
		$r['Keywords'] = array_values($this->Keywords);
		$r['Runtime'] = mmx::FormatTimeSpan($this->Runtime);
		$r['Year'] = $this->Year;
		$r['YearTill'] = $this->YearTill;
		$r['Seasons'] = $this->Seasons;
		$r['Episodes'] = $this->Episodes;
		return $r;
	}










	private static $cache = [];
	/**
	 * @param $iid_or_movie int|self
	 * @return self
	 */
	public static function Pick($iid_or_movie) {
		$iid = $iid_or_movie instanceof self ? $iid_or_movie->iid : $iid_or_movie;
		if (!isset(self::$cache[$iid])) self::$cache[$iid] = new self($iid);
		return self::$cache[$iid];
	}

	/**
	 * @param $iids_or_movies array
	 * @return array
	 */
	public static function Mass($iids_or_movies = array()) {
		$r = [];
		$to_be_loaded = [];
		foreach ($iids_or_movies as $iid) {
			$x = self::Pick($iid);
			$r[$x->iid] = $x;
			if ($x->_info === null) $to_be_loaded[$x->iid] = $x->iid;
		}
		if (!empty($to_be_loaded)) {
			$a = $to_be_loaded; // copy
			while (!empty($a)) {
				$dr = Database::Execute('SELECT `iid`,`Info`,`Time` FROM `mmx_movie` WHERE `iid` IN '.new Sql($a));
				while($dr->Read()) {
					/** @var $x self */
					$x = $r[$dr['iid']->AsInteger()];
					$x->set_info($dr['Time']->AsDateTime(),json_decode($dr['Info']->AsString(),JSON_OBJECT_AS_ARRAY));
					unset($a[$x->iid]);
				}
				$dr->Close();
				foreach ($a as $iid) self::ImportFromTMDb($iid,$to_be_loaded,[]);
			}
			$dr = Database::Execute('SELECT `iidActor`,`iidMovie`,`Info`,`Time` FROM `mmx_credit` WHERE `Rank` IS NOT NULL AND `Rank`>=0 AND `iidMovie` IN '.new Sql($to_be_loaded).' ORDER BY `Rank` ASC');
			while($dr->Read()) {
				/** @var $x self */
				$x = $r[$dr['iidMovie']->AsInteger()];
				$x->_info->Credits[] = new Credit( Actor::Pick($dr['iidActor']->AsInteger()) , $x , json_decode($dr['Info']->AsString(),JSON_OBJECT_AS_ARRAY) , $dr['Time']->AsDateTime() );
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
				$crdt[$iid2][] = Credit::EncodeJob(@$tmdb2['character']?:'Cast');
			}
			if (isset($tmdb['created_by'])) foreach ($tmdb['created_by'] as $tmdb2) if ($iid2 = @$tmdb2['id']) {
				if (!array_key_exists($iid2,$crdt)) $crdt[$iid2] = [];
				$crdt[$iid2][] = Credit::EncodeJob('Created by');
			}
			if (isset($tmdb['credits']['crew'])) foreach ($tmdb['credits']['crew'] as $tmdb2) if ($iid2 = @$tmdb2['id']) {
				if (!array_key_exists($iid2,$crdt)) $crdt[$iid2] = [];
				$crdt[$iid2][] = Credit::EncodeJob(@$tmdb2['job']?:'Crew');
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
		$to_show = [];
		$to_hide = [];
		$to_kill = [];
		$to_look = [];
		$to_save = [];
		$to_make = [];

		$dr = Database::Execute('SELECT `iidActor`,`Info`,`Rank`,`Date` FROM `mmx_credit` WHERE `iidMovie`=?',$iid);
		while($dr->Read()) {
			$iidActor = $dr['iidActor']->AsInteger();
			$seen[$iidActor] = $iidActor;
			$old_info = $dr['Info']->AsString();
			$old_rank = $dr['Rank']->AsIntegerOrNull();
			$old_date = ($d = $dr['Date']->AsDate()) === null ? '' : $d->Format('Y-m-d');

			if (!array_key_exists($iidActor,$crdt)) {
				// We found an actor that does not belong to the movie

				if ($old_rank === null)   // this is the first time that the movie sees this credit
					$to_hide[] = $iidActor; // hide this credit from the movie

				elseif ($old_rank >= 0)   // this was a strong credit
					$to_kill[] = $iidActor; // remove it altogether

			}
			else {
				// We found an actor that still exists in the movie credits

				if ($old_rank === null)     // this was a light credit.
					$to_save[] = $iidActor;   // make it strong

				elseif ($old_rank < 0)      // this was hidden
					$to_save[] = $iidActor;

				elseif ($old_rank !== $rrrr[$iidActor] || $old_info !== $crdt[$iidActor] || $old_date !== $dddd)  // there has been a change
					$to_save[] = $iidActor;

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
			Database::Execute('UPDATE `mmx_credit` SET `Info`=?,`Rank`=?,`Date`=?,`Time`=? WHERE `iidMovie`=? AND `iidActor`=?',$crdt[$iidActor],$rrrr[$iidActor],$d,$time,$iid,$iidActor);
		foreach ($to_make as $iidActor)
			Database::Execute('INSERT INTO `mmx_credit`(`iidMovie`,`iidActor`,`Info`,`Rank`,`Date`,`Time`) VALUES (?,?,?,?,?,?)',$iid,$iidActor,$crdt[$iidActor],$rrrr[$iidActor],$d,$time);


		$a = array_diff($to_look,$actors_to_skip);
		$b = array_merge($a,$actors_to_skip);
		$c = array_merge($movies_to_skip,[$iid]);
		foreach ($a as $iidActor) Actor::ImportFromTMDb($iidActor,$b,$c);


	}
}