<?php
/** A Rightsprofile is simply a named collection of rights. Such rightsprofiles can be used to determine which rights are assigned by default 
to new group members, or to assign a collection of rights to a user with one action. */
class Rightsprofile {
  
    #ID
    var $rightsprofile_id            = '';
    #content variables; to be changed directly when necessary
    //name
    var $name           = '';
    //rights
    var $rights     = array(); //an array of ($right_name)
    //link to the CI base object
    var $CI                 = null; 

    function Rightsprofile()
    {
        $this->CI =&get_instance(); 

    }
    
    /** Add a new Rightsprofile with the given data. Returns TRUE or FALSE depending on whether the operation was
    successfull. After a successfull 'add', $this->rightsprofile_id contains the new rightsprofile_id. */
    function add() {
        $this->rightsprofile_id = $this->CI->rightsprofile_db->add($this);
        return ($this->rightsprofile_id > 0);
    }

    /** Commit the changes in the data of this rightsprofile. Returns TRUE or FALSE depending on whether the operation was
    operation was successfull. */
    function commit() {
        return $this->CI->rightsprofile_db->commit($this);
    }
}
?>