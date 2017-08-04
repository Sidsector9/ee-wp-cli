<?php
class List_Command extends WP_CLI_Command {

	/**
	 * Lists all the site names line-by-line.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ee site site list
	 *
	 * @param array $_          Positional argument.
	 * @param array $assoc_args Associative argument.
	 *
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
			WP_CLI::log( WP_CLI::colorize( '%B' . $site_name['site_name'] . '%n' ) );
		}

		$db->close();
	}
}

WP_CLI::add_command( 'ee site list', 'List_Command' );
