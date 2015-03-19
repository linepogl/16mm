<?php
Database::SetPatchingSystem('mmm','mmm');
Database::AddPatcher('li','VersionLi');


if (Database::BeginPatchingSystem()) {
	Database::ExecuteCreateStandardTable('mmm','VersionLi',Sql::Integer);
	Database::ExecuteInsert('mmm','id',new ID(0),'VersionLi',0);
}
//
//if (Database::BeginPatch("li",1,"Credits")) {
//
//	Database::ExecuteCreateStandardTable('mmm_pers'
//		,'tmdb',Sql::String20
//		,'imdb',Sql::String20
//		,'FullName',Sql::String255
//		,'DateUpdated',Sql::DateTime
//		);
//	Database::ExecuteCreateStandardTable('mmm_prod'
//		,'tmdb',Sql::String20
//		,'imdb',Sql::String20
//		,'FullName',Sql::String255
//		,'DateUpdated',Sql::DateTime
//		);
//	Database::ExecuteCreateStandardTable('mmm_cred'
//		,'tmdb',Sql::String20
//		,'imdb',Sql::String20
//		,'FullName',Sql::String255
//		,'DateUpdated',Sql::DateTime
//		);
//
//}
