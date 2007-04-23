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
    
    /** Return an array of Attachment object for the given publication. */
    function getAttachmentsForPublication($pub_id) {
        $result = array();
        $Q = $this->CI->db->getwhere('attachments', array('pub_id' => $pub_id));
        foreach ($Q->result() as $row) {
            $result[] = $this->getByID($row->att_id);
        }
        return $result;
    }

}
?>