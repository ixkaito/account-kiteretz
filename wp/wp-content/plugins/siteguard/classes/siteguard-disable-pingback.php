<?php

class SiteGuard_Disable_Pingback extends SiteGuard_Base {

	function __construct( ) {
		global $config;
		if ( '1' == $config->get( 'disable_pingback_enable' ) ) {
			add_filter( 'xmlrpc_methods', array( $this, 'handler_xmlrpc_methods' ) );
		}
	}
	function init( ) {
		global $config;
		if ( true === check_multisite( ) ) {
			$config->set( 'disable_pingback_enable', '1' );
		} else {
			$config->set( 'disable_pingback_enable', '0' );
		}
		$config->update( );
	}
	function handler_xmlrpc_methods( $methods ) {
		unset( $methods['pingback.ping'] );
		unset( $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}
}

?>
