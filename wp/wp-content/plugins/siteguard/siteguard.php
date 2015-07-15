<?php
/*
Plugin Name: SiteGuard WP Plugin
Plugin URI: http://www.jp-secure.com/cont/products/siteguard_wp_plugin/index_en.html
Description: Only installing SiteGuard WP Plugin on WordPress, its security can be improved. SiteGurad WP Plugin is the plugin specialized for the protection against the attack to the management page and login. It also have the function to create the exclude rule for WAF (SiteGuard Lite, to use it, WAF should be installed on the Web server.)
Author: JP-Secure
Author URI: http://www.jp-secure.com/eng/
Text Domain: siteguard
Domain Path: /languages/
Version: 1.2.3
*/

/*  Copyright 2014 JP-Secure Inc

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SITEGUARD_VERSION', '1.2.3' );

define( 'SITEGUARD_PATH', plugin_dir_path( __FILE__ ) );
define( 'SITEGUARD_URL_PATH', plugin_dir_url( __FILE__ ) );

define( 'SITEGUARD_LOGIN_SUCCESS',   0 );
define( 'SITEGUARD_LOGIN_FAILED',    1 );
define( 'SITEGUARD_LOGIN_FAIL_ONCE', 2 );
define( 'SITEGUARD_LOGIN_LOCKED',    3 );

require_once( 'classes/siteguard-base.php' );
require_once( 'classes/siteguard-config.php' );
require_once( 'classes/siteguard-htaccess.php' );
require_once( 'classes/siteguard-admin-filter.php' );
require_once( 'classes/siteguard-rename-login.php' );
require_once( 'classes/siteguard-login-history.php' );
require_once( 'classes/siteguard-login-lock.php' );
require_once( 'classes/siteguard-login-alert.php' );
require_once( 'classes/siteguard-captcha.php' );
require_once( 'classes/siteguard-disable-pingback.php' );
require_once( 'classes/siteguard-waf-exclude-rule.php' );
require_once( 'classes/siteguard-updates-notify.php' );
require_once( 'admin/siteguard-menu-init.php' );

global $htaccess;
global $config;
global $admin_filter;
global $rename_login;
global $loginlock;
global $loginalert;
global $captcha;
global $login_history;
global $pingback;
global $waf_exclude_rule;
global $updates_notify;

$htaccess          = new SiteGuard_Htaccess( );
$config            = new SiteGuard_Config( );
$admin_filter      = new SiteGuard_AdminFilter( );
$rename_login      = new SiteGuard_RenameLogin( );
$loginlock         = new SiteGuard_LoginLock( );
$loginalert        = new SiteGuard_LoginAlert( );
$login_history     = new SiteGuard_LoginHistory( );
$captcha           = new SiteGuard_CAPTCHA( );
$pingback          = new SiteGuard_Disable_Pingback( );
$waf_exclude_rule  = new SiteGuard_WAF_Exclude_Rule( );
$updates_notify    = new SiteGuard_UpdatesNotify( );

function siteguard_activate( ) {
	global $config, $admin_filter, $rename_login, $login_history, $captcha, $loginlock, $loginalert, $pingback, $waf_exclude_rule, $updates_notify;

	load_plugin_textdomain(
		'siteguard',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);

	$config->set( 'show_admin_notices', '0' );
	$config->update( );
	$admin_filter->init();
	$rename_login->init();
	$login_history->init();
	$captcha->init();
	$loginlock->init();
	$loginalert->init();
	$pingback->init();
	$waf_exclude_rule->init();
	$updates_notify->init();
}
register_activation_hook( __FILE__, 'siteguard_activate' );

function siteguard_deactivate( ) {
	global $config;
	$config->set( 'show_admin_notices', '0' );
	$config->update( );
	SiteGuard_RenameLogin::feature_off( );
	SiteGuard_AdminFilter::feature_off( );
	SiteGuard_WAF_Exclude_Rule::feature_off( );
	SiteGuard_UpdatesNotify::feature_off( );
}
register_deactivation_hook( __FILE__, 'siteguard_deactivate' );


class SiteGuard extends SiteGuard_Base {
	var $menu_init;
	function __construct( ) {
		global $config;
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		$this->htaccess_check( );
		if ( is_admin( ) ) {
			$this->menu_init = new SiteGuard_Menu_Init( );
			add_action( 'admin_init', array( $this, 'upgrade' ) );
			if ( '0' === $config->get( 'show_admin_notices' ) && '1' == $config->get( 'renamelogin_enable' ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notices' ) );
				$config->set( 'show_admin_notices', '1' );
				$config->update( );
			}
		}
	}
	function plugins_loaded( ) {
		load_plugin_textdomain(
			'siteguard',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}
	function htaccess_check( ) {
		global $config;
		if ( '1' == $config->get( 'admin_filter_enable' ) ) {
			if ( ! SiteGuard_Htaccess::is_exists_setting( SiteGuard_AdminFilter::get_mark( ) ) ) {
				$config->set( 'admin_filter_enable', '0' );
				$config->update( );
			}
		}
		if ( '1' == $config->get( 'renamelogin_enable' ) ) {
			if ( ! SiteGuard_Htaccess::is_exists_setting( SiteGuard_RenameLogin::get_mark( ) ) ) {
				$config->set( 'renamelogin_enable', '0' );
				$config->update( );
			}
		}
		if ( '1' == $config->get( 'waf_exclude_rule_enable' ) ) {
			if ( ! SiteGuard_Htaccess::is_exists_setting( SiteGuard_WAF_Exclude_Rule::get_mark( ) ) ) {
				$config->set( 'waf_exclude_rule_enable', '0' );
				$config->update( );
			}
		}
	}
	function admin_notices( ) {
		global $rename_login;
		echo '<div class="updated" style="background-color:#719f1d;"><p><span style="border: 4px solid #def1b8;padding: 4px 4px;color:#fff;font-weight:bold;background-color:#038bc3;">';
		echo esc_html__( 'Login page URL was changed.', 'siteguard' ) . '</span>';
		echo '<span style="color:#eee;">';
		echo esc_html__( ' Please bookmark ', 'siteguard' ) . '<a style="color:#fff;text-decoration:underline;" href="' . esc_url( wp_login_url( ) ) . '">';
		echo esc_html__( 'new login URL', 'siteguard' ) . '</a>';
		echo esc_html__( '. Setting change is ', 'siteguard' ) . '<a style="color:#fff;text-decoration:underline;" href="' .  esc_url( menu_page_url( 'siteguard_rename_login', false ) ) . '">';
		echo esc_html__( 'here', 'siteguard' ) . '</a>';
		echo '.</span></p></div>';
		$rename_login->send_notify( );
	}
	function upgrade( ) {
		global $config, $rename_login, $admin_filter, $loginalert, $updates_notify;
		$upgrade_ok  = true;
		$old_version = $config->get( 'version' );
		if ( '' == $old_version ) {
			$old_version = '0.0.0';
		}
		if ( version_compare( $old_version, '1.0.3' ) < 0 ) {
			if ( '1' == $config->get( 'renamelogin_enable' ) ) {
				if ( true != $rename_login->feature_on( ) ) {
					$upgrade_ok = false;
				}
			}
		}
		if ( version_compare( $old_version, '1.0.6' ) < 0 ) {
			if ( '1' == $config->get( 'admin_filter_enable' ) ) {
				if ( true != $admin_filter->feature_on( $_SERVER['REMOTE_ADDR'] ) ) {
					$upgrade_ok = false;
				}
			}
		}
		if ( version_compare( $old_version, '1.1.1' ) < 0 ) {
			$loginalert->init();
		}
		if ( version_compare( $old_version, '1.2.0' ) < 0 ) {
			$updates_notify->init();
		}
		if ( $upgrade_ok && SITEGUARD_VERSION != $old_version ) {
			$config->set( 'version', SITEGUARD_VERSION );
			$config->update( );
		}
	}
}
$siteguard = new SiteGuard;
?>
