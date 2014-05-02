<?php
$odb_version      = '2.7.9';
$odb_release_date = '05/02/2014';
/**
 * @package Optimize Database after Deleting Revisions
 * @version 2.7.9
 */
/*
Plugin Name: Optimize Database after Deleting Revisions
Plugin URI: http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin/
Description: Optimizes the Wordpress Database after Cleaning it out - <a href="options-general.php?page=rvg_odb_admin"><strong>plug in options</strong></a>
Author: CAGE Web Design | Rolf van Gelder, Eindhoven, The Netherlands
Version: 2.7.9
Author URI: http://cagewebdev.com
*/

/********************************************************************************************

	ADD THE 'OPTIMIZE DATABASE' ITEM TO THE TOOLS MENU

*********************************************************************************************/
function optimize_db_main()
{	if (function_exists('add_management_page'))
	{	add_management_page(__('Optimize Database'), __('Optimize Database'),'administrator' ,'rvg-optimize-db.php', 'rvg_optimize_db');
    }
}
add_action('admin_menu', 'optimize_db_main');


/********************************************************************************************

	ADD THE 'OPTIMIZE DB OPTIONS' ITEM TO THE SETTINGS MENU

*********************************************************************************************/
function rvg_odb_admin_menu()
{	
	if (function_exists('add_options_page'))
	{	add_options_page(__('Optimize DB Options'), __('Optimize DB Options'), 'manage_options', 'rvg_odb_admin', 'rvg_odb_options_page');
    }
}
add_action( 'admin_menu', 'rvg_odb_admin_menu' );


/********************************************************************************************

	ADD THE '1 CLICK OPTIMIZE DATABASE' ITEM TO THE ADMIN BAR (IF ACTIVATED)

*********************************************************************************************/
function rvg_odb_admin_bar()
{	global $wp_admin_bar;
	if ( !is_super_admin() || !is_admin_bar_showing() ) return;
	$siteurl = site_url('/');
	$wp_admin_bar->add_menu( array('id' => 'optimize','title' => __('Optimize DB (1 click)'),'href' => __($siteurl.'wp-admin/tools.php?page=rvg-optimize-db.php&action=run') ) );
}
$rvg_odb_adminbar = get_option('rvg_odb_adminbar');
if($rvg_odb_adminbar == "Y") add_action( 'wp_before_admin_bar_render', 'rvg_odb_admin_bar' );


