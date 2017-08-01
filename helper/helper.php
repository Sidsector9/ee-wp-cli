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

/**
 * Generates configuration file for a site.
 */
function _generate_config_file( $path, $file_name, $config_data, $is_update = false ) {
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
