<?php
class List_Command extends WP_CLI_Command {
	/**
	 * @when before_wp_load
	 */
	public function __invoke( $_, $assoc_args ) {
		global $db;

		// Die if database is empty.
		$result = $db->query( 'SELECT site_name FROM ee_site_data' );
		if ( empty( $result->fetchArray() ) ) {
			WP_CLI::error( 'Database is empty.' );
			die;
		}

		// resets row pointer.
		$result->reset();

		// Fetch list of site names.
		while ( $site_name = $result->fetchArray() ) {
			WP_CLI::log( $site_name['site_name'] );
		}
	}
}

WP_CLI::add_command( 'ee site list', 'List_Command' );
