<?php
/************************************************************************************************
 *
 *	UTILITIES
 *
 ************************************************************************************************/
?>
<?php
class ODB_Utilities
{
	/********************************************************************************************
	 *	CONSTRUCTOR
	 ********************************************************************************************/	
    function __construct()
    {
	} // __construct()


	/********************************************************************************************
	 *	FORMAT SIZES FROM BYTES TO KB OR MB
	 ********************************************************************************************/
	function odb_format_size($size, $precision=1)
	{
		if($size>1024*1024)
			$table_size = (round($size/(1024*1024),$precision)).' MB';
		else
			$table_size = (round($size/1024,$precision)).' KB';
			
		return $table_size;
	} // odb_format_size()
	

	/********************************************************************************************
	 *	CALCULATE THE SIZE OF THE WORDPRESS DATABASE (IN BYTES)
	 ********************************************************************************************/
	function odb_get_db_size()
	{
		global $wpdb;
	
		$sql = "
		  SELECT SUM(data_length + index_length) AS size
			FROM information_schema.TABLES
		   WHERE table_schema = '".DB_NAME."'
		GROUP BY table_schema
		";	
		
		$res = $wpdb->get_results($sql);
		
		return $res[0]->size;
	} // odb_get_db_size()

	
	/********************************************************************************************
	 *	GET DATABASE TABLES
	 ********************************************************************************************/
	function odb_get_tables()
	{
		global $wpdb;

		$sql = "
         SHOW FULL TABLES
		 FROM `".DB_NAME."`
		WHERE table_type = 'BASE TABLE'		
		";		
		
		// GET THE DATABASE BASE TABLES
		// $odb_class->odb_tables = $wpdb->get_results($sql, ARRAY_N);
		// var_dump($wpdb->get_results($sql, ARRAY_N));
		return $wpdb->get_results($sql, ARRAY_N);
	} // odb_get_tables()
} // ODB_Utilities