/********************************************************************************************

	ACTIONS FOR THE SCHEDULER
	
	http://codex.wordpress.org/Plugin_API/Filter_Reference/cron_schedules

*********************************************************************************************/
function rvg_extra_schedules( $schedules ) {
	// ADD A WEEKLY SCHEDULE
	$schedules['weekly'] = array(
		'interval' => 604800,
		'display' => __('Once Weekly')
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'rvg_extra_schedules' ); 

add_action( 'rvg_optimize_database', 'rvg_optimize_db_cron' );

// REMOVE SCHEDULED TASK WHEN DEACTIVATED
register_deactivation_hook( __FILE__, 'rvg_deactivate_plugin' );
function rvg_deactivate_plugin()
{	// CLEAR CURRENT SCHEDULE (IF ANY)
	wp_clear_scheduled_hook('rvg_optimize_database');	
}

// RE-SCHEDULE TASK WHEN RE-ACTIVATED (OR AFTER UPDATE)
register_activation_hook( __FILE__, 'rvg_activate_plugin' );
function rvg_activate_plugin()
{	$rvg_odb_schedule = get_option('rvg_odb_schedule');
	if($rvg_odb_schedule)
	{	// PLUGIN RE-ACTIVATED: START SCHEDULER
		if( !wp_next_scheduled( 'rvg_optimize_database' ))
			wp_schedule_event( time(), $rvg_odb_schedule, 'rvg_optimize_database' );
	}
} # rvg_activate_plugin ()


/********************************************************************************************

	CREATE THE OPTIONS PAGE

*********************************************************************************************/
function rvg_odb_options_page()
{
	global $odb_version, $odb_release_date, $wpdb, $table_prefix;

	$timezone_format  = _x('YmdGis', 'timezone date format');
	$current_datetime = date_i18n($timezone_format);
	$current_date     = substr($current_datetime, 0, 8);
	$current_hour     = substr($current_datetime, 8, 2);
	
	# jQuery FRAMEWORK
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
		echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>';
	}
	else
	{	# 2.7.9
		echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>';
	}
	
	if(isset($_REQUEST['delete_log']))
		if($_REQUEST['delete_log'] == "Y") @unlink(dirname(__FILE__).'/rvg-optimize-db-log.html');
	
	// SAVE THE OPTIONS
 	if (isset($_POST['info_update']))
	{
		check_admin_referer();

		# DELETE ALL EXCLUDED TABLES
		$sql = "
		DELETE	FROM $wpdb->options
		WHERE	`option_name` LIKE 'rvg_ex_%'
		";
		$wpdb -> get_results($sql);
		
		# ADD EXCLUDED TABLES
		foreach ($_POST as $key => $value)
		{	if(substr($key,0,3) == 'cb_')
			{	$sql = "
				INSERT INTO $wpdb->options (option_name, option_value, autoload)
				VALUES ('rvg_ex_".substr($key,3)."','excluded','yes')
				";
				$wpdb -> get_results($sql);
			}
		}

		if(isset($_POST['rvg_odb_number']))
		{	$rvg_odb_number = trim($_POST['rvg_odb_number']);
			update_option('rvg_odb_number', $rvg_odb_number);
		}
		
		$rvg_clear_trash = 'N';
		if(isset($_POST['rvg_clear_trash']))
			$rvg_clear_trash = $_POST['rvg_clear_trash'];
		update_option('rvg_clear_trash', $rvg_clear_trash);
		
		$rvg_clear_spam = 'N';
		if(isset($_POST['rvg_clear_spam']))
			$rvg_clear_spam = $_POST['rvg_clear_spam'];
		update_option('rvg_clear_spam', $rvg_clear_spam);

		$rvg_clear_tags = 'N';
		if(isset($_POST['rvg_clear_tags']))
			$rvg_clear_tags = $_POST['rvg_clear_tags'];
		update_option('rvg_clear_tags', $rvg_clear_tags);
		
		$rvg_clear_transients = 'N';
		if(isset($_POST['rvg_clear_transients']))
			$rvg_clear_transients = $_POST['rvg_clear_transients'];
		update_option('rvg_clear_transients', $rvg_clear_transients);

		$rvg_odb_adminbar = 'N';
		if(isset($_POST['rvg_odb_adminbar']))
			$rvg_odb_adminbar = $_POST['rvg_odb_adminbar'];
		update_option('rvg_odb_adminbar', $rvg_odb_adminbar);
		
		$rvg_odb_logging_on = 'N';
		if(isset($_POST['rvg_odb_logging_on']))
			$rvg_odb_logging_on = $_POST['rvg_odb_logging_on'];
		update_option('rvg_odb_logging_on', $rvg_odb_logging_on);

		$rvg_odb_schedule = '';
		if(isset($_POST['rvg_odb_schedule']))
			$rvg_odb_schedule = $_POST['rvg_odb_schedule'];
		update_option('rvg_odb_schedule', $rvg_odb_schedule);

		$rvg_odb_schedulehour = '';
		if(isset($_POST['rvg_odb_schedulehour']))
			$rvg_odb_schedulehour = $_POST['rvg_odb_schedulehour'];
		update_option('rvg_odb_schedulehour', $rvg_odb_schedulehour);

		// CLEAR CURRENT SCHEDULE (IF ANY)
		wp_clear_scheduled_hook('rvg_optimize_database');

		// HAS TO BE SCHEDULED
		if($rvg_odb_schedule != '')		
			if( !wp_next_scheduled( 'rvg_optimize_database' ))
			{	
				$time = 0;
				if($rvg_odb_schedulehour == '')
				{	$time = time();
				}
				else
				{
					if($rvg_odb_schedulehour <= $current_hour)
					    // NEXT RUN TOMORROW
						$newdatetime = date('YmdHis', strtotime($current_date.$rvg_odb_schedulehour.'0000'.' + 1 day'));
					else
						// NEXT RUN TODAY
						$newdatetime = $current_date.$rvg_odb_schedulehour.'0000';
					// DATE TO UNIX TIMESTAMP (EPOCH)
					$time = strtotime($newdatetime);
				}
				// SCHEDULE THE EVENT
				wp_schedule_event( $time, $rvg_odb_schedule, 'rvg_optimize_database' );
			}
		
		// UPDATED MESSAGE
		echo "<div class='updated'><p><strong>Optimize Database after Deleting Revisions OPTIONS UPDATED</strong> - Click <a href='tools.php?page=rvg-optimize-db.php' style='font-weight:bold'>HERE</a> to run the optimization</p></div>";
	}
	
	$rvg_odb_number = get_option('rvg_odb_number');
	if(!$rvg_odb_number) $rvg_odb_number = '0';
	
	$rvg_clear_trash = get_option('rvg_clear_trash');
	if(!$rvg_clear_trash) $rvg_clear_trash = 'N';
	
	$rvg_clear_spam = get_option('rvg_clear_spam');
	if(!$rvg_clear_spam) $rvg_clear_spam = 'N';

	$rvg_clear_tags = get_option('rvg_clear_tags');
	if(!$rvg_clear_tags) $rvg_clear_tags = 'N';

	$rvg_clear_transients = get_option('rvg_clear_transients');
	if(!$rvg_clear_transients) $rvg_clear_transients = 'N';
	
	$rvg_odb_logging_on = get_option('rvg_odb_logging_on');
	if(!$rvg_odb_logging_on) $rvg_odb_logging_on = 'N';
	
	$rvg_odb_schedule = get_option('rvg_odb_schedule');
	if(!$rvg_odb_schedule) $rvg_odb_schedule = '';
	
	$rvg_odb_schedulehour = get_option('rvg_odb_schedulehour');
	
	$rvg_odb_adminbar = get_option('rvg_odb_adminbar');
	if(!$rvg_odb_adminbar) $rvg_odb_adminbar = 'N';
	?>
<script type="text/javascript">
function schedule_changed()
{	if(document.options.rvg_odb_schedule.value == 'daily' || document.options.rvg_odb_schedule.value == 'weekly')
		$("#schedulehour").show();
	else
		$("#schedulehour").hide();
}
</script>

<form name="options" method="post" action="">
  <div class="wrap">
    <h2>Using Optimize Database after Deleting Revisions</h2>
    <blockquote>
      <p><strong>'<em>Optimize Database after Deleting Revisions</em>' is an one-click plugin to clean and optimize your WordPress database.</strong></p>
      <p>To start the optimization:<br />
        <strong>WP Admin Panel</strong> &raquo; <strong>Tools</strong> &raquo; <strong>Optimize Database</strong>. Then click the '<strong>Start Optimization</strong>'-button. Et voila!<br />
        Note: if you use the Scheduler the Optimization will run automatically!
      <p>Plugin version:<br />
        <strong>v<?php echo $odb_version ?> (<?php echo $odb_release_date?>)</strong> </p>
      <p><strong>Author:</strong><br />
        <strong><a href="http://cagewebdev.com/" target="_blank">CAGE Web Design</a> | <a href="http://cage.nl/" target="_blank">Rolf van Gelder</a></strong>, Eindhoven, The Netherlands<br />
        <strong>Plugin URL:</strong><br />
        <a href="http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin/" target="_blank"><strong>http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin/</strong></a><br />
        <strong>Download URL:</strong><br />
        <strong><a href="http://wordpress.org/extend/plugins/rvg-optimize-database/" target="_blank">http://wordpress.org/extend/plugins/rvg-optimize-database/</a></strong></p>
      <p>&nbsp;</p>
    </blockquote>
    <h2>Optimize Database after Deleting Revisions - Options</h2>
    <?php
if($rvg_odb_adminbar == 'Y')  $rvg_odb_adminbar_checked  = ' checked="checked"'; else $rvg_odb_adminbar_checked = '';	
if($rvg_clear_trash == 'Y') $rvg_clear_trash_checked = ' checked="checked"'; else $rvg_clear_trash_checked = '';
if($rvg_clear_spam == 'Y')  $rvg_clear_spam_checked  = ' checked="checked"'; else $rvg_clear_spam_checked = '';
if($rvg_clear_tags == 'Y')  $rvg_clear_tags_checked  = ' checked="checked"'; else $rvg_clear_tags_checked = '';
if($rvg_clear_transients == 'Y')  $rvg_clear_transients_checked  = ' checked="checked"'; else $rvg_clear_transients_checked = '';
if($rvg_odb_logging_on == 'Y')  $rvg_odb_logging_on_checked  = ' checked="checked"'; else $rvg_odb_logging_on_checked = '';
?>
    <blockquote>
      <fieldset class='options'>
        <table class="editform" cellspacing="2" cellpadding="5" width="100%">
          <tr>
            <td colspan="3" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="4">
                <tr>
                  <td width="50%" align="right" valign="top"><span style="font-weight:bold;">Maximum number of - most recent - revisions to keep per post / page<br />
                    ('0' means: delete <u>ALL</u> revisions)</span></td>
                  <td width="50%" valign="top"><input type="text" size="5" name="rvg_odb_number" id="rvg_odb_number" value="<?php echo $rvg_odb_number?>" style="font-weight:bold;color:#00F;" /></td>
                </tr>
                <tr>
                  <td width="50%" align="right" valign="top"><span style="font-weight:bold;">Delete all trashed items</span></td>
                  <td width="50%" valign="top"><input name="rvg_clear_trash" type="checkbox" value="Y" <?php echo $rvg_clear_trash_checked?> /></td>
                </tr>
                <tr>
                  <td width="50%" align="right" valign="top"><span style="font-weight:bold;">Delete all spammed items</span></td>
                  <td width="50%" valign="top"><input name="rvg_clear_spam" type="checkbox" value="Y" <?php echo $rvg_clear_spam_checked?> /></td>
                </tr>
                <tr>
                  <td width="50%" align="right" valign="top"><span style="font-weight:bold;">Delete unused tags</span></td>
                  <td width="50%" valign="top"><input name="rvg_clear_tags" type="checkbox" value="Y" <?php echo $rvg_clear_tags_checked?> /></td>
                </tr>
                <tr>
                  <td width="50%" align="right" valign="top"><span style="font-weight:bold;">Delete expired transients</span></td>
                  <td width="50%" valign="top"><input name="rvg_clear_transients" type="checkbox" value="Y" <?php echo $rvg_clear_transients_checked?> /></td>
                </tr>
                <tr>
                  <td width="50%" align="right" valign="top"><span style="font-weight:bold;">Keep a log</span></td>
                  <td width="50%" valign="top"><input name="rvg_odb_logging_on" type="checkbox" value="Y" <?php echo $rvg_odb_logging_on_checked?> /></td>
                </tr>
                <tr>
                  <td width="50%" align="right"><span style="font-weight:bold;">Scheduler</span></td>
                  <td width="50%"><select name="rvg_odb_schedule" id="rvg_odb_schedule" onchange="schedule_changed();">
                      <option selected="selected" value="">NOT SCHEDULED</option>
                      <option value="hourly">run optimization HOURLY</option>
                      <option value="twicedaily">run optimization TWICE A DAY</option>
                      <option value="daily">run optimization DAILY</option>
                      <option value="weekly">run optimization WEEKLY</option>
                      <?php /*?><option value="test">run optimization TEST</option><?php */?>
                    </select>
                    <script type="text/javascript">
			        document.options.rvg_odb_schedule.value = '<?php echo $rvg_odb_schedule; ?>';		
			        </script> 
                    <span id="schedulehour" style="display:none;"> <span style="font-weight:bold;">Time</span>
                    <select name="rvg_odb_schedulehour" id="rvg_odb_schedulehour">
                      <?php
                    for($i=0; $i<=23; $i++)
                    {	if($i<10) $i = '0'.$i;
                    ?>
                      <option value="<?php echo $i?>"><?php echo $i.':00'.' hrs'?></option>
                      <?php	
                    }
                    ?>
                    </select>
                    <script type="text/javascript">
			        document.options.rvg_odb_schedulehour.value = '<?php echo $rvg_odb_schedulehour; ?>';
			        </script> 
                    </span> 
                    <script type="text/javascript">schedule_changed();</script></td>
                </tr>
                <tr>
                  <td align="right" valign="top"><span style="font-weight:bold;">Show '1-click' link in Admin Bar</span></td>
                  <td valign="top"><input name="rvg_odb_adminbar" type="checkbox" value="Y" <?php echo $rvg_odb_adminbar_checked?> />
                    (change will be visible after loading the next page)</td>
                </tr>
              </table></td>
          </tr>
          <?php
	# v2.7.8
	$names = $wpdb->get_results("SHOW TABLES FROM `".DB_NAME."`");
	$dbname = 'Tables_in_'.DB_NAME;
?>
          <tr>
            <td colspan="4" valign="top"><table id="table_list" width="100%" border="0" cellspacing="0" cellpadding="4" style="display:block;">
                <tr>
                  <td colspan="4" align="center"><span style="font-weight:bold;">EXCLUDE DATABASE TABLES FROM OPTIMIZATION: <span style="text-decoration:underline;color:#F00;">CHECKED</span> TABLES <span style="text-decoration:underline;color:#F00;">WON'T</span> BE OPTIMIZED!</span><br />
                    <a href="javascript:;" onclick="$('[id^=cb_]').attr('checked',true);">check all tables</a> | <a href="javascript:;" onclick="$('[id^=cb_]').attr('checked',false);">uncheck all tables</a> | <a href="javascript:;" onclick="$(':not([id^=cb_<?php echo $table_prefix; ?>])').filter('[id^=cb_]').attr('checked',true);">check all NON-WordPress tables</a></td>
                </tr>
                <tr>
                  <?php
	$c = 0;
	$t = 0;
	# v2.7.8
	for ($i=0; $i<count($names); $i++)
	{	$t++;
		$c++;
		if($c>4)
		{	$c = 1;
			echo '</tr>';
			echo '<tr>';
		}
		$style = 'normal';
		// WORDPRESS TABLE?
		if(substr($names[$i]->$dbname,0,strlen($table_prefix)) == $table_prefix) $style = 'bold;color:#00F;';
		
		$cb_checked = '';
		$sql = "
		SELECT	`option_value`
		FROM	$wpdb->options
		WHERE	`option_name` = 'rvg_ex_".$names[$i]->$dbname."'
		";
		$results = $wpdb -> get_results($sql);
		if(isset($results[0]->option_value))
			if($results[0]->option_value == 'excluded') $cb_checked = ' checked';		
		echo '<td width="25%" style="font-weight:'.$style.'"><input id="cb_'.$names[$i]->$dbname.'" name="cb_'.$names[$i]->$dbname.'" type="checkbox" value="1" '.$cb_checked.'  /> '.$names[$i]->$dbname.'</td>'."\n";
	} # for ($i=0; $i<count($names); $i++)
?>
                </tr>
              </table></td>
          </tr>
        </table>
      </fieldset>
    </blockquote>
    <p class="submit">
      <input class="button-primary button-large" type='submit' name='info_update' value='Save Options' style="font-weight:bold;" />
      &nbsp;
      <input class="button" type="button" name="delete_log" value="Go To Optimizer" onclick="self.location='tools.php?page=rvg-optimize-db.php'" style="font-weight:normal;" />
    </p>
  </div>
</form>
<?php
} // rvg_odb_options_page ()


