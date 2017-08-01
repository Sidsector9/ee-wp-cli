<?php

class EE_DB extends SQLite3 {
	function __construct() {
		$this->open( 'ee4.db' );
	}
}

global $db;
$db = new EE_DB();

$create_table = "CREATE TABLE IF NOT EXISTS `ee_site_data` (
	`ID`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`site_name`	TEXT NOT NULL UNIQUE,
	`site_type_code`	TEXT NOT NULL,
	`site_type`	TEXT NOT NULL,
	`cache_type`	TEXT,
	`php`	TEXT,
	`letsencrypt`	TEXT,
	`mysql`	TEXT
);";

$db->exec( $create_table );