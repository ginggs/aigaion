<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/** This class regulates the database access for a siteconfig.
 
*/

class Siteconfig_db {
    
  
    function Siteconfig_db()
    {
    }
     
    /** returns the config object for the current site */
    function getSiteConfig() {
        $CI = &get_instance();
        $result = new Siteconfig();
        $result->configSettings = array();
        $Q = $CI->db->get('config');
        foreach ($Q->result() as $R) {
            //where needed, interpret setting as other than string
            if ($R->setting == "ALLOWED_ATTACHMENT_EXTENSIONS") {
                $value = split(",",$R->value);
            } else {
                $value = $R->value;
            }
            $result->configSettings[$R->setting]=$value;
        }
        return $result;
    }
    
    /** returns the config object posted from the siteconfig edit form */
    function getFromPost() {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }
        $result = new Siteconfig();
        $result->configSettings['CFG_ADMIN']                        = $CI->input->post('CFG_ADMIN');
        $result->configSettings['CFG_ADMINMAIL']                    = $CI->input->post('CFG_ADMINMAIL');
        $result->configSettings['EXTERNAL_LOGIN_MODULE']            = $CI->input->post('EXTERNAL_LOGIN_MODULE');
        if ($CI->input->post('CREATE_MISSING_USERS')=='CREATE_MISSING_USERS') {
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
        $result->configSettings['LDAP_SERVER']                     = $CI->input->post('LDAP_SERVER');
        $result->configSettings['LDAP_BASE_DN']                     = $CI->input->post('LDAP_BASE_DN');
        $result->configSettings['LDAP_DOMAIN']                     = $CI->input->post('LDAP_DOMAIN');
        if ($CI->input->post('ENABLE_ANON_ACCESS')=='ENABLE_ANON_ACCESS') {
            $result->configSettings['ENABLE_ANON_ACCESS']           = 'TRUE';
        } else {
            $result->configSettings['ENABLE_ANON_ACCESS']           = 'FALSE';
        }
        $result->configSettings['ANONYMOUS_USER']                   = $CI->input->post('ANONYMOUS_USER');
        $result->configSettings['ALLOWED_ATTACHMENT_EXTENSIONS']    = split(',',$CI->input->post('ALLOWED_ATTACHMENT_EXTENSIONS'));
        if ($CI->input->post('ALLOW_ALL_EXTERNAL_ATTACHMENTS')=='ALLOW_ALL_EXTERNAL_ATTACHMENTS') {
            $result->configSettings['ALLOW_ALL_EXTERNAL_ATTACHMENTS'] = 'TRUE';
        } else {
            $result->configSettings['ALLOW_ALL_EXTERNAL_ATTACHMENTS'] = 'FALSE';
        }
        if ($CI->input->post('SERVER_NOT_WRITABLE')=='SERVER_NOT_WRITABLE') {
            $result->configSettings['SERVER_NOT_WRITABLE']          = 'TRUE';
        } else {
            $result->configSettings['SERVER_NOT_WRITABLE']          = 'FALSE';
        }
        $result->configSettings['WINDOW_TITLE']                     = $CI->input->post('WINDOW_TITLE');
        if ($CI->input->post('USE_UPLOADED_LOGO')=='USE_UPLOADED_LOGO') {
            $result->configSettings['USE_UPLOADED_LOGO']           = 'TRUE';
        } else {
            $result->configSettings['USE_UPLOADED_LOGO']           = 'FALSE';
        }
        if ($CI->input->post('ALWAYS_INCLUDE_PAPERS_FOR_TOPIC')=='ALWAYS_INCLUDE_PAPERS_FOR_TOPIC') {
            $result->configSettings['ALWAYS_INCLUDE_PAPERS_FOR_TOPIC'] ='TRUE';
        } else {
            $result->configSettings['ALWAYS_INCLUDE_PAPERS_FOR_TOPIC'] ='FALSE';
        }
        if ($CI->input->post('PUBLICATION_XREF_MERGE')=='PUBLICATION_XREF_MERGE') {
            $result->configSettings['PUBLICATION_XREF_MERGE']       = 'TRUE';
        } else {
            $result->configSettings['PUBLICATION_XREF_MERGE']       = 'FALSE';
        }
        if ($CI->input->post('CONVERT_LATINCHARS_IN')=='CONVERT_LATINCHARS_IN') {
            $result->configSettings['CONVERT_LATINCHARS_IN']='TRUE';
        } else {
            $result->configSettings['CONVERT_LATINCHARS_IN']='FALSE';
        }
        $result->configSettings['BIBTEX_STRINGS_IN']                = $CI->input->post('BIBTEX_STRINGS_IN');
        $result->configSettings['ATT_DEFAULT_READ']                = $CI->input->post('ATT_DEFAULT_READ');
        $result->configSettings['ATT_DEFAULT_EDIT']                = $CI->input->post('ATT_DEFAULT_EDIT');
        $result->configSettings['PUB_DEFAULT_READ']               = $CI->input->post('PUB_DEFAULT_READ');
        $result->configSettings['PUB_DEFAULT_EDIT']               = $CI->input->post('PUB_DEFAULT_EDIT');
        $result->configSettings['NOTE_DEFAULT_READ']               = $CI->input->post('NOTE_DEFAULT_READ');
        $result->configSettings['NOTE_DEFAULT_EDIT']               = $CI->input->post('NOTE_DEFAULT_EDIT');
        $result->configSettings['TOPIC_DEFAULT_READ']                = $CI->input->post('TOPIC_DEFAULT_READ');
        $result->configSettings['TOPIC_DEFAULT_EDIT']                = $CI->input->post('TOPIC_DEFAULT_EDIT');
        
        return $result;
    }

