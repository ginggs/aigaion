<?php
/** This class regulates the database access for Groups. */
class Group_db {
  
  
    function Group_db()
    {
    }
    
    function getByID($group_id)
    {
        $CI = &get_instance();
        $Q = $CI->db->query("SELECT * FROM users WHERE user_id=".$group_id." AND type='group'");
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  
    }
   
    function getFromRow($R)
    {
        $CI = &get_instance();
        //no access rights check - for various reasons (e.g. finding abbreviations) we need
        //to be able to read all accounts.
        $group = new Group;
        $group->group_id           = $R->user_id;
        $group->user_id           = $R->user_id;
        $group->name               = $R->surname;
        $group->abbreviation       = $R->abbreviation;

        //get rights profiles
        $Q = $CI->db->query("SELECT * FROM grouprightsprofilelink WHERE group_id=".$group->group_id);
        foreach ($Q->result() as $row) {
            $group->rightsprofile_ids[] = $row->rightsprofile_id;
        }
        
        //return result
        return $group;
    }


    /** Construct a group from the POST data present in the groups/edit view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        $CI = &get_instance();
        $group = new Group;
        //correct form?
        if ($CI->input->post('formname')!='group') {
            return null;
        }
        //get basic data
        $group->group_id           = $CI->input->post('group_id');
        $group->name               = $CI->input->post('name');
        $group->abbreviation       = $CI->input->post('abbreviation');
        //collect other data such as assigned rights profiles
        foreach ($CI->rightsprofile_db->getAllRightsprofiles() as $rightsprofile) {
            if ($CI->input->post('rightsprofile_'.$rightsprofile->rightsprofile_id)) {
                $group->rightsprofile_ids[] = $rightsprofile->rightsprofile_id;
            }
        }
        return $group;
    }
    
    /** Return all Groups from the database. */
    function getAllGroups() {
        $CI = &get_instance();
        $result = array();
        $Q = $CI->db->query("SELECT * from users WHERE type='group'");
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }
 

    /** Add a new group with the given data. Returns the new group_id, or -1 on failure. */
    function add($group) {
        $CI = &get_instance();
        //add only allowed with right rights:
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('user_edit_all')) {
            return -1;
        }
        //add new group
        $CI->db->query(
            $CI->db->insert_string("users", array('surname'=>$group->name,'abbreviation'=>$group->abbreviation,'type'=>'group'))
                             );
                                               
        $new_id = $CI->db->insert_id();
        //add rights profiles
        foreach ($group->rightsprofile_ids as $rightsprofile_id) {
            $CI->db->query($CI->db->insert_string("grouprightsprofilelink",array('group_id'=>$new_id,'rightsprofile_id'=>$rightsprofile_id)));
        }
        $group->user_id = $new_id;
        $group->group_id = $new_id;
        $CI->topic_db->subscribeUser( $group,1);
        
        return $new_id;
    }

    /** Commit the changes in the data of the given group. Returns TRUE or FALSE depending on 
    whether the operation was successfull. */
    function update($group) {
        $CI = &get_instance();
        //check rights
        $userlogin = getUserLogin();
        if (     !$userlogin->hasRights('user_edit_all')
            ) {
                return False;
        }
        //check whether this is the correct group...
        $group_test = $CI->group_db->getByID($group->group_id);
        if ($group_test == null) {
            return False;
        }
 
        $updatefields =  array('surname'=>$group->name,'abbreviation'=>$group->abbreviation);

        $CI->db->query(
            $CI->db->update_string("users",
                                         $updatefields,
                                         "user_id=".$group->group_id)
                              );
        //remove all rights profiles, then add the right ones again
        $CI->db->query("DELETE FROM grouprightsprofilelink WHERE group_id=".$group->group_id);
        //add rights profiles
        foreach ($group->rightsprofile_ids as $rightsprofile_id) {
            $CI->db->query($CI->db->insert_string("grouprightsprofilelink",array('group_id'=>$group->group_id,'rightsprofile_id'=>$rightsprofile_id)));
        }
        
        return True;
    }
}
?>