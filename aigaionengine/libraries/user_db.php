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
        $Q = $this->CI->db->query("SELECT * from users where user_id=".$user_id." AND type<>'group'");
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  
    }
   
    function getFromRow($R)
    {
        //no access rights check - for various reasons (e.g. finding abbreviations) we need
        //to be able to read all accounts.
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
        if ($this->CI->input->post('password')=='') {
            $user->password       = '';
        } else {
            $user->password       = md5($this->CI->input->post('password'));
        }
        $user->isAnonymous        = $this->CI->input->post('isAnonymous')=='isAnonymous';

        $user->preferences['theme']              = $this->CI->input->post('theme');
        $user->preferences['summarystyle']       = $this->CI->input->post('summarystyle');
        $user->preferences['authordisplaystyle'] = $this->CI->input->post('authordisplaystyle');
        $user->preferences['liststyle']          = $this->CI->input->post('liststyle');
        $user->preferences['newwindowforatt']    = $this->CI->input->post('newwindowforatt')=='newwindowforatt';

        $user->assignedrights = array();
        foreach (getAvailableRights() as $right=>$description) {
            if ($this->CI->input->post($right)) {
                $user->assignedrights[] = $right;
            }
        }

        //the ids of all groups that the user is a part of
        foreach ($this->CI->group_db->getAllGroups() as $group) {
            if ($this->CI->input->post('group_'.$group->group_id)) {
                $user->group_ids[] = $group->group_id;
            }
        }

        return $user;
    }
        
    /** Return all Users (anon and normal) from the database. */
    function getAllUsers() {
        $result = array();
        $Q = $this->CI->db->query("SELECT * from users WHERE type<>'group'");
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }
    /** Return all anonymous Users from the database. */
    function getAllAnonUsers() {
        $result = array();
        $Q = $this->CI->db->query("SELECT * from users WHERE type='anon'");
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }


    /** Add a new user with the given data. Returns the new user_id, or -1 on failure. */
    function add($user) {
        //add only allowed with right rights:
        if (!getUserLogin()->hasRights('user_edit_all')) {
            return -1;
        }
        //add new user
        $type = 'normal';
        if ($user->isAnonymous) {
            $type = 'anon';
        }
        $newwindowforatt ='FALSE';
        if ($user->preferences['newwindowforatt']) {
            $newwindowforatt ='TRUE';
        }
        $this->CI->db->query(
            $this->CI->db->insert_string("users",
                                         array('initials'           => $user->initials,
                                               'firstname'          => $user->firstname,
                                               'betweenname'        => $user->betweenname,
                                               'surname'            => $user->surname,
                                               'email'              => $user->email,
                                               'lastreviewedtopic'  => $user->lastreviewedtopic,
                                               'abbreviation'       => $user->abbreviation,
                                               'login'              => $user->login,
                                               'password'           => $user->password,
                                               'type'               => $type,
                                               'theme'              => $user->preferences['theme'],
                                               'summarystyle'       => $user->preferences['summarystyle'],
                                               'authordisplaystyle' => $user->preferences['authordisplaystyle'],
                                               'liststyle'          => $user->preferences['liststyle'],
                                               'newwindowforatt'    => $newwindowforatt
                                               ))
                              );
                                               
        if (getUserLogin()->hasRights('user_assign_rights')) {
            //add rights
            $new_id = $this->CI->db->insert_id();
            foreach ($user->assignedrights as $right) {
                $this->CI->db->query($this->CI->db->insert_string("userrights",array('user_id'=>$new_id,'right_name'=>$right)));
            }
        }
        
        //add group links, and rightsprofiles for these groups, to the user
        foreach ($user->group_ids as $group_id) {
            $this->CI->db->query($this->CI->db->insert_string("usergrouplink",array('user_id'=>$new_id,'group_id'=>$group_id)));
            $group = $this->CI->group_db->getByID($group_id);
            foreach ($group->rightsprofile_ids as $rightsprofile_id) {
                $rightsprofile = $this->CI->rightsprofile_db->getByID($rightsprofile_id);
                foreach ($rightsprofile->rights as $right) {
                    $this->CI->db->query("DELETE FROM userrights WHERE user_id=".$new_id." AND right_name='".$right."'");
                    $this->CI->db->query($this->CI->db->insert_string("userrights",array('user_id'=>$new_id,'right_name'=>$right)));
                }
                
            }
        }
        $user->user_id = $new_id;
        
        $this->CI->topic_db->subscribeUser( $user,1);
        return $new_id;
    }

    /** Commit the changes in the data of the given user. Returns TRUE or FALSE depending on 
    whether the operation was successfull. */
    function update($user) {
        //check rights
        $userlogin = getUserLogin();
        if (     !$userlogin->hasRights('user_edit_all')
             &&  
                (!$userlogin->hasRights('user_edit_self') || ($userlogin->userId() != $user->user_id))
            ) {
                return False;
        }
        //check whether this is the correct user...
        $user_test = $this->CI->user_db->getByID($user->user_id);
        if ($user_test == null) {
            return False;
        }
        //determine value for type field
        $type = 'normal';
        if ($user->isAnonymous) {
            $type = 'anon';
        }
        $newwindowforatt ='FALSE';
        if ($user->preferences['newwindowforatt']) {
            $newwindowforatt ='TRUE';
        }
        $updatefields =  array('initials'           => $user->initials,
                               'firstname'          => $user->firstname,
                               'betweenname'        => $user->betweenname,
                               'surname'            => $user->surname,
                               'email'              => $user->email,
                               'lastreviewedtopic'  => $user->lastreviewedtopic,
                               'abbreviation'       => $user->abbreviation,
                               'login'              => $user->login,
                               'type'               => $type,
                               'theme'              => $user->preferences['theme'],
                               'summarystyle'       => $user->preferences['summarystyle'],
                               'authordisplaystyle' => $user->preferences['authordisplaystyle'],
                               'liststyle'          => $user->preferences['liststyle'],
                               'newwindowforatt'    => $newwindowforatt
                               );
        //update password only if not empty
        if (isset($user->password) && ($user->password!="")) {
            $updatefields['password']=$user->password;
        }

        $this->CI->db->query(
            $this->CI->db->update_string("users",
                                         $updatefields,
                                         "user_id=".$user->user_id)
                              );
        //if the user is NOT anonymous, but it is the 'DEFAULT ANONYMOUS ACCOUNT from the site config settings, 
        //turn off the anonymous access and give a message warning
        if (!$user->isAnonymous) {
            if ($user->user_id==getConfigurationSetting("ANONYMOUS_USER")) {
                $siteconfig = $this->CI->siteconfig_db->getSiteConfig();
                $siteconfig->configSettings['ANONYMOUS_USER'] = '';
                $siteconfig->update();
                appendMessage("You just set the default anonymous user to non-anonymous. Therefore the default anonymous user configuration setting has been cleared.<br>");
            }
        }

        if (getUserLogin()->hasRights('user_assign_rights')) {
            //remove all rights, then add the right ones again
            $this->CI->db->query("DELETE FROM userrights WHERE user_id=".$user->user_id);
            //add rights
            foreach ($user->assignedrights as $right) {
                $this->CI->db->query($this->CI->db->insert_string("userrights",array('user_id'=>$user->user_id,'right_name'=>$right)));
            }
        }

        //groups assignment 
        if (getUserLogin()->hasRights('user_edit_all')) {
            //add group links, and rightsprofiles for these groups, to the user
            //BUT ONLY FOR GROUPS THAT WERE NOT YET LINKED TO THIS USER
            $oldgroups = array();
            $oldgrQ = $this->CI->db->query("SELECT * FROM usergrouplink WHERE user_id=".$user->user_id);
            foreach($oldgrQ->result() as $row) {
                $oldgroups[] = $row->group_id;
            }
            $this->CI->db->query("DELETE FROM usergrouplink WHERE user_id=".$user->user_id);
            foreach ($user->group_ids as $group_id) {
                //add group (anew)
                $this->CI->db->query($this->CI->db->insert_string("usergrouplink",array('user_id'=>$user->user_id,'group_id'=>$group_id)));
                //skip rights if already member of this group..
                if (in_array($group_id,$oldgroups))continue;
                //else add pertaining rights as well
                $group = $this->CI->group_db->getByID($group_id);
                foreach ($group->rightsprofile_ids as $rightsprofile_id) {
                    $rightsprofile = $this->CI->rightsprofile_db->getByID($rightsprofile_id);
                    foreach ($rightsprofile->rights as $right) {
                        $this->CI->db->query("DELETE FROM userrights WHERE user_id=".$user->user_id." AND right_name='".$right."'");
                        $this->CI->db->query($this->CI->db->insert_string("userrights",array('user_id'=>$user->user_id,'right_name'=>$right)));
                    }
                    
                }
            }
        }
        
        //if was this user: update preferences, check if user_assign_rights was removed from self...
        if ($user->user_id == $userlogin->userId()) {
            $userlogin->initPreferences();
            if (getUserLogin()->hasRights("user_assign_rights")) {
    	        if (!in_array("user_assign_rights",$user->assignedrights)) {
    	            appendErrorMessage("<b>You just removed your own right to assign user rights! Are you sure that this is correct? If not, re-assign this right before logging out!</b><br>");
    	        }
    	        appendMessage("Profile updated, changes to rights of users are applied after the user has logged in again.<br>");
    	    }
        }
        return True;
    }
    
}
?>