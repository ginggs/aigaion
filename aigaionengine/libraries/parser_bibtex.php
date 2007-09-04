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
  
  //getPublications: get the parsed publications. 
  function getPublications()
  {
    return $this->publications;
  }
  
  
  //TODO: AT THIS POINT, BIBTEX SHOULD BE STRIPPED, LEAVING ONLY UTF8 DATA! Input was from a form, 
  //therefore already in UTF8. So - take all affected fields and bash them through bibToUtf8?
  function bibliophileToPublication($bibliophileEntry)
  {
    $CI = &get_instance();
    $CI->load->helper('bibtexutf8');
    $CI->load->helper('utf8_to_ascii');
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
    foreach ($specialfields as $field)
    {
      //remove bibchars
      $publication->$field = bibCharsToUtf8FromString($publication->$field);
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
        $author_db      = $CI->author_db->getByExactName($author['firstname'], $author['von'], $author['surname']);
        if ($author_db  != null)
        {
          $authors[]    = $author_db;
        }
        else
        {
          //setByName will return data where bibtexchars are already stripped
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