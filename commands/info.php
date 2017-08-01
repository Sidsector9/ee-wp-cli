<?php
class Info_Command extends WP_CLI_Command {

	/**
	 * Shows information of a particular site in a tabular form.
	 *
	 * Example: wp ee site info example.com
	 *
	 * @param array $_          Positional argument.
	 * @param array $assoc_args Associative argument.
	 *
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

		// Check if the site even exists.
		$result = $db->query( 'SELECT site_name FROM ee_site_data WHERE site_name="' . $site_name . '"' );
		if ( empty( $result->fetchArray() ) ) {
			WP_CLI::error( 'Site does not exist.' );
			die;
		}

		// Reset the row pointer.
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

		// Display in a tabular format.
		WP_CLI\Utils\format_items( 'table', $table_array, $args );
	}
}

WP_CLI::add_command( 'ee site info', 'Info_Command' );
