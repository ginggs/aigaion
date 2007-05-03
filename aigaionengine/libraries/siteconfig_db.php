<?php
/** This class regulates the database access for a siteconfig.
 
*/

class Siteconfig_db {
    
    var $CI = null;
  
    function Siteconfig_db()
    {
        $this->CI = &get_instance();
    }
     
    /** returns the config object for the current site */
    function getSiteConfig() {
        $result = new Siteconfig();
        $result->configSettings = array();
        $Q = mysql_query("SELECT * FROM config");
        if ($Q) {
            while ($R = mysql_fetch_array($Q)) {
                //where needed, interpret setting as other than string
                if ($R["setting"] == "ALLOWED_ATTACHMENT_EXTENSIONS") {
                    $value = split(",",$R["value"]);
                } else {
                    $value = $R["value"];
                }
                $result->configSettings[$R["setting"]]=$value;
            }
        }
        return $result;
    }
    
    /** returns the config object posted from the siteconfig edit form */
    function getFromPost() {
        //correct form?
        if ($this->CI->input->post('formname')!='siteconfig') {
            return null;
        }
        $result = new Siteconfig();
        $result->configSettings['CFG_ADMIN']                        = $this->CI->input->post('CFG_ADMIN');
        $result->configSettings['CFG_ADMINMAIL']                    = $this->CI->input->post('CFG_ADMINMAIL');
        $result->configSettings['EXTERNAL_LOGIN_MODULE']            = $this->CI->input->post('EXTERNAL_LOGIN_MODULE');
        if ($this->CI->input->post('CREATE_MISSING_USERS')=='CREATE_MISSING_USERS') {
            $result->configSettings['CREATE_MISSING_USERS']           = 'TRUE';
        } else {
            $result->configSettings['CREATE_MISSING_USERS']           = 'FALSE';
        }
        if ($result->configSettings['EXTERNAL_LOGIN_MODULE']=='Aigaion') {
            $result->configSettings['USE_EXTERNAL_LOGIN']           = 'FALSE';
            $result->configSettings['CREATE_MISSING_USERS']         = 'FALSE';
        } else {
            $result->configSettings['USE_EXTERNAL_LOGIN']           = 'TRUE';
        }
        $result->configSettings['LDAP_SERVER']                     = $this->CI->input->post('LDAP_SERVER');
        $result->configSettings['LDAP_BASE_DN']                     = $this->CI->input->post('LDAP_BASE_DN');
        if ($this->CI->input->post('ENABLE_ANON_ACCESS')=='ENABLE_ANON_ACCESS') {
            $result->configSettings['ENABLE_ANON_ACCESS']           = 'TRUE';
        } else {
            $result->configSettings['ENABLE_ANON_ACCESS']           = 'FALSE';
        }
        $result->configSettings['ANONYMOUS_USER']                   = $this->CI->input->post('ANONYMOUS_USER');
        $result->configSettings['ALLOWED_ATTACHMENT_EXTENSIONS']    = split(',',$this->CI->input->post('ALLOWED_ATTACHMENT_EXTENSIONS'));
        if ($this->CI->input->post('ALLOW_ALL_EXTERNAL_ATTACHMENTS')=='ALLOW_ALL_EXTERNAL_ATTACHMENTS') {
            $result->configSettings['ALLOW_ALL_EXTERNAL_ATTACHMENTS'] = 'TRUE';
        } else {
            $result->configSettings['ALLOW_ALL_EXTERNAL_ATTACHMENTS'] = 'FALSE';
        }
        if ($this->CI->input->post('SERVER_NOT_WRITABLE')=='SERVER_NOT_WRITABLE') {
            $result->configSettings['SERVER_NOT_WRITABLE']          = 'TRUE';
        } else {
            $result->configSettings['SERVER_NOT_WRITABLE']          = 'FALSE';
        }
        $result->configSettings['WINDOW_TITLE']                     = $this->CI->input->post('WINDOW_TITLE');
        if ($this->CI->input->post('ALWAYS_INCLUDE_PAPERS_FOR_TOPIC')=='ALWAYS_INCLUDE_PAPERS_FOR_TOPIC') {
            $result->configSettings['ALWAYS_INCLUDE_PAPERS_FOR_TOPIC'] ='TRUE';
        } else {
            $result->configSettings['ALWAYS_INCLUDE_PAPERS_FOR_TOPIC'] ='FALSE';
        }
        if ($this->CI->input->post('PUBLICATION_XREF_MERGE')=='PUBLICATION_XREF_MERGE') {
            $result->configSettings['PUBLICATION_XREF_MERGE']       = 'TRUE';
        } else {
            $result->configSettings['PUBLICATION_XREF_MERGE']       = 'FALSE';
        }
        if ($this->CI->input->post('CONVERT_LATINCHARS_IN')=='CONVERT_LATINCHARS_IN') {
            $result->configSettings['CONVERT_LATINCHARS_IN']='TRUE';
        } else {
            $result->configSettings['CONVERT_LATINCHARS_IN']='FALSE';
        }
        $result->configSettings['BIBTEX_STRINGS_IN']                = $this->CI->input->post('BIBTEX_STRINGS_IN');
        
        return $result;
    }

    /** commit the config settings embodied in the given data */
    function commit($siteconfig) {
        //check rights
        $userlogin = getUserLogin();
        if (     !$userlogin->hasRights('database_manage')
            ) {
                return;
        }
        foreach ($siteconfig->configSettings as $setting=>$value) {
            if ($setting == 'ALLOWED_ATTACHMENT_EXTENSIONS') {
            	#check allowed extensions: all extensions should be prefixed with a . and should be trimmed of spaces
            	$templist = array();
            	foreach ($value as $ext) {
            		$ext = trim($ext);
            		if (($ext=="") || ($ext==".")) {
            			continue;
            		}
            		if (strpos($ext,".") === FALSE) {
            			$ext = ".".$ext;
            		}
            		$templist[] = $ext;
            	}
            	if (sizeof($templist)==0) {
            		$templist[] = ".pdf";
            	}                
            	$value = implode(',',$templist);
            }
        	#check existence of setting
        	mysql_query("INSERT IGNORE INTO config (setting) VALUES ('$setting')");
        	#update value
        	mysql_query("UPDATE config SET value='".addslashes($value)."' WHERE setting='$setting'");
        	if (mysql_error()) {
        		appendErrorMessage("Error updating config: <br>");
        		appendErrorMessage(mysql_error()."<br>");
        	}
	        #reset cached config settings
            $this->CI->latesession->set('SITECONFIG',null);
        }
    }
}
?>