/********************************************************************************************

	MAIN FUNCTION
	FOR DELETING REVISIONS, TRASH, SPAM, TAGS ORPHANS AND OPTIMIZING DATABASE TABLES

*********************************************************************************************/
function rvg_optimize_db()
{
	global $wpdb, $odb_version, $table_prefix;

	$timezone_format  = _x('G:i', 'timezone date format');
	$current_hour     = date_i18n($timezone_format);

	if(isset($_REQUEST['action']))
		if($_REQUEST['action'] == "delete_log")
			@unlink(dirname(__FILE__).'/rvg-optimize-db-log.html');

	/****************************************************************************************
	
		DELETE REVISIONS
	
	******************************************************************************************/
	
	// GET OPTIONS AND SET DEFAULT VALUES
	$max_revisions = get_option('rvg_odb_number');
	if(!$max_revisions)
	{	$max_revisions = 0;
		update_option('rvg_odb_number', $max_revisions);
	}
	
	$clear_trash = get_option('rvg_clear_trash');
	if(!$clear_trash)
	{	$clear_trash = 'N';
		update_option('rvg_clear_trash', $clear_trash);
	}
	$clear_trash_yn = ($clear_trash == 'N') ? 'NO' : 'YES';
	
	$clear_spam = get_option('rvg_clear_spam');
	if(!$clear_spam)
	{	$clear_spam = 'N';
		update_option('rvg_clear_spam', $clear_spam);
	}
	$clear_spam_yn = ($clear_spam == 'N') ? 'NO' : 'YES';

	$clear_tags = get_option('rvg_clear_tags');
	if(!$clear_tags)
	{	$clear_tags = 'N';
		update_option('rvg_clear_tags', $clear_tags);
	}
	$clear_tags_yn = ($clear_tags == 'N') ? 'NO' : 'YES';

	$clear_transients = get_option('rvg_clear_transients');
	if(!$clear_transients)
	{	$clear_transients = 'N';
		update_option('rvg_clear_transients', $clear_transients);
	}
	$clear_transients_yn = ($clear_transients == 'N') ? 'NO' : 'YES';

	$rvg_odb_logging_on = get_option('rvg_odb_logging_on');
	if(!$rvg_odb_logging_on)
	{	$rvg_odb_logging_on = 'N';
		update_option('rvg_odb_logging_on', $rvg_odb_logging_on);
	}
	$rvg_odb_logging_on_yn = ($rvg_odb_logging_on == 'N') ? 'NO' : 'YES';
	
	$rvg_odb_schedule = get_option('rvg_odb_schedule');
	if(!$rvg_odb_schedule)
	{	$rvg_odb_schedule = '';
		update_option('rvg_odb_schedule', $rvg_odb_schedule);
	}

	if($rvg_odb_schedule == 'hourly')
		$rvg_odb_schedule_txt = 'ONCE HOURLY';
	else if($rvg_odb_schedule == 'twicedaily')
		$rvg_odb_schedule_txt = 'TWICE DAILY';
	else if($rvg_odb_schedule == 'daily')
		$rvg_odb_schedule_txt = 'ONCE DAILY';
	else if($rvg_odb_schedule == 'weekly')
		$rvg_odb_schedule_txt = 'ONCE WEEKLY';			
	else if($rvg_odb_schedule == 'test')
		$rvg_odb_schedule_txt = 'TEST';

	$nextrun = '';			
	if(!isset($rvg_odb_schedule_txt))
	{	$rvg_odb_schedule_txt = 'NOT SCHEDULED';
	}
	else
	{	$timestamp = wp_next_scheduled('rvg_optimize_database');
		$nextrun = date_i18n('M j, Y @ G:i', $timestamp);
	}
	
	$total_savings = get_option('rvg_odb_total_savings');

	$log_url = plugins_url().'/rvg-optimize-database/rvg-optimize-db-log.html';

	$sql = "
	SELECT COUNT(*) cnt
	FROM $wpdb->options
	WHERE option_name LIKE 'rvg_ex_%'
	";
	$results = $wpdb -> get_results($sql);
	$number_excluded = $results[0] -> cnt;
?>
<div style="padding-left:8px;">
  <h2>Optimize your WordPress Database</h2>
  <?php
	if(isset($_REQUEST['action']))
		if($_REQUEST['action'] == "delete_log")
			echo '<div class="updated" style="position:relative;left:-15px;"><p><strong>Optimize Database after Deleting Revisions - LOG FILE DELETED</strong></p></div>';
?>
  <p><span style="font-style:italic;"><a href="http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin/" target="_blank" style="font-weight:bold;">Optimize Database after Deleting Revisions v<?php echo $odb_version?></a> - A WordPress Plugin by <a href="http://cagewebdev.com/" target="_blank" style="font-weight:bold;">CAGE Web Design</a> | <a href="http://cage.nl/" target="_blank" style="font-weight:bold;">Rolf van Gelder</a>, Eindhoven, The Netherlands</span></p>
  <p>Current options:<br />
    <strong>Maximum number of - most recent - revisions to keep per post / page:</strong> <span style="font-weight:bold;color:#00F;"><?php echo $max_revisions?></span><br />
    <strong>Delete trashed items:</strong> <span style="font-weight:bold;color:#00F;"><?php echo $clear_trash_yn?></span><br />
    <strong>Delete spammed items:</strong> <span style="font-weight:bold;color:#00F;"><?php echo $clear_spam_yn?></span><br />
    <strong>Delete unused tags:</strong> <span style="font-weight:bold;color:#00F;"><?php echo $clear_tags_yn?></span><br />
    <strong>Delete expired transients:</strong> <span style="font-weight:bold;color:#00F;"><?php echo $clear_transients_yn?></span><br />
    <strong>Keep a log:</strong> <span style="font-weight:bold;color:#00F;"><?php echo $rvg_odb_logging_on_yn?></span><br />
    <strong>Number of excluded tables:</strong> <span style="font-weight:bold;color:#00F;"><?php echo $number_excluded?></span><br />
    <strong>Scheduler:</strong> <span style="font-weight:bold;color:#00F;"><?php echo $rvg_odb_schedule_txt?></span>
    <?php
	if($nextrun)
	{
?>
    <br />
    <strong>Next scheduled run:</strong> <span style="font-weight:bold;color:#00F;"><?php echo $nextrun?> hrs (current server time: <?php echo $current_hour?>)</span>
    <?php		
	}
	if($total_savings)
	{
?>
    <br />
    <strong>Total savings since the first run:</strong> <span style="font-weight:bold;color:#00F;"><?php echo rvg_format_size($total_savings); ?></span>
    <?php
	}
    ?>
  <p class="submit">
    <input class="button" type="button" name="change_options" value="Change Options" onclick="self.location='options-general.php?page=rvg_odb_admin'" style="font-weight:normal;" />
    <?php
	if(file_exists(dirname(__FILE__).'/rvg-optimize-db-log.html'))
	{
?>
    &nbsp;
    <input class="button" type="button" name="view_log" value="View Log File" onclick="window.open('<?php echo $log_url?>','log','width=800,height=800,scrollbars=1')" style="font-weight:normal;" />
    &nbsp;
    <input class="button" type="button" name="delete_log" value="Delete Log File" onclick="self.location='tools.php?page=rvg-optimize-db.php&action=delete_log'" style="font-weight:normal;" />
    <?php	
	}
	$action = '';
	if(isset($_REQUEST['action'])) $action = $_REQUEST['action'];
	if($action != 'run')
	{
?>
    &nbsp;
    <input class="button-primary button-large" type="button" name="start_optimization" value="Start Optimization" onclick="self.location='tools.php?page=rvg-optimize-db.php&action=run'" style="font-weight:bold;" />
    <?php		
	}
?>
  </p>
</div>
<?php
	$action = '';
	if(isset($_REQUEST['action'])) $action = $_REQUEST['action'];
	if($action != 'run') return;
?>
<h2 style="padding-left:8px;">Starting Optimization...</h2>
<?php
	// GET THE SIZE OF THE DATABASE BEFORE OPTIMIZATION
	$start_size = rvg_get_db_size();

	// TIMESTAMP FOR LOG FILE
	$timezone_format  = _x('m/d/YH:i:s', 'timezone date format');
	$current_datetime = date_i18n($timezone_format);	
	$log_arr = array("time" => substr($current_datetime, 0, 10).'<br />'.substr($current_datetime,10));

	// FIND REVISIONS
	$results = rvg_get_revisions($max_revisions);

	$total_deleted = 0;	
	if(count($results)>0)
	{	// WE HAVE REVISIONS TO DELETE!
?>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td colspan="4" style="font-weight:bold;color:#00F;">DELETING REVISIONS:</td>
  </tr>
  <tr>
    <th align="right" style="border-bottom:solid 1px #999;">#</th>
    <th align="left" style="border-bottom:solid 1px #999;">post / page</th>
    <th align="left" style="border-bottom:solid 1px #999;">revision date</th>
    <th align="right" style="border-bottom:solid 1px #999;">revisions deleted</th>
  </tr>
  <?php
		// LOOP THROUGH THE REVISIONS AND DELETE THEM
  		$total_deleted = rvg_delete_revisions($results, true, $max_revisions);
	?>
  <tr>
    <td colspan="3" align="right" style="border-top:solid 1px #999;font-weight:bold;">total number of revisions deleted</td>
    <td align="right" style="border-top:solid 1px #999;font-weight:bold;"><?php echo $total_deleted?></td>
  </tr>
</table>
<?php		
	}
	else
	{
?>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td style="font-weight:bold;color:#21759b;">No REVISIONS found to delete...</td>
  </tr>
</table>
<?php		
	} // if(count($results)>0)
	
	// NUMBER OF DELETED REVISIONS FOR LOG FILE
	$log_arr["revisions"] = $total_deleted;

	/****************************************************************************************
	
		DELETE TRASHED ITEMS
	
	******************************************************************************************/
	if($clear_trash == 'Y')
	{
		// GET TRASHED POSTS / PAGES AND COMMENTS
		$results = rvg_get_trash();

		$total_deleted = 0;		
		if(count($results)>0)
		{	// WE HAVE TRASH TO DELETE!
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td colspan="4" style="font-weight:bold;color:#00F;">DELETING TRASHED ITEMS:</td>
  </tr>
  <tr>
    <th align="right" style="border-bottom:solid 1px #999;">#</th>
    <th align="left" style="border-bottom:solid 1px #999;">type</th>
    <th align="left" style="border-bottom:solid 1px #999;">IP address / title</th>
    <th align="left" nowrap="nowrap" style="border-bottom:solid 1px #999;">date</th>
  </tr>
  <?php
  			// LOOP THROUGH THE TRASHED ITEMS AND DELETE THEM
  			$total_deleted = rvg_delete_trash($results, true);
?>
</table>
<?php			
		}
		else
		{
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td style="font-weight:bold;color:#21759b;">No TRASHED ITEMS found to delete...</td>
  </tr>
</table>
<?php		
		} // if(count($results)>0)
		
		// NUMBER OF DELETED TRASH FOR LOG FILE
		$log_arr["trash"] = $total_deleted;

	} // if($clear_trash == 'Y')

	/****************************************************************************************
	
		DELETE SPAMMED ITEMS
	
	******************************************************************************************/
	if($clear_spam == 'Y')
	{
		// GET SPAMMED COMMENTS
		$results = rvg_get_spam();

		$total_deleted = 0;		
		if(count($results)>0)
		{	// WE HAVE SPAM TO DELETE!
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td colspan="4" style="font-weight:bold;color:#00F;">DELETING SPAMMED ITEMS:</td>
  </tr>
  <tr>
    <th align="right" style="border-bottom:solid 1px #999;">#</th>
    <th align="left" style="border-bottom:solid 1px #999;">comment author</th>
    <th align="left" style="border-bottom:solid 1px #999;">comment author email</th>
    <th align="left" nowrap="nowrap" style="border-bottom:solid 1px #999;">comment date</th>
  </tr>
  <?php
			// LOOP THROUGH SPAMMED ITEMS AND DELETE THEM
  			$total_deleted = rvg_delete_spam($results, true);	
?>
</table>
<?php			
		}
		else
		{
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td style="font-weight:bold;color:#21759b;">No SPAMMED ITEMS found to delete...</td>
  </tr>
</table>
<?php		
		} // if(count($results)>0)
		
	} // if($clear_spam == 'Y')
	
	// NUMBER OF SPAM DELETED FOR LOG FILE
	$log_arr["spam"] = $total_deleted;

	/****************************************************************************************
	
		DELETE UNUSED TAGS
	
	******************************************************************************************/
	if($clear_tags == 'Y')
	{
		// DELETE UNUSED TAGS
		$total_deleted = rvg_delete_tags();
	
		if($total_deleted>0)
		{	// TAGS DELETED
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td><span style="font-weight:bold;color:#00F;">NUMBER OF UNUSED TAGS DELETED:</span> <span style="font-weight:bold;"><?php echo $total_deleted;?></span></td>
  </tr>
</table>
<?php			
		}
		else
		{
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td style="font-weight:bold;color:#21759b;">No UNUSED TAGS found to delete...</td>
  </tr>
</table>
<?php		
		} // if(count($results)>0)
		
	} // if($clear_tags == 'Y')
	
	// NUMBER OF tags DELETED FOR LOG FILE
	$log_arr["tags"] = $total_deleted;

	/****************************************************************************************
	
		DELETE EXPIRED TRANSIENTS
	
	******************************************************************************************/
	if($clear_transients == 'Y')
	{
		// DELETE UNUSED TAGS
		$total_deleted = rvg_delete_transients();
	
		if($total_deleted>0)
		{	// TRANSIENTS DELETED
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td><span style="font-weight:bold;color:#00F;">NUMBER OF EXPIRED TRANSIENTS DELETED:</span> <span style="font-weight:bold;"><?php echo $total_deleted;?></span></td>
  </tr>
</table>
<?php			
		}
		else
		{
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td style="font-weight:bold;color:#21759b;">No EXPIRED TRANSIENTS found to delete...</td>
  </tr>
</table>
<?php		
		} // if(count($results)>0)
		
	} // if($clear_transients == 'Y')
	
	// NUMBER OF transients DELETED FOR LOG FILE
	$log_arr["transients"] = $total_deleted;

	/****************************************************************************************
	
		DELETE ORPHANS
	
	******************************************************************************************/
	$total_deleted = rvg_delete_orphans(true);
	if($total_deleted)
	{
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td colspan="4"><span style="font-weight:bold;color:#00F;">NUMBER OF POSTMETA ORPHANS DELETED:</span> <span style="font-weight:bold;"><?php echo $total_deleted;?></span></td>
  </tr>
</table>
<?php		
	}
	else
	{
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td style="font-weight:bold;color:#21759b;">No POSTMETA ORPHANS found to delete...</td>
  </tr>
</table>
<?php		
	}
	// FOR LOG FILE
	$log_arr["orphans"] = $total_deleted;

	/****************************************************************************************
	
		OPTIMIZE DATABASE TABLES
	
	******************************************************************************************/
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td colspan="4" style="font-weight:bold;color:#00F;">OPTIMIZING DATABASE TABLES:</td>
  </tr>
  <tr>
    <th style="border-bottom:solid 1px #999;" align="right">#</th>
    <th style="border-bottom:solid 1px #999;" align="left">table name</th>
    <th style="border-bottom:solid 1px #999;" align="left">optimization result</th>
    <th style="border-bottom:solid 1px #999;" align="left">engine</th>
    <th style="border-bottom:solid 1px #999;" align="right">table rows</th>
    <th style="border-bottom:solid 1px #999;" align="right">table size</th>
  </tr>
  <?php
	# OPTIMIZE THE DATABASE TABLES
	$cnt = rvg_optimize_tables(true);
?>
</table>
<?php
	// NUMBER OF TABLES
	$log_arr["tables"] = $cnt;
	// DATABASE SIZE BEFORE OPTIMIZATION
	$log_arr["before"] = rvg_format_size($start_size,3);
	// DATABASE SIZE AFTER OPTIMIZATION
	$end_size = rvg_get_db_size();
	$log_arr["after"] = rvg_format_size($end_size,3);
	// TOTAL SAVING
	$log_arr["savings"] = rvg_format_size(($start_size - $end_size),3);
	// WRITE RESULTS TO LOG FILE
	rvg_write_log($log_arr);

	$total_savings = get_option('rvg_odb_total_savings');
	$total_savings += ($start_size - $end_size);
	update_option('rvg_odb_total_savings',$total_savings);
?>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td colspan="2" style="font-weight:bold;color:#00F;">SAVINGS:</td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <th style="border-bottom:solid 1px #999;">size of the database</th>
  </tr>
  <tr>
    <td align="right">BEFORE optimization</td>
    <td align="right" style="font-weight:bold;"><?php echo rvg_format_size($start_size,3); ?></td>
  </tr>
  <tr>
    <td align="right">AFTER optimization</td>
    <td align="right" style="font-weight:bold;"><?php echo rvg_format_size($end_size,3); ?></td>
  </tr>
  <tr>
    <td align="right" style="font-weight:bold;">SAVINGS THIS TIME</td>
    <td align="right" style="font-weight:bold;border-top:solid 1px #999;"><?php echo rvg_format_size(($start_size - $end_size),3); ?></td>
  </tr>
  <tr>
    <td align="right" style="font-weight:bold;">TOTAL SAVINGS SINCE THE FIRST RUN</td>
    <td align="right" style="font-weight:bold;border-top:solid 1px #999;"><?php echo rvg_format_size($total_savings,3); ?></td>
  </tr>
</table>
<span style="font-weight:bold;color:#000;padding-left:8px;">~~~~~</span><br />
<br />
<span style="font-weight:bold;color:#00F;padding-left:8px;">DONE!</span><br />
<br />
<?php
	if(file_exists(dirname(__FILE__).'/rvg-optimize-db-log.html'))
	{
?>
&nbsp;
<input class="button" type="button" name="view_log" value="View Log File" onclick="window.open('<?php echo $log_url?>','log','width=800,height=800,scrollbars=1')" style="font-weight:normal;" />
&nbsp;
<input class="button" type="button" name="delete_log" value="Delete Log File" onclick="self.location='tools.php?page=rvg-optimize-db.php&action=delete_log'" style="font-weight:normal;" />
<?php	
	}
} // rvg_optimize_db ()


