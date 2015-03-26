<?php

class Credit {

	/** @var Actor */ public $actor;
	/** @var Movie */ public $movie;
	public $Cast = [];
	public $Crew = [];

	public function __construct(Movie $movie, Actor $actor){ $this->actor = $actor; $this->movie = $movie; }
	public function GetExtra(){ return implode(', ',array_map(function(/** @var $x Cast|Crew */$x){return $x->GetCaption();},array_merge($this->Cast,$this->Crew))); }




}