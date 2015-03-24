<?php
require('_.php');

$iid_end_actor = TMDb::GetActorLatest()['id'] + 1;
$iid_end_movie = TMDb::GetMovieLatest()['id'] + 1;
$iid_end_chain = TMDb::GetChainLatest()['id'] + 1;
$iid_end = max($iid_end_actor,$iid_end_movie,$iid_end_chain);

$log_folder = Oxygen::GetLogsFolder(true);
$log = function($what)use($log_folder) { $what=vsprintf($what,array_slice(func_get_args(),1)); $date = XDateTime::Now(); file_put_contents($log_folder.'/crawler.'.$date->Format('Ymd').'.log',$date->Format('Y-m-d H:i:s').' '.$what."\n",FILE_APPEND); };

$max_calls = 15;
for ($iid = intval(@$_GET['iid']); $iid < $iid_end; $iid++) {

	if ($iid < $iid_end_actor && !TMDb::HasActorInfo($iid)) {
		$data = TMDb::GetActorInfo($iid);
		$log('Actor %s %s (%s)',$iid,(new ID($iid))->AsHex(),$data === null?'null':'save');
		if (TMDb::CountCalls() >= $max_calls) break;
	}

	if ($iid < $iid_end_movie && !TMDb::HasMovieInfo($iid)) {
		$data = TMDb::GetMovieInfo($iid);
		$log('Movie %s %s (%s)',$iid,(new ID($iid))->AsHex(),$data === null?'null':'save');
		if (TMDb::CountCalls() >= $max_calls) break;
	}

	if ($iid < $iid_end_movie && !TMDb::HasActorInfo($iid)) {
		$data = TMDb::GetActorInfo($iid);
		$log('Chain %s %s (%s)',$iid,(new ID($iid))->AsHex(),$data === null?'null':'save');
		if (TMDb::CountCalls() >= $max_calls) break;
	}

}

sleep(8);
if ($iid < $iid_end) {
	Http::Fire(Oxygen::GetHrefBaseFull().basename(__FILE__).'?iid='.$iid);
}
