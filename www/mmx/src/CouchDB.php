<?php

class CouchDB {


	private static $base = 'http://127.0.0.1:5984/tmdb/';

	public static function Save($type,$iid,$data) {
		$url = self::$base.$type.'_'.$iid;
		$url = self::$base.'234243';
		$data = '{}';

		//try {
		//try {
			$r = Http::Call($url,'PUT',[],$data,'application/json');
		//}
		//catch (Exception $ex) {
		//	dump($ex,null);
		//	die;
		//}

		//$r = self::call('/tmdb/'.$type.'_'.$iid,'DELETE',null);
		//dump($r);

		//$data = '{"data":'.$data.'}';
		//$r = self::call('/tmdb/'.$type.'_'.$iid,'PUT',$data);
		//dump($r);
	}

	public static function Load($type,$iid) {
		$url = self::$base.$type.'_'.$iid;
		//try {
		//	$s = Http::Call($url);
		//}
		//catch (Exception $ex){
			$s = null;
		//}
		return $s;
	}



	private static function call($relative_url,$method,$data){
		$req = "$method $relative_url HTTP/1.0\r\nHost: 127.0.0.1\r\n";
		$req.="Accept: application/json,text/html,text/plain,*/*\r\n";
		$req .= 'Content-Type: application/json'."\r\n";

		if ($data) {
				$req .= 'Content-Length: '.strlen($data)."\r\n\r\n";
				$req .= $data."\r\n";
		} else {
				$req .= "\r\n";
			}


		$socket = @fsockopen('127.0.0.1',5984, $err_num, $err_string);

		fwrite($socket, $req);
		$response = '';
		while(!feof($socket))
			$response .= fgets($socket);


		return $response;

	}






}


