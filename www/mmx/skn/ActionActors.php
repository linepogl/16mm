<?php

class ActionActors extends MMAction {

	public function RenderJavascript(){
		echo "window.mmx.AddSeparator('Actors');";

		/** @var $actor Actor */
		foreach ([100,101,102,103,104,105,106,108,110] as $iid) {
			$actor = Actor::Find($iid);
			if (!$actor->Found) continue;
			echo "window.mmx.AddActorTile(".$actor->ToJson().");";
		}

		echo "window.mmx.AddSeparator('Movies');";
		foreach ([2,3,5,6,8,9,11] as $iid) {
			$movie = Movie::Find($iid);
			if (!$movie->Found) continue;
			echo "window.mmx.AddMovieTile(".$movie->ToJson().");";
		}

		echo "window.mmx.AddSeparator('Chains');";
		foreach ([1,2,3,4,5,6,7,8,9,10] as $iid) {
			$chain = Chain::Find($iid);
			if (!$chain->Found) continue;
			echo "window.mmx.AddMovieTile(".$chain->ToJson().");";
		}

	}



}