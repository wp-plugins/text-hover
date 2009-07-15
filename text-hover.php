<?php
/*
Plugin Name: Text Hover
Version: 1.0
Plugin URI: http://coffee2code.com/wp-plugins/text-hover
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Add hover text to regular text in posts.  Handy for providing explanations of names, terms, phrases, and acronyms mentioned in your blog.

Compatible with WordPress 2.2+, 2.3+, and 2.5.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

INSTALLATION:

1. Download the file http://coffee2code.com/wp-plugins/text-hover.zip and unzip it into your 
/wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. Go to the Options -> Text Hover (or in WP 2.5: Settings -> Text Hover) admin options page.  Optionally customize the options
(namely to define the terms/acronyms and their explanations).
4. Use the terms/acronyms in a post (terms/acronyms appearing in existing posts will also be affected by this plugin)

*/

/*
Copyright (c) 2007-2008 by Scott Reilly (aka coffee2code)

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


function c2c_text_hover( $text, $case_sensitive=true ) {
	$oldchars = array("(", ")", "[", "]", "?", ".", ",", "|", "\$", "*", "+", "^", "{", "}");
	$newchars = array("\(", "\)", "\[", "\]", "\?", "\.", "\,", "\|", "\\\$", "\*", "\+", "\^", "\{", "\}");
	$options = get_option('c2c_text_hover');
	$text_to_hover = $options['text_to_hover'];
	$text = ' ' . $text . ' ';
	if (!empty($text_to_hover)) {
		foreach ($text_to_hover as $old_text => $hover_text) {
			$old_text = stripslashes(str_replace($oldchars, $newchars, $old_text));
			// WILL match string within string, but WON'T match within tags
			$preg_flags = ($case_sensitive) ? 's' : 'si';
			$new_text = "$1<acronym title='" . htmlspecialchars($hover_text, ENT_QUOTES) . "'>$old_text</acronym>$2";
			$text = preg_replace("|(\s)$old_text([\s\?\!\.\,\-\+\]\)\}])+|$preg_flags", $new_text, $text);
		}
	}
	return trim($text);
} //end c2c_text_hover()

// Admin interface code

function c2c_admin_add_text_hover() {
	// Add menu under Options:
	$c = add_options_page('Text Hover Options', 'Text Hover', 10, basename(__FILE__), 'c2c_admin_text_hover');
	// Create option in options database if not there already:
	$options = array();
	$options['text_to_hover'] = array(
		"WP" => "WordPress"
	);
	add_option('c2c_text_hover', $options, 'Options for the Text Hover plugin by coffee2code');
} //end c2c_admin_add_text_hover()

function c2c_admin_text_hover() {
	// See if user has submitted form
	if ( isset($_POST['submitted']) ) {
		$options = array();
		
		$text_to_hover = $_POST['text_to_hover'];
		if ( !empty($text_to_hover) ) {
			$replacement_array = array();
			foreach (explode("\n", trim($text_to_hover)) AS $line) {
				list($shortcut, $text) = array_map('trim', explode("=>", $line, 2));
				if (!empty($shortcut)) $replacement_array[str_replace('\\', '', $shortcut)] = str_replace('\\', '', $text);
			}
			$options['text_to_hover'] = $replacement_array;
		}
		
		// Remember to put all the other options into the array or they'll get lost!
		update_option('c2c_text_hover', $options);
		echo '<div class="updated"><p>Plugin settings saved.</p></div>';
	}
	
	// Draw the Options page for the plugin.
	$options = get_option('c2c_text_hover');
	$text_to_hover = $options['text_to_hover'];
	$replacements = '';
	foreach ($text_to_hover AS $shortcut => $replacement) {
		$replacements .= "$shortcut => $replacement\n";
	}
	$action_url = $_SERVER[PHP_SELF] . '?page=' . basename(__FILE__);
echo <<<END
	<div class='wrap'>
		<h2>Text Hover Plugin Options</h2>
		<p>Text Hover is a plugin that allows you to add hover text for text in posts.
		   Very handy to create hover explanations of people mentioned in your blog, and/or
		   definitions of unique acronyms and terms you use. </p>
		
<form name="text_hover" action="$action_url" method="post">	
		<input type="hidden" name="submitted" value="1" />
		<p>Define terms/acronyms and hovertext explanations here.  The format should be like this:</p>
		
		<blockquote><code>WP => WordPress</code></blockquote>
		
		<p>Where <code>WP</code> is the term, acronym, or phrase you intend to use in your posts, and the 
		<code>WordPress</code> would be what you want to appear in a hover tooltip when a visitor hovers their
		mouse over the term.</p>
		
		<p>Other considerations:</p>
		
		<ul>
		<li>Terms and acronyms are assumed to be whole words within your posts (i.e. they are immediately prepended 
			by some sort of space character (space, tab, etc) and are immediately appended by a space character or 
			punctuation (which can include any of: ?!.,-+)]})</li>
		<li><strong><em>Define only one hovertext per line.</em></strong></li>
		<li><strong><em>Hovertexts must not span multiple lines (automatic linewrapping is okay).</em></strong></li>
		<li><strong><em>Don't use HTML in the hovertext.</em></strong></li>
		</ul>
		
		<textarea name="text_to_hover" id="text_to_hover" style="width: 98%; font-family: \"Courier New\", Courier, mono;" rows="15" cols="40">$replacements</textarea>
	<div class="submit"><input type="submit" name="Submit" value="Save Changes" /></div>
</form>
	</div>
END;
		$logo = get_option('siteurl') . '/wp-content/plugins/' . basename($_GET['page'], '.php') . '/c2c_minilogo.png';
		echo <<<END
		<style type="text/css">
			#c2c {
				text-align:center;
				color:#888;
				background-color:#ffffef;
				padding:5px 0 0;
				margin-top:12px;
				border-style:solid;
				border-color:#dadada;
				border-width:1px 0;
			}
			#c2c div {
				margin:0 auto;
				padding:5px 40px 0 0;
				width:45%;
				min-height:40px;
				background:url('$logo') no-repeat top right;
			}
			#c2c span {
				display:block;
				font-size:x-small;
			}
		</style>
		<div id='c2c' class='wrap'>
			<div>
			This plugin brought to you by <a href="http://coffee2code.com" title="coffee2code.com">Scott Reilly, aka coffee2code</a>.
			<span><a href="http://coffee2code.com/donate" title="Please consider a donation">Did you find this plugin useful?</a></span>
			</div>
		</div>
END;
} //end c2c_admin_text_hover()
add_action('admin_menu', 'c2c_admin_add_text_hover');

add_filter('the_content', 'c2c_text_hover', 2);
add_filter('get_the_excerpt', 'c2c_text_hover', 2);

?>