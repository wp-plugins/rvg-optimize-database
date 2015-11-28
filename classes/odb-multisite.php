<?php
/************************************************************************************************
 *
 *	MULTISITE CLASS
 *
 ************************************************************************************************/
?>
<?php
class ODB_MultiSite
{
	/********************************************************************************************
	 *	CONSTRUCTOR
	 ********************************************************************************************/	
    function __construct()
    {
	} // __construct()
	

	/********************************************************************************************
	 *	GET MULTI-SITE NETWORK INFORMATION
	 ********************************************************************************************/
	function odb_ms_network_info()
	{
		global $wpdb, $odb_class;
		
		$odb_class->odb_ms_prefixes [0] = $wpdb->base_prefix;
		$odb_class->odb_ms_blogids  [0] = 1;
		if (function_exists('is_multisite') && is_multisite())
		{	$odb_class->odb_blogids = $wpdb->get_col("SELECT blog_id FROM ".$wpdb->base_prefix."blogs");
			// FOR INSTANCE: mywp_2_, mywp_3_ etc.
			for($i=1; $i<count($odb_class->odb_blogids); $i++)
			{	$odb_class->odb_ms_prefixes [$i] = $wpdb->base_prefix.$odb_class->odb_blogids[$i].'_';
				$odb_class->odb_ms_blogids  [$i] = $odb_class->odb_blogids[$i];
			}
		} // if (function_exists('is_multisite') && is_multisite())
	} // odb_ms_network_info()
	
	
	/********************************************************************************************
	 *	GET AN OPTION FROM THE ROOT SITE OPTION TABLE
	 ********************************************************************************************/
	function odb_ms_get_option($option, $default = false)
	{
		global $odb_class;
		
		if(is_multisite() &&
			function_exists('is_plugin_active_for_network') &&
				is_plugin_active_for_network($odb_class->odb_main_file))
			return get_site_option($option, $default);
		else
			return get_option($option, $default);
	} // odb_ms_get_option()
	

	/********************************************************************************************
	 *	SAVE AN OPTION TO THE ROOT SITE OPTION TABLE
	 ********************************************************************************************/
	function odb_ms_update_option($option, $value)
	{
		// v4.0.2
		global $odb_class;
		
		if(is_multisite() &&
			function_exists('is_plugin_active_for_network') &&
				is_plugin_active_for_network($odb_class->odb_main_file))
			return update_site_option( $option, $value);
		else
			return update_option( $option, $value);
	} // odb_ms_update_option()	


	/********************************************************************************************
	 *	DELETE AN OPTION TO THE ROOT SITE OPTION TABLE
	 ********************************************************************************************/
	function odb_ms_delete_option($option)
	{
		// v4.0.2
		global $odb_class;
				
		if(is_multisite() &&
			function_exists('is_plugin_active_for_network') &&
				is_plugin_active_for_network($odb_class->odb_main_file))
			return delete_site_option($option);
		else
			return delete_option($option);
	} // odb_ms_delete_option()	
	
} // ODB_MultiSite