<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Topics extends Controller {

	function Topics()
	{
		parent::Controller();	
	}
	
	/** Pass control to the topics/browse/ controller */
	function index()
	{
		redirect('topics/browse');
	}

    /** Simple browse page for Topics. 
        This controller returns a full web page.
        Third parameter selects root topic_id for tree (default:1) */
	function browse()
	{
	    $root_id = $this->uri->segment(3,1);
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Topic';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        
        $root = $this->topic_db->getByID($root_id);
        $this->load->vars(array('subviews'  => array('topics//maintreerow'=>array())));
        $output .= "<div id='topictree-holder'>\n<ul class='topictree-list'>\n"
                    .$this->load->view('topics/tree',
                                      array('topics'   => $root->getChildren(),
                                            'showroot'  => True,
                                            'depth'     => -1
                                            ),  
                                      true)."</ul>\n</div>\n";
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}


	/** Entrypoint for adding a topic. Shows the necessary form. */
	function add()
	{
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Topic';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('topics/edit' , array(),  true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
    }
    
	/** Entrypoint for editing a category. Shows the necessary form. */
	function edit()
	{
	    $topic_id = $this->uri->segment(3,1);
	    if ($topic_id==1) {
	        redirect('topics/browse');
	    }
        $topic = $this->topic_db->getByID($topic_id);

	    if ($topic==null) {
	        appendErrorMessage('Topic does not exist.<br>');
	        redirect('');
	    }
        	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Topic';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('topics/edit' , array('topic'=>$topic),  true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);

    }
    
    /** Simple view page for single topic. 
        This controller returns a full web page.
        Third parameter selects topic_id (default:1)
        If topic 1 is chosen, user is redirected to browse/ controller */
	function view()
	{
	    $topic_id = $this->uri->segment(3,1);
	    if ($topic_id==1) {
	        redirect('topics/browse');
	    }
        $topic = $this->topic_db->getByID($topic_id);

	    if ($topic==null) {
	        appendErrorMessage('Topic does not exist.<br>');
	        redirect('topics/browse');
	    }
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Topic';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('topics/single', array('topic' => $topic),  true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}    
    
    function commit() {
        echo "COMMIT TOPIC";
    }
}
?>