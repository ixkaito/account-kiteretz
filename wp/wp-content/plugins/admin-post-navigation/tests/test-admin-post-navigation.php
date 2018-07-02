<?php

defined( 'ABSPATH' ) or die();

class Admin_Post_Navigation_Test extends WP_UnitTestCase {

	private $run_number = 1;

	public function setUp() {
		parent::setUp();

		do_action( 'load-post.php' );
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['post_ID'] );
		$this->unset_current_user();

		remove_filter( 'c2c_admin_post_navigation_post_statuses', array( $this, 'c2c_admin_post_navigation_post_statuses' ), 10, 3 );
		remove_filter( 'c2c_admin_post_navigation_orderby',       array( $this, 'c2c_admin_post_navigation_orderby' ), 10, 2 );
		remove_filter( 'c2c_admin_post_navigation_orderby',       array( $this, 'c2c_admin_post_navigation_orderby_title' ), 10, 2 );
		remove_filter( 'c2c_admin_post_navigation_orderby',       array( $this, 'c2c_admin_post_navigation_orderby_bad_value' ), 10, 2 );
	}


	//
	//
	// DATA PROVIDERS
	//
	//


	public function valid_orderbys() {
		return array(
			array( 'comment_count' ),
			array( 'ID' ),
			array( 'menu_order' ),
			array( 'post_author' ),
			array( 'post_content' ),
			array( 'post_content_filtered' ),
			array( 'post_date' ),
			array( 'post_excerpt' ),
			array( 'post_date_gmt' ),
			array( 'post_mime_type' ),
			array( 'post_modified' ),
			array( 'post_modified_gmt' ),
			array( 'post_name' ),
			array( 'post_parent' ),
			array( 'post_status' ),
			array( 'post_title' ),
			array( 'post_type' ),
		);
	}

	public function invalid_orderbys() {
		return array(
			array( 'title' ),
			array( 'id' ),
			array( 'gibberish' ),
		);
	}


	//
	//
	// HELPER FUNCTIONS
	//
	//


	private function create_user( $role, $set_as_current = true ) {
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		if ( $set_as_current ) {
			wp_set_current_user( $user_id );
		}
		return $user_id;
	}

	// helper function, unsets current user globally. Taken from post.php test.
	private function unset_current_user() {
		global $current_user, $user_ID;

		$current_user = $user_ID = null;
	}

	private function create_posts( $number = 5, $current_post_index = 2 ) {
		$user_id = $this->create_user( 'administrator' );

		$posts = $this->factory->post->create_many( $number, array( 'post_author' => $user_id ) );

		// Set publish_date to be different days. The default is now(), which the
		// script runs fast enough that all posts appear published at same second.
		foreach ( $posts as $i => $post ) {
			$new_date = '2' . str_pad( $this->run_number++, 3, '0', STR_PAD_LEFT ) . '-01-' . str_pad( $i+1, 2, '0', STR_PAD_LEFT ) . ' 13:01:00';
			wp_update_post( array( 'ID' => $post, 'edit_date' => true, 'post_date' => $new_date, 'post_date_gmt' => $new_date ), true );
		}

		$GLOBALS['post_ID'] = $posts[ $current_post_index ];
		$current_post = get_post( $posts[ $current_post_index ] );

		c2c_AdminPostNavigation::do_meta_box( $current_post->post_type, 'normal', $current_post );

		return $posts;
	}

	public function c2c_admin_post_navigation_post_statuses( $post_statuses, $post_type ) {
		$this->assertTrue( is_array( $post_statuses ) );
		$this->assertTrue( is_string( $post_type ) );

		// Add a post status.
		$post_statuses[] = 'trash';

		// Remove post status.
		$post_statuses_to_remove = array( 'draft' );
		if ( 'page' === $post_type ) {
			$post_statuses_to_remove[] = 'pending';
		}
		foreach ( $post_statuses_to_remove as $remove ) {
			if ( false !== $index = array_search( $remove, $post_statuses ) ) {
				unset( $post_statuses[ $index ] );
			}
		}

		return array_values( $post_statuses );
	}

	public function c2c_admin_post_navigation_orderby( $orderby, $post_type ) {
		return 'post_date';
	}

	public function c2c_admin_post_navigation_orderby_title( $orderby, $post_type ) {
		return 'post_title';
	}

	public function c2c_admin_post_navigation_orderby_bad_value( $orderby, $post_type ) {
		return 'gibberish';
	}


	//
	//
	// TESTS
	//
	//


	public function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_AdminPostNavigation' ) );
	}

	public function test_version() {
		$this->assertEquals( '2.1', c2c_AdminPostNavigation::version() );
	}

	/*
	 * c2c_AdminPostNavigation::next_post()
	 */

	public function test_navigate_next_to_post() {
		$posts = $this->create_posts();

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEquals( $posts[3], $next_post->ID );
	}

	public function test_navigate_next_at_end() {
		$posts = $this->create_posts( 5, 4 );

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEmpty( $next_post );
	}

	public function test_navigate_next_skips_unwhitelisted_post_status() {
		$posts = $this->create_posts();

		$post = get_post( $posts[3] );
		$post->post_status = 'trash';
		wp_update_post( $post );

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEquals( $posts[4], $next_post->ID );
	}

	public function test_navigate_next_when_no_editable_next() {
		$posts = $this->create_posts();
		$user_id = $this->create_user( 'author' );

		$post = get_post( $posts[2] );
		$post->post_author = $user_id;
		wp_update_post( $post );

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEmpty( $next_post );
	}

	/*
	 * c2c_AdminPostNavigation::previous_post()
	 */

	public function test_navigate_previous_to_post() {
		$posts = $this->create_posts();

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEquals( $posts[1], $previous_post->ID );
	}

	public function test_navigate_previous_at_beginning() {
		$posts = $this->create_posts( 5, 0 );

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEmpty( $previous_post );
	}

	public function test_navigate_previous_skips_unwhitelisted_post_status() {
		$posts = $this->create_posts();

		$post = get_post( $posts[1] );
		$post->post_status = 'trash';
		wp_update_post( $post );

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEquals( $posts[0], $previous_post->ID );
	}

	public function test_navigate_previous_when_no_editable_previous() {
		$posts = $this->create_posts();
		$user_id = $this->create_user( 'author' );

		$post = get_post( $posts[2] );
		$post->post_author = $user_id;
		wp_update_post( $post );

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEmpty( $previous_post );
	}

	public function test_navigate_by_post_title_on_posts_with_quotes_in_title() {
		add_filter( 'c2c_admin_post_navigation_orderby', array( $this, 'c2c_admin_post_navigation_orderby_title' ), 10, 2 );

		$posts = $this->create_posts();

		// Change post titles so post ordering by title is 3, 0, 2, 4, 1
		$new_post_titles = array(
			"Don't wake the dragon",
			'A very good post',
			"Can you 'dig' it?",
			'Everything must come to an end',
			'Be a good person',
		);
		foreach ( $new_post_titles as $i => $title ) {
			$post = get_post( $posts[ $i ] );
			$post->post_title = $title;
			wp_update_post( $post );
		}

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEquals( get_post( $posts[0] )->post_title, get_post( $next_post->ID )->post_title );

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEquals( get_post( $posts[4] )->post_title, get_post( $previous_post->ID )->post_title );
	}


	/*
	 * c2c_AdminPostNavigation::is_valid_orderby()
	 */

	/**
	 * @dataProvider valid_orderbys
	 */
	public function test_is_valid_orderby_with_valid( $orderby ) {
		$this->assertTrue( c2c_AdminPostNavigation::is_valid_orderby( $orderby ) );
	}

	/**
	 * @dataProvider invalid_orderbys
	 */
	public function test_is_valid_orderby_with_invalid( $orderby ) {
		$this->assertFalse( c2c_AdminPostNavigation::is_valid_orderby( $orderby ) );
	}


	/*
	 * c2c_AdminPostNavigation::get_post_type_orderby()
	 */


	public function test_get_post_type_orderby() {
		$this->assertEquals( 'post_title', c2c_AdminPostNavigation::get_post_type_orderby( 'page' ) );
		$this->assertEquals( 'post_date', c2c_AdminPostNavigation::get_post_type_orderby( 'post' ) );
	}

	public function test_get_post_type_orderby_for_user_with_no_saved_screen_option() {
		$user_id = $this->create_user( 'administrator' );

		$this->assertEquals( 'post_title', c2c_AdminPostNavigation::get_post_type_orderby( 'page', $user_id ) );
		$this->assertEquals( 'post_date', c2c_AdminPostNavigation::get_post_type_orderby( 'post', $user_id ) );
	}

	public function test_get_post_type_orderby_for_user_with_saved_screen_option() {
		$user_id = $this->create_user( 'administrator' );
		add_user_meta( $user_id, c2c_AdminPostNavigation::get_setting_name( 'page' ), 'ID', true );

		$this->assertEquals( 'ID', c2c_AdminPostNavigation::get_post_type_orderby( 'page', $user_id ) );
		// Ensure it doesn't affect value for other post types.
		$this->assertEquals( 'post_date', c2c_AdminPostNavigation::get_post_type_orderby( 'post', $user_id ) );
	}


	/*
	 * c2c_AdminPostNavigation::get_setting_name()
	 */


	public function test_get_setting_name() {
		$this->assertEquals( 'c2c_apn_page_orderby', c2c_AdminPostNavigation::get_setting_name( 'page' ) );
		$this->assertEquals( 'c2c_apn_post_orderby', c2c_AdminPostNavigation::get_setting_name( 'post' ) );
		$this->assertEquals( 'c2c_apn_book_orderby', c2c_AdminPostNavigation::get_setting_name( 'book' ) );
	}


	/*
	 * Filters.
	 */


	public function test_hooks_action_load_post_php() {
		$this->assertEquals( 10, has_action( 'load-post.php', array( 'c2c_AdminPostNavigation', 'register_post_page_hooks' ) ) );
	}

	public function test_hooks_action_admin_enqueue_scripts() {
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', array( 'c2c_AdminPostNavigation', 'admin_enqueue_scripts_and_styles' ) ) );
	}

	public function test_hooks_action_do_meta_boxes() {
		$this->assertEquals( 10, has_action( 'do_meta_boxes', array( 'c2c_AdminPostNavigation', 'do_meta_box' ) ) );
	}

	public function test_filter_c2c_admin_post_navigation_post_statuses_when_adding_post_status() {
		$posts = $this->create_posts();

		add_filter( 'c2c_admin_post_navigation_post_statuses', array( $this, 'c2c_admin_post_navigation_post_statuses' ), 10, 2 );

		$post = get_post( $posts[3] );
		wp_trash_post( $post->ID );
		$post = get_post( $posts[2] );

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEquals( $posts[3], $next_post->ID );
	}

	public function test_filter_c2c_admin_post_navigation_post_statuses_when_removing_post_status() {
		$posts = $this->create_posts();

		add_filter( 'c2c_admin_post_navigation_post_statuses', array( $this, 'c2c_admin_post_navigation_post_statuses' ), 10, 3 );

		$post = get_post( $posts[3] );
		$post->post_status = 'draft';
		wp_update_post( $post );
		$post = get_post( $posts[2] );

		$next_post = c2c_AdminPostNavigation::next_post();
		$this->assertEquals( $posts[4], $next_post->ID );
	}

	public function test_filter_c2c_admin_post_navigation_orderby() {
		add_filter( 'c2c_admin_post_navigation_orderby', array( $this, 'c2c_admin_post_navigation_orderby' ), 10, 2 );

		$posts = $this->create_posts();

		// Change post dates so post ordering by date is 3, 0, 2, 4, 1
		$new_post_dates = array(
			'2015-06-13 12:30:00',
			'2015-03-13 12:30:00',
			'2015-05-13 12:30:00',
			'2015-07-13 12:30:00',
			'2015-04-13 12:30:00',
		);
		foreach ( $new_post_dates as $i => $date ) {
			$post = get_post( $posts[ $i ] );
			$post->post_date = $date;
			wp_update_post( $post );
		}

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEquals( $posts[0], $next_post->ID );

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEquals( $posts[4], $previous_post->ID );
	}

	public function test_filter_c2c_admin_post_navigation_orderby_with_bad_value() {
		add_filter( 'c2c_admin_post_navigation_orderby', array( $this, 'c2c_admin_post_navigation_orderby_bad_value' ), 10, 2 );

		// Should function as if never hooked.
		$this->test_navigate_next_to_post();
		$this->test_navigate_previous_to_post();
	}

	/*
	 * TODO tests:
	 * - JS is not enqueued on frontend
	 * - JS is enqueue on appropriate admin page(s)
	 * - JS is not enqueued on inappropriate admin page(s)
	 * - CSS is not enqueued on frontend
	 * - CSS is enqueue on appropriate admin page(s)
	 * - CSS is not enqueued on inappropriate admin page(s)
	 */

}
