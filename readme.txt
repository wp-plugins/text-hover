=== Text Hover ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: text, post content, abbreviations, terms, acronyms, hover, help, coffee2code
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 3.0.1
Version: 3.0.1

Add hover text to regular text in posts. Handy for providing explanations of names, terms, phrases, and acronyms mentioned in your blog.


== Description ==

Add hover text to regular text in posts.  Handy for providing explanations of names, terms, phrases, and acronyms mentioned in your blog.

Hover text are defined as terms/acronyms/phrasees that you expect to use in your blog posts and the expand text you wish to appear when the visitor hovers their mouse over the term.  The admin options form for the plugin explains the format, which is quite simple.  An example of which is shown here :

`WP => WordPress
Matt => Matt Mullenweg
The Scooby Shack => the bar where the gang hangs out`

**Note:** This is not the same as my [Text Replace](http://wordpress.org/extend/plugins/text-replace) plugin, which defines terms or phrases that you want replaced by replacement text when displayed on your blog.  Text Hover instead adds the hover text as additional information for when visitors hover over the term, which is otherwise displayed in the post as you typed it.


== Installation ==

1. Unzip `text-hover.zip` inside the `/wp-content/plugins/` directory (or install via the built-in WordPress plugin installer)
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Go to the `Settings` -> `Text Hover` admin settings page and customize the settings (namely to define the terms/acronyms and their explanations).
1. Use the terms/acronyms in posts and/or pages (terms/acronyms appearing in existing posts will also be affected by this plugin)


== Screenshots ==

1. A screenshot of the admin options page for the plugin, where you define the terms/acronyms/phrases and their related hover text
2. A screenshot of the plugin in action for a post when the mouse is hovering over a defined hover text term


== Frequently Asked Questions ==

= In my posts, hover text terms do not appear any differently than regular text (though I can hover over them and see the hover text)!  What gives? =

The plugin currently makes use of the standard HTML tag `acronym` to specify the terms and their hover text.  Browsers have default handling and display of `acronym`.  It's possibly that the CSS for your theme is overriding the default display.  I use the following in my site's styles.css file to ensure it displays for me in the manner I prefer (which, by the same token, you can use more CSS formatting to further format the hover terms) :

`acronym {
	border-bottom:1px dotted #000;
}`

= Does this plugin modify the post content in the database? =

No.  The plugin filters post content on-the-fly.

= Will this work for posts I wrote prior to installing this plugin? =

Yes, if they include strings that you've now defined as terms.

= What post fields get handled by this plugin? =

By default, the plugin filters the post content, post excerpt fields, and widget text.  You can use the 'c2c_text_hover_filters' filter to modify that behavior (see Filters section).

= How can I get text hover to apply for post titles (or something not processed for text hover by default)? =

You can add to the list of filters that get processed for text hover terms.  See the Filters section for an example.

= Is the plugin case sensitive? =

By default, yes.  There is a setting you can change to make it case insensitive.  Or you can use the 'c2c_text_hover_case_sensitive' filter (see Filters section).  Note that the option applies to all terms/acronyms.  If you want to selectively have terms/acronyms be case insensitive, you should leave the case sensitive setting checked and add a listing for each case variation you wish to support.


== Filters ==

The plugin exposes three filters for hooking.  Typically, the code to utilize these hooks would go inside your active theme's functions.php file.

= c2c_text_hover_filters (filter) =

The 'c2c_text_hover_filters' hook allows you to customize what hooks get text hover applied to them.

Arguments:

* $hooks (array): Array of hooks that will be text hovered.

Example:

`// Enable text hover for post/page titles
add_filter( 'c2c_text_hover_filters', 'more_text_hovers' );
function more_text_hovers( $filters ) {
	$filters[] = 'the_title'; // Here you could put in the name of any filter you want
	return $filters;
}`

= c2c_text_hover (filter) =

The 'c2c_text_hover' hook allows you to customize or override the setting defining all of the text hover terms and their hover texts.

Arguments:

* $text_hover_array (array): Array of text hover terms and their hover texts.  This will be the value set via the plugin's settings page.

Example:

`// Add dynamic text hover
add_filter( 'c2c_text_hover', 'my_text_hovers' );
function my_text_hovers( $text_hover_array ) {
	// Add new term and hover text
	$text_hover_array['Matt'] => 'Matt Mullenweg';
	// Unset a term that we never want hover texted
	if ( isset( $text_hover_array['Drupal'] ) )
		unset( $text_hover_array['Drupal'] );
	// Important!
	return $text_hover_array;
}`

= c2c_text_hover_case_sensitive (filter) =

The 'c2c_text_hover_case_sensitive' hook allows you to customize or override the setting indicating if text hover should be case sensitive.

Arguments:

* $state (bool): Either true or false indicating if text hover is case sensitive.  This will be the value set via the plugin's settings page.

Example:

`// Prevent text hover from ever being case sensitive.
add_filter( 'c2c_text_hover_case_sensitive', '__return_false' );`


== Changelog ==

= 3.0.1 =
* Update plugin framework to 016

= 3.0 =
* Re-implementation by extending C2C_Plugin_015, which among other things adds support for:
    * Reset of options to default values
    * Better sanitization of input values
    * Offload of core/basic functionality to generic plugin framework
    * Additional hooks for various stages/places of plugin operation
    * Easier localization support
* Full localization support
* Disable auto-wrapping of text in the textarea input field for hovers
* Allow filtering of text hover terms and replacement via 'c2c_text_hover' filter
* Allow filtering of hooks that get text hover processing via 'c2c_text_hover_filters' filter
* Allow filtering/overriding of case_sensitive option via 'c2c_text_hover_case_sensitive' filter
* Filter 'widget_text' for text hover
* Rename class from 'TextHover' to 'c2c_TextHover'
* Assign object instance to global variable, $c2c_text_hover, to allow for external manipulation
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Update readme.txt
* Minor code reformatting (spacing)
* Add Filters and Upgrade Notice sections to readme.txt
* Note compatibility with WP 3.0+
* Drop support for versions of WordPress older than 2.8
* Add .pot file
* Update screenshot
* Add PHPDoc documentation
* Add package info to top of file
* Update copyright date
* Remove trailing whitespace

= 2.2 =
* Fixed bug that allowed text within tag attributes to be potentially replaced
* Fixed bug that prevented case sensitivity-related option from being taken into account
* Removed 'case_sensitive' argument from text_replace() function since it is controlled by a setting
* Changed pattern matching criteria to allow text-to-be-hovered to be book-ended on either side with single or double quotes (either plain or curly), square brackets, curly braces, or parentheses
* Added ability to filter text hover shortcuts via 'c2c_text_hover_option_text_to_hover'
* Changed the number of rows for textarea input from 5 to 15
* Changed plugin_basename to be a class variable initialized during constructor
* Removed use of single-use temp variable (and instead directly used the value it was holding)
* Minor code reformatting (mostly spacing)

= 2.1 =
* (Privately released betas previewing features released as part of v2.2)

= 2.0 =
* Encapsulated all functionality into its own class
* Added 'Settings' link to plugin's plugin listing entry
* Noted compatibility with WP2.8+
* Dropped support for pre-WP2.6
* Updated screenshots
* Updated copyright date

= 1.0 =
* Initial release


== Upgrade Notice ==

= 3.0 =
Significant and recommended update. Highlights: re-implementation; added more settings and hooks for customization; disable autowrap in textarea; misc improvements; verified WP 3.0 compatibility; dropped compatibility with WP older than 2.8.