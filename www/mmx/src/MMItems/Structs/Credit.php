<?php

class Credit {

	/** @var Actor */ public $actor;
	/** @var Movie */ public $movie;
	public $Characters = [];
	public $Jobs = [];

	public function __construct(Movie $movie, Actor $actor){ $this->actor = $actor; $this->movie = $movie; }
	public function GetExtra(){ return implode(', ',array_merge($this->Characters,$this->Jobs)); }

}