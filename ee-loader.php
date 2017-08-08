<?php
if ( ! defined( 'ROOT_PATH' ) ) {
	define( 'ROOT_PATH', dirname( __DIR__ ) );
}

if ( ! defined( 'CONFIG_DIR' ) ) {
	define( 'CONFIG_DIR', dirname( __DIR__ ) . '/ee-wp-cli/database/config/' );
}

if ( ! defined( 'CNF_EXT' ) ) {
	define( 'CNF_EXT', '.cnf' );
}

require_once 'helper/helper.php';
require_once 'database/database.php';
require_once 'commands/create.php';
require_once 'commands/delete.php';
require_once 'commands/info.php';
require_once 'commands/list.php';
require_once 'commands/show.php';
require_once 'commands/update.php';
