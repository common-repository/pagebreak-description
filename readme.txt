=== Pagebreak Description ===
Contributors: Katsushi Kawamori
Donate link: https://shop.riverforest-wp.info/donate/
Tags: block, next page, prev page, pagebreak, pagination
Requires at least: 5.0
Requires PHP: 8.0
Tested up to: 6.6
Stable tag: 1.09
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Page breaks and before/after descriptions

== Description ==
* Enter page breaks and before/after descriptions. Values are used in pagination.
* Do not mix this block with the Page Break block, as it may cause the comment to be misaligned.

= How it works =
[youtube https://youtu.be/4WNGiGnnjbc]

= Filter hooks =
~~~
/** ==================================================
 * Filter for CSS URL.
 * Default CSS -> /pagebreak-description/css/style.css
 */
add_filter(
	'pagebreak_description_css', 
	function() {

		/* If you put mydesign.css in wp-content/uploads */
		$wp_uploads = wp_upload_dir();
		$upload_url = $wp_uploads['baseurl'];
		if ( is_ssl() ) {
			$upload_url = str_replace( 'http:', 'https:', $upload_url );
		}
		$upload_url = untrailingslashit( $upload_url );
		$url = $upload_url . '/mydesign.css';

		return $url;
	},
	10,
	1
);
~~~

== Installation ==

1. Upload `pagebreak-description` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

none

== Screenshots ==

1. Block
2. View
3. Management screen

== Changelog ==

= [1.09] 2024/06/11 =
* Fix - CSS in a single file.
* Added - Management screen.
* Added - Uninstall script.

= [1.08] 2024/06/10 =
* Fix - Pagination Description.

= [1.07] 2024/06/10 =
* Added - Pagination before content.

= [1.06] 2024/06/10 =
* Added - Tooltip processing for mobile devices.

= [1.05] 2024/06/09 =
* Added - Display a tooltip on the page link with a description of that page.

= [1.04] 2024/06/08 =
* Fix - Insert title tags into page links.
* Removed - Uninstall script.

= [1.03] 2024/06/08 =
* Fix - Insert title tags into page links.
* Fix - Uninstall script.

= [1.02] 2024/06/08 =
* Added - Insert title tags into page links.
* Added - Uninstall script.

= [1.01] 2024/01/14 =
* Tweak - Support for Glotpress.

= [1.00] 2024/01/14 =
Initial release.

== Upgrade Notice ==

= 1.00 =