/********************************************************************************************

	EXECUTE OPTIMIZATION VIA CRON JOB

*********************************************************************************************/
function rvg_optimize_db_cron()
{
	global $wpdb, $odb_version, $table_prefix;

	// GET OPTIONS AND SET DEFAULT VALUES
	$max_revisions = get_option('rvg_odb_number');
	if(!$max_revisions)
	{	$max_revisions = 0;
		update_option('rvg_odb_number', $max_revisions);
	}

	$clear_trash = get_option('rvg_clear_trash');
	if(!$clear_trash)
	{	$clear_trash = 'N';
		update_option('rvg_clear_trash', $clear_trash);
	}

	$clear_spam = get_option('rvg_clear_spam');
	if(!$clear_spam)
	{	$clear_spam = 'N';
		update_option('rvg_clear_spam', $clear_spam);
	}
	
	$clear_tags = get_option('rvg_clear_tags');
	if(!$clear_tags)
	{	$clear_tags = 'N';
		update_option('rvg_clear_tags', $clear_tags);
	}
	
	$clear_transients = get_option('rvg_clear_transients');
	if(!$clear_transients)
	{	$clear_transients = 'N';
		update_option('rvg_clear_transients', $clear_transients);
	}
	
	// GET THE SIZE OF THE DATABASE BEFORE OPTIMIZATION
	$start_size = rvg_get_db_size();
	
	// TIMESTAMP FOR LOG FILE
	$log_arr = array("time" => date("m/d/Y").'<br />'.date("H:i:s"));

	// FIND THE REVISIONS
	$results = rvg_get_revisions($max_revisions);
	
	$total_deleted = 0;
	if(count($results)>0)
		// WE HAVE REVISIONS TO DELETE!
		$total_deleted = rvg_delete_revisions($results, false, $max_revisions);

	// NUMBER OF DELETED REVISIONS FOR LOG FILE
	$log_arr["revisions"] = $total_deleted;

	$total_deleted = 0;	
	if($clear_trash == 'Y')
	{	
		// GET TRASHED POSTS / PAGES AND COMMENTS
		$results = rvg_get_trash();
		
		if(count($results)>0)
			// WE HAVE TRASH TO DELETE!
			$total_deleted = rvg_delete_trash($results, false, $max_revisions);
			
	} // if($clear_trash == 'Y')

	// NUMBER OF DELETED TRASH FOR LOG FILE
	$log_arr["trash"] = $total_deleted;

	$total_deleted = 0;
	if($clear_spam == 'Y')
	{
		// GET SPAMMED COMMENTS
		$results = rvg_get_spam();
		
		if(count($results)>0)
			// WE HAVE SPAM TO DELETE!
			$total_deleted = rvg_delete_spam($results, false);
			
	} // if($clear_spam == 'Y')

	// NUMBER OF SPAM DELETED FOR LOG FILE
	$log_arr["spam"] = $total_deleted;
	
	if($clear_tags == "Y")
	{	// DELETE UNUSED TAGS
		$total_deleted = rvg_delete_tags();
	}
	
	// NUMBER OF DELETE TAGS FOR LOG FILE
	$log_arr["tags"] = $total_deleted;

	if($clear_transients == "Y")
	{	// DELETE UNUSED TAGS
		$total_deleted = rvg_delete_transients();
	}
	
	// NUMBER OF DELETED TAGS FOR LOG FILE
	$log_arr["transients"] = $total_deleted;
		
	// DELETE ORPHANS
	$total_deleted = rvg_delete_orphans(false);
	// NUMBER OF ORPHANS DELETED (FOR LOG FILE)
	$log_arr["orphans"] = $total_deleted;

	// OPTIMIZE DATABASE TABLES	
	$cnt = rvg_optimize_tables(false);
	
	// NUMBER OF TABLES
	$log_arr["tables"] = $cnt;
	// DATABASE SIZE BEFORE OPTIMIZATION
	$log_arr["before"] = rvg_format_size($start_size,3);
	// DATABASE SIZE AFTER OPTIMIZATION
	$end_size = rvg_get_db_size();
	$log_arr["after"] = rvg_format_size($end_size,3);
	// TOTAL SAVING
	$log_arr["savings"] = rvg_format_size(($start_size - $end_size),3);
	// WRITE RESULTS TO LOG FILE
	rvg_write_log($log_arr);
	
	$total_savings = get_option('rvg_odb_total_savings');
	$total_savings += ($start_size - $end_size);
	update_option('rvg_odb_total_savings',$total_savings);
	
} // rvg_optimize_db_cron ()


