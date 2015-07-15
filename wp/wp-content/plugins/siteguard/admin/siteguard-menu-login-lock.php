<?php

class SiteGuard_Menu_Login_Lock extends SiteGuard_Base {
	function __construct( ) {
		$this->render_page( );
	}
	function is_interval_value( $value ) {
		if ( '1' == $value || '5' == $value || '30' == $value ) {
			return true;
		}
		return false;
	}
	function is_threshold_value( $value ) {
		if ( '3' == $value || '10' == $value || '100' == $value ) {
			return true;
		}
		return false;
	}
	function is_locksec_value( $value ) {
		if ( '30' == $value || '60' == $value || '300' == $value ) {
			return true;
		}
		return false;
	}
	function render_page( ) {
		global $config;

		$opt_name_enable    = 'loginlock_enable';
		$opt_name_interval  = 'loginlock_interval';
		$opt_name_threshold = 'loginlock_threshold';
		$opt_name_locksec   = 'loginlock_locksec';

		$opt_val_enable    = $config->get( $opt_name_enable );
		$opt_val_interval  = $config->get( $opt_name_interval );
		$opt_val_threshold = $config->get( $opt_name_threshold );
		$opt_val_locksec   = $config->get( $opt_name_locksec );
		if ( isset( $_POST['update'] ) && check_admin_referer( 'siteguard-menu-login-lock-submit' ) ) {
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
			    || ( false == $this->is_interval_value( $_POST[ $opt_name_interval ] ) )
			    || ( false == $this->is_threshold_value( $_POST[ $opt_name_threshold ] ) )
			    || ( false == $this->is_locksec_value( $_POST[ $opt_name_locksec ] ) ) ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( 'ERROR: Invalid input value.', 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( false == $error ) {
				$opt_val_enable    = $_POST[ $opt_name_enable ];
				$opt_val_interval  = $_POST[ $opt_name_interval ];
				$opt_val_threshold = $_POST[ $opt_name_threshold ];
				$opt_val_locksec   = $_POST[ $opt_name_locksec ];
				$config->set( $opt_name_enable,    $opt_val_enable );
				$config->set( $opt_name_interval,  $opt_val_interval );
				$config->set( $opt_name_threshold, $opt_val_threshold );
				$config->set( $opt_name_locksec,   $opt_val_locksec );
				$config->update( );
				?>
				<div class="updated"><p><strong><?php esc_html_e( 'Options saved.', 'siteguard' ); ?></strong></p></div>
				<?php
			}
		}

		echo '<div class="wrap">';
		echo '<img src="' . SITEGUARD_URL_PATH . 'images/sg_wp_plugin_logo_40.png" alt="SiteGuard Logo" />';
		echo '<h2>' . esc_html__( 'Login Lock', 'siteguard' ) . '</h2>';
		echo '<div class="siteguard-description">'
		. esc_html__( 'You can find docs about this function on ', 'siteguard' )
		. '<a href="' . esc_html__( 'http://www.jp-secure.com/cont/products/siteguard_wp_plugin/login_lock_en.html', 'siteguard' ) 
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
			$error = check_multisite( );
			if ( is_wp_error( $error ) ) {
				echo '<p class="description">';
				echo $error->get_error_message( );
				echo '</p>';
			}
			?>
		</th>
		</tr><tr>
		<th scope="row"><?php esc_html_e( 'Interval', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="<?php echo $opt_name_interval ?>" id="<?php echo $opt_name_interval.'_1' ?>" value="1" <?php echo ( '1' == $opt_val_interval ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_interval.'_1' ?>"><?php esc_html_e( '1 second', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_interval ?>" id="<?php echo $opt_name_interval.'_5' ?>" value="5" <?php echo ( '5' == $opt_val_interval ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_interval.'_5' ?>"><?php esc_html_e( '5 seconds', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_interval ?>" id="<?php echo $opt_name_interval.'_30' ?>" value="30" <?php echo ( '30' == $opt_val_interval ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_interval.'_30' ?>"><?php esc_html_e( '30 seconds', 'siteguard' ) ?></label>
			</td>
		</tr><tr>
		<th scope="row"><?php esc_html_e( 'Threshold', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="<?php echo $opt_name_threshold ?>" id="<?php echo $opt_name_threshold.'_3' ?>" value="3" <?php echo ( '3' == $opt_val_threshold ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_threshold.'_3' ?>"><?php esc_html_e( '3 times', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_threshold ?>" id="<?php echo $opt_name_threshold.'_10' ?>" value="10" <?php echo ( '10' == $opt_val_threshold ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_threshold.'_10' ?>"><?php esc_html_e( '10 times', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_threshold ?>" id="<?php echo $opt_name_threshold.'_100' ?>" value="100" <?php echo ( '100' == $opt_val_threshold ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_threshold.'_100' ?>"><?php esc_html_e( '100 times', 'siteguard' ) ?></label>
			</td>
		</tr><tr>
		<th scope="row"><?php esc_html_e( 'Lock Time', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="<?php echo $opt_name_locksec ?>" id="<?php echo $opt_name_locksec.'_30' ?>" value="30" <?php echo ( '30' == $opt_val_locksec ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_locksec.'_30' ?>"><?php esc_html_e( '30 seconds', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_locksec ?>" id="<?php echo $opt_name_locksec.'_60' ?>" value="60" <?php echo ( '60' == $opt_val_locksec ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_locksec.'_60' ?>"><?php esc_html_e( '1 minute', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_locksec ?>" id="<?php echo $opt_name_locksec.'_300' ?>" value="300" <?php echo ( '300' == $opt_val_locksec ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_locksec.'_300' ?>"><?php esc_html_e( '5 minutes', 'siteguard' ) ?></label>
			</td>
		</tr>
		</table>
		<div class="siteguard-description">
		<?php esc_html_e( 'It is the function to decrease the vulnerability against an illegal login attempt attack such as a brute force attack or a password list attack. Especially, it is the function to prevent an automated attack. The connection source IP address the number of login failure of which reaches the specified number within the specified period is blocked for the specified time. Each user account is not locked.', 'siteguard' ) ?>
		</div>
		<hr />
		<input type="hidden" name="update" value="Y">

		<?php
		wp_nonce_field( 'siteguard-menu-login-lock-submit' );
		submit_button( );
		?>

		</form>
		</div>

		<?php
	}
}
?>
