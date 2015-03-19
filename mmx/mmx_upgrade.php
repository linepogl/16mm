<?php
Database::SetPatchingSystem('mmx','mmx');
Database::AddPatcher('li','VersionLi');


if (Database::BeginPatchingSystem()) {
	Database::ExecuteCreateStandardTable('mmx','VersionLi',Sql::Integer);
	Database::ExecuteInsert('mmx','id',new ID(0),'VersionLi',0);
}

if (Database::BeginPatch("li",1,"Credits")) {

	Database::ExecuteCreateStandardTable('mmx_person'
		,'tmdb',Sql::String20
		,'imdb',Sql::String20
		,'FullName',Sql::String255
		,'DateUpdated',Sql::DateTime
		);
	Database::ExecuteCreateStandardTable('mmx_production'
		,'tmdb',Sql::String20
		,'imdb',Sql::String20
		,'FullName',Sql::String255
		,'DateUpdated',Sql::DateTime
		);
	Database::ExecuteCreateStandardTable('mmx_credit'
		,'tmdb',Sql::String20
		,'imdb',Sql::String20
		,'FullName',Sql::String255
		,'DateUpdated',Sql::DateTime
		);

}
