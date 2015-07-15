<?php

class SiteGuard_Htaccess extends SiteGuard_Base {
	public static $htaccess_mark_start = '#SITEGUARD_PLUGIN_SETTINGS_START';
	public static $htaccess_mark_end   = '#SITEGUARD_PLUGIN_SETTINGS_END';

	function __construct( ) {
	}
	static function get_htaccess_file( ) {
		return ABSPATH.'.htaccess';
	}
	static function get_tmp_dir( ) {
		return SITEGUARD_PATH . 'tmp/';
	}
	static function get_htaccess_new_file( ) {
		return tempnam( SiteGuard_Htaccess::get_tmp_dir( ), 'htaccess_' );
	}
	static function make_tmp_dir( ) {
		$dir = SiteGuard_Htaccess::get_tmp_dir( );
		if ( ! wp_mkdir_p( $dir ) ) {
			siteguard_error_log( "make tempdir failed: $dir" );
			return false;
		}
		$htaccess_file = $dir . '.htaccess';

		if ( file_exists( $htaccess_file ) ) {
			return true;
		}

		if ( $handle = @fopen( $htaccess_file, 'w' ) ) {
			fwrite( $handle, 'Order deny,allow' . "\n" );
			fwrite( $handle, 'Deny from all' . "\n" );
			fclose( $handle );
		}

		return true;
	}
	static function is_exists_setting( $mark ) {
		$result = false;
		if ( '' == $mark ) {
			$mark_start = SiteGuard_Htaccess::$htaccess_mark_start;
			$mark_end   = SiteGuard_Htaccess::$htaccess_mark_end;
		} else {
			$mark_start = $mark . '_START';
			$mark_end   = $mark . '_END';
		}
		$current_file = SiteGuard_Htaccess::get_htaccess_file( );
		if ( ! file_exists( $current_file ) ) {
			return $result;
		}
		$fr = @fopen( $current_file, 'r' );
		if ( null == $fr ) {
			return $result;
		}
		$line_num = 0;
		$start_line = 0;
		$end_line = 0;
		while ( ! feof( $fr ) ) {
			$line = fgets( $fr, 4096 );
			$line_num++;
			if ( false !== strpos( $line, $mark_start ) ) {
				$start_line = $line_num;
			}
			if ( false !== strpos( $line, $mark_end ) ) {
				$end_line = $line_num;
				if ( $start_line > 0 && ( $end_line - $start_line ) > 1 ) {
					$result = true;
				}
				break;
			}
		}
		@fclose( $fr );

		return $result;
	}
	static function clear_settings( $mark ) {
		if ( ! SiteGuard_Htaccess::make_tmp_dir( ) ) {
			return false;
		}
		if ( '' == $mark ) {
			$mark_start = SiteGuard_Htaccess::$htaccess_mark_start;
			$mark_end   = SiteGuard_Htaccess::$htaccess_mark_end;
		} else {
			$mark_start = $mark . '_START';
			$mark_end   = $mark . '_END';
		}
		$flag_settings = false;
		$current_file = SiteGuard_Htaccess::get_htaccess_file( );
		if ( ! file_exists( $current_file ) ) {
			@touch( $current_file );
			@chmod( $current_file, 0604 );
		}
		$fr = @fopen( $current_file, 'r' );
		if ( null == $fr ) {
			siteguard_error_log( "fopen failed: $current_file" );
			return false;
		}
		$new_file = SiteGuard_Htaccess::get_htaccess_new_file( );
		$fw = @fopen( $new_file, 'w' );
		if ( null == $fw ) {
			siteguard_error_log( "fopen failed: $new_file" );
			return false;
		}
		while ( ! feof( $fr ) ) {
			$line = fgets( $fr, 4096 );
			if ( false !== strpos( $line, $mark_start ) ) {
				$flag_settings = true;
			}
			if ( false == $flag_settings ) {
				fputs( $fw, $line, 4096 );
			}
			if ( true == $flag_settings && false !== strpos( $line, $mark_end ) ) {
				$flag_settings = false;
			}
		}
		fclose( $fr );
		fclose( $fw );
		@chmod( $new_file, 0604 );
		if ( ! rename( $new_file, $current_file ) ) {
			siteguard_error_log( "rename failed: $new_file $current_file" );
			return false;
		}
		return true;
	}
	function update_settings( $mark, $data ) {
		if ( ! SiteGuard_Htaccess::make_tmp_dir( ) ) {
			return false;
		}
		$flag_write    = false;
		$flag_through  = true;
		$flag_wp       = false;
		$flag_wp_set   = false;
		$wp_settings   = '';
		$mark_start    = $mark . '_START';
		$mark_end      = $mark . '_END';
		$mark_wp_start = '# BEGIN WordPress';
		$mark_wp_end   = '# END WordPress';
		$current_file  = SiteGuard_Htaccess::get_htaccess_file( );
		if ( ! file_exists( $current_file ) ) {
			@touch( $current_file );
			@chmod( $current_file, 0604 );
		}
		if ( ! is_readable( $current_file ) ) {
			siteguard_error_log( "file not readable: $current_file" );
			return false;
		}
		$fr = @fopen( $current_file, 'r' );
		if ( null == $fr ) {
			siteguard_error_log( "fopen failed: $current_file" );
			return false;
		}
		$new_file = SiteGuard_Htaccess::get_htaccess_new_file( );
		if ( ! is_writable( $new_file ) ) {
			siteguard_error_log( "file not writable: $new_file" );
			return false;
		}
		$fw = @fopen( $new_file, 'w' );
		if ( null == $fw ) {
			siteguard_error_log( "fopen failed: $new_file" );
			return false;
		}
		while ( ! feof( $fr ) ) {
			$line = fgets( $fr, 4096 );

			// Save WordPress settings.
			// WordPress settings has to be written after SiteGuard settings.
			if ( false == $flag_write && false == $flag_wp_set && false !== strpos( $line, $mark_wp_start ) ) {
				$flag_wp     = true;
				$flag_wp_set = true;
			}
			if ( $flag_wp_set ) {
				$wp_settings .= $line;
				if ( false !== strpos( $line, $mark_wp_end ) ) {
					$flag_wp_set = false;
				}
				continue;
			}

			if ( false !== strpos( $line, $mark_start ) ) {
				fwrite( $fw, $line , strlen( $line ) );
				fwrite( $fw, $data,  strlen( $data ) );
				$flag_write = true;
				$flag_through  = false;
				continue;
			}
			if ( false == $flag_write && false !== strpos( $line, SiteGuard_Htaccess::$htaccess_mark_end ) ) {
				fwrite( $fw, $mark_start . "\n", strlen( $mark_start ) + 1 );
				fwrite( $fw, $data, strlen( $data ) );
				fwrite( $fw, $mark_end . "\n", strlen( $mark_end ) + 1 );
				$flag_write = true;
			}
			if ( false == $flag_through && false !== strpos( $line, $mark_end ) ) {
				$flag_through = true;
			}
			if ( $flag_through ) {
				fwrite( $fw, $line, strlen( $line ) );
				if ( false == $flag_wp && false !== strpos( $line, $mark_wp_start ) ) {
					$flag_wp = true;
				}
			}
		}
		if ( false == $flag_write ) {
			fwrite( $fw, "\n" . SiteGuard_Htaccess::$htaccess_mark_start . "\n", strlen( SiteGuard_Htaccess::$htaccess_mark_start ) + 2 );
			fwrite( $fw, $mark_start . "\n", strlen( $mark_start ) + 1 );
			fwrite( $fw, $data, strlen( $data ) );
			fwrite( $fw, $mark_end . "\n", strlen( $mark_end ) + 1 );
			fwrite( $fw, SiteGuard_Htaccess::$htaccess_mark_end . "\n", strlen( SiteGuard_Htaccess::$htaccess_mark_end ) + 1 );
		}
		if ( '' != $wp_settings ) {       // Write saved WordPress Settings
			fwrite( $fw, "\n", 1 );
			fwrite( $fw, $wp_settings, strlen( $wp_settings ) );
			fwrite( $fw, "\n", 1 );
		} else if ( false == $flag_wp ) { // Write empty WordPress Settings
			fwrite( $fw, "\n", 1 );
			fwrite( $fw, $mark_wp_start . "\n", strlen( $mark_wp_start ) + 1 );
			fwrite( $fw, $mark_wp_end   . "\n", strlen( $mark_wp_end ) + 1 );
			fwrite( $fw, "\n", 1 );
		}
		fclose( $fr );
		fclose( $fw );
		@chmod( $new_file, 0604 );
		if ( ! rename( $new_file, $current_file ) ) {
			siteguard_error_log( "rename failed: $new_file $current_file" );
			return false;
		}
		return true;
	}
}

?>
