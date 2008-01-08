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
    Note: THIS SCHEMA UPDATE IS NOT ACTIVATED YET, AS WE HAVE NO DATABASE SCHEMA UPDATES BEYOND THE 1.X=>2.0 MIGRATION
    */
    function updateSchemaV2_1() {
        if (checkVersion('V2.1')) {
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