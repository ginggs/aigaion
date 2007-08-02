<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/** This class regulates the database access for Attachments. Several accessors are present that return a Attachment or 
array of Attachment's. */
class Attachment_db {
  
  
    function Attachment_db()
    {
    }
    
    /** Return the Attachment object with the given id. */
    function getByID($att_id)
    {
        $CI = &get_instance();
        $Q = $CI->db->getwhere('attachments', array('att_id' => $att_id));
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  else {
            return null;
        }
    }
   
    /** Return the Attachment object stored in the given database row, or null if insufficient rights. */
    function getFromRow($R)
    {
        $CI = &get_instance();
        $userlogin  = getUserLogin();


        $attachment = new Attachment;
        foreach ($R as $key => $value)
        {
            if ($key=='ismain'||$key=='isremote') {
                $value = $value=='TRUE';
            }
            $attachment->$key = $value;
        }

        //check rights, if fail return null
        if ( !$userlogin->hasRights('attachment_read') || !$CI->accesslevels_lib->canReadObject($attachment))return null;

        return $attachment;
    }

    /** Construct an attachment from the POST data present in the attachments/edit or add view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        $CI = &get_instance();
        $attachment = new Attachment;
        //correct form?
        if ($CI->input->post('formname')!='attachment') {
            return null;
        }
        //get basic data
        $attachment->att_id             = $CI->input->post('att_id');
        $attachment->name               = $CI->input->post('name');
        $attachment->note               = $CI->input->post('note');
        $attachment->isremote           = $CI->input->post('isremote');
        $attachment->location           = $CI->input->post('location');
        $attachment->ismain             = $CI->input->post('ismain');
        $attachment->mime               = $CI->input->post('mime');
        $attachment->pub_id             = $CI->input->post('pub_id');
        $attachment->user_id            = $CI->input->post('user_id');

        return $attachment;
    }
        
    /** Return an array of Attachment object for the given publication. */
    function getAttachmentsForPublication($pub_id) {
        $CI = &get_instance();
        $result = array();
        $CI->db->orderby('ismain');
        $Q = $CI->db->getwhere('attachments', array('pub_id' => $pub_id));
        foreach ($Q->result() as $row) {
            $next  =$this->getByID($row->att_id);
            if ($next != null) {
                $result[] = $next;
            }
        }
        return $result;
    }


