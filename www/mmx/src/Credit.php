<?php

class Credit {

	/** @var Actor */ public $actor;
	/** @var Movie */ public $movie;
	public $Characters = [];
	public $Jobs = [];

	public function GetExtra(){ return implode(', ',$this->Characters + $this->Jobs); }

	public static function FromActor(Actor $actor) {
		$r = [];
		$response = TMDb::GetActorCredits($actor->iid);
		$a = @$response['cast'];
		$b = @$response['crew'];
		$c = [];
		if (is_array($a)) $c = $c + $a;
		if (is_array($b)) $c = $c + $b;
		foreach ($c as $info) {
			$movie_type = @$info['media_type'];
			$movie_iid = @$info['id'];
			if ($movie_iid === null || $movie_type === null) continue;
			$movie = Movie::FindGeneric($movie_iid,$movie_type);
			if ($movie === null) continue;

			/** @var $x Credit */
			$x = null; foreach ($r as $x) { if ($x->movie->iid === $movie->iid && $x->movie->GetTMDbType() === $movie->GetTMDbType()) break; else $x = null; }
			if ($x === null) {
				$x = new Credit();
				$x->actor = $actor;
				$x->movie = $movie;
				$r[] = $x;
			}
			$s = @$info['character']; if ($s !== null && $s !== '') $x->Characters[] = str_replace(['himself','herself'],['Himself','Herself'],$s);
			$s = @$info['job']; if ($s !== null && $s !== '') $x->Jobs[] = $s;
		}
		usort( $r , function(Credit $c1,Credit $c2){ return $c2->movie->Year - $c1->movie->Year; } );
		return $r;
	}

	public static function FromMovie(Movie $movie) {
		$r = [];

		if ($movie instanceof Chain)
			$response = TMDb::GetChainCredits($movie->iid);
		else
			$response = TMDb::GetMovieCredits($movie->iid);

		$a = @$response['cast'];
		$b = @$response['crew'];
		$c = [];
		if (is_array($a)) $c = $c + $a;
		if (is_array($b)) $c = $c + $b;
		foreach ($c as $info) {
			$actor_iid = @$info['id'];
			if ($actor_iid === null) continue;
			$actor = Actor::Find($actor_iid);
			if ($actor === null) continue;

			/** @var $x Credit */
			$x = null; foreach ($r as $x) { if ($x->actor->iid === $actor->iid) break; else $x = null; }
			if ($x === null) {
				$x = new Credit();
				$x->actor = $actor;
				$x->movie = $movie;
				$r[] = $x;
			}
			$s = @$info['character']; if ($s !== null && $s !== '') $x->Characters[] = str_replace(['himself','herself'],['Himself','Herself'],$s);
			$s = @$info['job']; if ($s !== null && $s !== '') $x->Jobs[] = $s;
		}
		return $r;
	}

}