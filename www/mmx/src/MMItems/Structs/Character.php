<?php

class Character {

	public $Name;
	public $Episodes;
	public function __construct($name = null,$episodes = null){ $this->Name = $name; $this->Episodes = $episodes; }

	const DELIMITER = '`';

	public function Pack() {
		return self::DELIMITER.$this->Name.($this->Episodes > 0 ? sprintf(':%X',$this->Episodes) : '');
	}

	public static function Unpack($string) {
		$i1 = strpos($string,self::DELIMITER);  if ($i1 !== 0) return null;
		$i2 = stripos($string,':');
		$r = new self();
		if ($i2 === false) {
			$r->Name = substr($string,1);
		}
		else {
			$r->Name = substr($string,1,$i2-1);
			$r->Episodes = intval(substr($string,$i2+1),16);
		}
		return $r;
	}

	public static function UnpackMany($s) {
		$r = [];
		while ($s !== '') {
			$i = strpos($s,self::DELIMITER,1);
			if ($i === false) { $ss = $s; $s = ''; } else { $ss = substr($s, 0, $i); $s = substr($s,$i); }
			$x = self::Unpack($ss);
			if ($x !== null) $r[] = $x;
		}
		return $r;
	}

}