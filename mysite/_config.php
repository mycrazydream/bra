<?php

global $project;
$project = 'mysite';

global $databaseConfig;
$databaseConfig = array(
	"type" => 'MySQLDatabase',
	"server" => 'localhost',
	"username" => 'root',
	"password" => 'sfgrd3003',//!3!3!69mcd
	"database" => 'SS_BRA',
	"path" => '',
);

MySQLDatabase::set_connection_charset('utf8');

// Set the current theme. More themes can be downloaded from
// http://www.silverstripe.org/themes/
SSViewer::set_theme('bra');

// Set the site locale
i18n::set_locale('en_US');

Director::set_environment_type("dev");

SiteTree::enable_nested_urls();