<?php
/**
 * @package Text_Hover
 * @author Scott Reilly
 * @version 3.0.1
 */
/*
Plugin Name: Text Hover
Version: 3.0.1
Plugin URI: http://coffee2code.com/wp-plugins/text-hover/
Author: Scott Reilly
Author URI: http://coffee2code.com
Text Domain: text-hover
Description: Add hover text to regular text in posts. Handy for providing explanations of names, terms, phrases, and acronyms mentioned in your blog.

Compatible with WordPress 2.8+, 2.9+, 3.0+.

=>> Read the accompanying readme.txt file for instructions and documentation.
=>> Also, visit the plugin's homepage for additional information and updates.
=>> Or visit: http://wordpress.org/extend/plugins/text-hover/

*/

/*
Copyright (c) 2007-2010 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/


if ( !class_exists( 'c2c_TextHover' ) ) :

require_once( 'c2c-plugin.php' );

class c2c_TextHover extends C2C_Plugin_016 {

	/**
	 * Handles installation tasks, such as ensuring plugin options are instantiated and saved to options table.
	 *
	 * @return void
	 */
	function c2c_TextHover() {
		$this->C2C_Plugin_016( '3.0.1', 'text-hover', 'c2c', __FILE__, array() );
	}

	/**
	 * Override the plugin framework's register_filters() to actually actions against filters.
	 *
	 * @return void
	 */
	function register_filters() {
		$filters = apply_filters( 'c2c_text_hover_filters', array( 'the_content', 'get_the_excerpt', 'widget_text' ) );
		foreach ( (array) $filters as $filter )
			add_filter( $filter, array( &$this, 'text_hover' ), 3 );
	}

	/**
	 * Initializes the plugin's configuration and localizable text variables.
	 *
	 * @return void
	 */
	function load_config() {
		$this->name = __( 'Text Hover', $this->textdomain );
		$this->menu_name = __( 'Text Hover', $this->textdomain );

		$this->config = array(
			'text_to_hover' => array( 'input' => 'textarea', 'datatype' => 'hash', 'default' => array(
					"WP" => "WordPress"
				), 'allow_html' => true, 'no_wrap' => true, 'input_attributes' => 'rows="15" cols="40"',
				'label' => '', 'help' => ''
			),
			'case_sensitive' => array( 'input' => 'checkbox', 'default' => true,
				'label' => __( 'Should the matching of terms/acronyms be case sensitive?', $this->textdomain ),
				'help' => __( 'i.e. if you define a term of \'WP\', should \'wp\' also be treated the same way? This setting applies to all terms. If you want to selectively have case insensitive terms, then leave this option checked and create separate entries for each variation.', $this->textdomain )
			)
		);
	}

	/**
	 * Outputs the text above the setting form
	 *
	 * @return void (Text will be echoed.)
	 */
	function options_page_description() {
		parent::options_page_description( __( 'Text Hover Settings', $this->textdomain ) );

		echo '<p>' . __( 'Text Hover is a plugin that allows you to add hover text for text in posts. Very handy to create hover explanations of people mentioned in your blog, and/or definitions of unique acronyms and terms you use.', $this->textdomain ) . '</p>';
		echo '<div class="c2c-hr">&nbsp;</div>';
		echo '<h3>' . __( 'Acronyms and hover text', $this->textdomain ) . '</h3>';
		echo '<p>' . __( 'Define terms/acronyms and hovertext explanations here.  The format should be like this:', $this->textdomain ) . '</p>';
		echo "<blockquote><code>WP => WordPress</code></blockquote>";
		echo '<p>' . __( 'Where <code>WP</code> is the term, acronym, or phrase you intend to use in your posts, and the <code>WordPress</code> would be what you want to appear in a hover tooltip when a visitor hovers their mouse over the term.', $this->textdomain ) . '</p>';
		echo '<p>' . __( 'Other considerations:', $this->textdomain ) . '</p>';
		echo '<ul class="c2c-plugin-list"><li>';
		echo __( 'Terms and acronyms are assumed to be whole words within your posts (i.e. they are immediately prepended by some sort of space character (space, tab, etc) and are immediately appended by a space character or punctuation (which can include any of: ?!.,-+)]})', $this->textdomain );
		echo '</li><li>';
		echo __( 'Only use quotes it they are actual part of the original or hovertext strings.', $this->textdomain );
		echo '</li><li><strong><em>';
		echo __( 'Define only one hovertext per line.', $this->textdomain );
		echo '</em></strong></li><li><strong><em>';
		echo __( 'Hovertexts must not span multiple lines.', $this->textdomain );
		echo '</em></strong></li><li><strong><em>';
		echo __( 'Don\'t use HTML in the hovertext.', $this->textdomain );
		echo '</em></strong></li></ul>';
	}

	/**
	 * Perform text hover replacements
	 *
	 * @param string $text Text to be processed for text hovers
	 * @return string Text with hovertexts already processed
	 */
	function text_hover( $text ) {
		$oldchars = array( "(", ")", "[", "]", "?", ".", ",", "|", "\$", "*", "+", "^", "{", "}" );
		$newchars = array( "\(", "\)", "\[", "\]", "\?", "\.", "\,", "\|", "\\\$", "\*", "\+", "\^", "\{", "\}" );
		$options = $this->get_options();
		$text = ' ' . $text . ' ';
		$text_to_hover = apply_filters( $this->admin_options_name.'_option_text_to_hover', $options['text_to_hover'] ); //legacy (pre-3.0)
		$text_to_hover = apply_filters( 'c2c_text_hover', $text_to_hover );
		$case_sensitive = apply_filters( 'c2c_text_hover_case_sensitive', $options['case_sensitive'] );
		$preg_flags = $case_sensitive ? 's' : 'si';
		foreach ( $text_to_hover as $old_text => $hover_text ) {
			$old_text = stripslashes( str_replace( $oldchars, $newchars, $old_text ) );
			// WILL match string within string, but WON'T match within tags
			$new_text = "$1<acronym title='" . esc_attr( $hover_text ) . "'>$2</acronym>$3";
			$text = preg_replace( "|(?!<.*?)([\s\'\"\.\x98\x99\x9c\x9d\xCB\x9C\xE2\x84\xA2\xC5\x93\xEF\xBF\xBD\(\[\{])($old_text)([\s\'\"\x98\x99\x9c\x9d\xCB\x9C\xE2\x84\xA2\xC5\x93\xEF\xBF\xBD\?\!\.\,\-\+\]\)\}])(?![^<>]*?>)|$preg_flags", $new_text, $text );
		}
		return trim( $text );
	}

} // end c2c_TextHover

$GLOBALS['c2c_text_hover'] = new c2c_TextHover();

endif; // end if !class_exists()

?>