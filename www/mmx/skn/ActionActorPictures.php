<?php

class ActionActorPictures extends MMAction {

	/** @var Actor */
	private $actor;
	public function __construct(Actor $actor = null){ parent::__construct(); $this->actor = $actor; }
	public function GetUrlArgs(){ return ['iid'=>$this->actor===null?null:$this->actor->iid] + parent::GetUrlArgs(); }
	public static function Make(){ return new static( Actor::Find(Http::$GET['iid']->AsInteger())); }

	public function RenderJavascript(){

		$a = [];
		/** @var $picture Picture */
		foreach ($this->actor->Pictures as $picture) {
			$a[] = $picture->Path;
		}
		echo "window.mmx.ShowPictures(".json_encode($a).");";

	}



}