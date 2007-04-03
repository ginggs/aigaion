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
    
    /** Return all Groups from the database. */
    function getAllGroups() {
        $result = array();
        $Q = $this->CI->db->query("SELECT * from users WHERE type='group'");
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }
    
}
?>