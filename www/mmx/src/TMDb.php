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
		return self::Call( "movie/$id" , $p );
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
		return self::Call( "person/$id" , $p );
	}
	public static function GetActorCredits($id,$p=self::WEEKLY){
		return self::Call( "person/$id/combined_credits" , $p );
	}
	public static function GetActorImages($id,$p=self::WEEKLY){
		return self::Call( "person/$id/images" , $p );
	}

	public static function GetChainInfo($id,$p=self::WEEKLY){
		return self::Call( "tv/$id" , $p );
	}
	public static function GetChainCredits($id,$p=self::WEEKLY){
		return self::Call( "tv/$id/credits" , $p );
	}
	public static function GetChainImages($id,$p=self::WEEKLY){
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




//	/** @return XDate */
//	private static function parse_date($string){
//		return empty($string)?null:XDate::MakeDate(intval(substr($string,0,4)),intval(substr($string,4,2)),intval(substr($string,6,2)));
//	}
//
//	private static $data = null;
//	private static function LoadData(){
//		$filename = Oxygen::GetDataFolder(true) . '/data.json';
//		if (file_exists($filename))
//			self::$data = json_decode(file_get_contents($filename),true);
//		else
//			self::$data = ['actors' => [],'movies' => []];
//	}
//	public static function SaveData(){
//		$filename = Oxygen::GetDataFolder(true) . '/data.json';
//		file_put_contents($filename,json_encode(self::$data));
//	}
//
//	public static function PinActor(Actor $actor,$pin){
//		if($pin == 0)
//			unset(self::$data['actors'][$actor->id]);
//		else
//			self::$data['actors'][$actor->id]=$pin;
//		return $pin;
//	}
//	public static function GetActorPin(Actor $actor){
//		return isset(self::$data['actors'][$actor->id]) ? self::$data['actors'][$actor->id] : 0;
//	}
//	public static function GetPinnedActors($pin = null){
//		$r = [];
//		foreach (self::$data['actors'] as $id => $pin2)
//			if ($pin === null || $pin === $pin2)
//				$r[$id] = Actor::Pick($id);
//		return $r;
//	}
//
//	public static function PinMovie(Movie $movie,$pin){
//		if($pin == 0)
//			unset(self::$data['movies'][$movie->id]);
//		else
//			self::$data['movies'][$movie->id]=$pin;
//		return $pin;
//	}
//	public static function GetMoviePin(Movie $movie){
//		return isset(self::$data['movies'][$movie->id]) ? self::$data['movies'][$movie->id] : 0;
//	}
//	public static function GetPinnedMovies($pin = null){
//		$r = [];
//		foreach (self::$data['movies'] as $id => $pin2)
//			if ($pin === null || $pin2 === $pin)
//				$r[$id] = Movie::Pick($id);
//		return $r;
//	}



}



