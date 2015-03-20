<?php
Database::SetPatchingSystem('mmx','mmx');
Database::AddPatcher('li','VersionLi');


if (Database::BeginPatchingSystem()) {
	Database::ExecuteCreateStandardTable('mmx','VersionLi',Sql::Integer);
	Database::ExecuteInsert('mmx','id',new ID(0),'VersionLi',0);
}
//
//if (Database::BeginPatch("li",1,"Credits")) {
//
//	Database::ExecuteCreateStandardTable('mmx_pers'
//		,'tmdb',Sql::String20
//		,'imdb',Sql::String20
//		,'FullName',Sql::String255
//		,'DateUpdated',Sql::DateTime
//		);
//	Database::ExecuteCreateStandardTable('mmx_prod'
//		,'tmdb',Sql::String20
//		,'imdb',Sql::String20
//		,'FullName',Sql::String255
//		,'DateUpdated',Sql::DateTime
//		);
//	Database::ExecuteCreateStandardTable('mmx_cred'
//		,'tmdb',Sql::String20
//		,'imdb',Sql::String20
//		,'FullName',Sql::String255
//		,'DateUpdated',Sql::DateTime
//		);
//
//}
