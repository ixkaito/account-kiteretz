<?php

class SiteGuard_Menu_Disable_Pingback extends SiteGuard_Base {
	function __construct( ) {
		$this->render_page( );
	}
	function render_page( ) {
		global $config;

		$opt_name_feature = 'disable_pingback_enable';
		$opt_val_feature  = $config->get( $opt_name_feature );
		if ( isset( $_POST['update'] ) && check_admin_referer( 'siteguard-menu-disable-pingback-submit' ) ) {
			$error = false;
			$errors = check_multisite( );
			if ( is_wp_error( $errors ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( $errors->get_error_message( ), 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( false == $error && false == $this->is_switch_value( $_POST[ $opt_name_feature ] ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( 'ERROR: Invalid input value.', 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( false == $error ) {
				$opt_val_feature = $_POST[ $opt_name_feature ];
				$config->set( $opt_name_feature, $opt_val_feature );
				$config->update( );
				?>
				<div class="updated"><p><strong><?php esc_html_e( 'Options saved.', 'siteguard' ); ?></strong></p></div>
				<?php
			}
		}

		echo '<div class="wrap">';
		echo '<img src="' . SITEGUARD_URL_PATH . 'images/sg_wp_plugin_logo_40.png" alt="SiteGuard Logo" />';
		echo '<h2>' . esc_html__( 'Disable Pingback', 'siteguard' ) . '</h2>';
		echo '<div class="siteguard-description">'
		. esc_html__( 'You can find docs about this function on ', 'siteguard' )
		. '<a href="' . esc_html__( 'http://www.jp-secure.com/cont/products/siteguard_wp_plugin/pingback_en.html', 'siteguard' ) 
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
			?>
		</th>
		</tr>
		</table>
		<input type="hidden" name="update" value="Y">
		<div class="siteguard-description">
		<?php esc_html_e( 'The pingback function is disabled and its abuse is prevented.', 'siteguard' ) ?>
		</div>
		<hr />
		<?php
		wp_nonce_field( 'siteguard-menu-disable-pingback-submit' );
		submit_button( );
		?>
		</form>
		</div>
		<?php
	}
}

?>
