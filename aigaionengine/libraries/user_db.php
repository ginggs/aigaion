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


    /** Construct a topic from the POST data present in the topics/edit view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        //correct form?
        if ($this->CI->input->post('formname')!='user') {
            return null;
        }
        //get basic data
        $user = new User;
        $user->user_id            = $this->CI->input->post('user_id');
        $user->initials           = $this->CI->input->post('initials');
        $user->firstname          = $this->CI->input->post('firstname');
        $user->betweenname        = $this->CI->input->post('betweenname');
        $user->surname            = $this->CI->input->post('surname');
        $user->email              = $this->CI->input->post('email');
        $user->lastreviewedtopic  = $this->CI->input->post('lastreviewedtopic');
        $user->abbreviation       = $this->CI->input->post('abbreviation');
        $user->login              = $this->CI->input->post('login');
        $user->password           = $this->CI->input->post('password');
        $user->isAnonymous        = $this->CI->input->post('isAnonymous');

        $user->preferences['theme']              = $this->CI->input->post('theme');
        $user->preferences['summarystyle']       = $this->CI->input->post('summarystyle');
        $user->preferences['authordisplaystyle'] = $this->CI->input->post('authordisplaystyle');
        $user->preferences['liststyle']          = $this->CI->input->post('liststyle');
        $user->preferences['newwindowforatt']    = $this->CI->input->post('newwindowforatt');

        $user->assignedrights = array();
        foreach (getAvailableRights() as $right=>$description) {
            if ($this->CI->input->post($right)) {
                $user->assignedrights[] = $right;
            }
        }

        //the ids of all groups that the user is a part of
        //$group_ids          = array();   NOT IMPLEMENTED YET
                                    
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