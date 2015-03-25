<?php

class ActionActorCredits extends MMAction {

	/** @var Actor */
	private $actor;
	public function __construct(Actor $actor = null){ parent::__construct(); $this->actor = $actor; }
	public function GetUrlArgs(){ return ['iid'=>$this->actor===null?null:$this->actor->iid] + parent::GetUrlArgs(); }
	public static function Make(){ return new static( Actor::Find(Http::$GET['iid']->AsInteger())); }

	public function RenderJavascriptInitialState() {
		echo "window.mmx.OpenActorCredits(".($this->actor->ToJson()).");";
	}
	public function RenderJavascript(){
		echo "window.mmx.AddSeparator(".new Js($this->actor->GetCaption().': Credits').");";
		/** @var $credit Credit */
		foreach ($this->actor->Credits as $credit) {
			echo "window.mmx.AddMovieTile(".$credit->movie->ToJson().",".new Js($credit->GetExtra()).");";
		}
		echo "window.mmx.ResolveSeparator('No production found.',".new Js(oxy::icoEmpty()).");";
	}



}