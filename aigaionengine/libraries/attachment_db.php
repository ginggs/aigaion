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
   
    /** Return the Attachment object stored in the given database row. */
    function getFromRow($R)
    {
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
            $result[] = $this->getByID($row->att_id);
        }
        return $result;
    }


    /** Add a new attachment with the given data. Returns the new att_id, or -1 on failure. */
    function add($attachment) {
        appendErrorMessage("Adding attachments, remote or uploaded, is not yet implemented.<br>");
        return -1;
    }

    /** tries to commit this attachment to the database. Note: not all fields are supposed to be edited.
    Generally, only the note and the name are considered to be editable! Furthermore the new name should 
    have the proper extension. If not, this method fixes the extension. Returns TRUE or FALSE depending 
    on whether the operation was operation was successfull. */
    function commit($attachment) {
 
        //attachment name should be correct wrt location! 
      	$ext1=$this->CI->file_upload->get_extension($attachment->location);
      	$ext2=$this->CI->file_upload->get_extension($attachment->name);
      	if ($ext1!=$ext2) {
      	    $attachment->name .= $ext1;
      	}

        $updatefields =  array('name'=>$attachment->name,'note'=>$attachment->note);
        
        $this->CI->db->query(
            $this->CI->db->update_string("attachments",
                                         $updatefields,
                                         "att_id=".$attachment->att_id)
                              );
        return True;
    }
}
?>