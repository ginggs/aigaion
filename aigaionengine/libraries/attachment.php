<?php
/** This class holds the data structure of an Attachment.

Database access for Attachments is done through the Attachment_db library */
class Attachment {

    #ID
    var $att_id            = '';
    #content variables; to be changed by user when necessary
    var $name               = '';
    var $note               = '';
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
   
}
?>