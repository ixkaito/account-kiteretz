=== Admin Post Navigation ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: admin, navigation, post, next, previous, edit, post types, coffee2code
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.7
Tested up to: 4.9
Stable tag: 2.1

Adds links to navigate to the next and previous posts when editing a post in the WordPress admin.


== Description ==

This plugin adds "&larr; Previous" and "Next &rarr;" links to the "Edit Post" admin page if a previous and next post are present, respectively. The link titles (visible when hovering over the links) reveal the title of the previous/next post. The links link to the "Edit Post" admin page for the previous/next posts so that you may edit them.

By default, a previous/next post is determined by the next lower/higher valid post based on the date the post was created and which is also a post the user can edit. Other post criteria such as post type (draft, pending, etc), publish date, post author, category, etc, are not taken into consideration when determining the previous or next post.

Users can customize how post navigation ordering is handled via the "Screen Options" panel available at the top of every page when editing a post. A dropdown presents options to order navigation by: 'ID', 'menu_order', 'post_date', 'post_modified', 'post_name', and 'post_title'. Post navigation can further be customized via filters (see Filters section).

NOTE: Be sure to save the post currently being edited (if you've made any changes) before navigating away to the previous/next post!

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/admin-post-navigation/) | [Plugin Directory Page](https://wordpress.org/plugins/admin-post-navigation/) | [GitHub](https://github.com/coffe2code/admin-post-navigation/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Unzip `admin-post-navigation.zip` inside the `/wp-content/plugins/` directory for your site (or install via the built-in WordPress plugin installer)
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. Optional: When editing a post type that supports admin navigation (which are all post types by default), use the "Screen Options" menu to customize how navigation is handled.
4. Optional: See documentation for available programmatic customizations


== Screenshots ==

1. A screenshot of the previous/next links adjacent to the 'Edit Post' admin page header when Javascript is enabled.
2. A screenshot of the previous/next links in their own 'Edit Post' admin page sidebar panel when Javascript is disabled for the admin user.


== Frequently Asked Questions ==

= How do I change it so the previous/next links find the adjacent post according to post_date? =

See the Filters section for the `c2c_admin_post_navigation_orderby` filter, which has just such an example.

= Can I change the link text to something other than "&larr; Previous" and/or "Next &rarr;"? =

Yes. See the Filters section for the `c2c_admin_post_navigation_prev_text` and/or `c2c_admin_post_navigation_next_text` filters, which have just such examples. To change or amend the overall markup for the links, look into the `c2c_admin_post_navigation_display` filter.


== Filters ==

The plugin is further customizable via six filters. Such code should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain).

= c2c_admin_post_navigation_orderby (filter) =

The 'c2c_admin_post_navigation_orderby' filter allows you to change the post field used in the ORDER BY clause for the SQL to find the previous/next post. By default this is 'post_date' for non-hierarchical post types (such as posts) and 'post_title' for hierarchical post types (such as pages). If you wish to change this, hook this filter. Note: users can customize the post navigation order field for themselves on a per-post type basis via "Screen Options" (see FAQ and screenshot for more info).

Arguments:

* $field (string) The current ORDER BY field
* $post_type (string) The post type being navigated
* $user_id (int) The user's ID

Example:

`
/**
 * Modify how Admin Post Navigation orders posts for navigation by changing the
 * ordering of pages by 'menu_order'.
 *
 * @param string $field     The field used to order posts for navigation.
 * @param string $post_type The post type being navigated.
 * @param int    $user_id.  The user's ID.
 * @return string
 */
function custom_order_apn( $field, $post_type, $user_id ) {
	// Only change the order for the 'page' post type.
	if ( 'page' === $post_type ) {
		$field = 'menu_order';
	}

	return $field;
}
add_filter( 'c2c_admin_post_navigation_orderby', 'custom_order_apn', 10, 3 );
`

= c2c_admin_post_navigation_post_statuses (filter) =

The 'c2c_admin_post_navigation_post_statuses' filter allows you to modify the list of post_statuses used as part of the search for the prev/next post. By default this array includes 'draft', 'future', 'pending', 'private', and 'publish'. If you wish to change this, hook this filter. This is not typical usage for most users.

Arguments:

* $post_statuses (array) The array of valid post_statuses
* $post_type (string) The post type

Example:

`
/**
 * Modify Admin Post Navigation to allow and disallow certain post statuses from being navigated.
 *
 * @param array  $post_statuses Post statuses permitted for admin navigation.
 * @param string $post_type     The post type.
 * @return array
 */
function change_apn_post_status( $post_statuses, $post_type ) {
	// Add a post status.
	// Note: by default these are already in the $post_statuses array: 'draft', 'future', 'pending', 'private', 'publish'
	$post_statuses[] = 'trash';

	// Remove post status(es).
	$post_statuses_to_remove = array( 'draft' ); // Customize here.
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
add_filter( 'c2c_admin_post_navigation_post_statuses', 'change_apn_post_status', 10, 2 );
`

