<?php
include_once("bibparse/PARSECREATORS.php");
include_once("bibparse/PARSEENTRIES.php");
include_once("bibparse/PARSEPAGE.php");
include_once("bibparse/PARSEMONTH.php");

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
  
  //instance of CI
  var $CI;
  
  //class constructor
  function Parser_Bibtex()
  {
    $this->cEntryParser   = new PARSEENTRIES;
    $this->cAuthorParser  = new PARSECREATORS;
    $this->cPageParser    = new PARSEPAGE;
    $this->cMonthParser   = new PARSEMONTH;
    
    $CI = &get_instance();
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
  	  
  	}
  }
  
  //getPublications: get the parsed publications
  function getPublications()
  {
    return $this->publications;
  }
  
  
  function bibliophileToPublication($bibliophileEntry)
  {
    $publication = $this->CI->publication;
    $CI = &get_instance();
    //we retrieve the following fields without special operations
    $fields = array(
    'bibtex_id',
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
    
    foreach ($fields as $field)
    {
      if ($bibliophileEntry[$field])
      {
        $publication->$field = $bibliophileEntry[$field];
      }
    }
  	if ($bibliophileEntry['author']) {
  		$publication->authors = $this->authorParser($bibliophileEntry['author']);
  	}
  
  	if ($bibliophileEntry['editor']) {
  		$publication->editors = bibParseAuthors($bibliophileEntry['editor']);
  	}
  	
  	if ($bibliophileEntry['pages'] != '') {
		  list($publication->firstpage, $publication->lastpage) = bibParsePages($entry['pages']);
    }
    
    if (!$bibliophileEntry['month'] || ($bibliophileEntry['month'] == '')) {
  		$publication->month = '0';
  	} else {
  		list($publication->month, $dummy) = bibParseMonth($bibliophileEntry['month']);
  	}
  	
  	$userFields = array_diff(array_keys($bibliophileEntry), bibGetSupportedFields());
  	$userFieldsText = "";
  	foreach ($userFields as $field) {
  		if (trim($bibliophileEntry[$field]) != "")
  			$userFieldsText.=$field."={".$bibliophileEntry[$field]."},\n";
  	}
  	if ($userFieldsText != '')
  	{
  	  $publication->userfields = $userFieldsText;
  	}
	}
}

?>