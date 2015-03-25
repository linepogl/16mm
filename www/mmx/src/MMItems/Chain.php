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

	protected function LoadFromTMDb() {
		$info = TMDb::GetChainInfo($this->iid);
		if ($info === null) return false;
		$this->_Timestamp = intval(@$info['timestamp']) ?: null;
		$this->_Title = @$info['name'];
		$this->_imdb = @$info['imdb_id'];
		$this->_OriginalTitle = @$info['original_name']; if ($this->_OriginalTitle === $this->_Title) $this->_OriginalTitle = null;
		$this->_Overview = @$info['overview'];
		$this->_Backdrop = @$info['backdrop_path'];
		$this->_Poster = @$info['poster_path'];
		$this->_Countries = []; $a = @$info['origin_country']; if (is_array($a)) foreach ($a as $key) $this->_Countries[] = $key;
		$this->_Languages = []; $a = @$info['languages']; if (is_array($a)) foreach ($a as $key) $this->_Languages[] = $key;
		$this->_Genres = []; $a = @$info['genres']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['id']; $name = @$aa['name']; if ($key !== null && $name !== null) $this->_Genres[$key] = $name; }
		$this->_Keywords = []; $a = @$info['keywords']['keywords']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['id']; $name = @$aa['name']; if ($key !== null && $name !== null) $this->_Keywords[$key] = $name; }
		$this->_Seasons = null; $this->_Episodes = null; $a = @$info['seasons']; if (is_array($a)) foreach ($a as $aa) { $this->_Seasons++; $this->_Episodes += intval(@$aa['episode_count']); }
		$this->_Year = strlen($d = @$info['first_air_date'])<4 ? null : intval(substr($d,0,4));
		$this->_YearTill = strlen($d = @$info['last_air_date'])<4 ? null : intval(substr($d,0,4)); if ($this->_YearTill===$this->_Year) $this->_YearTill = null;
		$this->LoadCreditsFromTMDb($info);
		$this->LoadPicturesFromTMDb($info);
		return true;
	}


	public function GetDataPath(){ return sprintf('../dat/chain/%03d/%03d/%03d.dat',$this->iid/1000000%1000,$this->iid/1000%1000,$this->iid%1000); }
	public function SaveIntoFile(){
		$this->Load();
		$path = $this->GetDataPath();
		Fs::Ensure(dirname($path));
		if (($f = @fopen($path,'w')) === false) return;
		fprintf($f,'%X',$this->_Timestamp);
		$k = 'n'; $s = $this->_Title;            if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'i'; $s = $this->_imdb;             if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'y'; $s = $this->_Year;             if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'z'; $s = $this->_YearTill;         if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 's'; $s = $this->_Seasons;          if ($s!==null) fprintf($f,"\n%s%X",$k,$s);
		$k = 'e'; $s = $this->_Episodes;         if ($s!==null) fprintf($f,"\n%s%X",$k,$s);
		$k = 'b'; $s = $this->_Backdrop;         if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'p'; $s = $this->_Poster;           if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 'o'; $s = $this->_OriginalTitle;    if ($s!==null) fprintf($f,"\n%s%s",$k,$s);
		$k = 't'; $s = $this->_Overview;         if ($s!==null) fprintf($f,"\n%s%s",$k,str_replace(["\\","\n"],["\\\\","\\n"],$s));
		$k = 'c'; $s = $this->_Countries;        if (!empty($s)) fprintf($f,"\n%s%s",$k,implode($s));
		$k = 'l'; $s = $this->_Languages;        if (!empty($s)) fprintf($f,"\n%s%s",$k,implode($s));
		$k = 'g'; $s = $this->_Genres;           if (!empty($s)) fprintf($f,"\n%s%s",$k,implode(',',$s));
		$k = 'k'; $s = $this->_Keywords;         if (!empty($s)) fprintf($f,"\n%s%s",$k,implode(',',$s));
		/** @var $x Credit  */ foreach ($this->_Credits  as $x) fprintf($f,"\n%s",$x->PackForMovie());
		/** @var $x Picture */ foreach ($this->_Pictures as $x) fprintf($f,"\n%s",$x->Pack());
		fclose($f);
	}
	public function LoadFromFile(){
		$path = $this->GetDataPath();
		if (($f = @fopen($path,'r')) === false) return false;
		$this->_Timestamp = intval(fgets($f),16);
		$this->_Credits = [];
		$this->_Countries = [];
		$this->_Languages = [];
		$this->_Genres = [];
		$this->_Keywords = [];
		$this->_Pictures = [];
		while (($line = fgets($f)) !== false) {
			$line = rtrim($line);
			if ($line === '') continue;
			switch($line[0]) {
				case 'n': $s = substr($line,1); $this->_Title         = $s === '' ? null : $s; break;
				case 'i': $s = substr($line,1); $this->_imdb          = $s === '' ? null : $s; break;
				case 'y': $s = substr($line,1); $this->_Year          = $s === '' ? null : intval($s); break;
				case 'z': $s = substr($line,1); $this->_YearTill      = $s === '' ? null : intval($s); break;
				case 's': $s = substr($line,1); $this->_Seasons       = $s === '' ? null : intval($s,16); break;
				case 'e': $s = substr($line,1); $this->_Episodes      = $s === '' ? null : intval($s,16); break;
				case 'b': $s = substr($line,1); $this->_Backdrop      = $s === '' ? null : $s; break;
				case 'p': $s = substr($line,1); $this->_Poster        = $s === '' ? null : $s; break;
				case 'o': $s = substr($line,1); $this->_OriginalTitle = $s === '' ? null : $s; break;
				case 't': $s = substr($line,1); $this->_Overview      = $s === '' ? null : str_replace(["\\n","\\\\"],["\n","\\",],$s); break;
				case 'c': $s = substr($line,1); $this->_Countries     = $s === '' ? [] : str_split($s,2); break;
				case 'l': $s = substr($line,1); $this->_Languages     = $s === '' ? [] : str_split($s,2); break;
				case 'g': $s = substr($line,1); $this->_Genres        = $s === '' ? [] : explode(',',$s); break;
				case 'k': $s = substr($line,1); $this->_Keywords      = $s === '' ? [] : explode(',',$s); break;
				case 'A':
				case 'B':
				case 'C':
					$x = Credit::UnpackForMovie($this,$line);
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

