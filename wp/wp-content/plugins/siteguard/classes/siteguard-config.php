<?php

class SiteGuard_Config {
	var $config;
	function __construct() {
		$this->config = get_option( 'siteguard_config' );
	}
	function set( $key, $value ) {
		$this->config[ $key ] = $value;
	}
	function get( $key ) {
		$this->config = get_option( 'siteguard_config' );
		return isset( $this->config[ $key ] ) ? $this->config[ $key ] : '';
	}
	function update( ) {
		update_option( 'siteguard_config', $this->config );
	}
}

?>
