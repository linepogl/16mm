<?php

define('_DAILY',XDate::Today()->Format('Ymd'));
define('_WEEKLY',XDate::Today()->Format('Y\wW'));
define('_MONTHLY',XDate::Today()->Format('Ym'));


class TMDb {
	private static $api_base = 'http://api.themoviedb.org/3/';
	private static $api_key = '5575cbca2608451f50c914555304c4d8';

	const DAILY = _DAILY;
	const WEEKLY = _WEEKLY;
	const MONTHLY = _MONTHLY;

	public static $calls = [];
	private static function Call($service,$cache_key = null){
		$key = 'TMDb::'.$service.($cache_key===null?'':'::'.$cache_key);
		$r = Scope::$APPLICATION->HARD[ $key ];
		if ($r === null) {
			$url = self::$api_base . $service . (strrpos($service,'?')===false?'?':'&') . 'api_key='. self::$api_key.'&lang=en&include_adult=true';
			self::$calls[] = $url;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
			$r = curl_exec($ch);
			curl_close($ch);
			$r = json_decode($r,true);
			Scope::$APPLICATION->HARD[$key] = $r;
		}
    if (isset($r['status_code'])) return null;
		return $r;
	}



	public static function GetConfiguration($p=self::DAILY){
		return self::Call( "configuration" , $p );
	}
	public static function GetMovieInfo($id,$p=self::WEEKLY){
		return self::Call( "movie/$id?append_to_response=credits,keywords,images" , $p );
	}
	public static function GetMovieCredits($id,$p=self::WEEKLY){
		return self::Call( "movie/$id/credits" , $p );
	}
	public static function GetMovieImages($id,$p=self::WEEKLY){
		return self::Call( "movie/$id/images" , $p );
	}
	public static function GetMovieSimilarMovies($id,$page = 1,$p=self::WEEKLY){
		return self::Call( "movie/$id/similar_movies?page=$page" , $p );
	}
	public static function GetActorInfo($id,$p=self::WEEKLY){
		return self::Call( "person/$id?append_to_response=combined_credits,images" , $p );
	}
	public static function GetActorCredits($id,$p=self::WEEKLY){
		return self::Call( "person/$id/combined_credits" , $p );
	}
	public static function GetActorImages($id,$p=self::WEEKLY){
		return self::Call( "person/$id/images" , $p );
	}

	public static function GetTVInfo($id,$p=self::WEEKLY){
		return self::Call( "tv/$id?append_to_response=credits,keywords,images" , $p );
	}
	public static function GetTVCredits($id,$p=self::WEEKLY){
		return self::Call( "tv/$id/credits" , $p );
	}
	public static function GetTVImages($id,$p=self::WEEKLY){
		return self::Call( "tv/$id/images" , $p );
	}

	public static function Search($searchstring,$page = 1){
		return self::Call( "search/multi?query=".new Url($searchstring).'&page='.$page);
	}
	public static function SearchMovie($searchstring,$page = 1){
		return self::Call( "search/movie?query=".new Url($searchstring).'&page='.$page);
	}
	public static function SearchTV($searchstring,$page = 1){
		return self::Call( "search/tv?query=".new Url($searchstring).'&page='.$page);
	}
	public static function SearchActor($searchstring,$page = 1){
		return self::Call( "search/person?query=".new Url($searchstring).'&page='.$page);
	}





	const BACKDROP_W300 = 'w300';
	const BACKDROP_W780 = 'w780';
	const BACKDROP_W1280 = 'w1280';
	const BACKDROP_ORIGINAL = 'original';
	const PROFILE_W45 = 'w45';
	const PROFILE_W185 = 'w185';
	const PROFILE_H632 = 'h632';
	const PROFILE_ORIGINAL = 'original';
	const POSTER_W92 = 'w92';
	const POSTER_W154 = 'w154';
	const POSTER_W185 = 'w185';
	const POSTER_W342 = 'w342';
	const POSTER_W500 = 'w500';
	const POSTER_ORIGINAL = 'original';
	public static function GetImageSrc( $path , $size ) {
		$base = self::GetConfiguration()['images']['base_url'];
		return "$base$size$path";
	}






	const DB_TABLE_NAME = 'mmx_tmdb';
	const DB_ACTOR = 1;
	const DB_MOVIE = 2;
	const DB_CHAIN = 3;
	private static function LoadFromDB($type,$iid) {
		$key = 'TMDb:'.$type.':'.$iid;
		$data = Scope::$APPLICATION[$key];
		if ($data===null) {
			$data = Database::ExecuteScalar('SELECT '.new SqlIden('Data').' FROM '.new SqlIden(self::DB_TABLE_NAME).' WHERE '.new SqlIden('Type').'=? AND '.new SqlIden('id').'=?',$type,$iid);
			Scope::$APPLICATION[$key] = $data;
		}
		return $data;
	}
	private static function SaveIntoDB($type,$iid,$data){
		$key = 'TMDb:'.$type.':'.$iid;
		if ($data === null)
			Database::Execute('DELETE FROM '.new SqlIden(self::DB_TABLE_NAME).' WHERE '.new SqlIden('Type').'=? AND '.new SqlIden('id').'=?',$type,$iid);
		elseif (0 === Database::ExecuteScalar('SELECT COUNT(*) FROM '.new SqlIden(self::DB_TABLE_NAME).' WHERE '.new SqlIden('Type').'=? AND '.new SqlIden('id').'=?',$type,$iid))
			Database::Execute('INSERT INTO '.new SqlIden(self::DB_TABLE_NAME).' ('.new SqlIden('Type').','.new SqlIden('id').','.new SqlIden('Data').') VALUES (?,?,?)',$type,$iid,$data);
		else
			Database::Execute('UPDATE '.new SqlIden(self::DB_TABLE_NAME).' SET '.new SqlIden('Data').'=? WHERE '.new SqlIden('Type').'=? AND '.new SqlIden('id').'=?',$data,$type,$iid);
		Scope::$APPLICATION[$key] = $data;
		return $data;
	}


}



