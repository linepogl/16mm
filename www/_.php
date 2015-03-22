<?php
require('oxy/_.php');
require('mmx/_.php');

Oxygen::SetTempFolder('../tmp');
Oxygen::SetDataFolder('../dat');
Oxygen::SetLogsFolder('../log');
Oxygen::SetDefaultActionName('16mm');
Oxygen::SetUrlRewriteFolderRules(['action'=>'16mm']);
//Oxygen::SetDatabaseManaged('localhost','mmx','root','');
Oxygen::Init();
