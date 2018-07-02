<?php
/**
 * Plugin Name: Admin Post Navigation
 * Version:     2.1
 * Plugin URI:  http://coffee2code.com/wp-plugins/admin-post-navigation/
 * Author:      Scott Reilly
 * Author URI:  http://coffee2code.com/
 * Text Domain: admin-post-navigation
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Description: Adds links to navigate to the next and previous posts when editing a post in the WordPress admin.
 *
 * Compatible with WordPress 4.7 through 4.9+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/admin-post-navigation/
 *
 * @package Admin_Post_Navigation
 * @author  Scott Reilly
 * @version 2.1
 */

/*
 * TODO:
 * - Add ability for navigation to save current post before navigating away.
 * - Add screen option allowing user selection of post navigation order
 * - Add more unit tests
 * - Add dropdown to post nav links to allow selecting different types of things
 *   to navigate to (e.g. next draft (if looking at a draft), next in category X)
 * - When navigating via menu_order, respect hierarchy and navigate siblings.
 * - Add filter to allow customizing the list of orderby options in screen options?
 * - Add post status as series of checkboxes in Screen Options
 * - Add support for secondary orderby
 */

/*
	Copyright (c) 2008-2018 by Scott Reilly (aka coffee2code)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'c2c_AdminPostNavigation' ) ) :

class c2c_AdminPostNavigation {

	/**
	 * Translated text for previous link.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private static $prev_text = '';

	/**
	 * Translated text for next link.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private static $next_text = '';

	/**
	 * Post fields available as orderby options.
	 *
	 * Note: not meant to be an exhaustive list, just the ones available to users
	 * via dropdown in Screen Options panel.
	 *
	 * @access private
	 * @since 2.1
	 *
	 * @var array
	 */
	private static $orderby_fields = array( 'ID', 'menu_order', 'post_date', 'post_modified', 'post_name', 'post_title' );

	/**
	 * Default post statuses for navigation.
	 *
	 * Filterable later.
	 *
	 * @access private
	 *
	 * @var array
	 */
	private static $post_statuses = array( 'draft', 'future', 'pending', 'private', 'publish' );

	/**
	 * Returns version of the plugin.
	 *
	 * @since 1.7
	 */
	public static function version() {
		return '2.1';
	}

	/**
	 * Class constructor: initializes class variables and adds actions and filters.
	 */
	public static function init() {
		add_filter( 'set-screen-option', array( __CLASS__, 'save_screen_settings' ), 10, 3 );
		add_action( 'load-post.php',     array( __CLASS__, 'register_post_page_hooks' ) );
	}

	/**
	 * Filters/actions to hook on the admin post.php page.
	 *
	 * @since 1.7
	 */
	public static function register_post_page_hooks() {
		// Load textdomain.
		load_plugin_textdomain( 'admin-post-navigation' );

		// Set translatable strings.
		self::$prev_text = apply_filters( 'c2c_admin_post_navigation_prev_text', __( '&larr; Previous', 'admin-post-navigation' ) );
		self::$next_text = apply_filters( 'c2c_admin_post_navigation_next_text', __( 'Next &rarr;', 'admin-post-navigation' ) );

		// Register hooks.
		add_action( 'admin_enqueue_scripts',      array( __CLASS__, 'admin_enqueue_scripts_and_styles' ) );
		add_action( 'do_meta_boxes',              array( __CLASS__, 'do_meta_box' ), 10, 3 );

		// Screen options.
		add_filter( 'screen_settings',            array( __CLASS__, 'screen_settings' ), 10, 2 );
	}

	/**
	 * Enqueues scripts and stylesheets on post edit admin pages.
	 *
	 * @since 2.0
	 */
	public static function admin_enqueue_scripts_and_styles() {
		wp_register_style( 'admin-post-navigation-admin', plugins_url( 'assets/admin-post-navigation.css', __FILE__ ), array(), self::version() );
		wp_enqueue_style( 'admin-post-navigation-admin' );

		wp_register_script( 'admin-post-navigation-admin', plugins_url( 'assets/admin-post-navigation.js', __FILE__ ), array( 'jquery' ), self::version(), true );
		wp_enqueue_script( 'admin-post-navigation-admin' );
	}

	/**
	 * Outputs screen settings.
	 *
	 * @since 2.1
	 *
	 * @param string    $screen_settings Screen settings markup.
	 * @param WP_Screen $screen          WP_Screen object.
	 * @return string
	 */
	public static function screen_settings( $screen_settings, $screen ) {
		if ( empty( $screen->post_type ) || ! self::is_post_type_navigable( $screen->post_type ) ) {
			return $screen_settings;
		}

		$option = self::get_setting_name( $screen->post_type );
		$value  = self::get_post_type_orderby( $screen->post_type, get_current_user_id() );

		$screen_settings .= '<fieldset class="">'
			. '<legend>' . __( 'Admin Post Navigation', 'admin-post-navigation' ) . '</legend>'
			. '<input type="hidden" name="wp_screen_options[option]" value="' . $option . '" />' . "\n"
			. '<label for="' . $option . '">'
			. sprintf( __( 'Navigation order for this post type (%s)', 'admin-post-navigation' ), $screen->post_type )
			. ' <select name="wp_screen_options[value]">';
		foreach ( self::$orderby_fields as $orderby ) {
			$screen_settings .= '<option value="' . $orderby . '" ' . selected( $value, $orderby, false ) . '>' . $orderby . '</option>';
		}
		$screen_settings .= '</select>'
			. '</label>' . "\n"
			. get_submit_button( __( 'Apply', 'admin-post-navigation' ), 'button', 'screen-options-apply', false )
			. '</fieldset>';

		return $screen_settings;
	}

	/**
	 * Saves screen option values.
	 *
	 * @since 2.1
	 *
	 * @param bool|int $status Screen option value. Default false to skip.
	 * @param string   $option The option name.
	 * @param int      $value  The number of rows to use.
	 * @return bool|string
	 */
	public static function save_screen_settings( $status, $option, $value ) {
		if ( 'c2c_apn_' == substr( $option, 0, 8 ) ) {
			$status = $value;
		}

		return $status;
	}

	/**
	 * Register meta box.
	 *
	 * By default, the navigation is present for all post types. Filter
	 * 'c2c_admin_post_navigation_post_types' to limit its use.
	 *
	 * @param string  $post_type The post type.
	 * @param string  $type      The mode for the meta box (normal, advanced, or side).
	 * @param WP_Post $post      The post.
	 */
	public static function do_meta_box( $post_type, $type, $post ) {
		if ( ! self::is_post_type_navigable( $post_type ) ) {
			return;
		}

		$post_statuses = self::get_post_statuses( $post_type );

		if ( in_array( $post->post_status, $post_statuses ) ) {
			add_meta_box(
				'adminpostnav',
				sprintf( __( '%s Navigation', 'admin-post-navigation' ), ucfirst( $post_type ) ),
				array( __CLASS__, 'add_meta_box' ),
				$post_type,
				'side',
				'core'
			);
		}
	}

	/**
	 * Adds the content for the post navigation meta_box.
	 *
	 * @param object $object
	 * @param array  $box
	 */
	public static function add_meta_box( $object, $box ) {
		$display = '';

		$context = self::_get_post_type_label( $object->post_type );

		$prev = self::previous_post();
		if ( $prev ) {
			$post_title = strip_tags( get_the_title( $prev ) );
			$display .= sprintf(
				'<a href="%s" id="admin-post-nav-prev" title="%s" class="admin-post-nav-prev add-new-h2">%s</a>',
				get_edit_post_link( $prev->ID ),
				esc_attr( sprintf( __( 'Previous %1$s: %2$s', 'admin-post-navigation' ), $context, $post_title ) ),
				self::$prev_text
			);
		}

		$next = self::next_post();
		if ( $next ) {
			if ( $display ) {
				$display .= ' ';
			}
			$post_title = strip_tags( get_the_title( $next ) );
			$display .= sprintf(
				'<a href="%s" id="admin-post-nav-next" title="%s" class="admin-post-nav-next add-new-h2">%s</a>',
				get_edit_post_link( $next->ID ),
				esc_attr( sprintf( __( 'Next %1$s: %2$s', 'admin-post-navigation' ), $context, $post_title ) ),
				self::$next_text
			);
		}

		$display = '<span id="admin-post-nav">' . $display . '</span>';
		$display = apply_filters( 'admin_post_nav', $display ); /* Deprecated as of v1.5 */
		echo apply_filters( 'c2c_admin_post_navigation_display', $display );
	}

	/**
	 * Gets label for post type.
	 *
	 * @since 1.7
	 *
	 * @param string  $post_type The post_type.
	 * @return string The label for the post_type.
	 */
	public static function _get_post_type_label( $post_type ) {
		$label = $post_type;
		$post_type_object = get_post_type_object( $label );
		if ( is_object( $post_type_object ) ) {
			$label = $post_type_object->labels->singular_name;
		}

		return strtolower( $label );
	}

	/**
	 * Returns the name of the screen option setting for the orderby setting for
	 * the given post type.
	 *
	 * @since 2.1
	 *
	 * @param string $post_type The post type.
	 * @return string
	 */
	public static function get_setting_name( $post_type ) {
		return 'c2c_apn_' . $post_type . '_orderby';
	}

	/**
	 * Determines if a post type has admin navigation enabled.
	 *
	 * By default, the navigation is enabled for all post types. Filter
	 * 'c2c_admin_post_navigation_post_types' to limit its use.
	 *
	 * @since 2.1
	 *
	 * @param string $post_type The post type.
	 * @return bool  True if post type has admin navigation enabled, else false.
	 */
	public static function is_post_type_navigable( $post_type ) {
		$post_types = (array) apply_filters( 'c2c_admin_post_navigation_post_types', get_post_types() );

		return in_array( $post_type, $post_types );
	}

	/**
	 * Determines if a given orderby field value is valid.
	 *
	 * Only post table fields are valid.
	 *
	 * @since 2.1
	 *
	 * @param string $orderby The orderby.
	 * @return bool. True if valid, false if not.
	 */
	public static function is_valid_orderby( $orderby ) {
		// By default, restrict orderby to actual post fields.
		$valid = array(
			'comment_count', 'ID', 'menu_order', 'post_author', 'post_content', 'post_content_filtered',
			'post_date', 'post_excerpt', 'post_date_gmt', 'post_mime_type', 'post_modified',
			'post_modified_gmt', 'post_name', 'post_parent', 'post_status', 'post_title', 'post_type',
		);

		// Filter the value.
		//$valid = (array) apply_filters( 'c2c_admin_post_navigation_valid_orderbys', $valid );

		return in_array( $orderby, $valid );
	}

	/**
	 * Determines the orderby value to use for a given post type's navigation.
	 *
	 * @since 2.1
	 *
	 * @param string $post_type The post type.
	 * @param int    $user_id   Optional. User ID of user, to account for the
	 *                          value the set via screen options.
	 * @return string
	 */
	public static function get_post_type_orderby( $post_type, $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( is_post_type_hierarchical( $post_type ) ) {
			$orderby = 'post_title';
		} else {
			$orderby = 'post_date';
		}

		// Get user-selected order for this post type.
		if ( $user_id ) {
			$user_orderby = get_user_meta( $user_id, self::get_setting_name( $post_type ), true );
			if ( $user_orderby && self::is_valid_orderby( $user_orderby ) ) {
				$orderby = $user_orderby;
			}
		}

		// Filter orderby value.
		$filter_orderby = apply_filters( 'c2c_admin_post_navigation_orderby', $orderby, $post_type, $user_id );
		if ( $filter_orderby && self::is_valid_orderby( $filter_orderby ) ) {
			$orderby = $filter_orderby;
		}

		return $orderby;
	}

	/**
	 * Returns the post statuses valid for navigation of the post type.
	 *
	 * @since 2.1
	 *
	 * @param string $post_type The post type.
	 * @return array
	 */
	public static function get_post_statuses( $post_type ) {
		return (array) apply_filters( 'c2c_admin_post_navigation_post_statuses', self::$post_statuses, $post_type );
	}

	/**
	 * Returns the previous or next post relative to the current post.
	 *
	 * Currently, a previous/next post is determined by the next lower/higher
	 * valid post based on relative sequential post ID and which the user can
	 * edit.  Other post criteria such as post type (draft, pending, etc),
	 * publish date, post author, category, etc, are not taken into
	 * consideration when determining the previous or next post.
	 *
	 * @param string $type   Optional. Either '<' or '>', indicating previous or next post, respectively. Default '<'.
	 * @param int    $offset Optional. Offset. Primarily for internal, self-referencial use. Default 0.
	 * @param int    $limit  Optional. Number of posts to get in the query. Not just the next post because a few might
	 *                       need to be traversed to find a post the user has the capability to edit. Default 15.
	 * @return WP_Post|false
	 */
	public static function query( $type = '<', $offset = 0, $limit = 15 ) {
		global $post_ID, $wpdb;

		if ( $type != '<' ) {
			$type = '>';
		}
		$offset = (int) $offset;
		$limit  = (int) $limit;

		$post = get_post( $post_ID );

		$post_type = esc_sql( get_post_type( $post->ID ) );

		$post_statuses = self::get_post_statuses( $post_type );

		if ( ! $post || ! $post_statuses ) {
			return false;
		}

		foreach( $post_statuses as $i => $v ) { $GLOBALS['wpdb']->escape_by_ref( $v ); $post_statuses[ $i ] = $v; }
		$post_statuses_sql = "'" . implode( "', '", $post_statuses ) . "'";

		$sql = "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = '$post_type' AND post_status IN (" . $post_statuses_sql . ') ';

		// Determine order.
		$orderby = self::get_post_type_orderby( $post_type );

		$datatype = in_array( $orderby, array( 'comment_count', 'ID', 'menu_order', 'post_parent' ) ) ? '%d' : '%s';
		$sql .= $wpdb->prepare( "AND {$orderby} {$type} {$datatype} ", $post->$orderby );

		$sort = $type == '<' ? 'DESC' : 'ASC';
		$sql .= "ORDER BY {$orderby} {$sort} LIMIT {$offset}, {$limit}";

		// Find the first post the user can actually edit.
		$posts = $wpdb->get_results( $sql );
		$result = false;
		if ( $posts ) {
			foreach ( $posts as $post ) {
				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$result = $post;
					break;
				}
			}
			if ( ! $result ) { // The fetch did not yield a post editable by user, so query again.
				$offset += $limit;
				// Double the limit each time (if haven't found a post yet, chances are we may not, so try to get through posts quicker).
				$limit += $limit;
				return self::query( $type, $offset, $limit );
			}
		}
		return $result;
	}

	/**
	 * Returns the next post relative to the current post.
	 *
	 * A convenience function that calls query().
	 *
	 * @return object The next post object.
	 */
	public static function next_post() {
		return self::query( '>' );
	}

	/**
	 * Returns the previous post relative to the current post.
	 *
	 * A convenience function that calls query().
	 *
	 * @return object The previous post object.
	 */
	public static function previous_post() {
		return self::query( '<' );
	}

} // end c2c_AdminPostNavigation

c2c_AdminPostNavigation::init();

endif; // end if !class_exists()
