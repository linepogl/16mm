<?php

define('DAILY',XDate::Today()->Format('Ymd'));
define('WEEKLY',XDate::Today()->Format('Y\wW'));
define('MONTHLY',XDate::Today()->Format('Ym'));

define('ACTOR_FOLDER',Oxygen::GetDataFolder().'/actor');
define('MOVIE_FOLDER',Oxygen::GetDataFolder().'/movie');
define('CHAIN_FOLDER',Oxygen::GetDataFolder().'/chain');

class TMDb {
	private static $api_base = 'http://api.themoviedb.org/3/';
	private static $api_key = '5575cbca2608451f50c914555304c4d8';

	private static $calls = [];
	private static $checked_nulls = [];
	private static function Call($service,$cache_key = null){
		$use_cache = $cache_key !== null;
		$key = "TMDb:$cache_key:$service";
		$r = $use_cache ? Scope::$APPLICATION[$key] : null;
		if ($r === null && !array_key_exists($key,self::$checked_nulls)) {
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
			if ($r === null || isset($r['status_code'])) { self::$checked_nulls[$key] = true; return null; }
			$r['timestamp'] = XDateTime::Now()->GetTimestamp();
			if ($use_cache) Scope::$APPLICATION[$key] = $r;
		}
		return $r;
	}
	public static function CountCalls(){ return count(self::$calls); }





	public static function GetConfiguration($p=DAILY){
		return self::Call( "configuration" , $p );
	}
	public static function GetActorLatest($p=DAILY){
		return self::Call( "person/latest" , $p );
	}
	public static function GetMovieLatest($p=DAILY){
		return self::Call( "movie/latest" , $p );
	}
	public static function GetChainLatest($p=DAILY){
		return self::Call( "tv/latest" , $p );
	}

	public static function GetMovieCredits($iid,$p=WEEKLY){
		return self::Call( "movie/$iid/credits" , $p );
	}
	public static function GetMovieImages($iid,$p=WEEKLY){
		return self::Call( "movie/$iid/images" , $p );
	}
	public static function GetMovieSimilarMovies($iid,$page = 1,$p=WEEKLY){
		return self::Call( "movie/$iid/similar_movies?page=$page" , $p );
	}
	public static function GetActorCredits($iid,$p=WEEKLY){
		return self::Call( "person/$iid/combined_credits" , $p );
	}
	public static function GetActorImages($iid,$p=WEEKLY){
		return self::Call( "person/$iid/images" , $p );
	}
	public static function GetChainCredits($iid,$p=WEEKLY){
		return self::Call( "tv/$iid/credits" , $p );
	}
	public static function GetChainImages($iid,$p=WEEKLY){
		return self::Call( "tv/$iid/images" , $p );
	}
	public static function Search($searchstring,$page = 1,$p=DAILY){
		return self::Call( "search/multi?query=".new Url($searchstring).'&page='.$page , $p);
	}
	public static function SearchMovie($searchstring,$page = 1,$p=DAILY){
		return self::Call( "search/movie?query=".new Url($searchstring).'&page='.$page , $p);
	}
	public static function SearchChain($searchstring,$page = 1,$p=DAILY){
		return self::Call( "search/tv?query=".new Url($searchstring).'&page='.$page , $p);
	}
	public static function SearchActor($searchstring,$page = 1,$p=DAILY){
		return self::Call( "search/person?query=".new Url($searchstring).'&page='.$page , $p);
	}



	private static function Save($f,$data) {
		if ($data !== null) {
			@Fs::Ensure(dirname($f));
			if ($fp = @fopen($f,'w')) {
				@fprintf($fp,'<?php return %s;',var_export($data,true));
				@fclose($fp);
			}
		}
		return $data;
	}
	private static function GetDataPath($folder,$iid) {
		return sprintf('%s/%03d/%03d/%03d.php',$folder,$iid/1000000%1000,$iid/1000%1000,$iid%1000);
	}
	public static function HasActorInfo($iid) { return file_exists(self::GetDataPath(ACTOR_FOLDER,$iid)); }
	public static function HasMovieInfo($iid) { return file_exists(self::GetDataPath(MOVIE_FOLDER,$iid)); }
	public static function HasChainInfo($iid) { return file_exists(self::GetDataPath(CHAIN_FOLDER,$iid)); }
	public static function GetActorInfo($iid,$force=false){
		$f = self::GetDataPath(ACTOR_FOLDER,$iid);
		return file_exists($f) && !$force ? include($f) : self::Save($f,self::Call("person/$iid?append_to_response=combined_credits,images" )); }
	public static function GetMovieInfo($iid,$force=false){ $f = self::GetDataPath(MOVIE_FOLDER,$iid); return file_exists($f) && !$force ? include($f) : self::Save($f,self::Call("movie/$iid?append_to_response=credits,keywords,images"  )); }
	public static function GetChainInfo($iid,$force=false){ $f = self::GetDataPath(CHAIN_FOLDER,$iid); return file_exists($f) && !$force ? include($f) : self::Save($f,self::Call("tv/$iid?append_to_response=credits,keywords,images"     )); }







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




}


