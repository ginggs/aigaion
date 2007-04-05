<?php
/** This class holds the data structure of a user. 

This User class is now mostly used for managing users and profiles.
Later on, this class will also be used in the login library.

Database access for Users is done through the User_db library */
class User {
  
    #ID
    var $user_id            = '';
    #content variables; to be changed directly when necessary
    //name
    var $initials           = '';
    var $firstname          = '';
    var $betweenname        = '';
    var $surname            = '';
    //other info
    var $email              = '';
    var $lastreviewedtopic  = 0;
    //login info
    var $abbreviation       = '';
    var $login              = '';
    var $password           = '';
    var $isAnonymous        = False;
    #system variables, not to be changed *directly* by user
    //preferences. Directly filled with default values, but that will change in the future
    var $preferences        = array('theme'=>'default',
                                    'summarystyle'=>'author',
                                    'authordisplaystyle'=>'fvl',
                                    'liststyle'=>'0',
                                    'newwindowforatt'=>'FALSE'
                                    ); //an array of ($preferencename=>preferencevalue)
    //assigned rights
    var $assignedrights     = array(); //an array of ($assignedright)
    //the ids of all groups that the user is a part of
    var $group_ids          = array();
    //link to the CI base object
    var $CI                 = null; 

    /** The class-tree (Category object) of  only those classes to which the user is subscribed */
    //var $personal_subscribed_tree    = null; //this is the tree as it is only filled with the topics for this individual user, i.e. the 'extra' subscribed topics
    //var $full_subscribed_tree    = null; //this is the tree as it is also filled with the topics from the group!
    //or dow we want to store the topics as a list of IDs?
    
    function User()
    {
        $this->CI =&get_instance(); 

    }
    
    /** Add a new user with the given data. Returns TRUE or FALSE depending on whether the operation was
    successfull. After a successfull 'add', $this->user_id contains the new user_id. */
    function add() {
        $this->user_id = $this->CI->user_db->add($this);
        return ($this->user_id > 0);
    }

    /** Commit the changes in the data of this user. Returns TRUE or FALSE depending on whether the operation was
    successfull. */
    function commit() {
        return $this->CI->user_db->commit($this);
    }
}
?>