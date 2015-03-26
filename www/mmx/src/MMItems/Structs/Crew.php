<?php

class Crew {

	public $Name;
	public $Episodes;
	public function __construct($name = null,$episodes = null){ $this->Name = $name; $this->Episodes = $episodes; }

	public function GetCaption(){ return $this->Name; }

}