<?php
class Info_Command extends WP_CLI_Command {
	/**
	 * @when before_wp_load
	 */
	public function __invoke( $_, $assoc_args ) {
		global $db;

		// return is site name is empty.
		if ( empty( $_[0] ) ) {
			WP_CLI::error( 'Site name cannot be empty.' );
			return;
		} else {
			$site_name = $_[0];
		}

		$result = $db->query( 'SELECT site_name FROM ee_site_data WHERE site_name="' . $site_name . '"' );
		if ( empty( $result->fetchArray() ) ) {
			WP_CLI::error( 'Site does not exist.' );
			die;
		}

		$result->reset();
		$result = $db->query( 'SELECT * FROM ee_site_data WHERE site_name="' . $site_name . '"' );

		$args = array(
			'Variables',
			'Values',
		);

		$table_array = array();

		foreach ( $result->fetchArray() as $key => $value ) {
			if ( 'string' === gettype( $key ) ) {
				$table_array[] = array(
					'Variables' => $key,
					'Values' => $value,
				);
			}
		}

		WP_CLI\Utils\format_items( 'table', $table_array, $args );
	}
}

WP_CLI::add_command( 'ee site info', 'Info_Command' );
