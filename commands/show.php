<?php
class Show_Command extends WP_CLI_Command {

	/**
	 * Displays details from the site configuration file.
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 * : The name of the site.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ee site show example.com
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
			WP_CLI::error( $site_name . ' does not exist' );
			die;
		} else {
			_get_config( $site_name );
		}

		$db->close();
	}
}

WP_CLI::add_command( 'ee site show', 'Show_Command' );
