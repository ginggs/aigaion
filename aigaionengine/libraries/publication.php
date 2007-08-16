<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php

/*
  Publication class
  Class for storing data of one single publication. The class is used
  in the Publication_model and Publication_list_model classes.
  
  Besides data storage, some publication handling functions are available.
  
  -- Functions --
  
*/

class Publication {
  //one var for each publication table field
  //system vars
  var $pub_id       = 0;
  var $user_id	    = '';
  var $group_id     = 0; //group to which access is restricted
  var $specialchars = 'FALSE';
  var $cleantitle   = '';
  var $cleanjournal = '';
  var $actualyear   = '';
  var $isBookmarked = False;
  
  //user vars
  var $pub_type     = '';
  var $bibtex_id		= '';
  var $title        = '';
  var $year         = '';
  var $month        = '';
  var $firstpage    = '';
  var $lastpage     = '';
  var $pages		    = ''; //note:not in DB
  var $journal      = '';
  var $booktitle    = '';
  var $edition      = '';
  var $series       = '';
  var $volume       = '';
  var $number       = '';
  var $chapter      = '';
  var $publisher    = '';
  var $location     = '';
  var $institution  = '';
  var $organization = '';
  var $school       = '';
  var $address      = '';
  var $report_type	= '';
  var $howpublished = '';
  var $note         = '';
  var $abstract     = '';
  var $issn         = '';
  var $isbn         = '';
  var $url          = '';
  var $doi          = '';
  var $crossref     = '';
  var $namekey      = '';
  var $userfields   = '';
  var $read_access_level  = 'intern';
  var $edit_access_level  = 'intern';
  var $derived_read_access_level  = 'intern';
  var $derived_edit_access_level  = 'intern';
   
  var $authors      = array(); //array of plain author class
  var $editors      = array(); //array of plain author class
  
  var $keywords     = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getKeywords()
  var $attachments  = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getAttachments()
  var $notes        = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getNotes()
  
  //class constructor
  function Publication()
  {
    
    //set default publication type
    $this->pub_type = 'Article';
  }
  
  /** tries to add this publication to the database. may give error message if unsuccessful, e.g. due
    insufficient rights. */
  function add() 
  {
        $CI = &get_instance();
    $result_id = $CI->publication_db->add($this);
    return ($result_id > 0);
  }
  
  /** tries to commit this publication to the database. Returns TRUE or FALSE depending 
      on whether the operation was operation was successfull. */
  function update() 
  {
        $CI = &get_instance();
    return $CI->publication_db->update($this);
  }
    /** Deletes this publication. Returns TRUE or FALSE depending on whether the operation was
    successful. */
    function delete() {
        $CI = &get_instance();
        return $CI->publication_db->delete($this);
    }
  
  function getKeywords()
  {
        $CI = &get_instance();
    if ($this->keywords == null)
    {
      $this->keywords = $CI->keyword_db->getKeywordsForPublication($this->pub_id);
    }
    return $this->keywords;
  }
  
  function getAttachments() 
  {
        $CI = &get_instance();
    if ($this->attachments == null) 
    {
        $this->attachments = $CI->attachment_db->getAttachmentsForPublication($this->pub_id);
    }
    return $this->attachments;
  }
  
  function getNotes() 
  {
        $CI = &get_instance();
    if ($this->notes == null) 
    {
        $this->notes = $CI->note_db->getNotesForPublication($this->pub_id);
    }
    return $this->notes;
  }
  
  /** returns formatted bibtex for this publication object. Does not do any crossref merging. */
  function getBiBTeX()
  {
    $CI = &get_instance();
    $CI->load->helper('specialchar');
    $CI->load->helper('string');
    $fields = array();
    $maxfieldname=0;
    //open entry
    $result = '@'.strtoupper($this->pub_type).'{'.$this->bibtex_id.",\n";
    //collect authors
    $authors = "";
    $first = true;
    foreach ($this->authors as $author) {
        if (!$first) $authors .= " and ";
        $first = false;
        $authors .= $author->getName('lvf');
    }
    $fields['author']=$authors;
    //collect editors
    $editors = "";
    $first = true;
    foreach ($this->editors as $editor) {
        if (!$first) $editors .= " and ";
        $first = false;
        $editors .= $editor->getName('lvf');
    }
    $fields['editor']=$editors;
    //collect keywords
    $keywords = "";
    $first = true;
    foreach ($this->getKeywords() as $keyword) {
        if (!$first) $keywords .= ",";
        $first = false;
        $keywords .= $keyword;
    }
    $fields['keywords']=$keywords;
    //initial maxfieldname: the longest of the above collected fields
    $maxfieldname = 8;


    //process fields array, converting to bibtex special chars as you go along.
    //maxfieldname determines the adjustment of the field names
    $spaces = repeater(' ',$maxfieldname);
    foreach ($fields as $name=>$value) {
        if ($value!='') {
            $result .= "  ".substr($spaces.$name,-$maxfieldname)." = {".latinToBibCharsFromString($value)."},\n";
        }
    }
    //close entry
    $result .= "}\n";    
    return $result;
  }
}
?>