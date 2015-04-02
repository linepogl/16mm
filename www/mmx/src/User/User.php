<?php

class User extends XItem {


	public $Username;

	public static function FillMeta(XMeta $m){
		$m->Username = XMeta::String();
	}







	private static $has_checked_current = false;
	private static $current = null;
	public static function GetCurrent(){
		if (self::$current === null && !self::$has_checked_current) {
			$id = Scope::$SESSION['User::idCurrent'];
			self::$current = User::Pick($id);
			self::$has_checked_current = true;
		}
		return self::$current;
	}
	public static function SetCurrent(User $user = null){
		Scope::$SESSION['User::idCurrent'] = id($user);
		self::$current = $user;
		self::$has_checked_current = true;
	}
	public static function IsLoggedIn(){
		return self::GetCurrent() !== null;
	}

}