<?php
Database::SetPatchingSystem('mmx','mmx');
Database::AddPatcher('li','VersionLi');


if (Database::BeginPatchingSystem()) {
	Database::ExecuteCreateStandardTable('mmx','VersionLi',Sql::Integer);
	Database::ExecuteInsert('mmx','id',new ID(0),'VersionLi',0);
}

if (Database::BeginPatch('li',1,'TMDb')) {

	Database::ExecuteCreateTable('mmx_actor'
		,'iid',Sql::Integer
		,'Info',Sql::Text
		,'Time',Sql::DateTime
	);
	Database::ExecuteCreateTable('mmx_movie'
		,'iid',Sql::Integer
		,'Info',Sql::Text
		,'Time',Sql::DateTime
	);
	Database::ExecuteCreateTable('mmx_credit'
		,'iidActor',Sql::Integer
		,'iidMovie',Sql::Integer
		,'Info',Sql::Text
		,'Rank',Sql::Integer
		,'Date',Sql::DateTime
		,'Time',Sql::DateTime
	);

	Database::ApplyPatch();
}

