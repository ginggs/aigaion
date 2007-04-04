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

}
?>