    /** commit the config settings embodied in the given data */
    function update($siteconfig) {
        $CI = &get_instance();
        $CI->load->library('file_upload');
        //check rights
        $userlogin = getUserLogin();
        if (     !$userlogin->hasRights('database_manage')
            ) {
                return;
        }
        foreach ($siteconfig->configSettings as $setting=>$value) {
            $CI = &get_instance();
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
            		//disallow a specific class of attachments permanently
            		if (!in_array(strtolower(substr($ext,-4)),array('.php','php3','php4','.exe','.bat'))) {
            		    $templist[] = $ext;
            		} else {
            		    appendErrorMessage("The extension '".$ext."' is never allowed for Aigaion attachments, and has been 
            		                        removed from the list of allowed attachments.");
            		}
            	}
            	if (sizeof($templist)==0) {
            		$templist[] = ".pdf";
            	}                
            	$value = implode(',',$templist);
            }
        	#check existence of setting
        	$CI->db->query("INSERT IGNORE INTO ".AIGAION_DB_PREFIX."config (setting) VALUES (".$CI->db->escape($setting).")");
        	#update value
            $CI->db->where('setting', $setting);
            $CI->db->update('config', array('value'=>$value));
        	if (mysql_error()) {
        		appendErrorMessage("Error updating config: <br/>");
        		appendErrorMessage(mysql_error()."<br/>");
        	}
        }
    	#upload (from post) new custom logo, if available
        if (  ($siteconfig->configSettings['USE_UPLOADED_LOGO']=='TRUE') 
            || (
                isset($_FILES['new_logo'])
                &&
                $_FILES['new_logo']['error']==0
                ) ) {
            $siteconfig->configSettings['USE_UPLOADED_LOGO']='TRUE';
            $max_size = 1024*10; // the max. size for uploading
            	
            $my_upload = new File_upload;
            $my_upload->upload_dir = AIGAION_ATTACHMENT_DIR.'/'; // "files" is the folder for the uploaded files (you have to create this folder)
            $my_upload->extensions = array('.jpg');
            $my_upload->max_length_filename = 100; // change this value to fit your field length in your database (standard 100)
            $my_upload->rename_file = true;
        	$my_upload->the_temp_file = $_FILES['new_logo']['tmp_name'];
        	$my_upload->the_file = $_FILES['new_logo']['name'];
        
        	$my_upload->http_error = $_FILES['new_logo']['error'];
        	if ($my_upload->http_error > 0) {
        		//appendErrorMessage("Error while uploading custom logo: ".$my_upload->error_text($my_upload->http_error));
        	} else {
    
            	$my_upload->replace = "y";
            	$my_upload->do_filename_check = "n"; // use this boolean to check for a valid filename
            
                if (!$my_upload->upload("custom_logo")) {
                    //if failed: set to false again and give message? no, cause maybe there just was no file uploaded :)
                    appendErrorMessage("Failed to upload custom logo. ".$my_upload->show_error_string().'<br/>' );
                    //$USE_UPLOADED_LOGO = "FALSE";
                } else {
                    appendMessage("New logo uploaded<br/>");
                }
            }
        } else {
            //appendMessage("No new logo<br/>".$siteconfig->configSettings['USE_UPLOADED_LOGO']);
        }
        #reset cached config settings
        $CI = &get_instance();
        $CI->latesession->set('SITECONFIG',null);
    }
}
?>