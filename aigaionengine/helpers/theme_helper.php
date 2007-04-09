<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for accessing icons and stylesheets
| -------------------------------------------------------------------
|
|   Provides access to the themes, icons and stylesheets, dependent on the theme 
|   settings of the current user.
|
|	Usage:
|       //load this helper:
|       $this->load->helper('theme'); 
|       //get available themes by name:
|       $list = getThemes(); 
|       //check whether theme exists:
|       $exists = themeExists($themeName); 
|       //retrieve url for icon:
|       $iconUrl = 
|       
*/

    /* Return a list of available themes. Themes are subdirectories of ROOT/themes/
       other than the CVS directory. */
    function getThemes() {
    	$themepath = APPPATH."/themes/";
    	$themelist = array();
    	if ($handle = opendir($themepath)) {
    		while (false !== ($nextfile = readdir($handle))) {
    			if (   ($nextfile != "." && $nextfile != "..") 
    			    && (strtolower($nextfile)!="cvs") 
    			    && (strtolower($nextfile)!=".svn") 
    			    && (is_dir($themepath."/".$nextfile))
    			    && file_exists($themepath."/".$nextfile."/css/styling.css")
    			    ) {
    				$themelist[] = $nextfile;
    			}
    		}
    		closedir($handle);
    	}
    	return $themelist;
    }
    
    /* this function checks whether a named theme exists. Themes are subdirectories 
       of ROOT/themes/ other than the CVS directory.
       Furthermore, the directory should not be empty :-/  (CVS will keep the dirs 
       even if the theme is gone). So we also check for theme/css/style.css */
    function themeExists($themeName) {
    	#don't accidentally accept CVS directory...
    	if (strtolower($themeName) == "cvs") return false;
    	$path = APPPATH."/themes/".$themeName."/";
    	return (file_exists($path) && file_exists($path."css/styling.css") && is_dir($path));
    }

    /** If a user is logged in, return name of theme, otherwise return name of default theme. */
    function getThemeName() {
        //no login implemented yet -- as soon as the login stuff works, this method
        //will look for the current user's theme preference
        return "default";
    }
    
    /* Return true iff icon exists in current theme */
    function iconExists($iconName) {
        return file_exists(APPPATH."/themes/".getThemeName()."/icons/".$iconName);
    }
    /** Return the Url of the requested icon (full file name!), taking current theme
        into account. */
    function getIconUrl($iconName) {
        return APPURL."themes/".getThemeName()."/icons/".$iconName;
    }
    /** Return the Url of the requested css file (full file name!), taking current 
        theme into account. */
    function getCssUrl($cssName) {
        return APPURL."themes/".getThemeName()."/css/".$cssName;
    }

?>