<?php

class ActionActors extends MMAction {

	public function RenderJavascript(){
		/** @var $actor Actor */
		for ($i = 100; $i < 200; $i++) {
			$actor = Actor::Find($i);
			if ($actor->NotFound()) continue;
			echo "window.mmx.AddActorTile(".$actor->ToJson().");";
		}

	}



}