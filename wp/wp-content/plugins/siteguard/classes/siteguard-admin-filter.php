<?php

class SiteGuard_AdminFilter extends SiteGuard_Base {
	public static $htaccess_mark = '#==== SITEGUARD_ADMIN_FILTER_SETTINGS';

	function __construct( ) {
		define( 'SITEGUARD_TABLE_LOGIN', 'siteguard_login' );
		add_action( 'wp_login', array( $this, 'handler_wp_login' ), 1, 2 );
	}
	static function get_mark( ) {
		return SiteGuard_AdminFilter::$htaccess_mark;
	}
	function init( ) {
		global $wpdb, $config;
		$table_name = $wpdb->prefix . SITEGUARD_TABLE_LOGIN;
		$sql = 'CREATE TABLE ' . $table_name . " (
			ip_address varchar(40) NOT NULL DEFAULT '',
			status INT NOT NULL DEFAULT 0,
			count INT NOT NULL DEFAULT 0,
			last_login_time datetime,
			UNIQUE KEY ip_address (ip_address)
		)
		CHARACTER SET 'utf8';";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $sql );
		$config->set( 'admin_filter_exclude_path', 'css,images,admin-ajax.php' );
		$config->set( 'admin_filter_enable', '0' );
		$config->update( );
	}
	function handler_wp_login( $login, $current_user ) {
		global $htaccess, $config;

		if ( '' == $current_user->user_login ) {
			return;
		}
		if ( 1 == $config->get( 'admin_filter_enable' ) ) {
			$this->feature_on( $_SERVER['REMOTE_ADDR'] );
		}
	}
	function cvt_exclude( $exclude ) {
		return str_replace( ',', '|', $exclude );
	}
	function update_settings( $ip_address ) {
		global $wpdb, $config;
		$htaccess_str = '';
		$table_name = $wpdb->prefix . SITEGUARD_TABLE_LOGIN;
		$exclude_paths = preg_split( '/,/', $config->get( 'admin_filter_exclude_path' ) );

		$now_str = current_time( 'mysql' );
		$now_bin = strtotime( $now_str );

		$wpdb->query( 'START TRANSACTION' );
		$wpdb->query( "DELETE FROM $table_name WHERE status = 1 AND last_login_time < SYSDATE() - INTERVAL 1 DAY;" );
		$data = array(
			'ip_address' => $ip_address,
			'status' => 1,
			'count' => 0,
			'last_login_time' => $now_str,
		);
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT status from $table_name WHERE ip_address = %s", $ip_address ) );
		if ( null == $result ) {
			$wpdb->insert( $table_name, $data );
		} else {
			$wpdb->update( $table_name, $data, array( 'ip_address' => $ip_address ) );
		}
		$parse_url = parse_url( site_url( ) );
		if ( false == $parse_url ) {
			$base = '/';
		} else {
			if ( isset( $parse_url['path'] ) ) {
				$base = $parse_url['path'] . '/';
			} else {
				$base = '/';
			}
		}
		$htaccess_str .= "<IfModule mod_rewrite.c>\n";
		$htaccess_str .= "    RewriteEngine on\n";
		$htaccess_str .= "    RewriteBase $base\n";
		$htaccess_str .= "    RewriteRule ^404-siteguard - [L]\n";
		foreach ( $exclude_paths as $path ) {
			$htaccess_str .= '    RewriteRule ^wp-admin/' . trim( $path ) . " - [L]\n";
		}
		$htaccess_str .= '    RewriteCond %{REMOTE_ADDR} !(127.0.0.1|'. $_SERVER['SERVER_ADDR'] . ")\n";
		$results = $wpdb->get_col( "SELECT ip_address FROM $table_name;" );
		if ( $results ) {
			foreach ( $results as $ip ) {
				$htaccess_str .= '    RewriteCond %{REMOTE_ADDR} !' . $ip . "\n";
			}
		}
		$htaccess_str .= "    RewriteRule ^wp-admin 404-siteguard [L]\n";
		$htaccess_str .= "</IfModule>\n";

		$wpdb->query( 'COMMIT' );

		return $htaccess_str;
	}
	function feature_on( $ip_addres ) {
		global $htaccess, $config;
		$mark = $this->get_mark( );
		$data = $this->update_settings( $ip_addres );
		return $htaccess->update_settings( $mark, $data );
	}
	static function feature_off( ) {
		$mark = SiteGuard_AdminFilter::get_mark( );
		SiteGuard_Htaccess::clear_settings( $mark );
	}
}

?>