/********************************************************************************************

	DELETE THE REVISIONS

*********************************************************************************************/
function rvg_delete_revisions($results, $display, $max_revisions)
{
	global $wpdb;
	
	$nr = 1;
	$total_deleted = 0;

	for($i=0; $i<count($results); $i++)
	{	$nr_to_delete = $results[$i]->cnt - $max_revisions;
		$total_deleted += $nr_to_delete;
		if($display)
		{
	?>
<tr>
  <td align="right" valign="top"><?php echo $nr?>.</td>
  <td valign="top" style="font-weight:bold;"><?php echo $results[$i]->post_title?></td>
  <td valign="top"><?php
		} // if($display)
		
		$sql_get_posts = "
		SELECT	`ID`, `post_modified`
		FROM	$wpdb->posts
		WHERE	`post_parent`=".$results[$i]->post_parent."
		AND		`post_type`='revision'
		ORDER	BY `post_modified` ASC		
		";
		$results_get_posts = $wpdb -> get_results($sql_get_posts);
		
		for($j=0; $j<$nr_to_delete; $j++)
		{
			if($display) echo $results_get_posts[$j]->post_modified.'<br />';
			
			$sql_delete = "
			DELETE FROM $wpdb->posts
			WHERE `ID` = ".$results_get_posts[$j]->ID."
			";
			$wpdb -> get_results($sql_delete);
			
		} // for($j=0; $j<$nr_to_delete; $j++)
		
		$nr++;
		if($display)
		{
?></td>
  <td align="right" valign="top" style="font-weight:bold;"><?php echo $nr_to_delete?></td>
</tr>
<?php
		} // if($display)
	} // for($i=0; $i<count($results); $i++)
	return $total_deleted;
} // rvg_delete_revisions ()


