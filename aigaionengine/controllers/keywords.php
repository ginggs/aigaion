<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Keywords extends Controller {

	function Keywords()
	{
		parent::Controller();
		
		$this->load->helper('publication');
	}
	
  /** Default function: list publications */
  function index()
	{
    $this->li_keywords();
	}

  function li_keywords($fieldname = "")
  {
    if ($fieldname == "")
      $fieldname = 'keywords';
    
    $keyword = $this->input->post($fieldname);
    if ($keyword != "")
    {
      $keywords = $this->keyword_db->getKeywordsLike($keyword);
      echo $this->load->view('keywords/li_keywords', array('keywords' => $keywords, true));
    }
  }
  
  /** 
  single
  
  Entry point for showing a list of publications that have been assigned the given keyword
  
  fails with error message when one of:
    non existing keyword_id
	    
  Parameters passed via segments:
      3rd:  keyword_id
      4rth: sort order
	  5th:  page number
	         
  Returns:
      A full HTML page with all a list of all publications that have been assigned the given keyword
  */
  function single()
  {
    $keyword_id   = $this->uri->segment(3);
    $order   = $this->uri->segment(4,'year');
    if (!in_array($order,array('year','type','recent','title','author'))) {
      $order='year';
    }
    $page   = $this->uri->segment(5,0);
    
    //load keyword
    $keyword = $this->keyword_db->getByID($keyword_id);
    //$keyword = $keywordResult[$keyword_id];
    if ($keyword == null)
    {
      appendErrorMessage("View publications for keyword: non-existing keyword_id passed");
      redirect('');
    }
    $keywordContent ['keyword'] = $keyword;
    
    $this->load->helper('publication');
    
    $userlogin = getUserLogin();
    
    //set header data
    $header ['title']       = 'Keyword: "'.$keyword->keyword.'"';
    $header ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
    $header ['sortPrefix']       = 'publications/keyword/'.$keyword->keyword_id.'/';
    $header ['exportCommand']    = '';//'export/keyword/'.$keyword_id.'/';
    $header ['exportName']    = 'Export for keyword';

    //set data
    $publicationContent['header']       = 'Publications for keyword "'.$keyword->keyword.'"';
    switch ($order) {
        case 'type':
            $publicationContent['header']          = 'Publications for keyword "'.$keyword->keyword.'" sorted by journal and type';
            break;
        case 'recent':
            $publicationContent['header']          = 'Publications for keyword "'.$keyword->keyword.'" sorted by recency';
            break;
        case 'title':
            $publicationContent['header']          = 'Publications for keyword "'.$keyword->keyword.'" sorted by title';
            break;
        case 'author':
            $publicationContent['header']          = 'Publications for keyword "'.$keyword->keyword.'" sorted by first author';
            break;
    }
    if ($userlogin->getPreference('liststyle')>0) {
        //set these parameters when you want to get a good multipublication list display
        $publicationContent['multipage']       = True;
        $publicationContent['currentpage']     = $page;
        $publicationContent['multipageprefix'] = 'publications/keyword/'.$keyword->keyword_id.'/'.$order.'/';
    }    
    $publicationContent['publications'] = $this->publication_db->getForKeyword($keyword,$order);
    $publicationContent['order'] = $order;

    
    //get output
    $output  = $this->load->view('header',              $header,              true);
    $output .= $this->load->view('keywords/single',     $keywordContent,      true);
    if ($publicationContent['publications'] != null) {
      $output .= $this->load->view('publications/list', $publicationContent,  true);
    }
    else
      $output .= "<div class='messagebox'>No publications found using this keyword</div>";
    
    $output .= $this->load->view('footer',              '',             true);
    
    //set output
    $this->output->set_output($output);  
  }  
}
?>