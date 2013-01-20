=== Optimize Database after Deleting Revisions ===
Contributors: CAGE Web Design | Rolf van Gelder
Donate link: http://cagewebdev.com
Plugin Name: Optimize Database after Deleting Revisions
Plugin URI: http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin
Tags: database, delete, revisions, optimize, post, posts, page, pages, clean, clean up, trash, spam, trashed, spammed, database size
Author URI: http://cagewebdev.com
Author: CAGE Web Design | Rolf van Gelder, Eindhoven, The Netherlands
Requires at least: 2.2.2
Tested up to: 3.5
Stable tag: 2.2.2
Version: 2.2.2

== Description ==

This plugin is a 'One Click' WordPress Database Cleaner / Optimizer.

= Main Features =
* Deletes redundant revisions of posts and pages (you optionally can keep an 'x'-amount of the most recent revisions)
* Deletes trashed posts, pages and comments (optional)
* Deletes spammed comments (optional)
* Deletes 'orphan postmeta items'
* Optimizes the database tables (optionally you can exclude certain tables from optimization)
* Creates a log file of the optimizations (optional)
* Optimization can be scheduled to automatically run once hourly, twice daily, once daily or once weekly (optional)

= Settings =
You can find the settings page in the WP Admin Panel &raquo; Settings &raquo; Optimize DB Options.

= Starting the Optimization =
You can start the Optimization in the WP Admin Panel &raquo; Tools &raquo; Optimize Database.
Note: if you use the Scheduler the Optimization will run automatically!

= Author =
CAGE Web Design | Rolf van Gelder, Eindhoven, The Netherlands - http://cagewebdev.com - http://cage.nl

= Plugin URL =
http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin

= Download URL =
http://wordpress.org/extend/plugins/rvg-optimize-database/

== Installation ==

* Upload the Plugin to the `/wp-content/plugins/` directory
* Activate the plugin in the WP Admin Panel &raquo; Plugins
* Change the settings (if needed) in the WP Admin Panel &raquo; Settings &raquo; Optimize DB Options.

== Changelog ==

= 2.2.2 [01/20/2013] =
* Bug fix: deleting of postmeta orphans didn't work correctly

= 2.2.1 [01/17/2013] =
* Bug fix: fixed some debug warnings

= 2.2 [01/11/2013] =
* NEW: 'Orphan Postmeta items' will be automatically deleted
* NEW: the possibility to exclude tables from Optimization (for instance for 'heavy traffic' tables)

= 2.1 [01/04/2013] =
* Bug fix: keeping a maximum number of revisions didn't work correctly

= 2.0 [12/18/2012] =
* NEW: Logging of the Optimizations (optional)
* NEW: Scheduling Optimizations for Automatic Execution (optional)
* Many other (technical and cosmetical) changes and improvements

= 1.3.4 [12/14/2012] =
* Changed the buttons for WP 3.5

= 1.3.3 [12/01/2012] =
* Some layout changes

= 1.3.2 [11/14/2012] =
* Shows more information about the optimized tables + other minor changes

= 1.3.1 [10/07/2012] =
* Minor changes

= 1.3 [10/06/2012] =
* Extra button for starting optimization, shows savings (in bytes) now

= 1.2 [10/03/2012] = 
* Major update: new options 'delete trash', 'delete spam', 'only optimize WordPress tables'

= 1.1.9 [09/27/2012] =
* Using a different method for retrieving database table names

= 1.1.8 [09/08/2012] =
* Another link fix

= 1.1.7 [09/03/2012] =
* Some textual and link fixes

= 1.1.6 [09/01/2012] =
* Fixed the link to the options page

= 1.1.3 [09/01/2012] =
* Moved the 'Optimize DB Options' item to Dashboard 'Settings' Menu and the 'Optimize Database' item to the Dashboard 'Tools' Menu. That makes more sense!

= 1.1.2 [08/30/2012] =
* Minor bug fix for the new option page

= 1.1 [08/29/2012] =
* Added: a new option page, in de plugins section, where you can define the maximum number of - most recent - revisions you want to keep per post or page

= 1.0.5 [08/21/2012] =
* Depreciated item ('has_cap') replaced, abandoned line of code removed

= 1.0.4 [06/06/2012] =
* Now also works with non short_open_tag's

= 1.0.3 [12/15/2011] =
* Some minor layout updates

= 1.0.2 [12/02/2011] =
* Some minor updates

= 1.0.1 [11/24/2011] =
* A few updates for the readme.txt file

= 1.0 [11/22/2011] =
* Initial release

== Frequently Asked Questions ==

= How can I change the settings of this plugin? =
* WP Admin Panel &raquo; Settings &raquo; Optimize DB Options'. There you can define the maximum number of - most recent - revisions you want to keep per post or page and some more options.

= How do I run this plugin? =
* WP Admin Panel &raquo; Tools &raquo; Optimize Database. Then click the 'Start Optimization'-button. Et voila!

= Why do I see 'Table does not support optimize, doing recreate + analyze instead' while optimizing my database? =
* That is because the table type of that table is not 'MyISAM'
