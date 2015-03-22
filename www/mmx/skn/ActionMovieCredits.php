<?php

class ActionMovieCredits extends MMAction {

	/** @var Movie */
	private $movie;
	public function __construct(Movie $movie = null){ parent::__construct(); $this->movie = $movie; }
	public function GetUrlArgs(){ return ['iid'=>$this->movie===null?null:$this->movie->iid,'type'=>$this->movie===null?null:$this->movie->GetTMDbType()] + parent::GetUrlArgs(); }
	public static function Make(){ return new static( Movie::FindGeneric(Http::$GET['iid']->AsInteger(),Http::$GET['type']->AsStringOrNull())); }

	public function RenderJavascriptInitialState() {
		echo "window.mmx.OpenMovie(".($this->movie->ToJson()).");";
	}
	public function RenderJavascript() {
		echo "window.mmx.AddSeparator(".new Js($this->movie->GetCaption().': Credits').");";

		/** @var $credit Credit */
		foreach (Credit::FromMovie($this->movie) as $credit) {
			if ($credit->actor === null) continue;
			echo "window.mmx.AddActorTile(".$credit->actor->ToJson().",".new Js($credit->GetExtra()).");";
		}
	}
}