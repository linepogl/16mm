<?php
require('_.php');

$iid_end_actor = TMDb::GetActorLatest()['id'] + 1;
$iid_end_movie = TMDb::GetMovieLatest()['id'] + 1;
$iid_end_chain = TMDb::GetChainLatest()['id'] + 1;
//$iid_end_actor = 999;
//$iid_end_movie = 999;
//$iid_end_chain = 999;
$iid_end = max($iid_end_actor,$iid_end_movie,$iid_end_chain);

$tmp_folder = Oxygen::GetDataFolder(true);
$log_folder = Oxygen::GetLogsFolder(true);
$log = function($what)use($log_folder) { $what=vsprintf($what,array_slice(func_get_args(),1)); $date = XDateTime::Now(); file_put_contents($log_folder.'/crawler.'.$date->Format('Ymd').'.log',$date->Format('Y-m-d H:i:s').' '.$what."\n",FILE_APPEND); };

$iid = intval(@file_get_contents("$tmp_folder/crawler"))+1;
if ($iid >= $iid_end) $iid = 1;

for ( ; $iid < $iid_end; $iid++) {

	if ($iid < $iid_end_actor) Actor::Find($iid)->Load();
	if ($iid < $iid_end_movie) Movie::Find($iid)->Load();
	if ($iid < $iid_end_chain) Chain::Find($iid)->Load();

	file_put_contents("$tmp_folder/crawler",$iid);
	if (TMDb::CountCalls() >= 15) break;
}
sleep(8);
if ($iid < $iid_end) {
	Http::Fire(Oxygen::GetHrefBaseFull().basename(__FILE__));
}
