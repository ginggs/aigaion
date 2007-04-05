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
        $Q = $this->CI->db->getwhere('rightsprofiles',array('rightsprofile_id'=>$rightsprofile_id));
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  
    }
        
    function getFromRow($R)
    {
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
    
}
?>