<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Accesslevels extends Controller {

	function Arrangements()
	{
		parent::Controller();	
	}
	
	/** There is no default controller . */
	function index()
	{
		redirect('');
	}



    /** 
    accesslevels/edit
    
    access point to start editing the access levels of an object

    Fails (with error message) when one of: 
        non existing object
        insufficient user rights

    Information passed through segments:
        3rd: object type
        4rth: object id
        
    Returns:
        a complete edit GUI for access levels
            
    */    
    function edit() {
        $type      = $this->uri->segment(3); 
        $object_id = $this->uri->segment(4); 
        if ($type=='topic') {
            $this->_edittopic();
            return;
        }
        //determine publication
        $publication = null;
        switch ($type) {
            case 'publication':
                $publication = $this->publication_db->getByID($object_id);
                break;
            case 'attachment':
                $attachment = $this->attachment_db->getByID($object_id);
                if ($attachment!=null)$publication = $this->publication_db->getByID($attachment->pub_id);
                break;
            case 'note':
                $note= $this->note_db->getByID($object_id);
                if ($note!=null)$publication = $this->publication_db->getByID($note->pub_id);
                break;
        }
        //publication null: redirect with error
        if ($publication==null) {
            appendErrorMessage("Couldn't find publication to edit access levels<br/>");
            redirect('');
        }
        
        $headerdata = array();
        $headerdata['title'] = 'Accesslevels: edit';
        $headerdata['javascripts'] = array('accesslevels.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('accesslevels/editforpublication',
                                     array('publication'=>$publication,'type'=>$type,'object_id'=>$object_id),  
                                     true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);	
    }  

    function _edittopic() {
        $object_id = $this->uri->segment(4); 
        
        $headerdata = array();
        $headerdata['title'] = 'Accesslevels: edit';
        $headerdata['javascripts'] = array('accesslevels.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('accesslevels/editfortopic',
                                     array('topic_id'=>$object_id),  
                                     true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);	
    }

    /** 
    accesslevels/set
    
    access point to actually new access levels for an object

    Fails (with error message) when one of: 
        non existing object
        insufficient user rights

    Information passed through segments:
        3rd: object type
        4rth: object id
        5th: e or r (edit or read access level)
        6th: new level
        
    Returns:
        to the accesslevels/edit controller, with a feedback message saying what other access levels were affected
            
    */    
    function set() {
        $type      = $this->uri->segment(3); 
        $object_id = $this->uri->segment(4); 
        $eorr      = $this->uri->segment(5); 
        $newlevel  = $this->uri->segment(6); 
        if ($eorr=='r') {
            $this->accesslevels_lib->setReadAccessLevel($type,$object_id,$newlevel);
        } else {
            $this->accesslevels_lib->setEditAccessLevel($type,$object_id,$newlevel);
        }
        redirect('accesslevels/edit/'.$type.'/'.$object_id);	
    }
}


?>