<?php

class Credit {

	/** @var Actor */ public $actor;
	/** @var Movie */ public $movie;
	public $Characters = [];
	public $Jobs = [];
	public $IsCast = false;
	public $IsCrew = false;

	public function __construct(Movie $movie, Actor $actor){ $this->actor = $actor; $this->movie = $movie; }
	public function GetExtra(){ return implode(', ',array_map(function($x){return $x->Name;},array_merge($this->Characters,$this->Jobs))); }

	public function PackForActor(){
		switch ($this->movie->Type) {
			case Movie::TMDb_TYPE: $x = $this->IsCast&&$this->IsCrew?'A': ($this->IsCast?'B':'C'); break;
			case Chain::TMDb_TYPE: $x = $this->IsCast&&$this->IsCrew?'D': ($this->IsCast?'E':'F'); break;
			default: return '!';
		}
		return sprintf("%s%X%s%s",$x,$this->movie->iid
			,implode(array_map(function($x){/**@var $x Job       */return $x->Pack();},$this->Jobs))
			,implode(array_map(function($x){/**@var $x Character */return $x->Pack();},$this->Characters))
		);
	}
	public function PackForMovie(){
		$x = $this->IsCast&&$this->IsCrew?'A': ($this->IsCast?'B':'C');
		return sprintf("%s%X%s%s",$x,$this->actor->iid
			,implode(array_map(function($x){/**@var $x Job       */return $x->Pack();},$this->Jobs))
			,implode(array_map(function($x){/**@var $x Character */return $x->Pack();},$this->Characters))
		);
	}

	public static function UnpackForActor(Actor $actor,$pack){
		preg_match( '/^([A-F])([0-9A-F]+)((~[^`]+)*)((`[^`]+)*)$/' , $pack , $matches );
		$count = count($matches);
		if ($count < 3) return null;
		$iid = intval($matches[2],16);
		switch ($matches[1]) {
			case 'A': $r = new Credit(Movie::Find($iid),$actor); $r->IsCrew = true; $r->IsCast = true; break;
			case 'B': $r = new Credit(Movie::Find($iid),$actor); $r->IsCast = true; break;
			case 'C': $r = new Credit(Movie::Find($iid),$actor); $r->IsCrew = true; break;
			case 'D': $r = new Credit(Chain::Find($iid),$actor); $r->IsCrew = true; $r->IsCast = true; break;
			case 'E': $r = new Credit(Chain::Find($iid),$actor); $r->IsCast = true; break;
			case 'F': $r = new Credit(Chain::Find($iid),$actor); $r->IsCrew = true; break;
			default: return null;
		}
		if (isset($matches[3])) $r->Jobs = Job::UnpackMany($matches[3]);
		if (isset($matches[5])) $r->Characters = Character::UnpackMany($matches[5]);
		return $r;
	}

	public static function UnpackForMovie(Movie $movie,$pack){
		preg_match( '/^([A-C])([0-9A-F]+)((~[^`]+)*)((`[^`]+)*)$/' , $pack , $matches );
		$count = count($matches);
		if ($count < 3) return null;
		$iid = intval($matches[2],16);
		switch ($matches[1]) {
			case 'A': $r = new Credit($movie,Actor::Find($iid)); $r->IsCrew = true; $r->IsCast = true; break;
			case 'B': $r = new Credit($movie,Actor::Find($iid)); $r->IsCast = true; break;
			case 'C': $r = new Credit($movie,Actor::Find($iid)); $r->IsCrew = true; break;
			default: return null;
		}
		if (isset($matches[3])) $r->Jobs = Job::UnpackMany($matches[3]);
		if (isset($matches[5])) $r->Characters = Character::UnpackMany($matches[5]);
		return $r;
	}




}