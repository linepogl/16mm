<?php


class Chain extends Title {

	public $Seasons;
	public $Episodes;
	public $YearFrom;
	public $YearTill;

	public function __construct() {
		parent::__construct();
		$this->Type = self::CHAIN;
	}

	public function GetTitle() { return $this->Title . ($this->YearFrom === null ? '' : ' ('.$this->YearFrom.($this->YearTill === null ? '' : '-'.$this->YearTill).')'); }

	public function ToArray() {
		$r = parent::ToArray();
		$r['Seasons'] = $this->Seasons;
		$r['Episodes'] = $this->Episodes;
		$r['YearFrom'] = $this->YearFrom;
		$r['YearTill'] = $this->YearTill;
		return $r;
	}

	public static function Find($id) {
		$info = TMDb::GetChainInfo($id);
		if ($info === null) return null;
		$r = new self();
		$r->id = $id;
		$r->Title = @$info['name'];
		$r->OriginalTitle = @$info['original_name'];
		if ($r->OriginalTitle === $r->Title) $r->OriginalTitle = null;
		$r->Description = @$info['overview'];
		$r->Image = @$info['backdrop_path'];
		$a = @$info['origin_country'];
		if (is_array($a)) foreach ($a as $key) {
			if ($key !== null) $r->Countries[] = $key;
		}
		$a = @$info['languages'];
		if (is_array($a)) foreach ($a as $key) {
			if ($key !== null) $r->Languages[] = $key;
		}
		$a = @$info['genres'];
		if (is_array($a)) foreach ($a as $aa) {
			$key = @$aa['id'];
			$name = @$aa['name'];
			if ($key !== null && $name !== null) $r->Genres[$key] = $name;
		}
		$a = @$info['seasons'];
		if (is_array($a)) foreach ($a as $aa) {
			$r->Seasons++;
			$r->Episodes += intval(@$aa['episode_count']);
		}
		$date = @$info['first_air_date'];
		if ($date !== null && strlen($date)>=4) {
			$r->YearFrom = intval(substr($date,0,4));
		}
		$date = @$info['last_air_date'];
		if ($date !== null && strlen($date)>=4) {
			$r->YearTill = intval(substr($date,0,4));
		}
		if ($r->YearTill===$r->YearFrom) $r->YearTill = null;
		return $r;
	}

}

