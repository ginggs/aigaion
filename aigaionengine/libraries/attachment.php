<?php
/** This class holds the data structure of an Attachment.

Database access for Attachments is done through the Attachment_db library */
class Attachment {

    #ID
    var $att_id             = '';
    #content variables; to be changed by user when necessary
    var $name               = '';
    var $note               = '';
    var $read_access_level  = 'intern';
    var $edit_access_level  = 'intern';
    #system variables, not to be changed by user
    var $mime               = '';
    var $location           = '';
    var $isremote           = False;
    var $ismain             = False;
    var $user_id            = -1;
    var $pub_id             = -1;
    var $CI                 = null; //link to the CI base object
    
    function Attachment()
    {
        $this->CI =&get_instance(); 
    }
    
    /** tries to add this publication to the database. may give error message if unsuccessful, e.g. due
    to illegal extension, upload error, etc. */
    function add() {
        $result_id = $this->CI->attachment_db->add($this);
        return ($result_id > 0);
    }
    /** tries to commit this attachment to the database. Note: not all fields are supposed to be edited.
    Generally, only the note and the name are considered to be editable! Returns TRUE or FALSE depending 
    on whether the operation was operation was successfull. */
    function update() {
        return $this->CI->attachment_db->update($this);
    }
   
}
?>