<?php
$version = '1.1.3';
$release_date = '09/01/2012';
/**
 * @package Optimize Database after Deleting Revisions
 * @version 1.1.3
 */
/*
Plugin Name: Optimize Database after Deleting Revisions
Plugin URI: http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin/
Description: Optimizes the Wordpress Database after Deleting Revisions - <a href="plugins.php?page=rvg_odb_admin"><strong>plug in options</strong></a>
Author: Rolf van Gelder, Eindhoven, The Netherlands
Version: 1.1.3
Author URI: http://cagewebdev.com
*/
?>
<?php
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
{	if (function_exists('add_options_page'))
	{	add_options_page(__('Optimize DB Options'), __('Optimize DB Options'), 'manage_options', 'rvg_odb_admin', 'rvg_odb_options_page');
    }
}
add_action( 'admin_menu', 'rvg_odb_admin_menu' );


/********************************************************************************************

	CREATE THE OPTIONS PAGE

*********************************************************************************************/
function rvg_odb_options_page() {
	global $version, $release_date;
	
	// If we are a postback, store the options
 	if ( isset( $_POST['info_update'] ) ) {
		check_admin_referer();
		
		// Update the Project ID
		$rvg_odb_number = trim($_POST['rvg_odb_number']);
		update_option('rvg_odb_number', $rvg_odb_number);

		// Give an updated message
		echo "<div class='updated'><p><strong>Optimize Database after Deleting Revisions options updated</strong> - Click <a href='options-general.php?page=rvg-optimize-db.php' style='font-weight:bold'>HERE</a> to run the optimization</p></div>";
	}
	$rvg_odb_number = get_option('rvg_odb_number');
	if(!$rvg_odb_number) $rvg_odb_number = '0';

	// Output the options page
	?>
<form method="post" action="">
  <div class="wrap">
    <h2>Using Optimize Database after Deleting Revisions</h2>
    <blockquote>
      <p><strong>'<em>Optimize Database after Deleting Revisions</em>' is an one-click plugin to optimize your WordPress database.<br />
        It deletes redundant revisions of posts and pages and, after that, optimizes all database tables.</strong></p>
      <p>Below you can define the <u>maximum number</u> of - most recent - revisions you want to <u>keep</u> per post or page.</p>
      <p>If you set the maximum number to '<strong>0</strong>' it means <strong>ALL REVISIONS</strong> will be deleted for all posts and pages.</p>
      <p>To start the optimization:<br />
        In the WordPress Dashboard go to &lsquo;<strong>Settings</strong>&lsquo;.<br />
        Click on &lsquo;<strong>Optimize Database</strong>&lsquo;. Et voila! </p>
      <p>Plugin version:<br />
        <strong>v<?php echo $version ?> (<?php echo $release_date?>)</strong> </p>
      <p>Author:<br />
        <strong><a href="http://cage.nl/" target="_blank">Rolf van Gelder</a>, <a href="http://cagewebdev.com/" target="_blank">CAGE Web Design</a></strong>, Eindhoven, The Netherlands<br />
        <br />
        Plugin URL:<br />
        <a href="http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin/" target="_blank"><strong>http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin/</strong></a><strong><br />
        </strong><br />
        Download URL:<br />
        <strong><a href="http://wordpress.org/extend/plugins/rvg-optimize-database/" target="_blank">http://wordpress.org/extend/plugins/rvg-optimize-database/</a></strong></p>
      <p>&nbsp;</p>
    </blockquote>
    <h2>Optimize Database after Deleting Revisions - Options</h2>
    <blockquote>
      <fieldset class='options'>
        <table class="editform" cellspacing="2" cellpadding="5">
          <tr>
            <td><label for="<?php echo rvg_odb_number; ?>" style="font-weight:bold;">Maximum number of - most recent - revisions to keep per post / page<br />
              </label></td>
            <td><input type="text" size="5" name="rvg_odb_number" id="rvg_odb_number" value="<?php echo $rvg_odb_number?>" style="font-weight:bold;color:#00F;" /></td>
          </tr>
        </table>
      </fieldset>
    </blockquote>
    <p class="submit">
      <input type='submit' name='info_update' value='Update Options' />
    </p>
  </div>
</form>
<?php
}


