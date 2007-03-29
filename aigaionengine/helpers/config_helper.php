<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for accessing the configuration settings of the system.
| -------------------------------------------------------------------
|
|   Provides access to the site configuration settings of the system.
|
|	Usage:
|       $this->load->helper('config'); //load this helper
|       $val = getSiteConfigSetting($settingName); //retrieve site configuration settings
|
|   Implementation:
|       The configuration settings are loaded from the database the first time a setting is 
|       requested; the settings are stored to be able to retrieve them faster on subsequent 
|       requests.
|
|       The reason for not initializing them upon loading the helper for the first time is
|       that this helper is autoloaded, and with autoload you're not sure in what order files
|       are autoloaded, so the database connection may not be ready yet.
|
|       Currently, the settings are simply stored in an array. However, they will be stored
|       in some Config model or something like that in the future. Furthermore it may be possible
|       that we will redesign this so the Config model is stored in the session and needs to be 
|       initialised from the database only once during a session...
|       
*/

$configSettings = array();

    /** Return the value of a certain Site Configuration Setting. */
    function getConfigurationSetting($settingName) {
        global $configSettings;
        if (sizeof($configSettings)==0) {
            initConfigurationSettings();
        }
        if (!array_key_exists($settingName,$configSettings)) {
            return "";
        }
        return $configSettings[$settingName];
    }

    /** Initializes the config settings from the database. Call this as well when 
    the settings have changed... */
    function initConfigurationSettings() {
        global $configSettings;
        $configSettings = array();
        $Q = mysql_query("SELECT * FROM config");
        if ($Q) {
            while ($R = mysql_fetch_array($Q)) {
                //where needed, interpret setting as other than string
                if ($R["setting"] == "ALLOWED_ATTACHMENT_EXTENSIONS") {
                    $value = split(",",$R["value"]);
                } else {
                    $value = $R["value"];
                }
                $configSettings[$R["setting"]]=$value;
            }
        }
    }

?>