= c2c_admin_post_navigation_post_types (filter) =

The 'c2c_admin_post_navigation_post_types' filter allows you to modify the list of post_types used as part of the search for the prev/next post. By default this array includes all available post types. If you wish to change this, hook this filter.

Arguments:

* $post_types (array) The array of valid post_types

Examples:

`
/**
 * Modify Admin Post Navigation to only allow navigating strictly for posts.
 *
 * @param array $post_types Post types that should have admin post navigation.
 * @return array
 */
function change_apn_post_types( $post_types ) {
	return array( 'post' );
}
add_filter( 'c2c_admin_post_navigation_post_types', 'change_apn_post_types' );
`

`
/**
 * Modify Admin Post Navigation to disallow navigation for the 'recipe' post type.
 *
 * @param array $post_types Post types that should have admin post navigation.
 * @return array
 */
function remove_recipe_apn_post_types( $post_types ) {
	if ( isset( $post_types['recipe'] ) ) {
		unset( $post_types['recipe'] ); // Removing a post type
	}
	return $post_types;
}
add_filter( 'c2c_admin_post_navigation_post_types', 'remove_recipe_apn_post_types' );
`

= c2c_admin_post_navigation_prev_text (filter) =

The 'c2c_admin_post_navigation_prev_text' filter allows you to change the link text used for the 'Previous' link. By default this is '&larr; Previous'.

Arguments:

* $text (string) The 'previous' link text.

Example:

`
/**
 * Changes the text for the 'previous' link to 'Older' output by the Admin Post Navigation plugin.
 *
 * @param string $text The text used to indicate the 'next' post.
 * @return string
 */
function my_c2c_admin_post_navigation_prev_text( $text ) {
	return 'Older';
}
add_filter( 'c2c_admin_post_navigation_prev_text', 'my_c2c_admin_post_navigation_prev_text' );
`

= c2c_admin_post_navigation_next_text (filter) =

The 'c2c_admin_post_navigation_next_text' filter allows you to change the link text used for the 'Next' link. By default this is 'Next &rarr;'.

Arguments:

* $text (string) The 'next' link text.

Example:

`
/**
 * Changes the text for the 'next' link to 'Newer' output by the Admin Post Navigation plugin.
 *
 * @param string $text The text used to indicate the 'next' post.
 * @return string
 */
function my_c2c_admin_post_navigation_next_text( $text ) {
	return 'Newer';
}
add_filter( 'c2c_admin_post_navigation_next_text', 'my_c2c_admin_post_navigation_next_text' );
`

= c2c_admin_post_navigation_display (filter) =

The 'c2c_admin_post_navigation_display' filter allows you to customize the output links for the post navigation.

Arguments:

* $text (string) The current output for the prev/next navigation link

Example:

`
/**
 * Change the markup displayed by the Admin Post Navigation plugin.
 *
 * @param string $text The text being output by the plugin.
 * @return string
 */
function override_apn_display( $text ) {
	// Simplistic example. You could preferably make the text bold using CSS.
	return '<strong>' . $text . '</strong>';
}
add_filter( 'c2c_admin_post_navigation_display', 'override_apn_display' );
`


== Changelog ==

= 2.1 (2017-12-26) =
* New: Add ability for users to customize the navigation order via a Screen Options dropdown.
    * Add optional `$user_id` arg to `get_post_type_orderby()`, and use it, to take into account user preference.
    * Add `$user_id` arg to 'c2c_admin_post_navigation_orderby' filter.
    * Add `get_setting_name()` helper function for getting the setting name for the given post type.
    * Add `screen_settings()` to output the dropdown.
    * Add `save_screen_settings()` to save user's preference.
* Fix: Resolve issue where navigation links failed to appear on posts with an apostrophe in their titles.
* New: Add `is_valid_orderby()` helper function to verify a given orderby value is valid.
* New: Add `get_post_statuses()` for getting post statuses valid for navigation of a given post type.
* New: Abstract logic for determining the orderby for a given post type into `get_post_type_orderby()`.
* New: Abstract logic for determining if a post type has admin navigation enabled into `is_post_type_navigable()`.
* New: Add README.md.
* Change: Use `get_the_title()` instead of `the_title_attribute()` to get post titles.
* Change: Discontinue sending `$user_id` arg to 'c2c_admin_post_navigation_post_statuses' filter.
* Change: Remove pre-WP 4.3 support for JS relocation of prev/next links.
* Change: Use `sprintf()` to format output markup rather than concatenating strings, variables, and function calls.
* Change: For unit tests, enable more error output.
* Change: For unit tests, default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable.
* Change: Add GitHub link to readme.
* Change: Note compatibility through WP 4.9+.
* Change: Remove support for WordPress older than 4.7 (should still work for earlier versions)
* Change: Update copyright date (2018).
* Change: Minor whitespace tweaks in unit test bootstrap

