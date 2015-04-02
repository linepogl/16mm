<?php

class ActionMovieCooperations extends MMAction {

	/** @var Movie */
	private $movie;
	public function __construct(Movie $movie = null){ parent::__construct(); $this->movie = $movie; }
	public function GetUrlArgs(){ return ['iid'=>$this->movie===null?null:$this->movie->iid,'type'=>$this->movie===null?null:$this->movie->Type] + parent::GetUrlArgs(); }
	public static function Make(){ return new static( Movie::FindGeneric(Http::$GET['iid']->AsInteger(),Http::$GET['type']->AsStringOrNull())); }

	public function RenderJavascriptInitialState() {
		echo "window.mmx.OpenMovieCooperations(".($this->movie->ToJson()).");";
	}
	public function RenderJavascript(){
		echo "window.mmx.AddSeparator(".new Js($this->movie->GetCaption().': common people').");";

		$movies = [];
		$movie_common_actors_count = [];
		$movie_common_actors = [];

		/** @var $credit1 Credit */
		/** @var $credit2 Credit */
		foreach ($this->movie->Credits as $credit1) {
			foreach ($credit1->actor->Credits as $credit2) {
				if ($credit2->movie === $this->movie) continue;
				$key = $credit2->movie->GetKey();
				if (!array_key_exists($key,$movies)) {
					$movies[$key] = $credit2->movie;
					$movie_common_actors_count[$key] = 0;
					$movie_common_actors[$key] = [];
				}
				$movie_common_actors_count[$key]++;
				$movie_common_actors[$key][] = $credit2->actor;
			}
		}

		arsort($movie_common_actors_count);

		/** @var $movie Movie */
		foreach ($movie_common_actors_count as $key => $number) {
			$movie = $movies[$key];
			$json_actors = '['.implode(',',array_map( function(Actor $x){return $x->ToJson();},$movie_common_actors[$key])).']';
			echo "window.mmx.AddMovieTile(".$movie->ToJson().",".new Js($number).",".$json_actors.");";
		}

		echo "window.mmx.ResolveSeparator('No person found.',".new Js(oxy::icoEmpty()).");";
	}



}