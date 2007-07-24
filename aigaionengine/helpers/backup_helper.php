<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for accessing backup functions
| -------------------------------------------------------------------
|
|   Provides access to backup functionality.
|
|	Usage:
|       //load this helper:
|       $this->load->helper('backup'); 
|       //get the contents of a backup file; type = win|unix|mac
|       $backup = getDatabaseBackup($type); 
|       
*/

    function getDatabaseBackup($type) {
		if ($type == "win")
			$linebreak = "\r\n";
		else if ($type == "unix")
			$linebreak = "\n";
		else if ($type == "mac")
			$linebreak = "\r";

    	$databaseTables = getDatabaseTables();

    	$result  = "";
    	$result .= getComment("Aigaion 2.0 database export", $linebreak);
    	$result .= $linebreak;
    
    	//comment starting with "- " will be displayed in the import result message
    	$result .= getComment("- Aigaion 2.0 database: ".AIGAION_DB_NAME, $linebreak);
    	$result .= getComment("- Export date: ".date('l dS \of F Y h:i:s A'), $linebreak);
    	$result .= getComment("- Mysql host: ".AIGAION_DB_HOST, $linebreak);
    	$result .= getComment("- Mysql version: ".mysql_get_server_info(), $linebreak);
    	$result .= getComment("- Php version: ".phpversion(), $linebreak);
    	$result .= $linebreak;

    	$result .= getComment("Drop old database", $linebreak);
    	$result .= getDropDBSQL($linebreak);
    	$result .= $linebreak;

    	$result .= getComment("Create new database", $linebreak);
    	$result .= getCreateDBSQL($linebreak);
    	$result .= $linebreak;

    	$result .= getComment("Create new tables", $linebreak);
    	$result .= $linebreak;

    	foreach ($databaseTables as $table) {
    		$result .= getComment("Create table ".$table, $linebreak);
    		$result .= getCreateTableSQL($table, $linebreak);
    		$result .= $linebreak;
    
    		$result .= getComment("Insert ".$table." data", $linebreak);
    		$result .= getInsertTableDataSQL($table, $linebreak);
    		$result .= $linebreak;
    	}
    
    	return $result;
    }

    function getComment($text, $linebreak = "\n")
    {
    	//alert!
    	//when you change the comment prefix, also change it in the siteimportfunctions!!!
    	// "-- " is also supported by phpmyadmin.
    	return "-- ".$text.$linebreak;
    }

    function getDatabaseTables()
    {
    	$tableNames = array();
    	$Q = mysql_query("SHOW TABLES FROM ".AIGAION_DB_NAME);
    	if (mysql_num_rows($Q) > 0) {
    		while ($R = mysql_fetch_array($Q)) {
    			$tableNames[] = $R['Tables_in_'.AIGAION_DB_NAME];
    		}
    	}
    	return $tableNames;
    }

    function getDropDBSQL($linebreak = "\n")
    {
    	$result = "";
    	$result .= "DROP DATABASE IF EXISTS ".AIGAION_DB_NAME.";".$linebreak;
    	$result .= $linebreak;
    
    	return $result;
    }

    function getCreateDBSQL($linebreak = "\n")
    {
    	global $MYSQL_DB;
    
    	$result  = "";
    	$result .= "CREATE DATABASE ".AIGAION_DB_NAME.";".$linebreak;
    	$result .= "USE ".AIGAION_DB_NAME.";".$linebreak;
    	$result .= $linebreak;
    
    	return $result;
    }
    
    function getCreateTableSQL($table, $linebreak = "\n")
    {
    	$result  = "";
    
    	$Q = mysql_query("SHOW CREATE TABLE ".AIGAION_DB_NAME.".".$table);
    	if (mysql_num_rows($Q) > 0) {
    		$R = mysql_fetch_row($Q);
    		$result .= $R[1].";".$linebreak;
    		$result .= $linebreak;
    	}
    	return $result;
    }
    
    function getInsertTableDataSQL($table, $linebreak = "\n")
    {
    	$Q = mysql_query("SELECT * FROM ".AIGAION_DB_NAME.".".$table);
    	if (mysql_num_rows($Q) == 0)
    		return "";
    
    	$result 		= "";
    	$fields 		= array();
    	$num_fields	= mysql_num_fields($Q);
    	for ($i = 0; $i < $num_fields; $i++) {
    		$fields[] = mysql_fetch_field($Q, $i);
    	}
    
    	$values = array();
    	$insertTo = "INSERT INTO {$table} VALUES ";
    	while ($R = mysql_fetch_row($Q)) {
    		for ($i = 0; $i < $num_fields; $i++) {
    			if (!isset($R[$i]) || is_null($R[$i])) {
    				$values[]     = 'NULL';
    			} else {
    				$values[] = "'".addslashes($R[$i])."'";
    			}
    		}
    		$result .= $insertTo."(".implode(", ", $values).");".$linebreak;
    		unset($values);
    	}
    	$result .= $linebreak;
    
    	return $result;
    }

?>