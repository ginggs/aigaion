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
  var $user_id	  = '';
  var $specialchars = '';
  var $cleantitle   = '';
  var $cleanjournal = '';
  var $actualyear   = '';
  var $CI           = null;
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
   
  var $authors      = array(); //array of plain author class
  var $editors      = array(); //array of plain author class
  
  var $keywords     = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getKeywords()
  var $attachments  = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getAttachments()
  var $notes        = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getNotes()
  
  //class constructor
  function Publication()
  {
    $this->CI =&get_instance(); 
  }
  
  /** tries to add this publication to the database. may give error message if unsuccessful, e.g. due
    insufficient rights. */
  function add() 
  {
    $result_id = $this->CI->publication_db->add($this);
    return ($result_id > 0);
  }
  
  /** tries to commit this publication to the database. Returns TRUE or FALSE depending 
      on whether the operation was operation was successfull. */
  function commit() 
  {
    return $this->CI->publication_db->commit($this);
  }
  
  function getKeywords()
  {
    //if ($this->keywords == null)
    //{
      $this->keywords = $this->CI->keyword_db->getKeywordsForPublication($this->pub_id);
    //}
    return $this->keywords;
  }
  
  function getAttachments() 
  {
    if ($this->attachments == null) 
    {
        $this->attachments = $this->CI->attachment_db->getAttachmentsForPublication($this->pub_id);
    }
    return $this->attachments;
  }
  
  function getNotes() 
  {
    if ($this->notes == null) 
    {
        $this->notes = $this->CI->note_db->getNotesForPublication($this->pub_id);
    }
    return $this->notes;
  }
}
?>