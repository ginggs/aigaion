<?php
/** This class regulates the database access for Rightsprofile's. */
class Rightsprofile_db {
  
    var $CI = null;
  
    function Rightsprofile_db()
    {
        $this->CI = &get_instance();
    }
    
    function getByID($rightsprofile_id)
    {
        //no access rights check
        $Q = $this->CI->db->getwhere('rightsprofiles',array('rightsprofile_id'=>$rightsprofile_id));
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  
    }
        
    function getFromRow($R)
    {
        //no access rights check 
        $rightsprofile = new Rightsprofile;
        foreach ($R as $key => $value)
        {
            $rightsprofile->$key = $value;
        }
        $Q = $this->CI->db->getwhere('rightsprofilerightlink',array('rightsprofile_id'=>$rightsprofile->rightsprofile_id));
        foreach ($Q->result() as $R)
        {
            $rightsprofile->rights[] = $R->right_name;
        }  
        return $rightsprofile;
    }

    /** Construct a rightsprofile from the POST data present in the rightsprofiles/edit view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        $rightsprofile = new Rightsprofile;
        //correct form?
        if ($this->CI->input->post('formname')!='rightsprofile') {
            return null;
        }
        //get basic data
        $rightsprofile->rightsprofile_id = $this->CI->input->post('rightsprofile_id',-1);
        $rightsprofile->name             = $this->CI->input->post('name','');
        //collect checked rights
        foreach (getAvailableRights() as $right=>$description) 
        {
            if ($this->CI->input->post($right)) {
                $rightsprofile->rights[] = $right;
            }
        }
        return $rightsprofile;
    }

    /** Return the names of all Rightsprofiles from the database. */
    function getAllRightsprofileNames() {
        $result = array();
        $Q = $this->CI->db->query("SELECT DISTINCT name FROM rightsprofiles ORDER BY name ASC");
        foreach ($Q->result() as $R) {
            $result[] = $R->name;
        }
        return $result;
    }

    /** Return all Rightsprofile objects from the database. */
    function getAllRightsprofiles() {
        $result = array();
        $Q = $this->CI->db->getwhere('rightsprofiles',array());
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }
    

    /** Add a new rightsprofile with the given data. Returns the new rightsprofile_id, or -1 on failure. */
    function add($rightsprofile) {
        $userlogin = getUserLogin();
        //add only allowed with right rights:
        if (!$userlogin->hasRights('user_edit_all')||!$userlogin->hasRights('user_assign_rights')) {
            return -1;
        }
        //add new rightsprofile
        $this->CI->db->query(
            $this->CI->db->insert_string("rightsprofiles", array('name'=>$rightsprofile->name))
                             );
                                               
        //add rights
        $new_id = $this->CI->db->insert_id();
        foreach ($rightsprofile->rights as $right) {
            $this->CI->db->query($this->CI->db->insert_string("rightsprofilerightlink",array('rightsprofile_id'=>$new_id,'right_name'=>$right)));
        }
        return $new_id;
    }

    /** Commit the changes in the data of the given rightsprofile. Returns TRUE or FALSE depending on 
    whether the operation was successfull. */
    function update($rightsprofile) {
         //check rights
        $userlogin = getUserLogin();
        if (     !$userlogin->hasRights('user_edit_all')
             ||
                 !$userlogin->hasRights('user_assign_rights')
            ) {
                return False;
        }

        $updatefields =  array('name'=>$rightsprofile->name);

        $this->CI->db->query(
            $this->CI->db->update_string("rightsprofiles",
                                         $updatefields,
                                         "rightsprofile_id=".$rightsprofile->rightsprofile_id)
                              );
                                               
        //remove all rights, then add the right ones again
        foreach (getAvailableRights() as $right) {
            $this->CI->db->query("DELETE FROM rightsprofilerightlink WHERE rightsprofile_id=".$rightsprofile->rightsprofile_id);
        }
        //add rights
        foreach ($rightsprofile->rights as $right) {
            $this->CI->db->query($this->CI->db->insert_string("rightsprofilerightlink",array('rightsprofile_id'=>$rightsprofile->rightsprofile_id,'right_name'=>$right)));
        }
        
        return True;
    }
}
?>