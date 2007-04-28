<?php
/** This class holds the data structure of a Note.

Database access for Notes is done through the Note_db library */
class Note {

    #ID
    var $note_id            = '';
    #content variables; to be changed by user when necessary
    var $text               = '';
    var $read_access_level  = 'intern';
    var $edit_access_level  = 'intern';
    #system variables, not to be changed by user
    var $user_id            = -1;
    var $pub_id             = -1;
    var $CI                 = null; //link to the CI base object
    
    function Note()
    {
        $this->CI =&get_instance(); 
    }
    
    /** tries to add this note to the database. may give error message if unsuccessful, e.g. due
    insufficient rights. */
    function add() {
        $result_id = $this->CI->note_db->add($this);
        return ($result_id > 0);
    }
    /** tries to commit this note to the database. Returns TRUE or FALSE depending 
    on whether the operation was operation was successfull. */
    function commit() {
        return $this->CI->note_db->commit($this);
    }
   
}
?>