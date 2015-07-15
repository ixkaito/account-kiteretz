<?php

class SiteGuard_LoginHistory extends SiteGuard_Base {

	function __construct( ) {
		define( 'SITEGUARD_TABLE_HISTORY', 'siteguard_history' );
		add_action( 'wp_login', array( $this, 'handler_wp_login' ), 1, 2 );
		add_action( 'wp_login_failed', array( $this, 'handler_wp_login_failed' ), 30 );
		add_action( 'xmlrpc_call', array( $this, 'handler_xmlrpc_call' ), 10, 1 );
	}
	function init( ) {
		global $wpdb;
		# operation
		#  0: Login failure
		#  1: Login success
		#  2: Fail once
		#  3: Login lock
		$table_name = $wpdb->prefix . SITEGUARD_TABLE_HISTORY;
		$sql = "CREATE TABLE $table_name  (
		  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  login_name VARCHAR(40) NOT NULL DEFAULT '',
		  ip_address VARCHAR(40) NOT NULL DEFAULT '',
		  operation INT NOT NULL DEFAULT 0,
		  time datetime,
		  UNIQUE KEY id (id)
		  )
		  CHARACTER SET 'utf8';";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $sql );
	}
	function handler_wp_login( $login, $current_user ) {

		if ( '' == $current_user->user_login ) {
			return;
		}
		$this->add_operation( SITEGUARD_LOGIN_SUCCESS, $current_user->user_login );
	}
	function handler_wp_login_failed( $username ) {
		global $loginlock;
		$this->add_operation( $loginlock->get_status( ), $username );
	}
	function handler_xmlrpc_call( $method ) {
		$current_user = wp_get_current_user( );
		if ( '' == $current_user->user_login ) {
			return;
		}
		$this->add_operation( SITEGUARD_LOGIN_SUCCESS, $current_user->user_login );
	}
	function is_exist( $user, $operation, $after_sec, $less_sec ) {
		global $wpdb;

		if ( $after_sec > $less_sec ) {
			return false;
		}

		$table_name = $wpdb->prefix . SITEGUARD_TABLE_HISTORY;
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$now = current_time( 'mysql' );
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE ip_address = %s AND login_name = %s AND operation = %d AND time BETWEEN %s - INTERVAL %d SECOND AND %s - INTERVAL %d SECOND; ", $ip_address, $user, $operation, $now, $less_sec, $now, $after_sec ) );
		if ( null == $id ) {
			return false;
		}
		return true;
	}
	function add_operation( $operation, $user_login ) {
		global $current_user;
		global $wpdb;

		if ( '' != $user_login ) {
			$user = $user_login;
		} else {
			get_currentuserinfo();
			$user = $current_user->user_login;
		}
		$table_name = $wpdb->prefix . SITEGUARD_TABLE_HISTORY;

		$wpdb->query( 'START TRANSACTION' );
		// delete old event
		$id = $wpdb->get_var( "SELECT id FROM $table_name ORDER BY id DESC LIMIT 9999,1;", 0, 0 );
		if ( null != $id ) {
			$wpdb->query( "DELETE FROM $table_name WHERE id < $id;" );
		}
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$data = array(
			'operation'  => $operation,
			'login_name' => $user,
			'ip_address' => $ip_address,
			'time'       => current_time( 'mysql' ),
		);
		$wpdb->insert( $table_name, $data );

		$wpdb->query( 'COMMIT' );
	}
	static function convert_operation( $operation ) {
		$result = '';
		switch ( $operation ) {
			case SITEGUARD_LOGIN_FAILED:
				$result = esc_html__( 'Failed', 'siteguard' );
				break;
			case SITEGUARD_LOGIN_SUCCESS:
				$result = esc_html__( 'Success', 'siteguard' );
				break;
			case SITEGUARD_LOGIN_FAIL_ONCE:
				$result = esc_html__( 'Fail once', 'siteguard' );
				break;
			case SITEGUARD_LOGIN_LOCKED:
				$result = esc_html__( 'Locked', 'siteguard' );
				break;
			default:
				$result = esc_html__( 'Unknown', 'siteguard' );
		}
		return $result;
	}
	function get_history( ) {
		global $wpdb;
		$table_name = $wpdb->prefix . SITEGUARD_TABLE_HISTORY;
		$results = $wpdb->get_results( "SELECT id, operation, login_name, ip_address, time FROM $table_name;", ARRAY_A );
		return $results;
	}
}

?>