/********************************************************************************************

	DELETE TRASHED POSTS AND PAGES

*********************************************************************************************/
function rvg_delete_trash($results, $display)
{
	global $wpdb;

	$nr = 1;
	$total_deleted = count($results);
	for($i=0; $i<count($results); $i++)
	{	if($display)
		{
?>
<tr>
  <td align="right" valign="top"><?php echo $nr; ?></td>
  <td valign="top"><?php echo $results[$i]->post_type; ?></td>
  <td valign="top"><?php echo $results[$i]->title; ?></td>
  <td valign="top" nowrap="nowrap"><?php echo $results[$i]->modified; ?></td>
</tr>
<?php
		}
		if($results[$i]->post_type == 'comment')
		{	// DELETE META DATA (IF ANY...)
			$sql_delete = "
			DELETE FROM $wpdb->commentmeta WHERE `comment_id` = ".$results[$i]->id."
			";
			$wpdb -> get_results($sql_delete);  
		}
		
		$nr++;
	} // for($i=0; $i<count($results); $i++)
	
	// DELETE TRASHED POSTS / PAGES
	$sql_delete = "
	DELETE FROM $wpdb->posts WHERE `post_status` = 'trash'			
	";
	$wpdb -> get_results($sql_delete);
	
	// DELETE TRASHED COMMENTS
	$sql_delete = "
	DELETE FROM $wpdb->comments WHERE `comment_approved` = 'trash'
	";
	$wpdb -> get_results($sql_delete);				

	return $total_deleted;
	
} // rvg_delete_trash ()


