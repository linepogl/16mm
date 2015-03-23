<?php

class ActionActorCooperations extends MMAction {

	/** @var Actor */
	private $actor;
	public function __construct(Actor $actor = null){ parent::__construct(); $this->actor = $actor; }
	public function GetUrlArgs(){ return ['iid'=>$this->actor===null?null:$this->actor->iid] + parent::GetUrlArgs(); }
	public static function Make(){ return new static( Actor::Find(Http::$GET['iid']->AsInteger())); }

	public function RenderJavascriptInitialState() {
		echo "window.mmx.OpenActorCooperations(".($this->actor->ToJson()).");";
	}
	public function RenderJavascript(){
		set_time_limit(0);
		echo "window.mmx.AddSeparator(".new Js($this->actor->GetCaption().': Cooperations').");";

		$actors = [];
		$actor_common_movies_count = [];
		$actor_common_movies = [];

		/** @var $credit1 Credit */
		/** @var $credit2 Credit */
		foreach ($this->actor->Credits as $credit1) {
			foreach ($credit1->movie->Credits as $credit2) {
				if ($credit2->actor === $this->actor) continue;
				$key = $credit2->actor->GetKey();
				if (!array_key_exists($key,$actors)) {
					$actor[$key] = $credit2->actor;
					$actor_common_movies[$key] = [];
					$actor_common_movies_count[$key] = 0;
				}
				$actor_common_movies[$key][] = $credit2->movie;
				$actor_common_movies_count[$key]++;
			}
		}

		arsort($actor_common_movies_count);

		/** @var $actor Actor */
		foreach ($actor_common_movies_count as $key => $number) {
			$actor = $actors[$key];
			$json_movies = '['.implode(',',array_map( function(Movie $x){return $x->ToJson();},$actor_common_movies[$key])).']';
			echo "window.mmx.AddActorTile(".$actor->ToJson().",".new Js($number).",".$json_movies.");";
		}

		echo "window.mmx.ResolveSeparator('No person found.',".new Js(oxy::icoEmpty()).");";
	}



}