<?php
class SiteGuard_Menu_INIT extends SiteGuard_Base {
	function __construct( ) {
		add_action( 'admin_menu', array( &$this, 'add_pages' ) );
		if ( isset( $_GET['page'] ) && false !== strpos( $_GET['page'], 'siteguard' ) ) {
			add_action( 'admin_print_styles', array( $this, 'menu_styles' ) );
		}
	}
	function menu_styles( ) {
		wp_enqueue_style( 'siteguard-menu', SITEGUARD_URL_PATH . 'css/siteguard-menu.css' );
	}
	function add_pages( ) {
		$icon_path = SITEGUARD_URL_PATH . 'images/plugin-icon.png';
		add_menu_page( esc_html__( 'SiteGuard', 'siteguard' ), esc_html__( 'SiteGuard', 'siteguard' ), 'manage_options', 'siteguard', array( $this, 'menu_dashboard' ), $icon_path );
		add_submenu_page( 'siteguard', esc_html__( 'Dashboard', 'siteguard' ),                esc_html__( 'Dashboard', 'siteguard' ) ,               'manage_options', 'siteguard',                    array( $this, 'menu_dashboard' ) );
		add_submenu_page( 'siteguard', esc_html__( 'Admin Page IP Filter', 'siteguard' ),     esc_html__( 'Admin Page IP Filter', 'siteguard' ),     'manage_options', 'siteguard_admin_filter',       array( $this, 'menu_admin_filter' ) );
		add_submenu_page( 'siteguard', esc_html__( 'Rename Login', 'siteguard' ),             esc_html__( 'Rename Login', 'siteguard' ),             'manage_options', 'siteguard_rename_login',       array( $this, 'menu_rename_login' ) );
		add_submenu_page( 'siteguard', esc_html__( 'CAPTCHA', 'siteguard' ),                  esc_html__( 'CAPTCHA', 'siteguard' ),                  'manage_options', 'siteguard_captcha',            array( $this, 'menu_captcha' ) );
		add_submenu_page( 'siteguard', esc_html__( 'Same Login Error Message', 'siteguard' ), esc_html__( 'Same Login Error Message', 'siteguard' ), 'manage_options', 'siteguard_same_error',         array( $this, 'menu_same_error' ) );
		add_submenu_page( 'siteguard', esc_html__( 'Login Lock', 'siteguard' ),               esc_html__( 'Login Lock', 'siteguard' ),               'manage_options', 'siteguard_login_lock',         array( $this, 'menu_login_lock' ) );
		add_submenu_page( 'siteguard', esc_html__( 'Login Alert', 'siteguard' ),              esc_html__( 'Login Alert', 'siteguard' ),              'manage_options', 'siteguard_login_alert',        array( $this, 'menu_login_alert' ) );
		add_submenu_page( 'siteguard', esc_html__( 'Fail once', 'siteguard' ),                esc_html__( 'Fail once', 'siteguard' ),                'manage_options', 'siteguard_fail_once',          array( $this, 'menu_fail_once' ) );
		add_submenu_page( 'siteguard', esc_html__( 'Disable Pingback', 'siteguard' ),         esc_html__( 'Disable Pingback', 'siteguard' ),         'manage_options', 'siteguard_disable_pingback',   array( $this, 'menu_disable_pingback' ) );
		add_submenu_page( 'siteguard', esc_html__( 'Updates Notify', 'siteguard' ),           esc_html__( 'Updates Notify', 'siteguard' ),           'manage_options', 'siteguard_updates_notify',     array( $this, 'menu_updates_notify' ) );
		add_submenu_page( 'siteguard', esc_html__( 'WAF Tuning Support', 'siteguard' ),       esc_html__( 'WAF Tuning Support', 'siteguard' ),       'manage_options', 'siteguard_waf_tuning_support', array( $this, 'menu_waf_tuning_support' ) );
	}

	function menu_dashboard( ) {
		include( 'siteguard-menu-dashboard.php' );
		$dashboard_menu = new SiteGuard_Menu_Dashboard( );
	}
	function menu_admin_filter( ) {
		include( 'siteguard-menu-admin-filter.php' );
		$admin_filter_menu = new SiteGuard_Menu_Admin_Filter( );
	}
	function menu_rename_login( ) {
		include( 'siteguard-menu-rename-login.php' );
		$rename_login_menu = new SiteGuard_Menu_Rename_Login( );
	}
	function menu_captcha( ) {
		include( 'siteguard-menu-captcha.php' );
		$captcha_menu = new SiteGuard_Menu_CAPTCHA( );
	}
	function menu_same_error( ) {
		include( 'siteguard-menu-same-error.php' );
		$same_error_menu = new SiteGuard_Menu_Same_Error( );
	}
	function menu_login_lock( ) {
		include( 'siteguard-menu-login-lock.php' );
		$login_lock_menu = new SiteGuard_Menu_Login_Lock( );
	}
	function menu_login_alert( ) {
		include( 'siteguard-menu-login-alert.php' );
		$login_alert_menu = new SiteGuard_Menu_Login_Alert( );
	}
	function menu_fail_once( ) {
		include( 'siteguard-menu-fail-once.php' );
		$fail_once_menu = new SiteGuard_Menu_Fail_Once( );
	}
	function menu_disable_pingback( ) {
		include( 'siteguard-menu-disable-pingback.php' );
		$disable_pingback_menu = new SiteGuard_Menu_Disable_Pingback( );
	}
	function menu_updates_notify( ) {
		include( 'siteguard-menu-updates-notify.php' );
		$waf_updates_notify_menu = new SiteGuard_Menu_Updates_Notify( );
	}
	function menu_waf_tuning_support( ) {
		include( 'siteguard-menu-waf-tuning-support.php' );
		$waf_tuning_support_menu = new SiteGuard_Menu_WAF_Tuning_Support( );
	}
}
?>