= 2.0 (2016-01-14) =
* New: Add support for RTL display.
* New: Enable post navigation for media when viewed/edited from list mode.
* New: Move CSS into enqueuable .css file.
* New: Move JS into enqueueable .js file.
* Bugfix: Navigate non-hierarchical posts by post_date by default for more expected ordering.
* Change: Use `the_title_attribute()` to get post title for use in attribute.
* Remove: Delete `add_css()` and `add_js()`.
* Change: Add support for language packs:
    * Don't load plugin translations from file.
    * Remove .pot file and /lang subdirectory.
* Change: Note compatibility through WP 4.4+.
* Change: Remove support for WordPress older than 4.0.
* Change: Explicitly declare methods in unit tests as public.
* Change: Update copyright date (2016).
* New: Add inline documentation for class variables.
* New: Create empty index.php to prevent files from being listed if web server has enabled directory listings.

= 1.9.2 (2015-08-19) =
* Bugfix: Fix so navigation links appear in WordPress 4.3 (by targeting h1 instead of h2). Backwards compatibility maintained.
* Update: Note compatibility through WP 4.3+

= 1.9.1 (2015-07-08) =
* Bugfix: Fix JS placement of navigation links to target the desired h2, which may not always be the first on the page
* Update: Add additional unit test using example for customizing post status navigation
* Update: Fix incorrect example for excluding post statuses via filter
* Update: Improve example for using hook to define custom order for navigation
* Update: Remove unused line of code.
* Update: Note compatibility through WP 4.2+

= 1.9 (2015-03-14) =
* Fix to only append navigation to the first h2 on the page. props @pomegranate
* Add filter 'c2c_admin_post_navigation_prev_text' to allow customization of the previous navigation link text. props @pomegranate
* Add filter 'c2c_admin_post_navigation_next_text' to allow customization of the next navigation link text. props @pomegranate
* Restrict orderby value to be an actual posts table field
* Add unit tests
* Prevent querying for a post if there isn't a global post_ID set or if no valid post_statuses were set
* Cast result of 'c2c_admin_post_navigation_post_statuses' filter to an array to avoid potential PHP warnings with improper use
* Improved sanitization of values returned via the 'c2c_admin_post_navigation_post_statuses' filter
* Add docs for new filters
* Documentation improvements
* Reformat plugin header
* Note compatibility through WP 4.1+
* Update copyright date (2015)
* Minor code reformatting (bracing, spacing)
* Change documentation links to wp.org to be https
* Add plugin icon
* Regenerate .pot

= 1.8 (2013-12-29) =
* Hide screen option checkbox for metabox if JS hides metabox for inline use
* Improve spacing within its metabox (when shown if JS is disabled)
* Note compatibility through WP 3.8+
* Update copyright date (2014)
* Change donate link
* Minor readme.txt tweaks (mostly spacing)
* Update banner
* Update screenshots

= 1.7.2 =
* Add check to prevent execution of code if file is directly accessed
* Note compatibility through WP 3.5+
* Update copyright date (2013)
* Move screenshots into repo's assets directory

= 1.7.1 =
* Use string instead of variable to specify translation textdomain
* Re-license as GPLv2 or later (from X11)
* Add 'License' and 'License URI' header tags to readme.txt and plugin file
* Add banner image for plugin page
* Remove ending PHP close tag
* Minor documentation tweaks
* Note compatibility through WP 3.4+

= 1.7 =
* Add support for localization
* Use post type label instead of post type name, when possible, in link title attribute
* Use larr/rarr characters to denote direction of navigation instead of larquo/rarquo
* Enhanced styling of navigation links
* Hook 'admin_enqueue_scripts' action instead of 'admin_head' to output CSS
* Hook 'load-post.php' to add actions for the post.php page rather than using $pagenow
* Add version() to return plugin version
* Add register_post_page_hooks()
* Remove admin_init() and hook 'do_meta_boxes' in register_post_page_hooks() instead
* Update screenshots for WP 3.3
* Note compatibility through WP 3.3+
* Drop compatibility with versions of WP older than 3.0
* Update screenshots for WP 3.3
* Tweak plugin description
* Add link to plugin directory page to readme.txt
* Minor code reformatting
* Minor readme.txt reformatting
* Update copyright date (2012)

= 1.6.1 =
* Use ucfirst() instead of strtoupper() to capitalize post type name for metabox title
* Note compatibility through WP 3.2+
* Minor code formatting changes (spacing)
* Add FAQ section to readme.txt
* Fix plugin homepage and author links in description in readme.txt

