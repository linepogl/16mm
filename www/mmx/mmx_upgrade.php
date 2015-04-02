<?php
Database::SetPatchingSystem('mmx','mmx');
Database::AddPatcher('li','VersionLi');


if (Database::BeginPatchingSystem()) {
	Database::ExecuteCreateStandardTable('mmx','VersionLi',Sql::Integer);
	Database::ExecuteInsert('mmx','id',new ID(0),'VersionLi',0);
}

if (Database::BeginPatch('li',1,'Users')) {
	Database::ExecuteCreateStandardTable('mmx_user', 'Username', Sql::String100);
	Database::ExecuteAddUniqueIndex('mmx_user','Username');
	Database::ApplyPatch();
}
