<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Export extends Controller {

	function Export()
	{
		parent::Controller();	
	}
	
	/** Pass control to the export/all/ */
	function index()
	{
		$this->all();
	}

    /** 
    export/all
    
    Export all (accessible) entries in the database
    
	Fails with error message when one of:
	    never
	    
	Parameters passed via URL segments:
	    3rd: type (bibtex|ris)
	         
    Returns:
        A clean text page with exported publications
    */
    function all() {
	    $type = $this->uri->segment(3,'bibtex');
	    if (!in_array($type,array('bibtex','ris'))) {
	        $type = 'bibtex';
	    }
        $userlogin = getUserLogin();

        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getAllPublicationsAsMap();
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view

        $output = $this->load->view('export/'.$type, array('nonxrefs'=>$pubs,'xrefs'=>$xrefpubs,'header'=>'All publications'), True);

        //set output
        $this->output->set_output($output);        

    }    
    /** 
    export/topic
    
    Export all (accessible) entries from one topic
    
	Fails with error message when one of:
	    non existing topic_id requested
	    
	Parameters passed via URL segments:
	    3rd: topic_id
	    4rth: type (bibtex|ris)
	         
    Returns:
        A clean text page with exported publications
    */
    function topic() {
	    $topic_id = $this->uri->segment(3,-1);
	    $type = $this->uri->segment(4,'bibtex');
	    $config = array();
	    $topic = $this->topic_db->getByID($topic_id,$config);
	    if ($topic==null) {
	        appendErrorMessage('Export requested for non existing topic<br/>');
	        redirect ('');
	    }
	    if (!in_array($type,array('bibtex','ris'))) {
	        $type = 'bibtex';
	    }
        $userlogin = getUserLogin();

        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getForTopicAsMap($topic->topic_id);
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view

        $output = $this->load->view('export/'.$type, array('nonxrefs'=>$pubs,'xrefs'=>$xrefpubs,'header'=>'All publications for topic "'.$topic->name.'"'), True);

        //set output
        $this->output->set_output($output);        

    }        
    /** 
    export/author
    
    Export all (accessible) entries from one author
    
	Fails with error message when one of:
	    non existing author_id requested
	    
	Parameters passed via URL segments:
	    3rd: author_id
	    4rth: type (bibtex|ris)
	         
    Returns:
        A clean text page with exported publications
    */
    function author() {
	    $author_id = $this->uri->segment(3,-1);
	    $type = $this->uri->segment(4,'bibtex');
	    $author = $this->author_db->getByID($author_id);
	    if ($author==null) {
	        appendErrorMessage('Export requested for non existing author<br/>');
	        redirect ('');
	    }
	    if (!in_array($type,array('bibtex','ris'))) {
	        $type = 'bibtex';
	    }
        $userlogin = getUserLogin();

        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getForAuthorAsMap($author->author_id);
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view

        $output = $this->load->view('export/'.$type, array('nonxrefs'=>$pubs,'xrefs'=>$xrefpubs,'header'=>'All publications for '.$author->getName()), True);

        //set output
        $this->output->set_output($output);        

    }       
    /** 
    export/bookmarklist
    
    Export all (accessible) entries from the bookmarklist of this user
    
	Fails with error message when one of:
	    insufficient rights
	    
	Parameters passed via URL segments:
	    3rth: type (bibtex|ris)
	         
    Returns:
        A clean text page with exported publications
    */
    function bookmarklist() {
	    $type = $this->uri->segment(4,'bibtex');
	    if (!in_array($type,array('bibtex','ris'))) {
	        $type = 'bibtex';
	    }
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
	        appendErrorMessage('Export: no bookmarklist rights<br/>');
	        redirect ('');
	    }
	    
        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getForBookmarkListAsMap();
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view

        $output = $this->load->view('export/'.$type, array('nonxrefs'=>$pubs,'xrefs'=>$xrefpubs,'header'=>'Exported from bookmarklist'), True);

        //set output
        $this->output->set_output($output);        

    }        
        
    /** 
    export/publication
    
    Export one publication
    
	Fails with error message when one of:
	    non existing pub_id requested
	    
	Parameters passed via URL segments:
	    3rd: pub_id
	    4rth: type (bibtex|ris)
	         
    Returns:
        A clean text page with exported publications
    */
    function publication() {
	    $pub_id = $this->uri->segment(3,-1);
	    $type = $this->uri->segment(4,'bibtex');
	    $publication = $this->publication_db->getByID($pub_id);
	    if ($publication==null) {
	        appendErrorMessage('Export requested for non existing publication<br/>');
	        redirect ('');
	    }
	    if (!in_array($type,array('bibtex','ris'))) {
	        $type = 'bibtex';
	    }
        $userlogin = getUserLogin();

        #collect to-be-exported publications 
        $publicationMap = array($publication->pub_id=>$publication);
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view

        $output = $this->load->view('export/'.$type, array('nonxrefs'=>$pubs,'xrefs'=>$xrefpubs), True);

        //set output
        $this->output->set_output($output);        

    }    
}
?>