= 1.6 =
* Add support for navigation in other post types
    * Add filter 'c2c_admin_post_navigation_post_types' for customizing valid post_types for search
    * Enable navigation for all post types by default
    * Allow per-post_type sort order for navigation by adding $post_type argument when applying filters for 'c2c_admin_post_navigation_orderby'
    * Pass additional arguments ($post_type and $post) to functions hooking 'c2c_admin_post_navigation_post_statuses'
* Ensure post navigation only appears on posts of the appropriate post_status
* For hierarchical post types, order by 'post_title', otherwise order by 'ID' (filterable)
* Move application of filters from admin_init() into new do_meta_box(), which is hooking 'do_meta_box' action, so they only fire when actually being used
* Output JavaScript via 'admin_print_footer_scripts' action rather than 'admin_footer'
* Rename class from 'AdminPostNavigation' to 'c2c_AdminPostNavigation'
* Switch from object instantiation to direct class invocation
* Explicitly declare all functions public static and class variables private static
* Documentation tweaks
* Note compatibility through WP 3.1+
* Update copyright date (2011)

= 1.5 =
* Change post search ORDER BY from 'post_date' to 'ID'
* Add filter 'c2c_admin_post_navigation_orderby' for customizing search ORDER BY field
* Add filter 'c2c_admin_post_navigation_post_statuses' for customizing valid post_statuses for search
* Deprecate (but still support) 'admin_post_nav' filter
* Add filter 'c2c_admin_post_navigation_display' filter as replacement to 'admin_post_nav' filter to allow modifying output
* Retrieve post title via get_the_title() rather than directly from object
* Also strip tags from the title prior to use in tag attribute
* Don't navigate to auto-saves
* Check for is_admin() before defining class rather than during constructor
* esc_sql() on SQL strings that have potentially been filtered
* Use esc_attr() instead of attribute_escape()
* Store plugin instance in global variable, $c2c_admin_post_navigation, to allow for external manipulation
* Fix localization of the two strings
* Instantiate object within primary class_exists() check
* Note compatibility with WP 3.0+
* Drop compatibility with version of WP older than 2.8
* Minor code reformatting (spacing)
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Remove trailing whitespace in header docs
* Add Upgrade Notice and Filters sections to readme.txt
* Add package info to top of plugin file

= 1.1.1 =
* Add PHPDoc documentation
* Note compatibility with WP 2.9+
* Update copyright date
* Update readme.txt (including adding Changelog)

= 1.1 =
* Add offset and limit arguments to query()
* Only get ID and post_title fields in query, not *
* Change the previous/next post query to ensure it only gets posts the user can edit
* Note compatibility with WP 2.8+

= 1.0 =
* Initial release


== Upgrade Notice ==

= 2.1 =
Recommended update: added screen option for users to customize post navigation order for each post type, fixed bug where navigation didn't appear for posts with apostrophe in title, updated unit test bootstrap file, noted compatibility is now WP 4.7-4.9+, and updated copyright date (2018)

= 2.0 =
Recommended update: added RTL support, moved CSS & JS into enqueueable files, enabled navigation for media files, adjustments to utilize language packs, minor unit test tweaks, noted compatibility through WP 4.4+, and updated copyright date

= 1.9.2 =
Bugfix: fix to display navigation links in WordPress 4.3; noted compatibility through WP 4.3+

= 1.9.1 =
Minor bugfix: fix to more reliably ensure the navigation links appear in certain situations; fix incorrect example code for excluding post statuses; noted compatibility through WP 4.2+

= 1.9 =
Feature update: fix to only apply navigation to first h2 on page; added filters to facilitate customizing link text; added unit tests; noted compatibility through WP 4.1+; added plugin icon

= 1.8 =
Minor update: hid screen options checkbox when JS is enabled since metabox is hidden; improved metabox spacing; noted compatibility through WP 3.8+

= 1.7.2 =
Trivial update: noted compatibility through WP 3.5+

= 1.7.1 =
Trivial update: noted compatibility through WP 3.4+; explicitly stated license

= 1.7 =
Recommended update: enhanced styling of navigation links; added support for localization; noted compatibility through WP 3.3+; and more

= 1.6.1 =
Trivial update: noted compatibility through WP 3.2+

= 1.6 =
Feature update: added support for non-'post' post types; fixed so navigation only appears for posts of appropriate post status; implementation changes; renamed class; updated copyright date; other minor code changes.

= 1.5 =
Recommended update. Highlights: find prev/next post by ID rather than post_date, fix navigation logic, added numerous filters to allow for customizations, miscellaneous improvements, dropped pre-WP 2.8 compatibility, added verified WP 3.0 compatibility.
