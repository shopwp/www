=== EDD Variable Pricing Descriptions ===
Contributors: sumobi, easydigitaldownloads
Tags: easy digital downloads, digital downloads, e-downloads, edd, variable pricing, pricing, description
Requires at least: 3.3
Tested up to: 4.7.3
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily add descriptions to variable-priced downloads in Easy Digital Downloads

== Description ==

**This plugin requires [Easy Digital Downloads](http://wordpress.org/extend/plugins/easy-digital-downloads/ "Easy Digital Downloads") version 1.5.2 or greater.**

EDD Variable Pricing Descriptions simply adds an "Option Description" input field for each variable-priced download. This allows you to add a longer description to the option name if needed. It integrates seamlessly into the Easy Digital Downloads plugin using the provided hooks and will automatically output the description onto the front end of the website where variable priced downloads are shown.

== Installation ==

1. Upload entire `edd-variable-pricing-descriptions` to the `/wp-content/plugins/` directory, or just upload the ZIP package via 'Plugins > Add New > Upload' in your WP Admin
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add optional descriptions to your variable priced downloads

= Styling =

This plugin does not add any CSS styling as it simply adds the description wrapped a `<p>` tag. The `<p>` tag does however have a class of `edd-variable-pricing-desc` should you wish to style the description more.

== Screenshots ==

1. The new "Option Description" input fields
2. The new field values being outputed onto the front-end

== Changelog ==

= 1.0.3 =
* Tweak: Update plugin information

= 1.0.2 =
* Fix: Undefined Index PHP Notice
* New: Modified the way descriptions are outputted to better future-proof them

= 1.0.1 =
* Fix: Undefined Index PHP Notice

= 1.0 =
* Initial release

== Upgrade Notice ==

Now requires Easy Digital Downloads 1.5.2 or greater. Descriptions are outputted using the new edd_after_price_option action hook introduced in EDD 1.5.2. This means the descriptions are less likely to break in future versions of EDD.
