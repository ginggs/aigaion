<?php
/** This class holds the data structure of a site configuration. 

*/

class SiteConfig {
  
    var $CI                 = null; //link to the CI base object

    var $configSettings = array();
        
    function SiteConfig()
    {
        $this->CI =&get_instance(); 
    }
    
    /** commit the config settings embodied in the given data */
    function commit() {
        $this->CI->siteconfig_db->commit($this);
    }
    
    
    
}
?>