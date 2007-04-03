<?php
/** This class regulates the database access for User's. Several accessors are present that return a User or 
array of User's. */
class User_db {
  
    var $CI = null;
  
    function User_db()
    {
        $this->CI = &get_instance();
    }
    
    function getByID($user_id)
    {
        $Q = $this->CI->db->query("SELECT * from users where user_id=".$user_id." AND NOT type='group'");
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  
    }
   
    function getFromRow($R)
    {
        $user = new User;
        $user->user_id            = $R->user_id;
        $user->initials           = $R->initials;
        $user->firstname          = $R->firstname;
        $user->betweenname        = $R->betweenname;
        $user->surname            = $R->surname;
        $user->email              = $R->email;
        $user->lastreviewedtopic  = $R->lastreviewedtopic;
        $user->abbreviation       = $R->abbreviation;
        $user->login              = $R->login;
        $user->password           = $R->password;
        $user->isAnonymous        = $R->type=='anon';
        //preferences: all other columns are preferences
        $user->preferences        = array();
        foreach ($R as $key => $value)
        {
            if (!isset($user->$key)) {
                $user->preferences[$key] = $value;
            }
        }
        //assigned rights
        $user->assignedrights     = array();
        $query = $this->CI->db->query("SELECT * FROM userrights WHERE user_id=".$R->user_id);
        foreach ($query->result() as $row) {
            $user->assignedrights[] = $row->right_name;
        }
        //the ids of all groups that the user is a part of
        $user->group_ids            = array();
        $query = $this->CI->db->query("SELECT * FROM usergrouplink WHERE user_id=".$R->user_id);
        foreach ($query->result() as $row) {
            $user->group_ids[] = $row->group_id;
        }
        //return result
        return $user;
    }
    
    /** Return all Users (anon and normal) from the database. */
    function getAllUsers() {
        $result = array();
        $Q = $this->CI->db->query("SELECT * from users WHERE NOT type='group'");
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }
    
}
?>