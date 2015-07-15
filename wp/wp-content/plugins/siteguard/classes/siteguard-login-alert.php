<?php

class SiteGuard_LoginAlert extends SiteGuard_Base {
	function __construct( ) {
		global $config;
		if ( '1' == $config->get( 'loginalert_enable' ) ) {
			add_action( 'wp_login', array( $this, 'handler_wp_login' ), 10, 2 );
		}
	}
	function init( ) {
		global $config;
		if ( true === check_multisite( ) ) {
			$config->set( 'loginalert_enable',  '1' );
		} else {
			$config->set( 'loginalert_enable',  '0' );
		}
		$config->set( 'loginalert_admin_only',  '1' );
		$config->set( 'loginalert_subject', __( 'New login at %SITENAME%', 'siteguard' ) );
		$config->set( 'loginalert_body',    __( "%USERNAME% logged in at %DATE% %TIME%\n\n== Login information ==\nIP Address: %IPADDRESS%\nReferer: %REFERER%\nUser-Agent: %USERAGENT%\n\n--\nSiteGuard WP Plugin", 'siteguard' ) );
		$config->update( );
	}
	function replace_valuable( $string, $username ) {
		$search  = array( '%SITENAME%', '%USERNAME%', '%DATE%', '%TIME%', '%IPADDRESS%', '%USERAGENT%', '%REFERER%' );
		$replace = array(
				get_option( 'blogname' ),
				$username,
				date( 'Y-m-d', current_time( 'timestamp' ) ),
				date( 'H:i:s', current_time( 'timestamp' ) ),
				isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '-',
				isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '-',
				isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '-',
			);
		return str_replace( $search, $replace, $string );
	}
	function handler_wp_login( $username, $user ) {
		global $config;

		if ( ( '1' == $config->get( 'loginalert_admin_only' ) ) && ( ! $user->has_cap( 'administrator' ) ) ) {
			return;
		}

		$user_email = $user->get( 'user_email' );

		$subject = $config->get( 'loginalert_subject' );
		$body    = $config->get( 'loginalert_body' );

		$subject = $this->replace_valuable( $subject, $username );
		$body    = $this->replace_valuable( $body, $username );

		@wp_mail( $user_email, esc_html( $subject ), esc_html( $body ) );

		return;
	}
}

?>
