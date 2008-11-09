<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
//include_once("bibparse/PARSECREATORS.php");
//include_once("bibparse/PARSEENTRIES.php");
//include_once("bibparse/PARSEPAGE.php");
//include_once("bibparse/PARSEMONTH.php");
appendErrorMessage("The Parser_Bibtex was initialized. However, it's functionality was taken over by Parser_Import, so this should not have happened.  It's not a porblem, since this class still works, but I would appreciate it if you could forward this warning to the developers.<br/>Regards, Dennis<br/>");
/** IN THE PROCESS OF BEING REPLACED WITH A PARSER THAT WORKS FOR ALLL INPUT TYPES, GIVEN THE RIGHT ENTRYPARSER */
class Parser_Bibtex
{
  //class member variables
  var $bibtexData   = '';
  var $publications = array();
  
  //the parser itself
  var $cEntryParser;
  var $cAuthorParser;
  var $cPageParser;
  var $cMonthParser;
  
  
  //class constructor
  function Parser_Bibtex()
  {
    $CI = &get_instance();
    $CI->load->library('parseentries');
    $CI->load->library('parsecreators');
    $CI->load->library('parsepage');
    $CI->load->library('parsemonth');
    $CI->load->helper('publication');
    
    $this->cEntryParser   = $CI->parseentries;
    $this->cAuthorParser  = $CI->parsecreators;
    $this->cPageParser    = $CI->parsepage;
    $this->cMonthParser   = $CI->parsemonth;
  }
  
  //loadData: get the data and store in the class;
  function loadImportData($data)
  {
    $this->bibtexData = $data;
    
    //as soon as we load new data, existing (parsed) publications become invalid
    unset($this->publications);
    $this->publications = array();
  }
  
  //parse: call actual parser, retrieve results and store in publications array;
  function parse()
  {
    //todo: load user strings and prepend to bibtex data
    
    //load bibtex to parser and extract entries
    $this->cEntryParser->loadBibtexString($this->bibtexData);
   	$this->cEntryParser->extractEntries();
  
    //retrieve parsed entries from parser
  	list($preamble, $strings, $entries) = $this->cEntryParser->returnArrays();
  
  	//now, $entries contains the parsed bibtex, Bibliophile style.
  	//we have to convert to our publication objects
  	foreach ($entries as $entry)
  	{
  	  $this->publications[] = $this->bibliophileToPublication($entry);
  	}
  }
  
