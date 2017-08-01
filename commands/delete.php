<?php
class Delete_Command extends WP_CLI_Command {
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
			WP_CLI::error( $site_name . ' does not exist' );
			die;
		} else {
			$result = $db->query( 'DELETE FROM ee_site_data WHERE site_name="' . $site_name . '"' );
			if ( $db->changes() > 0 ) {
				_delete_configuration_file( CONFIG_DIR, $site_name, true );
				WP_CLI::success( $site_name . ' successfully deleted.' );
			}
		}
	}
}

WP_CLI::add_command( 'ee site delete', 'Delete_Command' );