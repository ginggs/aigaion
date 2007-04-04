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