<?php
$version = '1.0.2';
/**
 * @package Optimize Database after Deleting Revisions
 * @version 1.0.2
 */
/*
Plugin Name: Optimize Database after Deleting Revisions
Plugin URI: http://cagewebdev.com
Description: Optimizes the Wordpress Database after Deleting Revisions
Author: Rolf van Gelder
Version: 1.0.2
Author URI: http://cagewebdev.com
*/
?>
<?php
function optimize_db_main()
{	if (function_exists('add_options_page')) {
	add_options_page('Optimize Database', 'Optimize Database',8 ,'rvg-optimize-db.php', 'rvg_optimize_db');
    }
}
add_action('admin_menu', 'optimize_db_main');

function rvg_optimize_db()
{
	global $wpdb;
		
	echo '<h2 style="padding-left:5px;">Optimizing your WordPress database</h2>';
	echo '<span style="padding-left:5px;font-style:italic;">rvg-optimize-db v'.$version.' - A WordPress Plugin by Rolf van Gelder</span><br /><br />';

	/***********************************
		DELETE REVISIONS
	***********************************/
	$sql = "SELECT `ID`,`post_date`,`post_title`,`post_modified`
			FROM $wpdb->posts
			WHERE `post_type` = 'revision'
			ORDER BY `ID` DESC";
	$results = $wpdb -> get_results($sql);
	if($results)
	{	$cnt = count($results);
		# print_r($results);
?>
<table border="0" cellspacing="5" cellpadding="5">
  <tr>
    <td colspan="3" style="font-weight:bold;color:#00F;">DELETING REVISIONS:</td>
  </tr>
<?php		
		for($i=0;$i<$cnt;$i++)
		{	# MULTI LINGUAL?
			$post_title = str_replace('--><!--','--> | <!--',$results[$i]->post_title);
?>
  <tr valign="top">
    <td align="right" style="font-weight:bold;"><?=($i+1)?></td>
    <td><?=$results[$i]->post_modified?></td>
    <td style="font-weight:bold;"><?=$post_title?></td>
  </tr>
<?php			
		}
?>
</table>
<br />
<?php
		# DELETE THE REVISIONS
		$sql = "DELETE FROM $wpdb->posts WHERE post_type = 'revision'";
		$results = $wpdb -> get_results($sql);		
	}
	else
	{	echo '<span style="font-weight:bold;color:#00F;padding-left:5px;">NO REVISIONS FOUND!</span><br /><br />';
	} // if($results)
	
	/***********************************
		OPTIMIZE TABLES
	***********************************/
	# GET TABLE NAMES
	$Tables = $wpdb -> get_results('SHOW TABLES IN '.DB_NAME);
	$Tables_in_DB_NAME = 'Tables_in_'.DB_NAME;
	# print_r($Tables);
?>
<table border="0" cellspacing="5" cellpadding="5">
  <tr>
    <td colspan="3" style="font-weight:bold;color:#00F;">OPTIMIZING DATABASE TABLES:</td>
  </tr>
<?php	
	for ($i=0; $i<count($Tables); $i++)
	{
		$query  = "OPTIMIZE TABLE ".$Tables[$i]->$Tables_in_DB_NAME;
		$result = $wpdb -> get_results($query);
		# print_r($result);
?>
  <tr>
    <td style="font-weight:bold;"><?=$Tables[$i]->$Tables_in_DB_NAME?></td>
    <td style="font-weight:bold;">=&gt;</td>    
    <td><?=$result[0]->Msg_text?></td>
  </tr>
<?php
	}
	$query = "$operation TABLE ";
?>
</table><br />
<span style="font-weight:bold;color:#00F;padding-left:5px;">DONE!</span>
<?php	
}
?>