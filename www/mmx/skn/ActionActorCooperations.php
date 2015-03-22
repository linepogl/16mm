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

		$movies = [];
		$actors = [];
		$actor_common_movies = [];

		/** @var $credit Credit */
		foreach (Credit::FromActor($this->actor) as $credit) {
			if ($credit->movie === null) continue;
			$movies[] = $credit->movie;
		}

		/** @var $movie Movie */
		foreach ($movies as $movie) {
			foreach (Credit::FromMovie($movie) as $credit) {
				if ($credit->actor === null) continue;
				if ($credit->actor->iid === $this->actor->iid) continue;
				if (array_key_exists($credit->actor->iid,$actor_common_movies))
					$actor_common_movies[$credit->actor->iid]++;
				else {
					$actor_common_movies[$credit->actor->iid] = 1;
					$actors[$credit->actor->iid] = $credit->actor;
				}
			}
		}

		arsort($actor_common_movies);

		foreach ($actor_common_movies as $iid => $number) {
			$actor = $actors[$iid];
			echo "window.mmx.AddActorTile(".$actor->ToJson().",".new Js($number).");";
		}

		echo "window.mmx.ResolveSeparator('No person found.',".new Js(oxy::icoEmpty()).");";
	}



}