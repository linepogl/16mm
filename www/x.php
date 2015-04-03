<?php
require('_.php');
Debug::EnableImmediateFlushing();


$iid_end_actor = TMDb::GetActorLatest()['id'];
$iid_end_movie = TMDb::GetMovieLatest()['id'];
$iid_end_chain = TMDb::GetChainLatest()['id'];

function ShowProgress() {
	global $iid_end_actor, $iid_end_movie, $iid_end_chain;
	$count = Database::ExecuteScalar('SELECT COUNT(`iid`) FROM `mmx_actor`')->AsInteger();
	Debug::Write('--> '.$count.' / '.$iid_end_actor.' actors ('.Language::FormatNumber(100 * $count / $iid_end_actor, 2).'%).');
	$count = Database::ExecuteScalar('SELECT COUNT(`iid`) FROM `mmx_movie`')->AsInteger();
	Debug::Write('--> '.$count.' / '.($iid_end_movie + $iid_end_chain).' movies ('.Language::FormatNumber(100 * $count / ($iid_end_movie + $iid_end_chain), 2).'%).');
	$count = Database::ExecuteScalar('SELECT COUNT(DISTINCT `iidActor`) FROM `mmx_credit` WHERE `iidActor` NOT IN (SELECT `iid` FROM `mmx_actor`)')->AsInteger();
	Debug::Write('--> '.$count.' hanging actors.');
	$count = Database::ExecuteScalar('SELECT COUNT(DISTINCT `iidMovie`) FROM `mmx_credit` WHERE `iidMovie` NOT IN (SELECT `iid` FROM `mmx_movie`)')->AsInteger();
	Debug::Write('--> '.$count.' hanging movies.');
}


ShowProgress();
$iids = Database::ExecuteColumnOf( MetaInteger::Type() , 'SELECT DISTINCT `iidActor` FROM `mmx_credit` WHERE `iidActor` NOT IN (SELECT `iid` FROM `mmx_actor`) LIMIT 500' );
if (!empty($iids)) {
	Debug::Write('### Importing up to 500 hanging actors.');
	Actor::Mass($iids);
}


ShowProgress();
$iids = Database::ExecuteColumnOf( MetaInteger::Type() , 'SELECT DISTINCT `iidMovie` FROM `mmx_credit` WHERE `iidMovie` NOT IN (SELECT `iid` FROM `mmx_movie`) LIMIT 300' );
if (!empty($iids)) {
	Debug::Write('### Importing up to 300 hanging movies.');
	Movie::Mass($iids);
}


ShowProgress();
for ($min = 1; ;$min += 1000) { $max = $min + 999;
	$iids = range($min,min($max,$iid_end_actor));
	$iids = array_diff($iids , Database::ExecuteColumnOf(MetaInteger::Type(),'SELECT `iid` FROM `mmx_actor` WHERE `iid`>=? AND `iid`<=?',$min,$max) );
	if (!empty($iids)) {
		Debug::Write("### Importing from actor range $min -> $max.");
		Actor::Mass( array_slice( $iids , 0 , 500 ) );
		break;
	}
}


ShowProgress();
for ($min = 1; ;$min += 1000) { $max = $min + 999;
	$iids = range($min,min($max,$iid_end_movie));
	$iids = array_diff($iids , Database::ExecuteColumnOf(MetaInteger::Type(),'SELECT `iid` FROM `mmx_movie` WHERE `iid`>=? AND `iid`<=?',$min,$max) );
	if (!empty($iids)) {
		Debug::Write("### Importing from movie range $min -> $max.");
		Movie::Mass( array_slice( $iids , 0 , 300 ) );
		break;
	}
}


ShowProgress();
for ($max = -1; ;$max -= 1000) { $min = $max - 999;
	$iids = range(max($min,-$iid_end_chain),$max);
	$iids = array_diff($iids , Database::ExecuteColumnOf(MetaInteger::Type(),'SELECT `iid` FROM `mmx_movie` WHERE `iid`>=? AND `iid`<=?',$min,$max) );
	if (!empty($iids)) {
		Debug::Write("### Importing from movie range $max -> $min.");
		Movie::Mass( array_slice( array_reverse($iids) , 0 , 300 ) );
		break;
	}
}


Debug::Write("Done.\n\n\n");
ShowProgress();



echo Js::BEGIN;
echo "window.location.href = window.location.href;";
echo Js::END;
die;
