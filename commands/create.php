<?php
class Create_Command extends WP_CLI_Command {

	/**
	 * Name of the site.
	 *
	 * @var string
	 */
	public $site_name;

	/**
	 * Code of the type of site. Useful during update()
	 * operation.
	 *
	 * @var string
	 */
	public $site_type_code = null;

	/**
	 * Type of the site.
	 *
	 * Example
	 * - HTML
	 * - PHP
	 * - WordPress
	 *
	 * @var string
	 */
	public $site_type;

	/**
	 * Type of cache used.
	 *
	 * @var string
	 */
	public $cache_type = 'disabled';

	/**
	 * Version of PHP used.
	 *
	 * @var string
	 */
	public $php = false;

	/**
	 * Set to `enabled` while installing letsencrypt.
	 *
	 * @var string
	 */
	public $letsencrypt = false;

	/**
	 * Set to `yes`.
	 *
	 * @var string
	 */
	public $mysql = 'no';

	/**
	 * List of site types.
	 *
	 * @var array
	 */
	public $site_types = array(
		'html',
		'php',
		'wp',
		'mysql',
		'wpfc',
		'wpredis',
	);

	/**
	 * Creates a new site to the database and adds a configuration file for the site.
	 *
	 * ## OPTIONS
	 *
	 * <name>
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
			$this->site_name = $_[0];
		}

		// If site type is empty.
		if ( empty( $assoc_args ) ) {
			WP_CLI::error( 'Site type cannot be empty.' );
			return;
		}

		// If more than 1 site type is given.
		if ( count( array_intersect( array_keys( $assoc_args ), $this->site_types ) ) > 1 ) {
			WP_CLI::error( 'Too many arguments.' );
			return;
		}

		// Enable letsencrypt.
		if ( array_key_exists( 'letsencrypt' , $assoc_args ) ) {
			$this->letsencrypt = true;
		}

		// Upgrade to PHP 7.0
		if ( array_key_exists( 'php7' , $assoc_args ) ) {
			$this->php = true;
		}

		$remaining = array_diff(  array_keys( $assoc_args ), $this->site_types );

		foreach ( $assoc_args as $key => $value ) {
			switch ( $key ) {
				case 'html':
					$this->site_type_code = 'html';
					$this->site_type      = 'HTML';
					$this->php            = 'no';
					break;

				case 'php':
					$this->site_type_code = 'php';
					$this->site_type      = 'PHP';
					$this->php            = '5.6';
					break;

				case 'php7':
					if ( empty( array_intersect( array_keys( $assoc_args ), $this->site_types ) ) ) {
						$this->site_type_code = 'php';
						$this->site_type      = 'PHP';
						$this->php            = $this->php ? '7.0' : '5.6';
					} else {
						$this->php            = $this->php ? '7.0' : '5.6';
					}
					break;

				case 'wp':
					$this->site_type_code = 'wp';
					$this->site_type      = 'WordPress';
					$this->php            = $this->php ? '7.0' : '5.6';
					$this->mysql          = 'yes';
					break;

				case 'mysql':
					$this->site_type_code = 'mysql';
					$this->site_type      = 'PHP+MySQL';
					$this->php            = $this->php ? '7.0' : '5.6';
					$this->mysql          = 'yes';
					break;

				case 'wpfc':
					$this->site_type_code = 'wpfc';
					$this->site_type      = 'WordPress';
					$this->php            = $this->php ? '7.0' : '5.6';
					$this->cache_type     = 'nginx fastcgi_cache';
					$this->mysql          = 'yes';
					break;

				case 'wpredis':
					$this->site_type_code = 'wpredis';
					$this->site_type      = 'WordPress';
					$this->php            = $this->php ? '7.0' : '5.6';
					$this->cache_type     = 'nginx redis_cache';
					$this->mysql          = 'yes';
					break;

				case 'letsencrypt':
					$this->letsencrypt    = $this->letsencrypt ? 'enabled' : 'disabled';
					break;

				default:
					WP_CLI::error( 'Incorrect arguments.' );
					return;
			}
		}

		// If site type is missing.
		if ( is_null( $this->site_type_code ) ) {
			WP_CLI::error( 'Site type is missing.' );
			return;
		}

		// Database table columns
		$table_columns = array(
			'site_name',
			'site_type_code',
			'site_type',
			'cache_type',
			'php',
			'letsencrypt',
			'mysql',
		);

		if ( ! $this->letsencrypt ) {
			$this->letsencrypt = 'disabled';
		}

		$config_details = array(
			'site_name' => $this->site_name,
			'site_type_code' => $this->site_type_code,
			'site_type' => $this->site_type,
			'cache_type' => $this->cache_type,
			'php' => $this->php,
			'letsencrypt' => $this->letsencrypt,
			'mysql' => $this->mysql,
		);

		if ( false === _generate_config_file( CONFIG_DIR, $this->site_name, $config_details ) ) {
			WP_CLI::error( 'Unable to create a configuration file for ' . $this->site_name );
			return;
		}

		// Get the insert query.
		$query = _prepare_insert_query(
			$table_columns,
			$this->site_name,
			$this->site_type_code,
			$this->site_type,
			$this->cache_type,
			$this->php,
			$this->letsencrypt,
			$this->mysql
		);

		// Fire the insert query.
		if ( false === $db->exec( $query ) ) {
			_delete_configuration_file( CONFIG_DIR, $this->site_name );
		} else {
			WP_CLI::success( $this->site_name . ' successfully created.' );
		}
	}
}

WP_CLI::add_command( 'ee site create', 'Create_Command' );	
