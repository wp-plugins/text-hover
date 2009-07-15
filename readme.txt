=== Text Hover ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: text, post content, abbreviations, terms, acronyms, hover, help, coffee2code
Requires at least: 2.6
Tested up to: 2.8.1
Stable tag: 2.0
Version: 2.0

Add hover text to regular text in posts. Handy for providing explanations of names, terms, phrases, and acronyms mentioned in your blog.

== Description ==

Add hover text to regular text in posts.  Handy for providing explanations of names, terms, phrases, and acronyms mentioned in your blog.

Hover text are defined as terms/acronyms/phrasees that you expect to use in your blog posts and the expand text you wish to appear when the visitor hovers their mouse over the term.  The admin options form for the plugin explains the format, which is quite simple.  An example of which is shown here :

`WP => WordPress
Matt => Matt Mullenweg
The Scooby Shack => the bar where the gang hangs out`

**Note:** This is not the same as my Text Replace plugin, which defines terms which you would use but that you want replaced by the associated replacement text when displayed on your blog.  Text Hover adds the hover text as additional information for when visitors hover over the term, which is otherwise displayed in the post as you typed it.

== Installation ==

1. Unzip `text-hover.zip` inside the `/wp-content/plugins/` directory, or upload `text-hover.php` there
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Go to the `Settings` -> `Text Hover` admin settings page and customize the settings (namely to define the terms/acronyms and their explanations).
1. Use the terms/acronyms in posts and/or pages (terms/acronyms appearing in existing posts will also be affected by this plugin)

== Frequently Asked Questions ==

= In my posts, hover text terms do not appear any differently than regular text (though I can hover over them and see the hover text)!  What gives? =

The plugin currently makes use of the standard HTML tag `acronym` to specify the terms and their hover text.  Browsers have default handling and display of `acronym`.  It's possibly that the CSS for your theme is overriding the default display.  I use the following in my site's styles.css file to ensure it displays for me in the manner I prefer (which, by the same token, you can use more CSS formatting to further format the hover terms) :
`acronym {
	border-bottom:1px dotted #000;
}`

= Does this plugin modify the post content in the database? =

No.  The plugin filters post content on-the-fly.

= Will this work for posts I wrote prior to installing this plugin? =

Yes.

= What post fields get handled by this plugin? =

The plugin filters the post content and post excerpt fields.

= Is the plugin case sensitive? =

By default, yes.  You can change this behavior via the plugin's settings page.  Note that the option applies to all terms/acronyms.  If you want selectively have terms/acronyms be case insensitive, you should leave the case sensitive setting checked and add a listing for each case variation you wish to support.

== Screenshots ==

1. A screenshot of the admin options page for the plugin, where you define the terms/acronyms/phrases and their related hover text
2. A screenshot of the plugin in action for a post when the mouse is hovering over a defined hover text term

== Changelog ==

= 2.0 =

= 1.0 =
* Initial release