    /** Add a new attachment with the given data. Returns the new att_id, or -1 on failure. 
    Quite a large method. */
    function add($attachment) {
        $CI = &get_instance();
        //check access rights (!)
        $userlogin    = getUserLogin();
        $user         = $CI->user_db->getByID($userlogin->userID());
        $publication  = $CI->publication_db->getByID($attachment->pub_id);
        if (    ($publication == null) 
             ||
                (!$userlogin->hasRights('attachment_edit'))
             || 
                (!$CI->accesslevels_lib->canEditObject($publication))
            ) 
        {
	        appendErrorMessage('Add attachment: insufficient rights.<br/>');
	        return;
        }

        if ($attachment->isremote) {
        	#determine real name (the one exposed to user)
        	#from alternative name or from original name
        	#
        	$realname=$attachment->location;
        	$ext=$CI->file_upload->get_extension($realname);
        	if (getConfigurationSetting('ALLOW_ALL_EXTERNAL_ATTACHMENTS')!='TRUE') {
        		if (!in_array($ext, getConfigurationSetting('ALLOWED_ATTACHMENT_EXTENSIONS'))) {
        			appendErrorMessage("ERROR UPLOADING: ".$ext." is not an allowed extension for remote files.<br/>"
        			."Allowed types: <b>".implode(',',getConfigurationSetting('ALLOWED_ATTACHMENT_EXTENSIONS'))."</b>"
        			."Need other file types? Ask <a href='mailto:"
        			.getConfigurationSetting("CFG_ADMINMAIL")."'>"
        			.getConfigurationSetting("CFG_ADMIN")."</a><br/>");
        		    return -1;
        		}
        	}
        
        	if ($attachment->name!="") {
        		$realname = $attachment->name;
        	}
        
        	# get mime type...
        	//// $mime = $ext; //not good... how to get proper mime info here?
        	//$mime = $_FILES['upload']['type']; // answer: like this #DR: NO!!! there is no files upload here :)

            //fix often used types..
            if ($ext == ".pdf") {
                $mime="application/pdf";
            }
            if ($ext == ".doc") {
                $mime="application/msword";
            }
            if ($ext == ".txt") {
                $mime="text/plain";
            }
        
            //the first attachment is always a main attachment
            $Q = $CI->db->query('SELECT * FROM attachments WHERE pub_id = '.$attachment->pub_id);
            if ($Q->num_rows() == 0) {
                $attachment->ismain = True;
            }
        
        	#if ismain, old main attachment should be un-main-ed
    		if ($attachment->ismain) {
    			$res = $CI->db->query("UPDATE attachments SET ismain='FALSE' where pub_id=".$attachment->pub_id);
    			if (mysql_error()) {
    				appendErrorMessage("Error un-'main'-ing other attachments: ".mysql_error());
    				return -1;
    			}
    		}
    		#store link in database
    		$ismain = 'FALSE';
    		if ($attachment->ismain) {
    		    $ismain = 'TRUE';
    		}
    		$CI->db->insert('attachments',
    		                array('pub_id'=>$attachment->pub_id,
    		                      'note'=>$attachment->note, 
    		                      'name'=>$realname, 
    		                      'location'=>$attachment->location, 
    		                      'mime'=>$attachment->mime, 
    		                      'ismain'=>$ismain, 
    		                      'isremote'=>'TRUE', 
    		                      'user_id'=>$userlogin->userId())
    		                ); 
    		if (mysql_error()) {
    			appendErrorMessage("Error adding attachment: ".mysql_error()."<br/>");
    			return -1;
    		}        	
            $new_id = mysql_insert_id();
            $attachment->att_id = $new_id;
            $CI->accesslevels_lib->initAttachmentAccessLevels($attachment);
        	return $attachment->att_id;
	    } else {
        	# upload not possible: return with error
        	if (getConfigurationSetting("SERVER_NOT_WRITABLE") == "TRUE") {
        		appendErrorMessage("You cannot upload attachment files to this server (the server is declared write-only); please use remote attachments instead.<br/>");
        		return -1;
        	}
        
        	$CI->file_upload->http_error = $_FILES['upload']['error'];
        
        	if ($CI->file_upload->http_error > 0) {
        		appendErrorMessage("Error while uploading: ".$CI->file_upload->error_text($CI->file_upload->http_error));
        		return -1;
        	}
        
        	# prepare upload of file from temp to permanent location
        	$CI->file_upload->the_file = $_FILES['upload']['name'];
        	$CI->file_upload->the_temp_file = $_FILES['upload']['tmp_name'];
        	$CI->file_upload->extensions = getConfigurationSetting("ALLOWED_ATTACHMENT_EXTENSIONS");  // specify the allowed extensions here
        	$CI->file_upload->upload_dir = AIGAION_ATTACHMENT_DIR."/";  // is the folder for the uploaded files (you have to create this folder)
        	$CI->file_upload->max_length_filename = 255; // change this value to fit your field length in your database (standard 100)
        	$CI->file_upload->rename_file = true;
        	$CI->file_upload->replace = "n"; 
        	$CI->file_upload->do_filename_check = "n"; // use this boolean to check for a valid filename
        
        	# determine real name (the one exposed to user) and storename (the one
        	# used for storage) of file, from alternative name or from original name
        	$realname=$_FILES['upload']['name'];
        	$ext = $CI->file_upload->get_extension($realname);
        	if (isset($attachment->name) && ($attachment->name != "")) {
        		if ($CI->file_upload->get_extension($attachment->name) != $ext) {
        			$attachment->name .= $ext;
        		}
        		$realname = $attachment->name;
        	}
        	$storename = (str_replace(' ', '_', $realname))."-".$this->generateUniqueSuffix();
        	# sanitize quotes and other stuff out of name
        	$storename = str_replace (array("'", '"', "\\", "/"), "", $storename);
        
        	# get mime type...
        	$mime = $_FILES['upload']['type'];
        	# and fix some problematic types - is this needed?
        	# DR: yes, I've run into problems here sometimes with my apache not finding the right mime types :/
        	if ($ext == ".doc") {
        		$mime = "application/msword";
        	}
        
        	# execute the actual upload
        	if ($CI->file_upload->upload($storename)) {  
        	    // storename is an additional filename information, use this to rename the uploaded file
        		//echo "mime:".$mime.".";
        		# upload was succesful:
        		# if ismain, old main attachment should be un-main-ed
        		if ($attachment->ismain) {
        			$res = $CI->db->query("UPDATE attachments SET ismain='FALSE' where pub_id=".$attachment->pub_id);
        			if (mysql_error()) {
        				appendErrorMessage("Error un-'main'-ing other attachments: ".mysql_error());
        				return -1;
        			}
        		}
                //the first attachment is always a main attachment
                $Q = $CI->db->query('SELECT * FROM attachments WHERE pub_id = '.$attachment->pub_id);
                if ($Q->num_rows() == 0) {
                    $attachment->ismain = True;
                }
            
        		# add appropriate info about new attachment to database
        		$ismain = 'FALSE';
        		if ($attachment->ismain) {
        		    $ismain = 'TRUE';
        		}
        		$CI->db->insert('attachments',
        		                array('pub_id'=>$attachment->pub_id,
        		                      'note'=>$attachment->note, 
        		                      'name'=>$realname, 
        		                      'location'=>$storename.$ext, 
        		                      'mime'=>$attachment->mime, 
        		                      'ismain'=>$ismain, 
        		                      'isremote'=>'FALSE', 
        		                      'user_id'=>$userlogin->userId())
    		                   ); 
        		if (mysql_error()) {
        			appendErrorMessage("Error adding attachment: ".mysql_error()."<br/>");
        			return -1;
        		}
        		
        		# check if file is really there
        		if (!is_file(AIGAION_ATTACHMENT_DIR."/".$storename.$ext))
        		{
        	        appendErrorMessage("Error uploading. The file was not written to disk.<br/>
                    Is this error entirely unexpected? You might want to check whether 
                    the php settings 'upload_max_filesize', 'post_max_size' and 
                    'max_execution_time' are all large enough for uploading
                    your attachments... Please check this with your administrator.<br/>");
        		}
        		
                $new_id = mysql_insert_id();
                $attachment->att_id = $new_id;
                $CI->accesslevels_lib->initAttachmentAccessLevels($attachment);
            	return $attachment->att_id;
        	} else {
        		appendErrorMessage("ERROR UPLOADING: ".$CI->file_upload->show_error_string()
        		  ."<br/>Is the error due to allowed file types? Ask <a href='mailto:"
        		  .getConfigurationSetting("CFG_ADMINMAIL")."'>"
        		  .getConfigurationSetting("CFG_ADMIN")."</a> for more types.<br/>");
        		return -1;
        	}
        }
        appendErrorMessage("GENERIC ERROR UPLOADING. THIS SHOULD NOT HAVE BEEN LOGICALLY POSSIBLE. PLEASE CONTACT YOUR DATABASE ADMINISTRATOR.<br/>"); 
        //but nevertheless,  murphy's law dicates that we add an error feedback message here :)
        return -1;
    }

    /** tries to commit this attachment to the database. Note: not all fields are supposed to be edited.
    Generally, only the note and the name are considered to be editable! Furthermore the new name should 
    have the proper extension. If not, this method fixes the extension. Returns TRUE or FALSE depending 
    on whether the operation was operation was successfull. */
    function update($attachment) {
        $CI = &get_instance();
        //check access rights (by looking at the original attachment in the database, as the POST
        //data might have been rigged!)
        $userlogin  = getUserLogin();
        $user       = $CI->user_db->getByID($userlogin->userID());
        $attachment_testrights = $CI->attachment_db->getByID($attachment->att_id);
        if (    ($attachment_testrights == null) 
             ||
                (!$userlogin->hasRights('attachment_edit'))
             || 
                 (!$CI->accesslevels_lib->canEditObject($attachment_testrights))
            ) 
        {
	        appendErrorMessage('Update attachment: insufficient rights.<br/>');
	        return;
        }
 
        //attachment name should be correct wrt location! 
        if (!$attachment->isremote) {
          	$ext1=$CI->file_upload->get_extension($attachment->location);
          	$ext2=$CI->file_upload->get_extension($attachment->name);
          	if ($ext1!=$ext2) {
          	    $attachment->name .= $ext1;
          	}
        }
		if ($attachment->ismain) {
			$res = $CI->db->query("UPDATE attachments SET ismain='FALSE' where pub_id=".$attachment->pub_id);
			if (mysql_error()) {
				appendErrorMessage("Error un-'main'-ing other attachments: ".mysql_error());
				return -1;
			}
		}
    
		# add appropriate info about new attachment to database
		$ismain = 'FALSE';
		if ($attachment->ismain) {
		    $ismain = 'TRUE';
		}
        
        $updatefields =  array('name'=>$attachment->name,'note'=>$attachment->note,'ismain'=>$ismain);
        
        $CI->db->query(
            $CI->db->update_string("attachments",
                                         $updatefields,
                                         "att_id=".$attachment->att_id)
                              );
        return True;
    }

    function generateUniqueSuffix()
    {
    	$suffix = md5(time());
    	while (file_exists($suffix)) {
    		$suffix= md5(time());
    	}
    	return $suffix;
    }

}
?>