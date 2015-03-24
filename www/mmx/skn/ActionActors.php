<?php

class ActionActors extends MMAction {

	public function RenderJavascript(){

		/** @var $actor Actor */
		foreach ([100,101,102,103,104,105,106,108,110] as $iid) {
			$actor = Actor::Find($iid);
			if ($actor->NotFound()) continue;
			echo "window.mmx.AddActorTile(".$actor->ToJson().");";
		}

	}



}