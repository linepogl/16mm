<?php
require('_.php');

Debug::EnableImmediateFlushing();

//$x = Actor::ImportFromTMDb(1);
//$x = Movie::ImportFromTMDb(2);
//$x = Movie::ImportFromTMDb(140979);
//$x = Movie::ImportFromTMDb(-4194);


//$a = Movie::Mass([100,200,300,-400,500,600,-700,800,900,-1000,1100]);
//$a = Movie::Mass([100]);
//dump($a,[1000,1000,1000,1000,1000]);


$count = Database::ExecuteScalar( 'SELECT COUNT(`iidActor`) FROM `mmx_credit` WHERE `iidActor` NOT IN (SELECT `iid` FROM `mmx_actor`)' )->AsInteger();
Debug::Write($count.' missing actors.');
$count = Database::ExecuteScalar( 'SELECT COUNT(`iidMovie`) FROM `mmx_credit` WHERE `iidMovie` NOT IN (SELECT `iid` FROM `mmx_movie`)' )->AsInteger();
Debug::Write($count.' missing movies.');


Actor::ImportFromTMDb(718);
die;


$iids = Database::ExecuteColumnOf( MetaInteger::Type() , 'SELECT `iidActor` FROM `mmx_credit` WHERE `iidActor` NOT IN (SELECT `iid` FROM `mmx_actor`) LIMIT 100' );
if (!empty($iids)) {
	Debug::Write('Importing 100 missing actors.');
	Actor::Mass($iids);
}

$iids = Database::ExecuteColumnOf( MetaInteger::Type() , 'SELECT `iidMovie` FROM `mmx_credit` WHERE `iidMovie` NOT IN (SELECT `iid` FROM `mmx_movie`) LIMIT 100' );
if (!empty($iids)) {
	Debug::Write('Importing 100 missing movies.');
	Movie::Mass($iids);
}


for ($i = 1; ;$i += 100) {
	$iids = range($i,$i + 99);
	$iids = array_diff($iids , Database::ExecuteColumnOf(MetaInteger::Type(),'SELECT `iid` FROM `mmx_actor` WHERE `iid` IN '.new Sql($iids)) );
	if (!empty($iids)) {
		Debug::Write('Importing actor range '.$i.' -> '.($i+99).'.');
		Actor::Mass($iids);
		break;
	}
}

for ($i = 1; ;$i += 100) {
	$iids = range($i,$i + 99);
	$iids = array_diff($iids , Database::ExecuteColumnOf(MetaInteger::Type(),'SELECT `iid` FROM `mmx_movie` WHERE `iid` IN '.new Sql($iids)) );
	if (!empty($iids)) {
		Debug::Write('Importing movie range '.$i.' -> '.($i+99).'.');
		Movie::Mass($iids);
		break;
	}
}


for ($i = -1; ;$i -= 100) {
	$iids = range($i-99,$i);
	$iids = array_diff($iids , Database::ExecuteColumnOf(MetaInteger::Type(),'SELECT `iid` FROM `mmx_movie` WHERE `iid` IN '.new Sql($iids)) );
	if (!empty($iids)) {
		$iids = array_reverse($iids);
		Debug::Write('Importing movie range '.$i.' -> '.($i-99).'.');
		Movie::Mass($iids);
		break;
	}
}


Debug::Write("Done.\n\n\n");
