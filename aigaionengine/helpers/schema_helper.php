<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for checking, and possibly updating, the database schema.
| -------------------------------------------------------------------
|
|   Provides information whether the version of the code matches the version of the database schema.
|   Provides methods to update the database schema if needed.
|   Used by the login module.
|
|	Usage:
|       $this->load->helper('schema'); //load this helper
|       $schemaIsOK = checkSchema(); //is schema up to date? If not, and current user has sufficient rights, 
|                       a database update is executed. Return true if in the end the schema is up to date.
|
|   Implementation:
|       If a schema update has been committed, you should 
|           - change the checkSchema method to check for the new schema version
|           - upon fail, make the checkSchema method call the appropriate update function 
|             if the current user has sufficient rights.
|       The login module is implemented in such a way that the user is logged out with an appropriate message 
|       if this check fails.
|
|       
*/

    /** 
    This method checks for the latest schema version. If you make a schema update, change this method to
    check for the new version number. Also change this method to call the correct new schema update function.
    */
    function checkSchema() {
        $CI = &get_instance();
        $Q = $CI->db->get('aigaiongeneral');
        if ($Q->num_rows()>0) {
            $R = $Q->row();
            $version = $R->version;
            if ($version == 'V2.0') { 
                return True;
            } else {
                $userlogin = getUserLogin(); //note: a not logged in user has no rights :)
                if ($userlogin->hasRights("database_manage")) {
                    //sufficient rights: attempt to update schema
                    //but first: push a database backup to the user? or save one in a safe place?
                    if (getConfigurationSetting('SERVER_NOT_WRITABLE')!='TRUE') {
                        //do backup, store in attachment dir
                        appendErrorMessage("Actually, we should still do a forced database backup saved in a 
                                            safe place on the server before performing the actual update code.<br/>");
                    }
                    $CI->load->helper('schema_updates_v2');
                    return updateSchemaV2_0();
                }
                return False;
            }
        }
        return False;
    }

?>