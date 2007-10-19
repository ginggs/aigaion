<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Search extends Controller {

	function Search()
	{
		parent::Controller();	
	}
	
	/** Default: advanced search form */
	function index()
	{
		$this->advanced();
	}

    /** 
    search/quicksearch
        
    Fails not
    
    Parameters:
        search query through form value
    
    Returns a full html page with a search result. */
	function quicksearch()
	{
        $query = $this->input->post('searchstring');
	    if (trim($query)=='') {
	        appendErrorMessage('Search: no query.<br/>');
	        redirect('');
	    }
	    $this->load->library('search_lib');
	    $searchresults = $this->search_lib->simpleSearch($query);
	    
        //get output: search result page
        $headerdata = array();
        $headerdata['title'] = 'Aigaion 2.0: Search results';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        $output .= $this->load->view('search/results',
                                      array('searchresults'=>$searchresults),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
    /** 
    search/advanced
    */
	function advanced()
	{
	    echo 'advanced search not implemented yet';
	}
}
?>