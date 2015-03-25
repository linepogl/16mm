<?php

class Picture {

	public $Type;
	public $Path;
	public $Width;
	public $Height;

	public function Pack(){
		switch ($this->Type) {
			case 'profile': $x = 'P'; break;
			case 'backdrop': $x = 'Q'; break;
			case 'poster': $x = 'R'; break;
			default: return null;
		}
		return sprintf('%s%Xx%X%s',$x,$this->Width,$this->Height,$this->Path);
	}
	public static function Unpack($pack){
		switch ($pack[0]) {
			case 'P': $x = 'profile'; break;
			case 'Q': $x = 'backdrop'; break;
			case 'R': $x = 'poster'; break;
			default: return null;
		}
		$i = strpos($pack,'x'); if ($i === false) return null;
		$j = strpos($pack,'/'); if ($j === false) return null;
		$r = new Picture();
		$r->Type = $x;
		$r->Width = intval(substr($pack,1,$i-1),16) ?: null;
		$r->Height = intval(substr($pack,$i+1,$j-$i-1),16) ?: null;
		$r->Path = substr($pack,$j);
		return $r;
	}

}