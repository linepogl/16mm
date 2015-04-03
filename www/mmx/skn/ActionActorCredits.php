<?php

class ActionActorCredits extends MMAction {

	/** @var Actor */
	private $actor;
	public function __construct(Actor $actor = null){ parent::__construct(); $this->actor = $actor; }
	public function GetUrlArgs(){ return ['iid'=>$this->actor===null?null:$this->actor->iid] + parent::GetUrlArgs(); }
	public static function Make(){ return new static( Actor::Pick(Http::$GET['iid']->AsInteger())); }

	public function RenderJavascriptInitialState() {
		Actor::Mass([$this->actor]);
		echo "window.mmx.OpenActorCredits(".($this->actor->ToJson()).");";
	}
	public function RenderJavascript(){

		Movie::Mass( array_map( function(Credit $x){ return $x->_movie; } , $this->actor->Credits ) );

		echo "window.mmx.AddSeparator(".new Js($this->actor->GetCaption().': Credits').");";
		/** @var $credit Credit */
		foreach ($this->actor->Credits as $credit) {
			echo "window.mmx.AddMovieTile(".$credit->_movie->ToJson().",".new Js($credit->GetCaption()).");";
		}
		echo "window.mmx.ResolveSeparator('No production found.',".new Js(oxy::icoEmpty()).");";
	}



}