/********************************************************************************************

	MAIN FUNCTION FOR DELETING REVISIONS AND OPTIMIZING DATABASE TABLES

*********************************************************************************************/
function rvg_optimize_db()
{
	global $wpdb, $version;

	/****************************************************************************************
	
		DELETE REVISIONS
	
	******************************************************************************************/
	$max_revisions = get_option('rvg_odb_number');	
?>

<h2 style="padding-left:8px;">Optimizing your WordPress Database</h2>
<p><span style="padding-left:8px;font-style:italic;"><a href="http://cagewebdev.com/index.php/optimize-database-after-deleting-revisions-wordpress-plugin/" target="_blank" style="font-weight:bold;">Optimize Database after Deleting Revisions v<?php echo $version?></a> - A WordPress Plugin by <a href="http://cagewebdev.com/" target="_blank" style="font-weight:bold;">Rolf van Gelder</a>, Eindhoven, The Netherlands</span></p>
<p><span style="padding-left:8px;font-style:normal;">Maximum number of - most recent - revisions to keep per post / page: <span style="font-weight:bold;color:#00F;"><?php echo $max_revisions?></span> - click <a href="plugins.php?page=rvg_odb_admin" style="font-weight:bold;">HERE</a> to change this value.</span></p>
<?php
	$sql = "
	SELECT `post_parent`, `post_title`, COUNT(*) cnt
	FROM $wpdb->posts
	WHERE `post_type` = 'revision'
	GROUP BY `post_parent`
	HAVING COUNT(*) > ".$max_revisions."
	ORDER BY UCASE(`post_title`)	
	";
	$results = $wpdb -> get_results($sql);
	
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
		$nr = 1;
		$total_deleted = 0;
		for($i=0; $i<count($results); $i++)
		{	$nr_to_delete = $results[$i]->cnt - $max_revisions;
			$total_deleted += $nr_to_delete;
?>
  <tr>
    <td align="right" valign="top"><?php echo $nr?>.</td>
    <td valign="top" style="font-weight:bold;"><?php echo $results[$i]->post_title?></td>
    <td valign="top"><?php			
			$sql_get_posts = "
			SELECT `ID`, `post_modified`
			FROM $wpdb->posts
			WHERE `post_parent`=".$results[$i]->post_parent."
			AND `post_type`='revision'
			ORDER BY `post_modified` ASC		
			";
			$results_get_posts = $wpdb -> get_results($sql_get_posts);
			for($j=0; $j<$nr_to_delete; $j++)
			{
				echo $results_get_posts[$j]->post_modified.'<br />';
				$sql_delete = "
				DELETE FROM $wpdb->posts
				WHERE `ID` = ".$results_get_posts[$j]->ID."
				";
				$results_delete = $wpdb -> get_results($sql_delete);
			}
			$nr++;
?></td>
    <td align="right" valign="top" style="font-weight:bold;"><?php echo $nr_to_delete?></td>
  </tr>
  <?php			
		}
?>
  <tr>
    <td colspan="3" align="right" style="border-top:solid 1px #999;font-weight:bold;">total number of revisions deleted</td>
    <td align="right" style="border-top:solid 1px #999;font-weight:bold;"><?php echo $total_deleted?></td>
  </tr>
</table>
<br />
<?php		
	}
	else
	{
		echo '<br /><span style="font-weight:bold;color:#00F;padding-left:8px;">NO REVISIONS FOUND TO DELETE...</span><br /><br />';
	}
?>
<?php
	/****************************************************************************************
	
		OPTIMIZE TABLES
	
	******************************************************************************************/
	# GET TABLE NAMES
	$Tables = $wpdb -> get_results('SHOW TABLES IN '.DB_NAME);
	$Tables_in_DB_NAME = 'Tables_in_'.DB_NAME;
?>
<table border="0" cellspacing="8" cellpadding="2">
  <tr>
    <td colspan="3" style="font-weight:bold;color:#00F;">OPTIMIZING DATABASE TABLES:</td>
  </tr>
  <tr>
    <th style="border-bottom:solid 1px #999;" align="right">#</th>
    <th style="border-bottom:solid 1px #999;" align="left">table name</th>
    <th style="border-bottom:solid 1px #999;" align="left">optimization result</th>
  </tr>
  <?php	
	for ($i=0; $i<count($Tables); $i++)
	{
		$query  = "OPTIMIZE TABLE ".$Tables[$i]->$Tables_in_DB_NAME;
		$result = $wpdb -> get_results($query);
?>
  <tr>
    <td align="right"><?php echo ($i+1)?>.</td>
    <td style="font-weight:bold;"><?php echo $Tables[$i]->$Tables_in_DB_NAME ?></td>
    <td><?php echo $result[0]->Msg_text ?></td>
  </tr>
  <?php
	}
?>
</table>
<br />
<span style="font-weight:bold;color:#00F;padding-left:5px;">DONE!</span>
<?php	
}
?>
