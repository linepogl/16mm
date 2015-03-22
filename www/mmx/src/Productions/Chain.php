<?php


class Chain extends Movie {

	public $Seasons;
	public $Episodes;
	public $YearTill;

	const TMDb_TYPE = 'tv';
	public function GetKey(){ return 'C'.$this->iid; }
	public function GetCaption() { return $this->Title . ($this->Year === null ? '' : ' ('.$this->Year.($this->YearTill === null ? '' : '-'.$this->YearTill).')'); }

	public function ToArray() {
		$r = parent::ToArray();
		$r['Seasons'] = $this->Seasons;
		$r['Episodes'] = $this->Episodes;
		$r['Year'] = $this->Year;
		$r['YearTill'] = $this->YearTill;
		return $r;
	}

	public static function Find($iid) {
		$info = TMDb::GetChainInfo($iid);
		if ($info === null) return null;
		$r = new self();
		$r->iid = $iid;
		$r->Title = @$info['name'];
		$r->OriginalTitle = @$info['original_name'];
		if ($r->OriginalTitle === $r->Title) $r->OriginalTitle = null;
		$r->Overview = @$info['overview'];
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
			$r->Year = intval(substr($date,0,4));
		}
		$date = @$info['last_air_date'];
		if ($date !== null && strlen($date)>=4) {
			$r->YearTill = intval(substr($date,0,4));
		}
		if ($r->YearTill===$r->Year) $r->YearTill = null;
		return $r;
	}


	public static function Search($search_string,$page=1){
		$r = [];
		$c = TMDb::SearchTV($search_string,$page);
		$a = @$c['results'];
		if (is_array($a)) foreach ($a as $aa) {
			$iid = @$aa['id'];
			$x = Chain::Find($iid);
			if ($x === null) continue;
			$r[] = $x;
		}
		return $r;
	}

}

