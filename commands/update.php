<?php
class Update_Command extends WP_CLI_Command {

	/**
	 * This array is referred during update() operation.
	 *
	 * Example: wp ee site update example.com --wp
	 * The index of the argument `wp` from the below array
	 * will be used for update() operation.
	 *
	 * @var array
	 */
	public $site_update_argument = array(
		'html',
		'php',
		'mysql',
		'wp',
		'wpfc',
		'wpredis',
	);

	/**
	 * Array of features.
	 *
	 * @var array
	 */
	public $site_feature_argument = array(
		'php7',
		'letsencrypt',
	);

	/**
	 * During update() operation, the `site_type_code` will be fetched and
	 * the index returned from $site_update_argument array will be used
	 * to compare it with the below null, true and false values.
	 *
	 * The columns of booelan values are in this order:
	 * html, php, php7, mysql, wp, wpfc, wpredis
	 *
	 * @var array
	 */
	public $site_update_constraints = array(
		'html'    => array( null, true, true, true, true, true ),
		'php'     => array( false, null, true, true, true, true ),
		'mysql'   => array( false, false, null, true, true, true ),
		'wp'      => array( false, false, false, null, true, true ),
		'wpfc'    => array( false, false, false, true, null, true ),
		'wpredis' => array( false, false, true, true, true, null ),
	);

	/**
	 * Updates the specified website.
	 *
	 * Example:
	 * wp ee site update example.com --wpfc --letsencrypt
	 *
	 * @param array $_          Positional argument.
	 * @param array $assoc_args Associative argument.
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $_, $assoc_args ) {
		global $db;

		$feature_only = false;

		// return is site name is empty.
		if ( empty( $_[0] ) ) {
			WP_CLI::error( 'Site name cannot be empty.' );
			return;
		} else {
			$site_name = $_[0];
		}

		// Check if the site even exists.
		$result = _get_column_values( $site_name, array( 'site_name' ) );

		if ( empty( $result->fetchArray() ) ) {
			WP_CLI::error( $site_name . ' does not exist' );
			return;
		}

		if ( empty( $assoc_args ) ) {
			WP_CLI::error( 'Insufficient arguments' );
			return;
		}

		// Assoc_arg keys.
		$assoc_args_keys   = array_keys( $assoc_args );

		// Count of all associative arguments that are passed.
		$passed_args_count = count( $assoc_args );

		// Array of all valid associative arguments.
		$valid_args        = array_merge( $this->site_update_argument, $this->site_feature_argument );

		// Array of site_types passed in associative arguments.
		$types             = array_values( array_intersect( $this->site_update_argument, $assoc_args_keys ) );

		// Array of site_features passed in associative arguments.
		$features          = array_values( array_intersect( $assoc_args_keys, $this->site_feature_argument ) );

		// Count of intersection of all valid arguments and arguments that are passed.
		$valid_count       = count( array_intersect( $valid_args, $assoc_args_keys ) );

		// Return if arguments are invalid.
		if ( $passed_args_count !== $valid_count ) {
			WP_CLI::error( 'Unrecognized arguments passed' );
			return;
		}

		// Return if 2 update types are passed.
		if ( count( $types ) > 1 ) {
			WP_CLI::error( 'Cannot update to more than 1 type at the same time.' );
			return;
		}

		$result             = _get_column_values( $site_name, array( '*' ) );
		$result_array       = $result->fetchArray();
		$site_type_code_old = $result_array['site_type_code'];
		$site_type_code_new = ! empty( $types ) ? $types[0] : null;
		$index              = array_search( $site_type_code_new, $this->site_update_argument );
		$is_updatable       = $this->site_update_constraints[ $site_type_code_old ][ $index ];

		if ( empty( $types ) && ! empty( $features ) ) {
			$is_updatable = true;
			$feature_only = true;
		} elseif ( false === $is_updatable || null === $is_updatable ) {
			WP_CLI::error( 'Cannot update from ' . $site_type_code_old . ' to ' . $site_type_code_new );
			return;
		}

		// Assigning variables with current values of the site.
		$site_type_code = $result_array['site_type_code'];
		$site_type      = $result_array['site_type'];
		$php            = $result_array['php'];
		$cache_type     = $result_array['cache_type'];
		$mysql          = $result_array['mysql'];
		$letsencrypt    = $result_array['letsencrypt'];

		foreach ( $assoc_args_keys as $key ) {
			switch ( $key ) {
				case 'php':
					$site_type_code = 'php';
					$site_type      = 'PHP';
					$php            = '7.0' === $php ? '7.0' : '5.6';
					break;

				case 'php7':
					if ( 'html' === $site_type_code ) {
						$site_type_code = 'php';
						$site_type      = 'PHP';
						$php            = '7.0';
					} else {
						$php            = '7.0';
					}
					break;

				case 'mysql':
					$site_type_code = 'mysql';
					$site_type      = 'PHP+MySQL';
					$php            = '7.0' === $php ? '7.0' : '5.6';
					$mysql          = 'yes';
					break;

				case 'wp':
					$site_type_code = 'wp';
					$site_type      = 'WordPress';
					$php            = '7.0' === $php ? '7.0' : '5.6';
					$mysql          = 'yes';
					break;

				case 'wpfc':
					$site_type_code = 'wpfc';
					$site_type      = 'WordPress';
					$php            = '7.0' === $php ? '7.0' : '5.6';
					$cache_type     = 'nginx fastcgi_cache';
					$mysql          = 'yes';
					break;

				case 'wpredis':
					$site_type_code = 'wpredis';
					$site_type      = 'WordPress';
					$php            = '7.0' === $php ? '7.0' : '5.6';
					$cache_type     = 'nginx redis_cache';
					$mysql          = 'yes';
					break;

				case 'letsencrypt':
					$letsencrypt    = 'enabled';
					break;
			}
		}

		$column_names = array(
			'site_type_code',
			'site_type',
			'php',
			'cache_type',
			'mysql',
			'letsencrypt',
		);

		$query = _prepare_update_query( $column_names, $site_name , $site_type_code, $site_type, $php, $cache_type, $mysql, $letsencrypt );

		if ( false === $db->query( $query ) ) {
			WP_CLI::error( 'Failed to update from ' . $site_type_code_old . ' to ' . $site_type_code_new );
		} else {
			$config_details = array(
				'site_name' => $site_name,
				'site_type_code' => $site_type_code,
				'site_type' => $site_type,
				'cache_type' => $cache_type,
				'php' => $php,
				'letsencrypt' => $letsencrypt,
				'mysql' => $mysql,
			);
			_generate_config_file( CONFIG_DIR, $site_name, $config_details, true );
			if ( $feature_only ) {
				WP_CLI::success( 'Successfully updated' );
			} else {
				WP_CLI::success( 'Successfully updated from ' . $site_type_code_old . ' to ' . $site_type_code_new );
			}
		}
	}
}

WP_CLI::add_command( 'ee site update', 'Update_Command' );
