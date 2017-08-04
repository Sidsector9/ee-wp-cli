<?php
class Delete_Command extends WP_CLI_Command {

	/**
	 * Deletes a site from the database and also deletes its configuration file.
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 *
	 * ## EXAMPLES
	 *
	 *     wp ee site delete example.com
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

		// Check if a site even exists in the database.
		$result = $db->query( 'SELECT site_name FROM ee_site_data WHERE site_name="' . $site_name . '"' );

		if ( empty( $result->fetchArray() ) ) {
			WP_CLI::error( $site_name . ' does not exist' );
			die;
		} else {
			if ( ! WP_CLI\Utils\get_flag_value( $assoc_args, 'noprompt' ) ) {
				$question = 'Are you sure you want to delete ' . $site_name . '?: ';
				WP_CLI::confirm( $question );
			}
			$result = $db->query( 'DELETE FROM ee_site_data WHERE site_name="' . $site_name . '"' );
			if ( $db->changes() > 0 ) {
				_delete_configuration_file( CONFIG_DIR, $site_name, true );
				WP_CLI::success( $site_name . ' successfully deleted.' );
			}
		}
	}
}

WP_CLI::add_command( 'ee site delete', 'Delete_Command' );
