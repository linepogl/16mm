<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
require('oxy/_.php');
require('mmx/_.php');

Oxygen::SetTempFolder('../tmp');
Oxygen::SetDataFolder('../dat');
Oxygen::SetLogsFolder('../log');
Oxygen::SetDefaultActionName('16mm');
Oxygen::SetUrlRewriteFolderRules(['action'=>'16mm']);
//Oxygen::SetDatabaseManaged('localhost','mmx','root','');
Oxygen::Init();
