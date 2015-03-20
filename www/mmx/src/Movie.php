<?php


class Movie extends Title {

	/** @var XTimeSpan */ public $Runtime;
	public $Year;

	public function __construct() {
		parent::__construct();
		$this->Type = self::MOVIE;
	}

	public function GetTitle() { return $this->Title . ($this->Year === null ? '' : ' ('.$this->Year.')'); }

	public function ToArray() {
		$r = parent::ToArray();
		$r['Runtime'] = mmx::FormatTimeSpan($this->Runtime);
		$r['RuntimeInMinutes'] = $this->Runtime === null ? null : $this->Runtime->GetTotalMinutes();
		$r['Year'] = $this->Year;
		return $r;
	}

	public static function Find($id) {
		$info = TMDb::GetMovieInfo($id);
		if ($info === null) return null;
		$r = new self();
		$r->id = $id;
		$r->Title = @$info['title'];
		$r->OriginalTitle = @$info['original_title'];
		if ($r->OriginalTitle === $r->Title) $r->OriginalTitle = null;
		$r->Description = @$info['overview'];
		$r->Image = @$info['backdrop_path'];
		$a = @$info['production_countries'];
		if (is_array($a)) foreach ($a as $aa) {
			$key = @$aa['iso_3166_1'];
			if ($key !== null) $r->Countries[] = $key;
		}
		$a = @$info['spoken_languages'];
		if (is_array($a)) foreach ($a as $aa) {
			$key = @$aa['iso_639_1'];
			if ($key !== null) $r->Languages[] = $key;
		}
		$a = @$info['genres'];
		if (is_array($a)) foreach ($a as $aa) {
			$key = @$aa['id'];
			$name = @$aa['name'];
			if ($key !== null && $name !== null) $r->Genres[$key] = $name;
		}
		$minutes = @$info['runtime'];
		if ($minutes !== null) {
			$r->Runtime = XTimeSpan::Make(0, 0, intval($minutes));
		}
		$date = @$info['release_date'];
		if ($date !== null && strlen($date)>=4) {
			$r->Year = intval(substr($date,0,4));
		}
		return $r;
	}

}

