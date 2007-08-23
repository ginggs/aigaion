<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
//include_once("bibparse/PARSECREATORS.php");
//include_once("bibparse/PARSEENTRIES.php");
//include_once("bibparse/PARSEPAGE.php");
//include_once("bibparse/PARSEMONTH.php");

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
  function loadData($data)
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
  
  //getPublications: get the parsed publications
  function getPublications()
  {
    return $this->publications;
  }
  
  
  function bibliophileToPublication($bibliophileEntry)
  {
    $CI = &get_instance();
    $publication = $CI->publication;
    
    
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

    if (isset($bibliophileEntry['author'])) {
      $authors          = array();
      $bibtex_authors   = $this->cAuthorParser->parse($bibliophileEntry['author']);
      
      foreach ($bibtex_authors as $author)
      {
        $author_db      = $CI->author_db->getByExactName($author['firstname'], $author['von'], $author['surname']);
        if ($author_db  != null)
        {
          $authors[]    = $author_db;
        }
        else
        {
          $author_db    = $CI->author_db->setByName($author['firstname'], $author['von'], $author['surname']);
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
        $editor_db      = $CI->author_db->getByExactName($editor['firstname'], $editor['von'], $editor['surname']);
        if ($editor_db  != null)
        {
          $editors[]    = $editor_db;
        }
        else
        {
          $editor_db    = $CI->author_db->setByName($editor['firstname'], $editor['von'], $editor['surname']);
          $editors[]    = $editor_db;
        }
      }

      $publication->editors = $editors;
      unset($bibliophileEntry['editor']);
    }
  	
  	if (isset($bibliophileEntry['pages']) && ($bibliophileEntry['pages'] != '')) {
  	  list($publication->firstpage, $publication->lastpage) = $this->cPageParser->init($bibliophileEntry['pages']);
		  unset($bibliophileEntry['pages']);
    }
    
    if (!isset($bibliophileEntry['month']) || ($bibliophileEntry['month'] == '')) {
  		$publication->month = '0';
  	} else {
  		list($publication->month, $dummy) = $this->cMonthParser->init($bibliophileEntry['month']);
  		unset($bibliophileEntry['month']);
  	}
  	
  	$userFields = array_diff(array_keys($bibliophileEntry), getFullFieldArray());
  	$userFieldsText = "";
  	foreach ($userFields as $field) {
  		if (trim($bibliophileEntry[$field]) != "")
  			$userFieldsText.=$field."={".$bibliophileEntry[$field]."},\n";
  	}
  	if ($userFieldsText != '')
  	{
  	  $publication->userfields = $userFieldsText;
  	}
  	return $publication;
	}
}

?>