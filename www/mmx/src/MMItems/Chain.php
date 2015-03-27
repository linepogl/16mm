<?php

/**
 * @property-read int     Seasons
 * @property-read int     Episodes
 * @property-read int     YearTill
 */
class Chain extends Movie {

	/** @var int     */ protected $_Seasons;
	/** @var int     */ protected $_Episodes;
	/** @var int     */ protected $_YearTill;

	const TMDb_TYPE = 'tv';
	private function __construct($iid) {
		$this->iid = $iid;
		$this->Type = self::TMDb_TYPE;
	}


	public function GetKey(){ return 'C'.$this->iid; }
	public function GetCaption() { $this->Load(); return $this->_Title . ($this->_Year === null ? '' : ' ('.$this->_Year.($this->_YearTill === null ? '' : '-'.$this->_YearTill).')'); }

	public function ToArray() {
		$r = parent::ToArray();
		$r['Seasons'] = $this->Seasons;
		$r['Episodes'] = $this->Episodes;
		$r['YearTill'] = $this->YearTill;
		return $r;
	}


	protected function GetCouchUrl(){ return "/chain/$this->iid"; }

	protected function GetTMDbInfo() {
		$info = TMDb::GetChainInfo($this->iid);
		if ($info === null) return null;
		unset($info['created_by']);
		unset($info['homepage']);
		unset($info['networks']);
		unset($info['original_language']);
		unset($info['popularity']);
		unset($info['production_companies']);
		unset($info['vote_average']);
		unset($info['vote_count']);
		if (isset($info['first_air_date'])) { $info['date'] = $info['first_air_date']; unset($info['first_air_date']); }
		if (isset($info['last_air_date'])) { $info['date_till'] = $info['last_air_date']; unset($info['last_air_date']); }
		if (isset($info['origin_country'])) { $info['countries'] = $info['origin_country']; unset($info['origin_country']); }
		if (isset($info['genres'])) {
			$a = []; foreach ($info['genres'] as $aa) if (isset($aa['id'])&&isset($aa['name'])) $a[$aa['id']] = $aa['name'];
			$info['genres'] = $a;
		}
		if (isset($info['original_title'])&&isset($info['title'])&&$info['original_title']===$info['title']) unset($info['original_title']);
		if (isset($info['credits']['cast'])) { $info['cast'] = []; foreach ($info['credits']['cast'] as $info2) {
			$aa = [];
			if (isset($info2['id'])) $aa['actor'] = $info2['id'];
			if (isset($info2['character'])) $aa['character'] = str_replace(['himself','herself'],['Himself','Herself'],$info2['character']);
			if (isset($info2['episode_count'])) $aa['episodes'] = $info2['episode_count'];
			$info['cast'][ $info2['credit_id'] ] = $aa;
		}}
		if (isset($info['credits']['crew'])) { $info['crew'] = []; foreach ($info['credits']['crew'] as $info2) {
			$aa = [];
			if (isset($info2['id'])) $aa['actor'] = $info2['id'];
			if (isset($info2['department'])) $aa['department'] = $info2['department'];
			if (isset($info2['job'])) $aa['job'] = $info2['job'];
			if (isset($info2['episode_count'])) $aa['episodes'] = $info2['episode_count'];
			$info['crew'][ $info2['credit_id'] ] = $aa;
		}}
		unset($info['credits']);
		if (isset($info['keywords']['keywords'])) {
			$a = []; foreach ($info['keywords']['keywords'] as $aa) if (isset($aa['id'])&&isset($aa['name'])) $a[$aa['id']] = $aa['name'];
			$info['keywords'] = $a;
		}
		if (isset($info['backdrop_path'])) { $info['backdrop'] = $info['backdrop_path']; unset($info['backdrop_path']); }
		if (isset($info['images']['backdrops'])) { $info['backdrops'] = []; foreach ($info['images']['backdrops'] as $info2) if(isset($info2['file_path'])) $info['backdrops'][$info2['file_path']] = [@$info2['width'],@$info2['height']]; }
		if (isset($info['poster_path'])) { $info['poster'] = $info['poster_path']; unset($info['poster_path']); }
		if (isset($info['images']['posters'])) { $info['posters'] = []; foreach ($info['images']['posters'] as $info2) if(isset($info2['file_path'])) $info['posters'][$info2['file_path']] = [@$info2['width'],@$info2['height']]; }
		unset($info['images']);
		ksort($info);
		return $info;
	}
	protected function LoadInfo($info) {
		if (!parent::LoadInfo($info)) return false;
		$this->_Seasons = @$info['number_of_seasons'] ?: null;
		$this->_Episodes = @$info['number_of_episodes'] ?: null;
		$this->_Year = strlen($d = @$info['first_air_date'])<4 ? null : intval(substr($d,0,4));
		$this->_YearTill = strlen($d = @$info['last_air_date'])<4 ? null : intval(substr($d,0,4)); if ($this->_YearTill===$this->_Year) $this->_YearTill = null;
		return true;
	}









	private static $cache = [];
	/** @return Chain */
	public static function Find($iid) {
		if (!array_key_exists($iid, self::$cache)) self::$cache[$iid] = new self($iid);
		return self::$cache[$iid];
	}

	public static function Search($search_string,$page=1,&$pages=1,&$count=0){
		$r = [];
		$c = TMDb::SearchChain($search_string,$page);
		$a = @$c['results'];
		if (is_array($a)) foreach ($a as $aa) {
			$iid = @$aa['id'];
			$x = Chain::Find($iid);
			if (!$x->Found) continue;
			$r[] = $x;
		}
		$pages = intval(@$c['total_pages']);
		$count = intval(@$c['total_results']);
		return $r;
	}

}

