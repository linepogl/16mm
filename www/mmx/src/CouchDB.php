<?php



class CouchDB {
	private static $host = '127.0.0.1';
	private static $port = 5984;


	private function __construct(){}
	private $code = 200;
	private $text = '';
	private $body = '';
	public function IsOK(){ return $this->code >= 200 && $this->code <= 299; }
	public function GetResultArray(){ return json_decode($this->body,true); }



	private static $calls = [];
	private static function call($method,$what,$data = null) {
		$req = "$method $what HTTP/1.0\r\n";
		$req .= "Host: ".self::$host."\r\n";
		$req .= "Accept: application/json,text/html,text/plain,*/*\r\n";
		if ($data !== null) {
			$req .= "Content-Type: application/json\r\n";
			$req .= "Content-Length: ".strlen($data)."\r\n";
			$req .= "\r\n";
			$req .= $data;
		}
		$req .= "\r\n";

		self::$calls[] = $req;

		$socket = fsockopen(self::$host,self::$port,$err_code,$err_string);
		fwrite($socket, $req);

		$r = new self();

		$head = true;
		$first = rtrim(fgets($socket));
		preg_match('/^HTTP[^ ]+ ([0-9]+) (.*)/',$first,$matches);
		$r->code = intval(@$matches[1]);
		$r->text = @$matches[2];

		while(!feof($socket)) {
			$line = fgets($socket);
			if ($head)
				$head = trim($line) !== '';
			else
				$r->body .= $line;
		}
		$r->body = substr($r->body,0,-1);

		return $r;
	}


	public static function Load($what) {
		return self::call('GET',$what);
	}
	public static function LoadMany($what,$ids) {
		return self::call('POST',$what.'?include_docs=true','{"keys":'.json_encode($ids).'}');
	}
	public static function Save($what,$data=array()) {
		return self::call('PUT',$what,$data === null ? null : json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}


}


