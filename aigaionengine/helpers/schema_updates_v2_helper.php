<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for updating the database schema for Aigaion 2.x database versions.
| -------------------------------------------------------------------
|
|   Contains a cascaded set of database update methods. Every new update script should check older versions
|   before continuing.
|
|	Usage:
|       $this->load->helper('schema_updates_v2'); //load this helper
|       $success = updateSchemaV2_x(); 
|           call the 'latest' schema update number 2.x (with x depending on what the latest update is....)
|
|   Implementation:
|       See also the schema_helper (!)
|       Note to developers: DOCUMENT YOUR DATABASE UPDATE CODE
|       
|       
*/


    /** 
    
    */
    function updateSchemaV2_7() {
        $CI = &get_instance();
        if (checkVersion('V2.7', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_6()) { //FIRST CHECK OLDER VERSION
            return False;
        }
       
        //add 'pages' column.
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  CHANGE `firstname` `firstname`  varchar(255);");
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  CHANGE `surname` `surname`  varchar(255);");
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  CHANGE `email` `email`  varchar(255);");
                                  
       
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.7');
    }
 
    /** 
    
    */
    function updateSchemaV2_6() {
        $CI = &get_instance();
        if (checkVersion('V2.6', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_5()) { //FIRST CHECK OLDER VERSION
            return False;
        }
       
        //add 'pages' column.
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."publication` 
                                  ADD `pages` VARCHAR(255)
                                  NOT NULL 
                                  default '';");
                                  
        $Q = $CI->db->get('publication');
        
        foreach ($Q->result() as $R) {
            $pages = "";
            if (($R->firstpage != "0") || ($R->lastpage != "0")) {
            	if ($R->firstpage != "0") {
            		$pages = $R->firstpage;
            	}
            	if (($R->firstpage != $R->lastpage)&& (trim($R->lastpage) != "0")&& (trim($R->lastpage) != "")) {
            		if ($pages != "") {
            			$pages .= "--";
            		}
            		$pages .= $R->lastpage;
            	}
            }
            $R->pages = $pages;
            $CI->db->update('publication',$R,array('pub_id'=>$R->pub_id));
            
        }
        
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.6');
    }

 
    /** 
    
    */
    function updateSchemaV2_5() {
        $CI = &get_instance();
        if (checkVersion('V2.5', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_4()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        //authordisplaystyle gets extra option 'default' 
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  CHANGE `authordisplaystyle` `authordisplaystyle`  varchar(255) NOT NULL default 'vlf'");
                                  //note: can it be that MODIFY COLUMN needs to be CHANGE for some MYSQL versions? :(

        //account types is extended with 'external'
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  CHANGE `type` `type` enum('group','anon','normal','external') NOT NULL default 'normal'");
                                  //note: can it be that MODIFY COLUMN needs to be CHANGE for some MYSQL versions? :(

        //password_invalidated (TRUE|FALSE) column for user table
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  ADD `password_invalidated` enum('TRUE','FALSE') 
                                  NOT NULL 
                                  default 'FALSE' 
                                  AFTER `theme`");
       
        
        //all anonymous accounts are 'password_invalidated'
        $CI->db->update('users', array('password_invalidated'=>'TRUE'),array('type'=>'anon'));
        
        //change names of some config settings. Set setting name to X where setting name was Y
        //$CI->db->update('config', array('setting'=>'X'),array('setting'=>'Y'));
        $CI->db->update('config', array('setting'=>'LOGIN_ENABLE_ANON'),array('setting'=>'ENABLE_ANON_ACCESS'));
        $CI->db->update('config', array('setting'=>'LOGIN_DEFAULT_ANON'),array('setting'=>'ANONYMOUS_USER'));
        $CI->db->update('config', array('setting'=>'LOGIN_CREATE_MISSING_USER'),array('setting'=>'CREATE_MISSING_USERS'));

        //new config settings introduced 
        $CI->db->insert('config',array('setting'=>'LOGIN_ENABLE_DELEGATED_LOGIN','value'=>'FALSE'));
        $CI->db->insert('config',array('setting'=>'LOGIN_DELEGATES','value'=>''));
        $CI->db->insert('config',array('setting'=>'LOGIN_DISABLE_INTERNAL_LOGIN','value'=>'FALSE'));
        $CI->db->insert('config',array('setting'=>'LOGIN_MANAGE_GROUPS_THROUGH_EXTERNAL_MODULE','value'=>'FALSE'));

        
        
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.5');
    }


    /** 
    Note: add default preferences
    */
    function updateSchemaV2_4() {
        $CI = &get_instance();
        if (checkVersion('V2.4', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_3()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_THEME','value'=>'default'));
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_LANGUAGE','value'=>'english'));
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_SUMMARYSTYLE','value'=>'author'));
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_AUTHORDISPLAYSTYLE','value'=>'fvl'));
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_LISTSTYLE','value'=>'50'));
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.4');
    }

    /** 
    Note: add language preference
    */
    function updateSchemaV2_3() {
        if (checkVersion('V2.3', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_2()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        //ATTEMPT TO RUN DATABASE UPDATING CODE FOR THIS VERSION... if fail, rollback?
        mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` ADD COLUMN `language` VARCHAR(20) NOT NULL DEFAULT 'english';");
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.3');
    }
    
    /** 
    Note: set release to 2.0.2.beta
    */
    function updateSchemaV2_2() {
        if (checkVersion('V2.2', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_1()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        //ATTEMPT TO RUN DATABASE UPDATING CODE FOR THIS VERSION... if fail, rollback?
        if (!setReleaseVersion('2.0.2.beta','bugfix','Many bug fixes.')) 
            return False;
        
        return setVersion('V2.2');
    }
    
    /** 
    Initial schema update, bugfixes and install scripts
    */
    function updateSchemaV2_1() {
        if (checkVersion('V2.1', true)) {
            return True;
        }
        if (!updateSchemaV2_0()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        //ATTEMPT TO RUN DATABASE UPDATING CODE FOR THIS VERSION... if fail, rollback?
        if (!setReleaseVersion('2.0.1.beta','bugfix,features','The first bug reports on the beta release are fixed. Furthermore, automated install scripts have been added to the release.')) 
            return False;
        
        return setVersion('V2.1');
    }
    
    /** 
    Note: this is the first schema check for Aigaion 2.0
    
    In contrast to higher schema check versions, this schema check does NOT execute any
    database modifying code. It only checks whether the database is currently version 2.0...
    And if not, it gives a warning message that you should run the Aigaion 1.x => 2.0 
    migration scripts.
    
    This is because we decided to keep all code transforming an 
    Aigaion 1.x database into an Aigaion 2.0 database out of these files: that transformation
    is done in an update/install/migration script for Aigaion 2.0 that is not part of the main 
    Aigaion 2 engine.
    */
    function updateSchemaV2_0() {
        $CI = &get_instance();
        $Q = $CI->db->get('aigaiongeneral');
        if ($Q->num_rows()>0) {
            $R = $Q->row();
            $version = $R->version;
            if ($version != 'V2.0') {
                appendErrorMessage("The database has not been migrated from Aigaion 1.x towards Aigaion 2.0. <br/>
                                    Automatic update is not possible, even from an account with sufficient rights.<br/><br/> 
                                    PLEASE ASK YOUR ADMINISTRATOR TO RUN THE MIGRATION SCRIPTS.<br/>");
                return False;
            } else {
                return True;
            }
        }
        return False;
    }

?>