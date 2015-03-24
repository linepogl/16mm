<?php
Database::SetPatchingSystem('mmx','mmx');
Database::AddPatcher('li','VersionLi');


if (Database::BeginPatchingSystem()) {
	Database::ExecuteCreateStandardTable('mmx','VersionLi',Sql::Integer);
	Database::ExecuteInsert('mmx','id',new ID(0),'VersionLi',0);
}

if (Database::BeginPatch("li",1,"TMDb")) {
	Database::ExecuteCreateTable('mmx_tmdb', 'Type', Sql::Integer, 'id', Sql::ID, 'Data', Sql::Text);
	Database::ExecuteAddPrimaryKey('mmx_tmdb','Type','id');
	Database::ApplyPatch();
}
