<?php

class ActionHome extends Action {

	public function IsPermitted() {
		return true;
	}

	public function Render() {

//		echo 'xxx';

//			$c = TMDb::GetMovieInfo(100);
//			$c = TMDb::GetTVInfo(100);
//			$c = TMDb::GetTVCredits(100);
//			$c = TMDb::GetActorInfo(100);
//			$c = TMDb::GetActorCredits(1836);
//			$c = TMDb::GetActorCredits(1000);

//			$c = TMDb::GetActorCredits(1836);
//		dump($c);



		echo Js::BEGIN;
		for ($i = 100; $i < 200; $i++) {
			$c = Movie::Find($i);
			if ($c === null) continue;
			echo "window.mmx.GetTitleTile({$c->ToJson()});";
		}
		for ($i = 100; $i < 200; $i++) {
			$c = Chain::Find($i);
			if ($c === null) continue;
			echo "window.mmx.GetTitleTile({$c->ToJson()});";
		}
		echo Js::END;


	}
}