/********************************************************************************************

	DELETE SPAMMED ITEMS

*********************************************************************************************/
function rvg_delete_spam($results, $display)
{
	global $wpdb;

	$nr = 1;
	$total_deleted = count($results);
	for($i=0; $i<count($results); $i++)
	{	if($display)
		{
?>
<tr>
  <td align="right" valign="top"><?php echo $nr; ?></td>
  <td valign="top"><?php echo $results[$i]->comment_author; ?></td>
  <td valign="top"><?php echo $results[$i]->comment_author_email; ?></td>
  <td valign="top" nowrap="nowrap"><?php echo $results[$i]->comment_date; ?></td>
</tr>
<?php
		} // if($display)
		$sql_delete = "
		DELETE FROM $wpdb->commentmeta WHERE `comment_id` = ".$results[$i]->comment_ID."
		";
		$wpdb -> get_results($sql_delete);
		$nr++;				
	} // for($i=0; $i<count($results); $i++)
	
	$sql_delete = "
	DELETE FROM $wpdb->comments WHERE `comment_approved` = 'spam'
	";
	$wpdb -> get_results($sql_delete);
	
	return $total_deleted;
	
} // rvg_delete_spam ()


/********************************************************************************************

	DELETE UNUSED TAGS

*********************************************************************************************/
function rvg_delete_tags()
{
	$total_deleted = 0;

	$tags = get_terms('post_tag', array('hide_empty' => 0));
	for($i=0; $i<count($tags); $i++)
		if($tags[$i]->count < 1)
		{	$total_deleted++;
			// echo $tags[$i]->term_id.' '.$tags[$i]->name.'<br />';
			wp_delete_term($tags[$i]->term_id,'post_tag');
		}

	return $total_deleted;
} // rvg_delete_tags ()


/********************************************************************************************

	DELETE EXPIRED TRANSIENTS

*********************************************************************************************/
function rvg_delete_transients()
{
	global $wpdb;
	
	$delay = time() - 60;	// one minute delay

	$total_deleted = 0;
	
	$sql = "
	SELECT *
	FROM $wpdb->options
	WHERE (
		option_name LIKE '_transient_timeout_%'
		OR option_name LIKE '_site_transient_timeout_%'
		OR option_name LIKE 'displayed_galleries_%'
	)
	AND option_value < '$delay'
	";

	$results = $wpdb -> get_results($sql);
	$total_deleted = count($results);

	$sql = "
	DELETE FROM $wpdb->options
	WHERE (
		option_name LIKE '_transient_timeout_%'
		OR option_name LIKE '_site_transient_timeout_%'
		OR option_name LIKE 'displayed_galleries_%'
	)
	AND option_value < '$delay'
	";

	$wpdb -> get_results($sql);
	
	$sql = "
	SELECT *
	FROM $wpdb->options
	WHERE (
		option_name LIKE '_transient_timeout_%'
		OR option_name LIKE '_site_transient_timeout_%'
	)
	AND option_value < '$delay'
	";
	
	$results = $wpdb -> get_results($sql);
	$total_deleted += count($results);

	$sql = "
	DELETE FROM $wpdb->options
	WHERE (
		option_name LIKE '_transient_timeout_%'
		OR option_name LIKE '_site_transient_timeout_%'
	)
	AND option_value < '$delay'	
	";
	
	$wpdb -> get_results($sql);

	return $total_deleted;
} // rvg_delete_transients ()


/********************************************************************************************

	DELETE ORPHAN POSTMETA RECORDS

*********************************************************************************************/
function rvg_delete_orphans($display)
{
	global $wpdb;
	
	$meta_orphans = 0;
	$post_orphans = 0;

	
	// DELETE POST ORPHANS (AUTO DRAFTS)
	$sql_delete = "
	SELECT COUNT(*) cnt
	FROM $wpdb->posts
	WHERE ID NOT IN (SELECT post_id FROM $wpdb->postmeta)
	AND post_status = 'auto-draft'
	";

	$results = $wpdb -> get_results($sql_delete);
	
	$post_orphans = $results[0] -> cnt;
	
	if($post_orphans > 0)
	{	$sql_delete = "
		DELETE FROM $wpdb->posts
		WHERE ID NOT IN (SELECT post_id FROM $wpdb->postmeta)
		AND post_status = 'auto-draft'
		";
		$wpdb -> get_results($sql_delete);		
	}
	
	// DELETE POSTMETA ORPHANS
	$sql_delete = "
	SELECT COUNT(*) cnt
	FROM $wpdb->postmeta
	WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts)
	";
	
	$results = $wpdb -> get_results($sql_delete);
	
	$meta_orphans = $results[0] -> cnt;
	
	if($meta_orphans > 0)
	{	$sql_delete = "
		DELETE FROM $wpdb->postmeta
		WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts)
		";
		$wpdb -> get_results($sql_delete);		
	}

	return ($meta_orphans + $post_orphans);
	
} // rvg_delete_orphans ()


/********************************************************************************************

	OPTIMIZE DATABASE TABLES

*********************************************************************************************/
function rvg_optimize_tables($display)
{
	global $wpdb, $table_prefix;

	# v2.7.8
	$names  = $wpdb->get_results("SHOW TABLES FROM `".DB_NAME."`");
	$dbname = 'Tables_in_'.DB_NAME;
	$cnt    = 0;
	for ($i=0; $i<count($names); $i++)
	{
		$excluded = get_option('rvg_ex_'.$names[$i]->$dbname);
		
		if(!$excluded)
		{	# TABLE NOT EXCLUDED
			$cnt++;
			$query  = "OPTIMIZE TABLE ".$names[$i]->$dbname;
			$result = $wpdb -> get_results($query);
			
			// v2.7.5
			$sql = "
			SELECT engine, (
			data_length + index_length
			) AS size, table_rows
			FROM information_schema.TABLES
			WHERE table_schema = '".strtolower(DB_NAME)."'
			AND   table_name   = '".$names[$i]->$dbname."'
			";

			$table_info = $wpdb -> get_results($sql);
			
			if($display)
			{
?>
<tr>
  <td align="right" valign="top"><?php echo $cnt?>.</td>
  <td valign="top" style="font-weight:bold;"><?php echo $names[$i]->$dbname ?></td>
  <td valign="top"><?php echo $result[0]->Msg_text ?></td>
  <td valign="top"><?php echo $table_info[0]->engine ?></td>
  <td align="right" valign="top"><?php echo $table_info[0]->table_rows ?></td>
  <td align="right" valign="top"><?php echo rvg_format_size($table_info[0]->size) ?></td>
</tr>
<?php
			} // if($display)
		} // if(!$excluded)
	} // for ($i=0; $i<count($names); $i++)
	return $cnt;
	
} // rvg_optimize_tables ()


