<?php

class ActionActors extends MMAction {

	public function RenderJavascript(){
		echo "window.mmx.AddSeparator('Actors');";

		/** @var $actor Actor */
		foreach (Actor::Mass([100,101,102,103,104,105,106,108,110]) as $actor) {
			if (!$actor->Found) continue;
			echo "window.mmx.AddActorTile(".$actor->ToJson().");";
		}

		echo "window.mmx.AddSeparator('Movies');";
		/** @var $movie Movie */
		foreach (Movie::Mass([2,3,5,6,8,9,11,-1,-2,-3,-4,-5,-6,-7,-8]) as $movie) {
			if (!$movie->Found) continue;
			echo "window.mmx.AddMovieTile(".$movie->ToJson().");";
		}

	}



}