<?php
/** This class regulates the database access for Groups. */
class Group_db {
  
    var $CI = null;
  
    function Group_db()
    {
        $this->CI = &get_instance();
    }
    
    function getByID($group_id)
    {
        $Q = $this->CI->db->query("SELECT * FROM users WHERE user_id=".$group_id." AND type='group'");
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  
    }
   
    function getFromRow($R)
    {
        $group = new Group;
        $group->group_id           = $R->user_id;
        $group->name               = $R->surname;
        $group->abbreviation       = $R->abbreviation;

        //return result
        return $group;
    }


    /** Construct a group from the POST data present in the groups/edit view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        $group = new Group;
        //correct form?
        if ($this->CI->input->post('formname')!='group') {
            return null;
        }
        //get basic data
        $group->group_id           = $this->CI->input->post('group_id');
        $group->name               = $this->CI->input->post('name');
        $group->abbreviation       = $this->CI->input->post('abbreviation');
        //collect other data such as assigned rights profiles
        return $group;
    }
    
    /** Return all Groups from the database. */
    function getAllGroups() {
        $result = array();
        $Q = $this->CI->db->query("SELECT * from users WHERE type='group'");
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }


    /** Add a new group with the given data. Returns the new group_id, or -1 on failure. */
    function add($group) {
        //add new group
        $this->CI->db->query(
            $this->CI->db->insert_string("users", array('surname'=>$group->name,'abbreviation'=>$group->abbreviation,'type'=>'group'))
                             );
                                               
        $new_id = $this->CI->db->insert_id();
        //add rights profiles...
        //not yet
        return $new_id;
    }

    /** Commit the changes in the data of the given group. Returns TRUE or FALSE depending on 
    whether the operation was successfull. */
    function commit($group) {
 
        $updatefields =  array('name'=>$group->name,'abbreviation'=>$group->abbreviation);

        $this->CI->db->query(
            $this->CI->db->update_string("users",
                                         $updatefields,
                                         "user_id=".$group->group_id)
                              );
        
        return True;
    }
}
?>