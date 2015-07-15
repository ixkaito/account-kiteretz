<?php

class SiteGuard_Menu_Rename_Login extends SiteGuard_Base {
	function __construct( ) {
		$this->render_page( );
	}
	function render_page( ) {
		global $rename_login, $config;

		$opt_name_feature           = 'renamelogin_enable';
		$opt_name_rename_login_path = 'renamelogin_path';

		$opt_val_feature           = $config->get( $opt_name_feature );
		$opt_val_rename_login_path = $config->get( $opt_name_rename_login_path );
		if ( isset( $_POST['update'] ) && check_admin_referer( 'siteguard-menu-rename-login-submit' ) ) {
			$error = false;
			$errors = check_multisite( );
			if ( is_wp_error( $errors ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( $errors->get_error_message( ), 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( false == $error && '1' == $_POST[ $opt_name_feature ] && false == $this->check_module( 'rewrite' ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( 'To use this function, “mod_rewrite” should be loaded on Apache.', 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
				$config->set( $opt_name_feature, '0' );
				$config->update( );
				$rename_login->feature_off( );
				$opt_val_feature = '0';
			}
			if ( false == $error && false == $this->is_switch_value( $_POST[ $opt_name_feature ] ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( 'ERROR: Invalid input value.', 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( false == $error && '1' == $_POST[ $opt_name_feature ] ) {
				$incompatible_plugin = $rename_login->get_active_incompatible_plugin( );
				if ( null != $incompatible_plugin ) {
					echo '<div class="error settings-error"><p><strong>';
					echo esc_html__( 'This function and Plugin "', 'siteguard' ) . $incompatible_plugin . esc_html__( '" cannot be used at the same time.', 'siteguard' );
					echo '</strong></p></div>';
					$error = true;
					$config->set( $opt_name_feature, '0' );
					$config->update( );
					$rename_login->feature_off( );
					$opt_val_feature = '0';
					$opt_val_rename_login_path = stripslashes( $_POST[ $opt_name_rename_login_path ] );
				}
			}
			if ( false == $error && 1 != preg_match( '/^[a-zA-Z0-9_-]+$/', $_POST[ $opt_name_rename_login_path ] ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( 'It is only an alphanumeric character, a hyphen, and an underbar that can be used for New Login Path.', 'siteguard' );
				echo '</strong></p></div>';
				$opt_val_rename_login_path = stripslashes( $_POST[ $opt_name_rename_login_path ] );
				$error = true;
			}
			if ( false == $error && 1 == preg_match( '/^(wp-admin|wp-login$|login$)/', $_POST[ $opt_name_rename_login_path ], $matches ) ) {
				echo '<div class="error settings-error"><p><strong>';
				echo esc_html( $matches[0] ) . esc_html__( ' can not be used for New Login Path.', 'siteguard' );
				echo '</strong></p></div>';
				$opt_val_rename_login_path = stripslashes( $_POST[ $opt_name_rename_login_path ] );
				$error = true;
			}
			if ( false == $error ) {
				$opt_val_feature           = $_POST[ $opt_name_feature ];
				$opt_val_rename_login_path = $_POST[ $opt_name_rename_login_path ];
				$config->set( $opt_name_feature,           $opt_val_feature );
				$config->set( $opt_name_rename_login_path, $opt_val_rename_login_path );
				$config->update( );
				if ( '0' == $opt_val_feature ) {
					$rename_login->feature_off( );
				} else {
					$rename_login->feature_on( );
					$rename_login->send_notify( );
				}
				?>
				<div class="updated"><p><strong><?php esc_html_e( 'Options saved.', 'siteguard' ); ?></strong></p></div>
				<?php
			}
		}

		echo '<div class="wrap">';
		echo '<img src="' . SITEGUARD_URL_PATH . 'images/sg_wp_plugin_logo_40.png" alt="SiteGuard Logo" />';
		echo '<h2>' . esc_html__( 'Rename Login', 'siteguard' ) . '</h2>';
		echo '<div class="siteguard-description">'
		. esc_html__( 'You can find docs about this function on ', 'siteguard' )
		. '<a href="' . esc_html__( 'http://www.jp-secure.com/cont/products/siteguard_wp_plugin/rename_login_en.html', 'siteguard' ) 
		. '" target="_blank">' 
		. esc_html__( 'here', 'siteguard' ) 
		. '</a>' 
		. esc_html__( '.', 'siteguard' ) 
		. '</div>';
		?>
		<form name="form1" method="post" action="">
		<table class="form-table">
		<tr>
		<th scope="row" colspan="2">
			<ul class="siteguard-radios">
			<li>
			<input type="radio" name="<?php echo $opt_name_feature ?>" id="<?php echo $opt_name_feature.'_on' ?>" value="1" <?php echo ( '1' == $opt_val_feature ? 'checked' : '') ?> >
			<label for="<?php echo $opt_name_feature.'_on' ?>"><?php echo esc_html_e( 'ON', 'siteguard' ) ?></label>
			</li><li>
			<input type="radio" name="<?php echo $opt_name_feature ?>" id="<?php echo $opt_name_feature.'_off' ?>" value="0" <?php echo ( '0' == $opt_val_feature ? 'checked' : '') ?> >
			<label for="<?php echo $opt_name_feature.'_off' ?>"><?php echo esc_html_e( 'OFF', 'siteguard' ) ?></label>
			</li>
			</ul>
			<?php
			$error = check_multisite( );
			if ( is_wp_error( $error ) ) {
				echo '<p class="description">';
				echo $error->get_error_message( );
				echo '</p>';
			}
			echo '<p class="description">';
			esc_html_e( 'To use this function, “mod_rewrite” should be loaded on Apache.', 'siteguard' );
			echo '</p>';
			?>
		</th>
		</tr><tr>
		<th scope="row"><label for="<?php echo $opt_name_rename_login_path ?>"><?php esc_html_e( 'New Login Path', 'siteguard' ); ?></label></th>
		<td>
			<?php echo site_url() . '/' ?><input type="text" name="<?php echo $opt_name_rename_login_path ?>" id="<?php echo $opt_name_rename_login_path ?>" value="<?php echo esc_attr( $opt_val_rename_login_path ) ?>" >
			<?php
			echo '<p class="description">';
			esc_html_e( 'An alphanumeric character, a hyphen, and an underbar can be used.', 'siteguard' );
			echo '</p>';
			?>
		</td>
		</tr>
		</table>
		<input type="hidden" name="update" value="Y">
		<div class="siteguard-description">
		<?php esc_html_e( 'It is the function to decrease the vulnerability against an illegal login attempt attack such as a brute force attack or a password list attack. The login page name (wp-login.php) is changed. The initial value is “login_&lt;5 random digits&gt;” but it can be changed to a favorite name.', 'siteguard' ) ?> 
		</div>
		<hr />
		<?php
		wp_nonce_field( 'siteguard-menu-rename-login-submit' );
		submit_button( );
		?>
		</form>
		</div>
		<?php
	}
}

?>
