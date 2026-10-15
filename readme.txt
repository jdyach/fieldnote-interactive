=== Fieldnote Interactive ===
Contributors: jdyach
Tags: interactivity api, filter, bookmarks, reading list, block
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A filterable post grid and a save-for-later reading list, built with the WordPress Interactivity API.

== Description ==

Fieldnote Interactive adds three blocks:

* Filterable Post Grid: category filters and pagination that update in place without reloading the page. The browser URL and back button work as expected, and the grid still works as normal links when JavaScript is unavailable.
* Save for Later: a bookmark toggle for posts. Works in single post templates and inside Query Loops.
* Reading List: a header button showing how many posts the visitor has saved, with a panel to revisit or remove them.

Saved posts are stored in the visitor's own browser (localStorage). Nothing is sent to or stored on your server, and no cookies are set.

With a block theme, the Reading List is added after your header navigation and the Save button after single post titles automatically. You can move or remove them in the Site Editor.

== Installation ==

1. Upload the plugin zip from Plugins > Add New > Upload Plugin, then activate it.
2. Add the Filterable Post Grid block to any page.

== Frequently Asked Questions ==

= Does this store any personal data? =

No. The reading list lives only in the visitor's browser.

= Does it work without JavaScript? =

The post grid does: filters and pagination fall back to regular links. The Save button and Reading List need JavaScript, so they stay hidden when it is unavailable.

== Changelog ==

= 1.0.0 =
* Initial release.
