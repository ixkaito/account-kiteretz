<?php

class SiteGuard_Menu_Admin_Filter extends SiteGuard_Base {
	function __construct( ) {
		$this->render_page( );
	}
	function cvt_camma2ret( $exclude ) {
		$result = str_replace( ' ', '', $exclude );
		return    str_replace( ',', "\r\n", $result );
	}
	function cvt_ret2camma( $exclude ) {
		$result = str_replace( ' ', '', $exclude );
		$result = str_replace( ',', '', $result );
		$result = preg_replace( '/(\r\n){2,}/', "\r\n", $result );
		$result = preg_replace( '/\r\n$/', '', $result );
		$result = str_replace( "\r\n", ',', $result );
		$result = str_replace( "\r",   ',', $result );
		return    str_replace( "\n",   ',', $result );
	}
	function render_page( ) {
		global $admin_filter, $config;

		$opt_name_feature = 'admin_filter_enable';
		$opt_name_exclude = 'admin_filter_exclude_path';

		$opt_val_feature = $config->get( $opt_name_feature );
		$opt_val_exclude = $this->cvt_camma2ret( $config->get( $opt_name_exclude ) );
		if ( isset( $_POST['update'] ) && check_admin_referer( 'siteguard-menu-admin-filter-submit' ) ) {
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
				$admin_filter->feature_off( );
				$opt_val_feature = '0';
			}
			if ( false == $error && false == $this->is_switch_value( $_POST[ $opt_name_feature ] ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( 'ERROR: Invalid input value.', 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( false == $error ) {
				$opt_val_feature = $_POST[ $opt_name_feature ];
				$opt_val_exclude = $this->cvt_ret2camma( stripslashes( $_POST[ $opt_name_exclude ] ) );
				$config->set( $opt_name_feature, $opt_val_feature );
				$config->set( $opt_name_exclude, $opt_val_exclude );
				$config->update( );
				$opt_val_exclude = $this->cvt_camma2ret( $opt_val_exclude );
				$mark = $admin_filter->get_mark( );
				if ( '0' == $opt_val_feature ) {
					$admin_filter->feature_off( );
				} else {
					$admin_filter->feature_on( $_SERVER['REMOTE_ADDR'] );
				}
				?>
				<div class="updated"><p><strong><?php esc_html_e( 'Options saved.', 'siteguard' ); ?></strong></p></div>
				<?php
			}
		}

		echo '<div class="wrap">';
		echo '<img src="' . SITEGUARD_URL_PATH . 'images/sg_wp_plugin_logo_40.png" alt="SiteGuard Logo" />';
		echo '<h2>' . esc_html__( 'Admin Page IP Filter', 'siteguard' ) . '</h2>';
		echo '<div class="siteguard-description">'
		. esc_html__( 'You can find docs about this function on ', 'siteguard' )
		. '<a href="' . esc_html__( 'http://www.jp-secure.com/cont/products/siteguard_wp_plugin/admin_filter_en.html', 'siteguard' ) 
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
			<input type="radio" name="<?php echo $opt_name_feature ?>" id="<?php echo $opt_name_feature.'_on' ?>" value="1" <?php echo ( '1' == $opt_val_feature ? 'checked' : '' ) ?> >
			<label for="<?php echo $opt_name_feature.'_on' ?>" ><?php echo esc_html_e( 'ON', 'siteguard' ) ?></label>
			</li>
			<li>
			<input type="radio" name="<?php echo $opt_name_feature ?>" id="<?php echo $opt_name_feature.'_off' ?>" value="0" <?php echo ( '0' == $opt_val_feature ? 'checked' : '') ?> >
			<label for="<?php echo $opt_name_feature.'_off' ?>" ><?php echo esc_html_e( 'OFF', 'siteguard' ) ?></label>
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
		<th scope="row"><label for="<?php echo $opt_name_exclude ?>"><?php echo esc_html_e( 'Exclude Path', 'siteguard' ) ?></label></th>
		<td><textarea name="<?php echo $opt_name_exclude ?>" id="<?php echo $opt_name_exclude ?>" col=40 rows=5 ><?php echo esc_textarea( $opt_val_exclude ) ?></textarea>
		<p class="description"><?php esc_html_e( 'The path of /wp-admin/ henceforth is specified. To specify more than one, separate them with new line. ', 'siteguard' ) ?></p></td>
		</tr>
		</table>
		<input type="hidden" name="update" value="Y">
		<div class="siteguard-description">
		<?php esc_html_e( 'It is the function for the protection against the attack to the management page (under /wp-admin/.) To the access from the connection source IP address which does not login to the management page, 404 (Not Found) is returned. At the login, the connection source IP address is recorded and the access to that page is allowed. The connection source IP address which does not login for more than 24 hours is sequentially deleted. The URL (under /wp-admin/) where this function is excluded can be specified.', 'siteguard' ); ?> 
		</div>
		<hr />
		<?php
		wp_nonce_field( 'siteguard-menu-admin-filter-submit' );
		submit_button( );
		?>
		</form>
		</div>

		<?php
	}
}
?>
