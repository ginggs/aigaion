<?php
/** This class holds the data structure of a group. 

Groups are structurally very similar to Users, and they even use the same tables. 
However, conceptually they are very different, which is why we made separate classes for them. */
class Group {
  
    #ID
    var $group_id            = '';
    #content variables; to be changed directly when necessary
    //name
    var $name            = '';
    //other info
    var $abbreviation       = '';
    #system variables, not to be changed *directly* by user
    //link to the CI base object
    var $CI                 = null; 

    function Group()
    {
        $this->CI =&get_instance(); 

    }


    /** Add a new Group with the given data. Returns TRUE or FALSE depending on whether the operation was
    successfull. After a successfull 'add', $this->group_id contains the new group_id. */
    function add() {
        $this->group_id = $this->CI->group_db->add($this);
        return ($this->group_id > 0);
    }

    /** Commit the changes in the data of this group. Returns TRUE or FALSE depending on whether the operation was
    operation was successfull. */
    function commit() {
        return $this->CI->group_db->commit($this);
    }
}
?>