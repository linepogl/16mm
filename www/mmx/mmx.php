<?php
require('mmx_dictionary.php');
if (false) { class mmx extends _mmx {} }
abstract class _mmx extends _mmx_dictionary {

	//
	//
	// Icons
	//
	//

	// 000 - Basic
	public static function icoActor                 (){ return new Glyph('mmx-icon',0xE002); }

	public static function icoPeople                (){ return new Glyph('mmx-icon',0xE002); }
	public static function icoTitles                (){ return new Glyph('mmx-icon',0xE003); }


	public static function icoHome                  (){ return new Glyph('mmx-icon',0xE001); }
	public static function icoPerson                (){ return new Glyph('mmx-icon',0xE002); }
	public static function icoMovie                 (){ return new Glyph('mmx-icon',0xE003); }

	public static function icoRatingBest            (){ return new Glyph('mmx-icon',0xE101); }
	public static function icoRatingOkay            (){ return new Glyph('mmx-icon',0xE102); }
	public static function icoRatingSoSo            (){ return new Glyph('mmx-icon',0xE103); }
	public static function icoRatingFail            (){ return new Glyph('mmx-icon',0xE105); }
	public static function icoRatingHalf            (){ return new Glyph('mmx-icon',0xE104); }
	public static function icoRatingStar            (){ return new Glyph('mmx-icon',0xE201); }

	public static function icoRatingNone            (){ return new Glyph('mmx-icon',0xE100); }
	public static function icoTarget            (){ return new Glyph('mmx-icon',0xE201); }



	public static function FormatCountry($code) { return ucwords(strtolower(oxy::txtCountry_($code))); }
	public static function FormatLanguage($code) { return ucwords(strtolower(oxy::txtLang_($code))); }

}
Oxygen::RegisterResourceManager('mmx','_mmx');
