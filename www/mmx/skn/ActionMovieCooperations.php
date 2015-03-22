<?php

class ActionMovieCooperations extends MMAction {

	/** @var Movie */
	private $movie;
	public function __construct(Movie $movie = null){ parent::__construct(); $this->movie = $movie; }
	public function GetUrlArgs(){ return ['iid'=>$this->movie===null?null:$this->movie->iid,'type'=>$this->movie===null?null:$this->movie->GetTMDbType()] + parent::GetUrlArgs(); }
	public static function Make(){ return new static( Movie::FindGeneric(Http::$GET['iid']->AsInteger(),Http::$GET['type']->AsStringOrNull())); }

	public function RenderJavascriptInitialState() {
		echo "window.mmx.OpenMovieCooperations(".($this->movie->ToJson()).");";
	}
	public function RenderJavascript(){
		set_time_limit(0);
		echo "window.mmx.AddSeparator(".new Js($this->movie->GetCaption().': common people').");";

		$actors = [];
		$movies = [];
		$movie_common_actors = [];

		/** @var $credit Credit */
		foreach (Credit::FromMovie($this->movie) as $credit) {
			if ($credit->actor === null) continue;
			$actors[] = $credit->actor;
		}

		/** @var $actor Actor */
		foreach ($actors as $actor) {
			foreach (Credit::FromActor($actor) as $credit) {
				if ($credit->movie === null) continue;
				if ($credit->movie->iid === $this->movie->iid && $credit->movie->GetTMDbType() === $this->movie->GetTMDbType()) continue;
				$key = $credit->movie->GetKey();
				if (array_key_exists($key,$movie_common_actors))
					$movie_common_actors[$key]++;
				else {
					$movie_common_actors[$key] = 1;
					$movies[$key] = $credit->movie;
				}
			}
		}

		arsort($movie_common_actors);

		/** @var $movie Movie */
		foreach ($movie_common_actors as $key => $number) {
			$movie = $movies[$key];
			echo "window.mmx.AddMovieTile(".$movie->ToJson().",".new Js($number).");";
		}

		echo "window.mmx.ResolveSeparator('No person found.',".new Js(oxy::icoEmpty()).");";
	}



}