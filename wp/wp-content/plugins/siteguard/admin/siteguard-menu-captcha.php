<?php

class SiteGuard_Menu_CAPTCHA extends SiteGuard_Base {
	function __construct( ) {
		$this->render_page( );
	}
	function is_captcha_switch_value( $value ) {
		if ( '0' == $value || '1' == $value || '2' == $value ) {
			return true;
		}
		return false;
	}
	function render_page( ) {
		global $config, $captcha;

		$opt_name_enable             = 'captcha_enable';
		$opt_name_login              = 'captcha_login';
		$opt_name_comment            = 'captcha_comment';
		$opt_name_lostpassword       = 'captcha_lostpasswd';
		$opt_name_registuser         = 'captcha_registuser';

		$opt_val_enable             = $config->get( $opt_name_enable );
		$opt_val_login              = $config->get( $opt_name_login );
		$opt_val_comment            = $config->get( $opt_name_comment );
		$opt_val_lostpassword       = $config->get( $opt_name_lostpassword );
		$opt_val_registuser         = $config->get( $opt_name_registuser );
		if ( isset( $_POST['update'] ) && check_admin_referer( 'siteguard-menu-captcha-submit' ) ) {
			$error = false;
			$errors = check_multisite( );
			if ( is_wp_error( $errors ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( $errors->get_error_message( ), 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( false == $error && '1' == $_POST[ $opt_name_enable ] ) {
				$ret = $captcha->check_requirements( );
				if ( is_wp_error( $ret ) ) {
					echo '<div class="error settings-error"><p><strong>' . $ret->get_error_message( ) . '</strong></p></div>';
					$error = true;
					$config->set( $opt_name_enable, '0' );
					$config->update( );
				}
			}
			if ( ( false == $error )
			  && ( ( false == $this->is_switch_value( $_POST[ $opt_name_enable ] ) )
			    || ( false == $this->is_captcha_switch_value( $_POST[ $opt_name_login ] ) )
			    || ( false == $this->is_captcha_switch_value( $_POST[ $opt_name_comment ] ) )
			    || ( false == $this->is_captcha_switch_value( $_POST[ $opt_name_lostpassword ] ) )
			    || ( false == $this->is_captcha_switch_value( $_POST[ $opt_name_registuser ] ) ) ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( 'ERROR: Invalid input value.', 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( false == $error ) {
				$opt_val_enable             = $_POST[ $opt_name_enable ];
				$opt_val_login              = $_POST[ $opt_name_login ];
				$opt_val_comment            = $_POST[ $opt_name_comment ];
				$opt_val_lostpassword       = $_POST[ $opt_name_lostpassword ];
				$opt_val_registuser         = $_POST[ $opt_name_registuser ];
				$config->set( $opt_name_enable,             $opt_val_enable );
				$config->set( $opt_name_login,              $opt_val_login );
				$config->set( $opt_name_comment,            $opt_val_comment );
				$config->set( $opt_name_lostpassword,       $opt_val_lostpassword );
				$config->set( $opt_name_registuser,         $opt_val_registuser );
				$config->update( );
				?>
				<div class="updated"><p><strong><?php esc_html_e( 'Options saved.', 'siteguard' ); ?></strong></p></div>
				<?php
			}
		}

		echo '<div class="wrap">';
		echo '<img src="' . SITEGUARD_URL_PATH . 'images/sg_wp_plugin_logo_40.png" alt="SiteGuard Logo" />';
		echo '<h2>' . esc_html__( 'CAPTCHA', 'siteguard' ) . '</h2>';
		echo '<div class="siteguard-description">'
		. esc_html__( 'You can find docs about this function on ', 'siteguard' )
		. '<a href="' . esc_html__( 'http://www.jp-secure.com/cont/products/siteguard_wp_plugin/captcha_en.html', 'siteguard' ) 
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
			$error = $captcha->check_requirements( );
			if ( is_wp_error( $error ) ) {
				echo '<p class="description">';
				echo $error->get_error_message( );
				echo '</p>';
			}
			?>
		</th>
		</tr><tr>
		<th scope="row"><?php esc_html_e( 'Login page', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="<?php echo $opt_name_login ?>" id="<?php echo $opt_name_login.'_jp' ?>" value="1" <?php echo ( '1' == $opt_val_login ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_login.'_jp' ?>"><?php esc_html_e( 'Hiragana (Japanese)', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_login ?>" id="<?php echo $opt_name_login.'_en' ?>" value="2" <?php echo ( '2' == $opt_val_login ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_login.'_en' ?>"><?php esc_html_e( 'Alphanumeric', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_login ?>" id="<?php echo $opt_name_login.'_off' ?>" value="0" <?php echo ( '0' == $opt_val_login ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_login.'_off' ?>"><?php esc_html_e( 'Disable', 'siteguard' ) ?></label>
			</td>
		</tr><tr>
		<th scope="row"><?php esc_html_e( 'Comment page', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="<?php echo $opt_name_comment ?>" id="<?php echo $opt_name_comment.'_jp' ?>" value="1" <?php echo ( '1' == $opt_val_comment ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_comment.'_jp' ?>"><?php esc_html_e( 'Hiragana (Japanese)', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_comment ?>" id="<?php echo $opt_name_comment.'_en' ?>" value="2" <?php echo ( '2' == $opt_val_comment ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_comment.'_en' ?>"><?php esc_html_e( 'Alphanumeric', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_comment ?>" id="<?php echo $opt_name_comment.'_off' ?>" value="0" <?php echo ( '0' == $opt_val_comment ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_comment.'_off' ?>"><?php esc_html_e( 'Disable', 'siteguard' ) ?></label>
			</td>
		</tr><tr>
		<th scope="row"><?php esc_html_e( 'Lost password page', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="<?php echo $opt_name_lostpassword ?>" id="<?php echo $opt_name_lostpassword.'_jp' ?>" value="1" <?php echo ( '1' == $opt_val_lostpassword ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_lostpassword.'_jp' ?>"><?php esc_html_e( 'Hiragana (Japanese)', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_lostpassword ?>" id="<?php echo $opt_name_lostpassword.'_en' ?>" value="2" <?php echo ( '2' == $opt_val_lostpassword ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_lostpassword.'_en' ?>"><?php esc_html_e( 'Alphanumeric', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_lostpassword ?>" id="<?php echo $opt_name_lostpassword.'_off' ?>" value="0" <?php echo ( '0' == $opt_val_lostpassword ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_lostpassword.'_off' ?>"><?php esc_html_e( 'Disable', 'siteguard' ) ?></label>
			</td>
		</tr><tr>
		<th scope="row"><?php esc_html_e( 'Registration user page', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="<?php echo $opt_name_registuser ?>" id="<?php echo $opt_name_registuser.'_jp' ?>" value="1" <?php echo ( '1' == $opt_val_registuser ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_registuser.'_jp' ?>"><?php esc_html_e( 'Hiragana (Japanese)', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_registuser ?>" id="<?php echo $opt_name_registuser.'_en' ?>" value="2" <?php echo ( '2' == $opt_val_registuser ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_registuser.'_en' ?>"><?php esc_html_e( 'Alphanumeric', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="<?php echo $opt_name_registuser ?>" id="<?php echo $opt_name_registuser.'_off' ?>" value="0" <?php echo ( '0' == $opt_val_registuser ? 'checked' : '') ?> >
				<label for="<?php echo $opt_name_registuser.'_off' ?>"><?php esc_html_e( 'Disable', 'siteguard' ) ?></label>
			</td>
		</tr>
		</table>
		<div class="siteguard-description">
		<?php esc_html_e( 'It is the function to decrease the vulnerability against an illegal login attempt attack such as a brute force attack or a password list attack, or to receive less comment spam. For the character of CAPTCHA, hiragana and alphanumeric characters can be selected.', 'siteguard' ) ?>
		</div>
		<input type="hidden" name="update" value="Y">
		<hr />

		<?php
		wp_nonce_field( 'siteguard-menu-captcha-submit' );
		submit_button();
		?>
		</form>
		</div>
		<?php
	}
}
?>
