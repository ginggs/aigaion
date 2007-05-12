<?php
/** This class holds the data structure of a site configuration. 

*/

class SiteConfig {
  
    var $CI                 = null; //link to the CI base object
    //don't access directly!
    var $configSettings = array();
    
    function SiteConfig()
    {
        $this->CI =&get_instance(); 
    }
    
    /** commit the config settings embodied in the given data */
    function update() {
        $this->CI->siteconfig_db->update($this);
    }
    
    function getConfigSetting($name) {
        if (!isset($this->configSettings[$name])) {
            $this->configSettings[$name] = '';
        }
        return $this->configSettings[$name];
    }
    
}
?>