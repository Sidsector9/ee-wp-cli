<?php
/**
 * Prepares insert query.
 * Used in both `create` and `update` commands.
 */
function _prepare_insert_query() {
	$count = 1;
	$column = func_get_arg( 0 );
	$column_names = '';
	$column_values = '';

	while ( $count < func_num_args() ) {
		$column_names .= $column[ $count - 1 ] . ',';
		if ( empty( func_get_arg( $count ) ) ) {
			$column_values .= 'NULL,';
		} else {
			$column_values .= '"' . func_get_arg( $count ) . '",';
		}
		$count++;
	}

	// Remove the trailing comma.
	$column_names = rtrim( $column_names, ',' );
	$column_values = rtrim( $column_values, ',' );

	// Insert query to be returned.
	return 'INSERT INTO ee_site_data (' . $column_names . ') VALUES (' . $column_values . ')';
}

function _prepare_update_query() {
	$count        = 2;
	$column       = func_get_arg( 0 );
	$site_name    = func_get_arg( 1 );
	$column_names = '';

	while ( $count < func_num_args() ) {
		$column_names .= $column[ $count - 2 ] . "='" . ( null === func_get_arg( $count ) ? 'NULL' : func_get_arg( $count ) ) . "',";
		$count++;
	}

	$column_names = rtrim( $column_names, ',' );
	return "UPDATE ee_site_data SET " . $column_names . " WHERE site_name='" . $site_name . "'";
}

/**
 * Generates configuration file for a site.
 */
function _generate_config_file( $path, $file_name, $config_data, $is_update = false ) {
	if ( $is_update ) {
		_delete_configuration_file( CONFIG_DIR, $file_name );
	}

	if ( file_exists( $path . $file_name . CNF_EXT ) ) {
		WP_CLI::error( 'A config file with the name ' . $file_name . CNF_EXT . ' already exists.' );
		die();
	} else {
		$config_file = fopen( $path . $file_name . CNF_EXT, 'w' );
		foreach ( $config_data as $key => $value ) {
			fwrite( $config_file, $key . ' = ' . $value . PHP_EOL );
		}
		return $config_file;
	}
}

/**
 * Deletes configuration file for a site.
 */
function _delete_configuration_file( $path, $site_name, $bypass_error = false ) {
	if ( ! file_exists( $path . $site_name . CNF_EXT ) && $bypass_error ) {
		WP_CLI::error( 'Configuration file for ' . $site_name . ' does not exist.' );
	} else {
		$flag = unlink( $path . $site_name . CNF_EXT );
		return $flag;
	}
}

/**
 * Get config details
 */
function _get_config( $site_name ) {
	$path_to_config = CONFIG_DIR . $site_name . CNF_EXT;
	if ( ! file_exists( $path_to_config ) ) {
		WP_CLI::error( 'Configuration file for ' . $site_name . ' not found.' );
	} else {
		$file = fopen( $path_to_config, 'r' );
		$config_details = '';
		while ( $line = fgets( $file ) ) {
			$config_details .= $line;
		}
		WP_CLI::log( $config_details );
	}
}

/**
 * Get column values.
 */
function _get_column_values( $site_name, $columns ) {
	global $db;

	$column_string = implode( ',', $columns );
	$result = $db->query( 'SELECT ' . $column_string . ' FROM ee_site_data WHERE site_name="' . $site_name . '"' );
	return $result;
}
