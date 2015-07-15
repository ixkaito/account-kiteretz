<?php

require_once( 'siteguard-login-history-table.php' );

class SiteGuard_Menu_Dashboard extends SiteGuard_Base {
	var $wp_list_table;
	function __construct( ) {
		$this->wp_list_table = new SiteGuard_LoginHistory_Table( );
		$this->wp_list_table->prepare_items( );
		$this->render_page( );
	}
	function render_page( ) {
		global $config, $login_history;
		$img_path = SITEGUARD_URL_PATH . 'images/';
		$admin_filter_enable     = $config->get( 'admin_filter_enable' );
		$renamelogin_enable      = $config->get( 'renamelogin_enable' );
		$captcha_enable          = $config->get( 'captcha_enable' );
		$same_error_enable       = $config->get( 'same_login_error' );
		$loginlock_enable        = $config->get( 'loginlock_enable' );
		$loginalert_enable       = $config->get( 'loginalert_enable' );
		$fail_once_enable        = $config->get( 'loginlock_fail_once' );
		$disable_pingback_enable = $config->get( 'disable_pingback_enable' );
		$updates_notify_enable   = $config->get( 'updates_notify_enable' );
		$waf_exclude_rule_enable = $config->get( 'waf_exclude_rule_enable' );
		echo '<div class="wrap">';
		echo '<img src="' . $img_path . 'sg_wp_plugin_logo_40.png" alt="SiteGuard Logo" />';
		echo '<h2>' . esc_html__( 'Dashboard', 'siteguard' ) . "</h2>\n";
		echo '<div class="siteguard-description">'
		. esc_html__( 'You can find docs, FAQ and more detailed information about SiteGuard WP Plugin on ', 'siteguard' )
		. '<a href="' . esc_html__( 'http://www.jp-secure.com/cont/products/siteguard_wp_plugin/index_en.html', 'siteguard' ) . '" target="_blank">' . esc_html__( 'SiteGuard WP Plugin Page', 'siteguard' ) . '</a>' . esc_html__( '.', 'siteguard' ) . '</div>';
		echo '<h3>' . esc_html__( 'Setting status', 'siteguard' ) . "</h3>\n";
		$error = check_multisite( );
		if ( is_wp_error( $error ) ) {
			echo '<p class="description">';
			echo $error->get_error_message( );
			echo '</p>';
		}
		?>
		<table class="siteguard-form-table">
		<tr>
		<th scpoe="row">
		<img src=<?php echo '"' . $img_path . ( '1' == $admin_filter_enable ? 'yes.png" alt="yes"' : 'yes_glay.png" alt="no"' ) ?>>
		<a href="?page=siteguard_admin_filter"><?php esc_html_e( 'Admin Page IP Filter', 'siteguard' ) ?></a></th>
		<td><?php esc_html_e( 'The management directory (/wp-admin/) is protected against the connection source which does not login.', 'siteguard' ) ?></td>
		</tr><tr>
		<th scpoe="row">
		<img src=<?php echo '"' . $img_path . ( '1' == $renamelogin_enable ? 'yes.png" alt="yes"' : 'yes_glay.png" alt="no"' ) ?>>
		<a href="?page=siteguard_rename_login"><?php esc_html_e( 'Rename Login', 'siteguard' ) ?></a></th>
		<td><?php esc_html_e( 'The login page name is changed.', 'siteguard' ) ?></td>
		</tr><tr>
		<th scpoe="row">
		<img src=<?php echo '"' . $img_path . ( '1' == $captcha_enable ? 'yes.png" alt="yes"' : 'yes_glay.png" alt="no"' ) ?>>
		<a href="?page=siteguard_captcha"><?php esc_html_e( 'CAPTCHA', 'siteguard' ) ?></a></th>
		<td><?php esc_html_e( 'CAPTCHA is added to the login page or comment post.', 'siteguard' ) ?></td>
		</tr><tr>
		<th scpoe="row">
		<img src=<?php echo '"' . $img_path . ( '1' == $same_error_enable ? 'yes.png" alt="yes"' : 'yes_glay.png" alt="no"' ) ?>>
		<a href="?page=siteguard_same_error"><?php esc_html_e( 'Same Login Error Message', 'siteguard' ) ?></a></th>
		<td><?php esc_html_e( 'Instead of the detailed error message at the login error, the single message is returned.', 'siteguard' ) ?></td>
		</tr><tr>
		<th scope="row">
		<img src=<?php echo '"' . $img_path . ( '1' == $loginlock_enable ? 'yes.png" alt="yes"' : 'yes_glay.png" alt="no"' ) ?>>
		<a href="?page=siteguard_login_lock"><?php esc_html_e( 'Login Lock', 'siteguard' ) ?></a></th>
		<td><?php esc_html_e( 'The connection source which repeats login failure is being locked within a certain period.', 'siteguard' ) ?></td>
		</tr><tr>
		<th scope="row">
		<img src=<?php echo '"' . $img_path . ( '1' == $loginalert_enable ? 'yes.png" alt="yes"' : 'yes_glay.png" alt="no"' ) ?>>
		<a href="?page=siteguard_login_alert"><?php esc_html_e( 'Login Alert', 'siteguard' ) ?></a></th>
		<td><?php esc_html_e( 'E-mail notifies that there was login.', 'siteguard' ) ?></td>
		</tr><tr>
		<th scope="row">
		<img src=<?php echo '"' . $img_path . ( '1' == $fail_once_enable ? 'yes.png" alt="yes"' : 'yes_glay.png" alt="no"' ) ?>>
		<a href="?page=siteguard_fail_once"><?php esc_html_e( 'Fail once', 'siteguard' ) ?></a></th>
		<td><?php esc_html_e( 'The first login must fail even if the input is correct.', 'siteguard' ) ?></td>
		</tr><tr>
		<th scpoe="row">
		<img src=<?php echo '"' . $img_path . ( '1' == $disable_pingback_enable ? 'yes.png" alt="yes"' : 'yes_glay.png" alt="no"' ) ?>>
		<a href="?page=siteguard_disable_pingback"><?php esc_html_e( 'Disable Pingback', 'siteguard' ) ?></a></th>
		<td><?php esc_html_e( 'The abuse of pingback is prevented.', 'siteguard' ) ?></td>
		</tr><tr>
		<th scpoe="row">
		<img src=<?php echo '"' . $img_path . ( '1' == $updates_notify_enable ? 'yes.png" alt="yes"' : 'yes_glay.png" alt="no"' ) ?>>
		<a href="?page=siteguard_updates_notify"><?php esc_html_e( 'Updates Notify', 'siteguard' ) ?></a></th>
		<td><?php esc_html_e( 'If WordPress core, plugins, and themes updates are needed , sends email to notify administrators.', 'siteguard' ) ?></td>
		</tr><tr>
		<th scpoe="row">
		<img src=<?php echo '"' . $img_path . ( '1' == $waf_exclude_rule_enable ? 'yes.png" alt="yes"' : 'yes_glay.png" alt="no"' ) ?>>
		<a href="?page=siteguard_waf_tuning_support"><?php esc_html_e( 'WAF Tuning Support', 'siteguard' ) ?></a></th>
		<td><?php esc_html_e( 'The exclude rule for WAF (SiteGuard Lite) is created.', 'siteguard' ) ?></td>
		</tr>
		</table>
		<hr />
		<form name="form1" method="post" action="">
		<?php echo '<h3>' . esc_html__( 'Login history', 'siteguard' ) . "</h3>\n"; ?>
		<?php $this->wp_list_table->display( ) ?>
		<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>">
		</form>
		</div>
		<?php
	}
}

?>
