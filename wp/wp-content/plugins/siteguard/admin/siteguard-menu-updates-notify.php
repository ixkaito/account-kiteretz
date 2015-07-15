<?php

class SiteGuard_Menu_Updates_Notify extends SiteGuard_Base {
	function __construct( ) {
		$this->render_page( );
	}
	function is_notify_value( $value ) {
		if ( '0' == $value || '1' == $value || '2' == $value ) {
			return true;
		}
		return false;
	}
	function render_page( ) {
		global $config, $updates_notify;

		$opt_name_enable  = 'updates_notify_enable';
		$opt_name_wpcore  = 'notify_wpcore';
		$opt_name_plugins = 'notify_plugins';
		$opt_name_themes  = 'notify_themes';

		$opt_val_enable  = $config->get( $opt_name_enable );
		$opt_val_wpcore  = $config->get( $opt_name_wpcore );
		$opt_val_plugins = $config->get( $opt_name_plugins );
		$opt_val_themes  = $config->get( $opt_name_themes );
		if ( isset( $_POST['update'] ) && check_admin_referer( 'siteguard-menu-updates-notify-submit' ) ) {
			$error = false;
			$errors = check_multisite( );
			if ( is_wp_error( $errors ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( $errors->get_error_message( ), 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( ( false == $error )
			  && ( ( false == $this->is_switch_value( $_POST[ $opt_name_enable ] ) )
			    || ( false == $this->is_switch_value( $_POST[ $opt_name_wpcore ] ) )
			    || ( false == $this->is_notify_value( $_POST[ $opt_name_plugins ] ) )
			    || ( false == $this->is_notify_value( $_POST[ $opt_name_themes ] ) ) ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( 'ERROR: Invalid input value.', 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( false == $error && '1' == $_POST[ $opt_name_enable ] ) {
				$ret = $updates_notify->check_requirements( );
				if ( is_wp_error( $ret ) ) {
					echo '<div class="error settings-error"><p><strong>' . $ret->get_error_message( ) . '</strong></p></div>';
					$error = true;
					$config->set( $opt_name_enable, '0' );
					$config->update( );
				}
			}
			if ( false == $error ) {
				$opt_val_enable  = $_POST[ $opt_name_enable ];
				$opt_val_wpcore  = $_POST[ $opt_name_wpcore ];
				$opt_val_plugins = $_POST[ $opt_name_plugins ];
				$opt_val_themes  = $_POST[ $opt_name_themes ];
				$config->set( $opt_name_enable,  $opt_val_enable );
				$config->set( $opt_name_wpcore,  $opt_val_wpcore );
				$config->set( $opt_name_plugins, $opt_val_plugins );
				$config->set( $opt_name_themes,  $opt_val_themes );
				$config->update( );
				if ( '1' == $opt_val_enable ) {
					SiteGuard_UpdatesNotify::feature_on( );
				} else {
					SiteGuard_UpdatesNotify::feature_off( );
				}
				?>
				<div class="updated"><p><strong><?php esc_html_e( 'Options saved.', 'siteguard' ); ?></strong></p></div>
				<?php
			}
		}

		echo '<div class="wrap">';
		echo '<img src="' . SITEGUARD_URL_PATH . 'images/sg_wp_plugin_logo_40.png" alt="SiteGuard Logo" />';
		echo '<h2>' . esc_html__( 'Updates Notify', 'siteguard' ) . '</h2>';
		echo '<div class="siteguard-description">'
		. esc_html__( 'You can find docs about this function on ', 'siteguard' )
		. '<a href="' . esc_html__( 'http://www.jp-secure.com/cont/products/siteguard_wp_plugin/updates_notify_en.html', 'siteguard' ) 
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
			<input type="radio" name="<?php echo $opt_name_enable ?>" id="<?php echo $opt_name_enable.'_on' ?>" value="1" <?php echo ( '1' == $opt_val_enable ? 'checked' : '') ?> >
			<label for="<?php echo $opt_name_enable.'_on' ?>"><?php esc_html_e( 'ON', 'siteguard' ) ?></label>
			</li><li>
			<input type="radio" name="<?php echo $opt_name_enable ?>" id="<?php echo $opt_name_enable.'_off' ?>" value="0" <?php echo ( '0' == $opt_val_enable ? 'checked' : '') ?> >
			<label for="<?php echo $opt_name_enable.'_off' ?>"><?php esc_html_e( 'OFF', 'siteguard' ) ?></label>
			</li>
			</ul>
			<?php
			$error = $updates_notify->check_requirements( );
			if ( is_wp_error( $error ) ) {
				echo '<p class="description">';
				echo $error->get_error_message( );
				echo '</p>';
			}
			?>
		</th>
		</tr><tr>
		<th scope="row"><?php esc_html_e( 'WordPress updates', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="<?php echo $opt_name_wpcore ?>" id="<?php echo $opt_name_wpcore.'_0' ?>" value="0" <?php echo ( '0' == $opt_val_wpcore ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_wpcore.'_0' ?>"><?php esc_html_e( 'Disable', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_wpcore ?>" id="<?php echo $opt_name_wpcore.'_1' ?>" value="1" <?php echo ( '1' == $opt_val_wpcore ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_wpcore.'_1' ?>"><?php esc_html_e( 'Enable', 'siteguard' ) ?></label>
			</td>
		</tr><tr>
		<th scope="row"><?php esc_html_e( 'Plugins updates', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="<?php echo $opt_name_plugins ?>" id="<?php echo $opt_name_plugins.'_0' ?>" value="0" <?php echo ( '0' == $opt_val_plugins ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_plugins.'_0' ?>"><?php esc_html_e( 'Disable', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_plugins ?>" id="<?php echo $opt_name_plugins.'_1' ?>" value="1" <?php echo ( '1' == $opt_val_plugins ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_plugins.'_1' ?>"><?php esc_html_e( 'All plugins', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_plugins ?>" id="<?php echo $opt_name_plugins.'_2' ?>" value="2" <?php echo ( '2' == $opt_val_plugins ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_plugins.'_2' ?>"><?php esc_html_e( 'Active plugins only', 'siteguard' ) ?></label>
			</td>
		</tr><tr>
		<th scope="row"><?php esc_html_e( 'Themes updates', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="<?php echo $opt_name_themes ?>" id="<?php echo $opt_name_themes.'_0' ?>" value="0" <?php echo ( '0' == $opt_val_themes ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_themes.'_0' ?>"><?php esc_html_e( 'Disable', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_themes ?>" id="<?php echo $opt_name_themes.'_1' ?>" value="1" <?php echo ( '1' == $opt_val_themes ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_themes.'_1' ?>"><?php esc_html_e( 'All themes', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_themes ?>" id="<?php echo $opt_name_themes.'_2' ?>" value="2" <?php echo ( '2' == $opt_val_themes ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_themes.'_2' ?>"><?php esc_html_e( 'Active themes only', 'siteguard' ) ?></label>
			</td>
		</tr>
		</table>
		<div class="siteguard-description">
		<?php esc_html_e( 'Basic of security is that always you use the latest version. If WordPress core, plugins, and themes updates are needed , sends email to notify administrators. Check for updates will be run every 24 hours.', 'siteguard' ) ?>
		</div>
		<hr />
		<input type="hidden" name="update" value="Y">

		<?php
		wp_nonce_field( 'siteguard-menu-updates-notify-submit' );
		submit_button( );
		?>

		</form>
		</div>

		<?php
	}
}
?>
