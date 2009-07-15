<?php
/*
Plugin Name: Text Hover
Version: 2.0
Plugin URI: http://coffee2code.com/wp-plugins/text-hover
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Add hover text to regular text in posts. Handy for providing explanations of names, terms, phrases, and acronyms mentioned in your blog.

Compatible with WordPress 2.6+, 2.7+, 2.8+.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

INSTALLATION:

1. Download the file http://coffee2code.com/wp-plugins/text-hover.zip and unzip it into your /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. Go to the Settings -> Text Hover admin settings page and customize the settings (namely to define the terms/acronyms and their explanations).
4. Use the terms/acronyms in posts and/or pages (terms/acronyms appearing in existing posts will also be affected by this plugin)

*/

/*
Copyright (c) 2007-2009 by Scott Reilly (aka coffee2code)

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

if ( !class_exists('TextHover') ) :

class TextHover {
	var $admin_options_name = 'c2c_text_hover';
	var $nonce_field = 'update-text_hover';
	var $show_admin = true;	// Change this to false if you don't want the plugin's admin page shown.
	var $config = array();
	var $options = array(); // Don't use this directly

	function TextHover() {
		$this->config = array(
			// input can be 'checkbox', 'text', 'textarea', 'hidden', or 'none'
			'text_to_hover' => array('input' => 'textarea', 'datatype' => 'hash', 'default' => array(
					"WP" => "WordPress"
				), 'label' => '',
				'help' => '',
				'input_attributes' => 'style="width: 98%; font-family: \"Courier New\", Courier, mono;" rows="5" cols="40"'
			),
			'case_sensitive' => array('input' => 'checkbox', 'default' => true,
				'label' => 'Should the matching of terms/acronyms be case sensitive?',
				'help' => 'i.e. if you define a term of \'WP\', should \'wp\' also be treated the same way? This setting applies to all terms. If you want to selectively have case insensitive terms, then leave this option checked and create separate entries for each variation.')
		);

		add_action('admin_menu', array(&$this, 'admin_menu'));		

		add_filter('the_content', array(&$this, 'text_hover'), 2);
		add_filter('get_the_excerpt', array(&$this, 'text_hover'), 2);
	}

	function install() {
		$this->options = $this->get_options();
		update_option($this->admin_options_name, $this->options);
	}

	function admin_menu() {
		static $plugin_basename;
		if ( $this->show_admin ) {
			global $wp_version;
			if ( current_user_can('manage_options') ) {
				$plugin_basename = plugin_basename(__FILE__); 
				if ( version_compare( $wp_version, '2.6.999', '>' ) )
					add_filter( 'plugin_action_links_' . $plugin_basename, array(&$this, 'plugin_action_links') );
				add_options_page(__('Text Hover', 'text-hover'), __('Text Hover', 'text-hover'), 9, $plugin_basename, array(&$this, 'options_page'));
			}
		}
	}

	function plugin_action_links( $action_links ) {
		static $plugin_basename;
		if ( !$plugin_basename ) $plugin_basename = plugin_basename(__FILE__); 
		$settings_link = '<a href="options.php?page='.$plugin_basename.'">' . __('Settings', 'text-hover') . '</a>';
		array_unshift( $action_links, $settings_link );

		return $action_links;
	}

	function get_options() {
		if ( !empty($this->options) ) return $this->options;
		// Derive options from the config
		$options = array();
		foreach ( array_keys($this->config) as $opt ) {
			$options[$opt] = $this->config[$opt]['default'];
		}
        $existing_options = get_option($this->admin_options_name);
        if ( !empty($existing_options) ) {
            foreach ( $existing_options as $key => $value )
                $options[$key] = $value;
        }            
		$this->options = $options;
        return $options;
	}

	function options_page() {
		static $plugin_basename;
		if ( !$plugin_basename ) $plugin_basename = plugin_basename(__FILE__); 
		$options = $this->get_options();
		// See if user has submitted form
		if ( isset($_POST['submitted']) ) {
			check_admin_referer($this->nonce_field);

			foreach (array_keys($options) AS $opt) {
				$options[$opt] = $_POST[$opt];
				if (($this->config[$opt]['input'] == 'checkbox') && !$options[$opt])
					$options[$opt] = 0;
				if ($this->config[$opt]['datatype'] == 'array')
					$options[$opt] = explode(',', str_replace(array(', ', ' ', ','), ',', $options[$opt]));
				elseif ($this->config[$opt]['datatype'] == 'hash') {
					if ( !empty($options[$opt]) ) {
						$new_values = array();
						foreach (explode("\n", $options[$opt]) AS $line) {
							list($shortcut, $text) = array_map('trim', explode("=>", $line, 2));
							if (!empty($shortcut)) $new_values[str_replace('\\', '', $shortcut)] = str_replace('\\', '', $text);
						}
						$options[$opt] = $new_values;
					}
				}
			}
			// Remember to put all the other options into the array or they'll get lost!
			update_option($this->admin_options_name, $options);

			echo "<div id='message' class='updated fade'><p><strong>" . __('Settings saved', 'text-hover') . '</strong></p></div>';
		}

		$action_url = $_SERVER[PHP_SELF] . '?page=' . $plugin_basename;
		$logo = plugins_url() . '/' . basename($_GET['page'], '.php') . '/c2c_minilogo.png';

		echo <<<END
		<div class='wrap'>
			<div class='icon32' style='width:44px;'><img src='$logo' alt='A plugin by coffee2code' /><br /></div>
			<h2>Text Hover Settings</h2>
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
			<li style="list-style:disc outside; margin-left:1em;">Terms and acronyms are assumed to be whole words within your posts (i.e. they are immediately prepended 
				by some sort of space character (space, tab, etc) and are immediately appended by a space character or 
				punctuation (which can include any of: ?!.,-+)]})</li>
			<li style="list-style:disc outside; margin-left:1em;"><strong><em>Define only one hovertext per line.</em></strong></li>
			<li style="list-style:disc outside; margin-left:1em;"><strong><em>Hovertexts must not span multiple lines (automatic linewrapping is okay).</em></strong></li>
			<li style="list-style:disc outside; margin-left:1em;"><strong><em>Don't use HTML in the hovertext.</em></strong></li>
			</ul>

END;
				wp_nonce_field($this->nonce_field);
		echo '<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform form-table">';
				foreach (array_keys($options) as $opt) {
					$input = $this->config[$opt]['input'];
					if ($input == 'none') continue;
					$label = $this->config[$opt]['label'];
					$value = $options[$opt];
					if ($input == 'checkbox') {
						$checked = ($value == 1) ? 'checked=checked ' : '';
						$value = 1;
					} else {
						$checked = '';
					};
					if ($this->config[$opt]['datatype'] == 'array') {
						if (!is_array($value))
							$value = '';
						else {
							if ($input == 'textarea' || $input == 'inline_textarea')
								$value = implode("\n", $value);
							else
								$value = implode(', ', $value);
						}
					} elseif ($this->config[$opt]['datatype'] == 'hash') {
						if (!is_array($value))
							$value = '';
						else {
							$new_value = '';
							foreach ($value AS $shortcut => $replacement) {
								$new_value .= "$shortcut => $replacement\n";
							}
							$value = $new_value;
						}
					}
					echo "<tr valign='top'>";
					if ($input == 'textarea') {
						echo "<td colspan='2'>";
						if ($label) echo "<strong>$label</strong><br />";
						echo "<textarea name='$opt' id='$opt' {$this->config[$opt]['input_attributes']}>" . $value . '</textarea>';
					} else {
						echo "<th scope='row'>$label</th><td>";
						if ($input == "inline_textarea")
							echo "<textarea name='$opt' id='$opt' {$this->config[$opt]['input_attributes']}>" . $value . '</textarea>';
						elseif ($input == 'select') {
							echo "<select name='$opt' id='$opt'>";
							foreach ($this->config[$opt]['options'] as $sopt) {
								$selected = $value == $sopt ? " selected='selected'" : '';
								echo "<option value='$sopt'$selected>$sopt</option>";
							}
							echo "</select>";
						} else
							echo "<input name='$opt' type='$input' id='$opt' value='$value' $checked {$this->config[$opt]['input_attributes']} />";
					}
					if ($this->config[$opt]['help']) {
						echo "<br /><span style='color:#777; font-size:x-small;'>";
						echo $this->config[$opt]['help'];
						echo "</span>";
					}
					echo "</td></tr>";
				}
		$txt = __('Save Changes');
		echo <<<END
			</tbody></table>
			<input type="hidden" name="submitted" value="1" />
			<div class="submit"><input type="submit" name="Submit" class="button-primary" value="{$txt}" /></div>
		</form>
			</div>
END;

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
	}

	function text_hover( $text, $case_sensitive=true ) {
		$oldchars = array("(", ")", "[", "]", "?", ".", ",", "|", "\$", "*", "+", "^", "{", "}");
		$newchars = array("\(", "\)", "\[", "\]", "\?", "\.", "\,", "\|", "\\\$", "\*", "\+", "\^", "\{", "\}");
		$options = $this->get_options();
		$text_to_hover = $options['text_to_hover'];
		$text = ' ' . $text . ' ';
		if ( !empty($text_to_hover) ) {
			foreach ( $text_to_hover as $old_text => $hover_text ) {
				$old_text = stripslashes(str_replace($oldchars, $newchars, $old_text));
				// WILL match string within string, but WON'T match within tags
				$preg_flags = ($case_sensitive) ? 's' : 'si';
				$new_text = "$1<acronym title='" . htmlspecialchars($hover_text, ENT_QUOTES) . "'>$old_text</acronym>$2";
				$text = preg_replace("|(\s)$old_text([\s\?\!\.\,\-\+\]\)\}])+|$preg_flags", $new_text, $text);
			}
		}
		return trim($text);
	} //end text_hover()

} // end TextHover

endif; // end if !class_exists()

if ( class_exists('TextHover') ) :
	$text_hover = new TextHover();
	if ( isset($text_hover) )
		register_activation_hook( __FILE__, array(&$text_hover, 'install') );
endif;
?>