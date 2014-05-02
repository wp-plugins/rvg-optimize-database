=== Optimize Database after Deleting Revisions ===
Contributors: CAGE Web Design | Rolf van Gelder
Donate link: http://cagewebdev.com
Plugin Name: Optimize Database after Deleting Revisions
Plugin URI: http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin
Tags: database, delete, revisions, optimize, post, posts, page, pages, clean, clean up, trash, spam, trashed, spammed, database size, scheduler, transients
Author URI: http://cagewebdev.com
Author: CAGE Web Design | Rolf van Gelder, Eindhoven, The Netherlands
Requires at least: 2.0
Tested up to: 3.9
Stable tag: 2.7.9
Version: 2.7.9

== Description ==

This plugin is a 'One Click' WordPress Database Cleaner / Optimizer.

= Main Features =
* Deletes redundant revisions of posts and pages (you optionally can keep an 'x'-amount of the most recent revisions)
* Deletes trashed posts, pages and comments (optional)
* Deletes spammed comments (optional)
* Deletes unused tags (optional)
* Deletes 'orphan postmeta items'
* Deletes 'expired transients'
* Optimizes the database tables (optionally you can exclude certain tables from optimization)
* Creates a log file of the optimizations (optional)
* Optimization can be scheduled to automatically run once hourly, twice daily, once daily or once weekly at a specific time (optional)
* 'Optimize DB (1 click)' link in the admin bar (optional)

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

= Disclaimer =
No warranty, use at own risk!

== Installation ==

* Upload the Plugin to the `/wp-content/plugins/` directory
* Activate the plugin in the WP Admin Panel &raquo; Plugins
* Change the settings (if needed) in the WP Admin Panel &raquo; Settings &raquo; Optimize DB Options.

== Changelog ==

= 2.7.9 [05/02/2014] =
* BUG FIX: some minor bug fixes (thanks Mike!)

= 2.7.8 [05/01/2014] =
* CHANGE: replaced depreciated MySQL queries (from WP 3.9 / PHP 5.5)

= 2.7.7 [02/16/2014] =
* BUG FIX: made jQuery also https compatible

= 2.7.6 [01/16/2014] =
* BUG FIX: empty lines removed from output (gave problems with some RSS feeds)

= 2.7.5 [12/13/2013] =
* CHANGE: two queries optimized for better performance

= 2.7.4 [12/11/2013] =
* BUG FIX: added 'backticks' around the database name in a few queries

= 2.7.3 [12/09/2013] =
* BUG FIX: deleted some CR/LF's from the end of the plugin *sigh*

= 2.7.2 [12/09/2013] =
* BUG FIX: forgot to delete a debug item... oops! sorry!

= 2.7.1 [12/09/2013] =
* BUG FIX: query and depreciated item (mysql_list_tables) fixed

= 2.7 [12/06/2013] =
* NEW: deletion of expired transients (optional)

= 2.6 [07/22/2013] =
* NEW: deletion of unused tags (optional)

= 2.5.1 [05/24/2013] =
* BUG FIX: some short tags removed
* CHANGE: schedule time is only relevant and therefore only shown for 'daily' and 'weekly' schedules
* NEW: option to turn the '1-click' button in the admin bar on/off

= 2.5 [05/24/2013] =
* NEW: you can set a time (hour) for the scheduler to run (thanks to frekel)
* NEW: '1-click run button' in the admin bar (thanks to JB ORSI)

= 2.3.1 [05/03/2013] =
* BUG FIX: fixed a problem with 'invalid header' (during installation) 

= 2.3 [04/26/2013] =
* BUG FIX: fixed the 'Stealing Cron Schedules' issue 

= 2.2.9 [04/10/2013] =
* BUG FIX: bug fix for the 'check all NON-WordPress tables' link

= 2.2.8 [03/19/2013] =
* BUG FIX: bug fix for deleting Post Orphans

= 2.2.7 [03/18/2013] =
* NEW: 'Orphan Post items' (like 'Auto Drafts') will be automatically deleted too now (thanks to: 0izys)

= 2.2.6 [03/05/2013] =
* Text change: 'logging on' changed to 'keep a log' (thanks to: Neil Parks)
* NEW: number of orphans deleted now also shown in the log file
* NEW: 'Go To Optimizer' button on options page (thanks to: RonDsy)

= 2.2.5 [02/20/2013] =
* Bug fix: fixed an (innocent) PHP warning (in error.log)

= 2.2.4 [02/12/2013] =
* Bug fix: error corrected in readme.txt file

= 2.2.3 [02/09/2013] =
* Bug fix: fixed an (innocent) PHP warning (in error.log)

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
* WP Admin Panel &raquo; Tools &raquo; Optimize Database. Then click the 'Start Optimization'-button.
* Click the 'Optimize DB (1 click)' link in the Admin Bar (if enabled)

= Why do I see 'Table does not support optimize, doing recreate + analyze instead' while optimizing my database? =
* That is because the table type of that table is not 'MyISAM'

= I scheduled the optimization for 8pm but it runs at 6pm (my local time) =
* The scheduler uses the local time of the web server which can differ from your own local time
