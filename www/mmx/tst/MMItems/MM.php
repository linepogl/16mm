<?php

class MM {


	/** @return Actor */
	public static function ImportActorFromTMDb($iid) {
		$info = TMDb::GetActorInfo($iid);

	}

	/** @return Movie */
	public static function ImportMovieFromTMDb($iid) {
		if ($iid > 0) {

		}
		else {

		}
	}

}