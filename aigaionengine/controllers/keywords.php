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
}
?>