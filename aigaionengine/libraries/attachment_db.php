<?php
/** This class regulates the database access for Attachments. Several accessors are present that return a Attachment or 
array of Attachment's. */
class Attachment_db {
  
    var $CI = null;
  
    function Attachment_db()
    {
        $this->CI = &get_instance();
    }
    
    /** Return the Attachment object with the given id. */
    function getByID($att_id)
    {
        $Q = $this->CI->db->getwhere('attachments', array('att_id' => $att_id));
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
        $userlogin = getUserLogin();
        //check rights; if fail: return null
        if (!$userlogin->hasRights('attachment_read')) {
            return null;
        }
        if ($userlogin->isAnonymous() && $R->read_access_level!='public') {
            return null;
        }
        if (($R->read_access_level=='private') && ($userlogin->userId() != $R->user_id) && (!$userlogin->hasRights('attachment_read_all'))) {
            return null;
        }
        //rights were OK; read data
        $attachment = new Attachment;
        foreach ($R as $key => $value)
        {
            if ($key=='ismain'||$key=='isremote') {
                $value = $value=='TRUE';
            }
            $attachment->$key = $value;
        }
        return $attachment;
    }

    /** Construct an attachment from the POST data present in the attachments/edit or add view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        $attachment = new Attachment;
        //correct form?
        if ($this->CI->input->post('formname')!='attachment') {
            return null;
        }
        //get basic data
        $attachment->att_id             = $this->CI->input->post('att_id');
        $attachment->name               = $this->CI->input->post('name');
        $attachment->note               = $this->CI->input->post('note');
        $attachment->isremote           = $this->CI->input->post('isremote');
        $attachment->location           = $this->CI->input->post('location');
        $attachment->ismain             = $this->CI->input->post('ismain');
        $attachment->mime               = $this->CI->input->post('mime');
        $attachment->pub_id             = $this->CI->input->post('pub_id');
        $attachment->user_id            = $this->CI->input->post('user_id');
        return $attachment;
    }
        
    /** Return an array of Attachment object for the given publication. */
    function getAttachmentsForPublication($pub_id) {
        $result = array();
        $Q = $this->CI->db->getwhere('attachments', array('pub_id' => $pub_id));
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
        if ($attachment->isremote) {
        	#determine real name (the one exposed to user)
        	#from alternative name or from original name
        	#
        	$realname=$attachment->location;
        	$ext=$this->CI->file_upload->get_extension($realname);
        	if (getConfigurationSetting('ALLOW_ALL_EXTERNAL_ATTACHMENTS')!='TRUE') {
        		if (!in_array($ext, getConfigurationSetting('ALLOWED_ATTACHMENT_EXTENSIONS'))) {
        			appendErrorMessage("ERROR UPLOADING: ".$ext." is not an allowed extension for remote files.<br>"
        			."Allowed types: <b>".implode(',',getConfigurationSetting('ALLOWED_ATTACHMENT_EXTENSIONS'))."</b>"
        			."Need other file types? Ask <a href='mailto:"
        			.getConfigurationSetting("CFG_ADMINMAIL")."'>"
        			.getConfigurationSetting("CFG_ADMIN")."</a><br>");
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
            $Q = $this->CI->db->query('SELECT * FROM attachments WHERE pub_id = '.$attachment->pub_id);
            if ($Q->num_rows() == 0) {
                $attachment->ismain = True;
            }
        
        	#if ismain, old main attachment should be un-main-ed
    		if ($attachment->ismain) {
    			$res = mysql_query("UPDATE attachments SET ismain='FALSE' where pub_id=".$attachment->pub_id);
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
    		$res = mysql_query("INSERT INTO attachments 
    		                    (pub_id, note, name, location, mime, ismain, isremote, user_id) 
    		             VALUES (".$attachment->pub_id.",'"
    		                      .addslashes($attachment->note)."', '"
    		                      .addslashes($realname)."', '"
    		                      .addslashes($attachment->location)."','"
    		                      .addslashes($attachment->mime)."','"
    		                      .$ismain."', 'TRUE', "
    		                      .getUserLogin()->userId().")");
    		if (mysql_error()) {
    			appendErrorMessage("Error adding attachment: ".mysql_error()."<br>");
    			return -1;
    		}        	
        	return mysql_insert_id();
	    } else {
        	# upload not possible: return with error
        	if (getConfigurationSetting("SERVER_NOT_WRITABLE") == "TRUE") {
        		appendErrorMessage("You cannot upload attachment files to this server (the server is declared write-only); please use remote attachments instead.<br>");
        		return -1;
        	}
        
        	$this->CI->file_upload->http_error = $_FILES['upload']['error'];
        
        	if ($this->CI->file_upload->http_error > 0) {
        		appendErrorMessage("Error while uploading: ".$this->CI->file_upload->error_text($this->CI->file_upload->http_error));
        		return -1;
        	}
        
        	# prepare upload of file from temp to permanent location
        	$this->CI->file_upload->the_file = $_FILES['upload']['name'];
        	$this->CI->file_upload->the_temp_file = $_FILES['upload']['tmp_name'];
        	$this->CI->file_upload->extensions = getConfigurationSetting("ALLOWED_ATTACHMENT_EXTENSIONS");  // specify the allowed extensions here
        	$this->CI->file_upload->upload_dir = AIGAION_ATTACHMENT_DIR."/";  // is the folder for the uploaded files (you have to create this folder)
        	$this->CI->file_upload->max_length_filename = 255; // change this value to fit your field length in your database (standard 100)
        	$this->CI->file_upload->rename_file = true;
        	$this->CI->file_upload->replace = "n"; 
        	$this->CI->file_upload->do_filename_check = "n"; // use this boolean to check for a valid filename
        
        	# determine real name (the one exposed to user) and storename (the one
        	# used for storage) of file, from alternative name or from original name
        	$realname=$_FILES['upload']['name'];
        	$ext = $this->CI->file_upload->get_extension($realname);
        	if (isset($attachment->name) && ($attachment->name != "")) {
        		if ($this->CI->file_upload->get_extension($attachment->name) != $ext) {
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
        	if ($this->CI->file_upload->upload($storename)) {  
        	    // storename is an additional filename information, use this to rename the uploaded file
        		//echo "mime:".$mime.".";
        		# upload was succesful:
        		# if ismain, old main attachment should be un-main-ed
        		if ($attachment->ismain) {
        			$res = mysql_query("UPDATE attachments SET ismain='FALSE' where pub_id=".$attachment->pub_id);
        			if (mysql_error()) {
        				appendErrorMessage("Error un-'main'-ing other attachments: ".mysql_error());
        				return -1;
        			}
        		}
                //the first attachment is always a main attachment
                $Q = $this->CI->db->query('SELECT * FROM attachments WHERE pub_id = '.$attachment->pub_id);
                if ($Q->num_rows() == 0) {
                    $attachment->ismain = True;
                }
            
        		# add appropriate info about new attachment to database
        		$ismain = 'FALSE';
        		if ($attachment->ismain) {
        		    $ismain = 'TRUE';
        		}
        		$res = mysql_query("INSERT INTO attachments 
        		                    (pub_id, note, name, location, mime, ismain, isremote, user_id) 
        		             VALUES (".$attachment->pub_id.",'"
        		                      .addslashes($attachment->note)."', '"
        		                      .addslashes($realname)."', '"
        		                      .addslashes($storename.$ext)."','"
        		                      .addslashes($attachment->mime)."','"
        		                      .$ismain."', 'FALSE', "
        		                      .getUserLogin()->userId().")");
        		if (mysql_error()) {
        			appendErrorMessage("Error adding attachment: ".mysql_error()."<br>");
        			return -1;
        		}
        		
        		# check if file is really there
        		if (!is_file(AIGAION_ATTACHMENT_DIR."/".$storename.$ext))
        		{
        	        appendErrorMessage("Error uploading.<br>
                    Is this error entirely unexpected? You might want to check whether 
                    the php settings 'upload_max_filesize', 'post_max_size' and 
                    'max_execution_time' are all large enough for uploading
                    your attachments... Please check this with your administrator.<br>");
        		}
        		
        		return mysql_insert_id();
        	} else {
        		appendErrorMessage("ERROR UPLOADING: ".$this->CI->file_upload->show_error_string()
        		  ."<br>Is thee error due to allowed file types? Ask <a href='mailto:".getConfigurationSetting("CFG_ADMINMAIL")."'>"
        		  .getConfigurationSetting("CFG_ADMIN")."</a> for more types.<br>");
        		return -1;
        	}
        }
        appendErrorMessage("GENERIC ERROR UPLOADING. THIS SHOULD NOT HAVE BEEN LOGICALLY POSSIBLE<br/>"); 
        //but nevertheless,  murphy's law dicates that we add an error feedback message here :)
        return -1;
    }

    /** tries to commit this attachment to the database. Note: not all fields are supposed to be edited.
    Generally, only the note and the name are considered to be editable! Furthermore the new name should 
    have the proper extension. If not, this method fixes the extension. Returns TRUE or FALSE depending 
    on whether the operation was operation was successfull. */
    function commit($attachment) {
 
        //attachment name should be correct wrt location! 
        if (!$attachment->isremote) {
          	$ext1=$this->CI->file_upload->get_extension($attachment->location);
          	$ext2=$this->CI->file_upload->get_extension($attachment->name);
          	if ($ext1!=$ext2) {
          	    $attachment->name .= $ext1;
          	}
        }
		if ($attachment->ismain) {
			$res = mysql_query("UPDATE attachments SET ismain='FALSE' where pub_id=".$attachment->pub_id);
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
        
        $this->CI->db->query(
            $this->CI->db->update_string("attachments",
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