  //getPublications: get the parsed publications. 
  function getPublications()
  {
    return $this->publications;
  }
  
  
  function bibliophileToPublication($bibliophileEntry)
  {
    $CI = &get_instance();
    $CI->load->library('bibtex2utf8');
    $CI->load->helper('utf8_to_ascii');
    $CI->load->helper('attachments');
    $publication = new Publication(); 
    
    
    //we retrieve the following fields without special operations
    $fields = array(
    'title',
    'year',
    'journal',
    'booktitle',
    'edition',
    'series',
    'volume',
    'number',
    'chapter',
    'publisher',
    'location',
    'institution',
    'organization',
    'school',
    'address',
    'report_type',
    'howpublished',
    'note',
    'abstract',
    'issn',
    'isbn',
    'url',
    'doi',
    'crossref',
    'namekey',
    'keywords'
    );
    //the following fields should after retrieval be de-bibtexxed
    $specialfields = array(
                    'title',
                    'journal',
                    'booktitle',
                    'series',
                    'publisher',
                    'location',
                    'institution',
                    'organization',
                    'school',
                    'note',
                    'abstract'
    );
    $publication->pub_type = ucfirst(strtolower($bibliophileEntry['bibtexEntryType']));
    unset($bibliophileEntry['bibtexEntryType']);
    
    foreach ($fields as $field)
    {
      if (isset($bibliophileEntry[$field]))
      {
        $publication->$field = $bibliophileEntry[$field];
      }
    }
    if (isset($bibliophileEntry['bibtexCitation'])) {
      $publication->bibtex_id = $bibliophileEntry['bibtexCitation'];
      unset($bibliophileEntry['bibtexCitation']);
    }
    
    //check for specialchars
    if (getConfigurationSetting('CONVERT_BIBTEX_TO_UTF8')!='FALSE') {
      foreach ($specialfields as $field)
      {
        //remove bibchars
        $publication->$field = $CI->bibtex2utf8->bibCharsToUtf8FromString($publication->$field);
      }
    }
    
    //create cleantitle and cleanjournal
    $publication->cleantitle    = utf8_to_ascii($publication->title);
    $publication->cleanjournal    = utf8_to_ascii($publication->journal);

    if (isset($bibliophileEntry['author'])) {
      $authors          = array();
      $bibtex_authors   = $this->cAuthorParser->parse($bibliophileEntry['author']);
      
      //if exact match exists: take that one; otherwise create a new one
      foreach ($bibtex_authors as $author)
      {
        //getByExactName will return data where bibtexchars are already stripped
        $author_db      = $CI->author_db->getByExactName($author['firstname'], $author['von'], $author['surname'], $author['jr']);
        if ($author_db  != null)
        {
          $authors[]    = $author_db;
        }
        else
        {
          //setByName will return data where bibtexchars are already stripped
          $author_db    = $CI->author_db->setByName($author['firstname'], $author['von'], $author['surname'], $author['jr']);
          $authors[]    = $author_db;
        }
      }

      $publication->authors = $authors;
      unset($bibliophileEntry['author']);
    }

    if (isset($bibliophileEntry['editor'])) {
      $editors          = array();
      $bibtex_editors   = $this->cAuthorParser->parse($bibliophileEntry['editor']);
      
      foreach ($bibtex_editors as $editor)
      {
        $editor_db      = $CI->author_db->getByExactName($editor['firstname'], $editor['von'], $editor['surname'], $editor['jr']);
        if ($editor_db  != null)
        {
          $editors[]    = $editor_db;
        }
        else
        {
          $editor_db    = $CI->author_db->setByName($editor['firstname'], $editor['von'], $editor['surname'], $editor['jr']);
          $editors[]    = $editor_db;
        }
      }

      $publication->editors = $editors;
      unset($bibliophileEntry['editor']);
    }
  	
  	if (isset($bibliophileEntry['pages']) && ($bibliophileEntry['pages'] != '')) {

      //DR 29-09-2008: we no longer store firstpage and lastpage separately
      //list($publication->firstpage, $publication->lastpage) = $this->cPageParser->init($bibliophileEntry['pages']);
      $publication->pages = $bibliophileEntry['pages'];
      unset($bibliophileEntry['pages']);
    }
    
    if (!isset($bibliophileEntry['month']) || ($bibliophileEntry['month'] == '')) {
  		$publication->month = '0';
  	} else {
  		list($publication->month, $dummy) = $this->cMonthParser->init($bibliophileEntry['month']);
  		unset($bibliophileEntry['month']);
  	}
  	
  	if ($publication->keywords)
    {
      $keywords = preg_replace('/ *([^,]+)/',
  						                 "###\\1",
  						                 $publication->keywords);
  						
      $keywords = explode('###', $keywords);
      
        //NOTE: this will give problems when our data is in UTF8, due to substr and strlen. Don't forget to check!
      foreach ($keywords as $keyword)
      {
        if (trim($keyword) != '')
        {
          if ((substr($keyword, -1, 1) == ',') || (substr($keyword, -1, 1) == ';'))
            $keyword = substr($keyword, 0, strlen($keyword) - 1);
          
          $keyword_array[] = $keyword;
        }
      }
      $publication->keywords = $keyword_array;
    }
    
  	$userFields = array_diff(array_keys($bibliophileEntry), getFullFieldArray());
  	$userFieldsText = "";
  	foreach ($userFields as $field) {
  		if (trim($bibliophileEntry[$field]) != "") {
  		    //this field should be checked to see if it contains an url or a doi (fieldname: pdf, ee, ...) 
  		    if (in_array(strtolower(trim($field)),array('pdf','ee'))) {
                list($parsed, $attUrl, $doi) = parseUrlField($field, $bibliophileEntry[$field]);
                //if $parsed is True, the contents of the field have been successfully interpreted 
                //and $attUrl or $doi contain the result. Information that has been interpreted as a 
                //DOI will not be returned as attUrl as well.
                if ($parsed) {
                    if ($attUrl!='') {
                        if ($publication->url!='') {
                            $publication->url.='    \n'.$attUrl;
                        } else {
                            $publication->url = $attUrl;
                        }
                    } else {
                        $publication->doi=$doi;
                    } 
                } else {
                    $userFieldsText.=$field."={".$bibliophileEntry[$field]."},\n";
                }
  		    } else {
  			    $userFieldsText.=$field."={".$bibliophileEntry[$field]."},\n";
  			}
  		}
  	}
  	if ($userFieldsText != '')
  	{
  	  $publication->userfields = $userFieldsText;
  	}
  	
  	//some post processing:
  	//TODO: url and doi fields can be handled better... use attachments helper to clean and strip the contents of those fields
  	if ($publication->doi != '') {
  	    //try to parse
  	    list($parsed, $attUrl, $doi) = parseUrlField('doi', $publication->doi);
  	    if ($parsed) {
  	        $publication->doi = $doi;
  	    }
  	}
  	if ($publication->url != '') {
  	    //try to parse
  	    list($parsed, $attUrl, $doi) = parseUrlField('url', $publication->url);
  	    if ($parsed && ($doi != '')) {
  	        $publication->doi = $doi;
  	        $publication->url = '';
  	    }
  	}
  	return $publication;
  }
}

?>