/********************************************************************************************

	WRITE LINE TO LOG FILE

*********************************************************************************************/
function rvg_write_log($log_arr)
{
	global $odb_version;
	
	$rvg_odb_logging_on = get_option('rvg_odb_logging_on');
	if(!$rvg_odb_logging_on)
	{	$rvg_odb_logging_on = 'N';
		update_option('rvg_odb_logging_on', $rvg_odb_logging_on);
	}
		
	if($rvg_odb_logging_on == "Y")
	{	$file = dirname(__FILE__).'/rvg-optimize-db-log.html';
		if(!file_exists($file))
		{
			// NEW LOG FILE
			$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Optimize Database after Deleting Revisions v'.$odb_version.' - LOG</title>
<style type="text/css">
body, td, th {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
}
th {
	border-top:solid 1px #000;
	border-bottom:solid 1px #000;
}
td {
	padding-bottom:4px;
	border-bottom:dotted 1px #CCC;
}
#header {
	margin-left:6px;
	margin-bottom:8px;
}
#header a {
	text-decoration:none;
	font-weight:bold;
}
</style>
</head>
<body>
<div id="header">
<h2><a href="http://wordpress.org/extend/plugins/rvg-optimize-database/" target="_blank">Optimize Database after Deleting Revisions v'.$odb_version.'</a></h2>
  A WordPress Plugin by <a href="http://cagewebdev.com" target="_blank"><strong>CAGE Web Design</strong></a> | <a href="http://cage.nl/rg_biography.php" target="_blank"><strong>Rolf van Gelder</strong></a>, Eindhoven, The Netherlands</strong>
</div>
<table width="100%" border="0" cellspacing="6" cellpadding="1">
  <tr>
    <th width="9%" align="left" valign="top">time</th>
    <th width="9%" align="right" valign="top">deleted<br />
      revisions</th>
    <th width="9%" align="right" valign="top">deleted<br />
      trash</th>
    <th width="9%" align="right" valign="top">deleted<br />
      spam</th>
    <th width="9%" align="right" valign="top">deleted<br />
      tags</th>
    <th width="9%" align="right" valign="top">deleted<br />
      transients</th>	  
    <th width="9%" align="right" valign="top">deleted<br />
      orphans</th>	  
    <th width="9%" align="right" valign="top">nr of optimized tables</th>
    <th width="9%" align="right" valign="top">database size BEFORE</th>
    <th width="9%" align="right" valign="top">database size AFTER</th>
    <th width="9%" align="right" valign="top">SAVINGS</th>
  </tr>
</table>
			';

			// file_put_contents($file,'<strong><a href="http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin/" target="_blank" style="font-weight:bold;text-decoration:none;">Optimize Database after Deleting Revisions v'.$odb_version.'</a><br />A WordPress Plugin by <a href="http://cagewebdev.com" target="_blank" style="text-decoration:none;">CAGE Web Design | Rolf van Gelder</a>, Eindhoven, The Netherlands</strong><hr noshade="noshade" size="1">');
			file_put_contents($file,$html,FILE_APPEND);
		}

		$html = '
<table width="100%" border="0" cellspacing="6" cellpadding="0">  
  <tr>
    <td width="9%" valign="top"><strong>'.$log_arr["time"].'</strong></td>
    <td width="9%" align="right" valign="top">'.$log_arr["revisions"].'</td>
    <td width="9%" align="right" valign="top">'.$log_arr["trash"].'</td>
    <td width="9%" align="right" valign="top">'.$log_arr["spam"].'</td>
    <td width="9%" align="right" valign="top">'.$log_arr["tags"].'</td>
    <td width="9%" align="right" valign="top">'.$log_arr["transients"].'</td>
    <td width="9%" align="right" valign="top">'.$log_arr["orphans"].'</td>	
    <td width="9%" align="right" valign="top">'.$log_arr["tables"].'</td>
    <td width="9%" align="right" valign="top">'.$log_arr["before"].'</td>
    <td width="9%" align="right" valign="top">'.$log_arr["after"].'</td>
    <td width="9%" align="right" valign="top">'.$log_arr["savings"].'</td>
  </tr>
</table>		
		';
					
		// print_r($log_arr);
		file_put_contents($file,$html,FILE_APPEND);
	}
	
} // rvg_write_log ()


/********************************************************************************************

	GET REVISIONS

*********************************************************************************************/
function rvg_get_revisions($max_revisions)
{
		global $wpdb;

		$sql = "
		SELECT	`post_parent`, `post_title`, COUNT(*) cnt
		FROM	$wpdb->posts
		WHERE	`post_type` = 'revision'
		GROUP	BY `post_parent`
		HAVING	COUNT(*) > ".$max_revisions."
		ORDER	BY UCASE(`post_title`)	
		";
	
		return $wpdb -> get_results($sql);
		
} // rvg_get_revisions ()


/********************************************************************************************

	GET TRASHED POSTS / PAGES AND COMMENTS

*********************************************************************************************/
function rvg_get_trash()
{
		global $wpdb;

		$sql = "
		SELECT	`ID` AS id, 'post' AS post_type, `post_title` AS title, `post_modified` AS modified
		FROM	$wpdb->posts
		WHERE	`post_status` = 'trash'
		UNION ALL
		SELECT	`comment_ID` AS id, 'comment' AS post_type, `comment_author_IP` AS title, `comment_date` AS modified
		FROM	$wpdb->comments
		WHERE	`comment_approved` = 'trash'
		ORDER	BY post_type, UCASE(title)		
		";
		
		return $wpdb -> get_results($sql);
		
} // rvg_get_trash ()


/********************************************************************************************

	GET SPAMMED COMMENTS

*********************************************************************************************/
function rvg_get_spam()
{
		global $wpdb;

		$sql = "
		SELECT	`comment_ID`, `comment_author`, `comment_author_email`, `comment_date`
		FROM	$wpdb->comments
		WHERE	`comment_approved` = 'spam'
		ORDER	BY UCASE(`comment_author`)
		";
		
		return $wpdb -> get_results($sql);
		
} // rvg_get_trash ()


/********************************************************************************************

	CALCULATE THE SIZE OF THE WORDPRESS DATABASE (IN BYTES)

*********************************************************************************************/
function rvg_get_db_size()
{
	global $wpdb;
	
	// v2.7.5
	$sql = "
	SELECT SUM( data_length + index_length ) size
	FROM information_schema.TABLES
	WHERE table_schema = '".strtolower(DB_NAME)."'
	GROUP BY table_schema
	";
	
	$res = $wpdb -> get_results($sql);
	
	return $res[0]->size;
	
} // rvg_get_db_size ()


/********************************************************************************************

	FORMAT SIZES FROM BYTES TO KB OR MB

*********************************************************************************************/
function rvg_format_size($size, $precision=1)
{
	if($size>1024*1024)
		$table_size = (round($size/(1024*1024),$precision)).' MB';
	else
		$table_size = (round($size/1024,$precision)).' KB';
		
	return $table_size;
} // rvg_format_size ()
?>