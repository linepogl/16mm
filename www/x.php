<?php
require('_.php');
//dump(TMDb::Find('tt0455275'));
//dump(TMDb::SearchChain('Prison break'));
//dump(TMDb::GetChainInfo(2288));
//die;
//$x = Actor::Find(1);
//$x = Actor::Find(1)->Load();
//$x = Movie::Find(100)->Load();
//$x = Chain::Find(500);
//$x->Load();

//$x = TMDb::GetMovieInfo(100);
//$x = TMDb::GetMovieInfo(1200);
//$x = TMDb::GetChainInfo(500)->Load();

//$x = CouchDB::Save('movie',1,'{"test":"test"}');
//$x = CouchDB::Load('movie',1);


//$x = Chain::Find(100)->Load();
//$x = Actor::Find(1)->Load();

//foreach ($x->Credits as $credit) {
//	dump($credit->movie->Load());
//}

//dump($x,null);

//$x = Movie::Find(2)->Load();
//$x = Movie::Find(3)->Load();
//$x = Movie::Find(4)->Load();
//$x = Movie::Find(5)->Load();
$x = Actor::Find(-5)->Load();




die;
$f = '../dat/actor';
//$f = '../dat/movie';
//$f = '../dat/chain';
foreach (Fs::BrowseRecursively($f,'*',Fs::BROWSE_NO_FOLDERS) as $ff) {

	$a = include("$f/$ff");

	dump($a);

//	$a = TMDb::FilterActor($a);
//	$s = TMDb::Export($a);
//	file_put_contents("$f/$ff",'<?php return '.$s.';');

	//	file_put_contents("$f/$ff",'<?php return '.file_get_contents("$f/$ff").';');
	//	dump($s,null);
	//	$s = export($a);
//		dump($s,null);

		break;
}
