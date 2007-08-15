<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Version extends Controller {

	function Version()
	{
		parent::Controller();	
	}
	
	/**  */
	function index()
	{
	    $this->release();
	}
	

	function release() {
        $this->db->orderby('type','desc');
        $this->db->limit(1);
        $Q = $this->db->get('changehistory');
        foreach ($Q->result() as $R) {
            $output = $R->version;
        }
        
        //set output
        $this->output->set_output($output);

	}
	//return detailed change history of all versions newer than the given
	function details() {
        $this->db->orderby('type','desc');
        $fromversion = $this->uri->segment(3,'');
        if ($fromversion=='') {
            $Q = $this->db->get('changehistory');
        } else {
            $Q = $this->db->getwhere('changehistory','version>'.$this->db->escape($fromversion));
        }
        $output = '<changehistory>';
        foreach ($Q->result() as $R) {
            $output .= '<version><release>'.$R->version.'</release><type>'.$R->type.'</type>';
            $output .= '<description>'.$R->description.'</description></version>';        
        }
        $output .= '</changehistory>';
        
        //set output
        $this->output->set_output($output);

	}

	
}
?>