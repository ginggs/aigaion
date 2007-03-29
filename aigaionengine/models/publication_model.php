<?php
/*
Web based document management system
Copyright (C) 2007  (in alphabetical order):
Wietse Balkema, Arthur van Bunningen, Dennis Reidsma, Sebastan Schleussner

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

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
  var $entered_by	  = '';
  var $specialchars = '';
  var $cleantitle   = '';
  var $cleanjournal = '';
  var $actualyear   = '';
  
  //user vars
  var $pub_type     = '';
  var $type         = '';
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
  var $report_type	= ''; //note: rename field from type
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

  var $keywords     = array();
  
  var $authors      = array(); //array of plain author class
  var $editors      = array(); //array of plain author class
  
  var $attachments  = array();
  
  //class constructor
  function Publication()
  {
  }
}

/*
  Publication_model class
  Class for storing and retrieving Publication data.
  The actual publication data are stored in an Publication object:
  $this->$data;
  
  -- Functions --
  
  bool loadByID($pub_id)
    Retrieves a pulbication from the database by pub_id.
    returns true on success, false on fail.
    
  bool loadFromArray($pub_array)
  bool _loadFromRow($pub_row)
    Retrieves a publication from a publication result row.
    returns true on success, false on fail.
    
  bool loadFromPost()
    Retrieves an publication from an input post.
    returns true on success, false on fail.
    
  int addObject()
    Adds a publication to the database.
    returns the pub_id or false on fail.
  
  bool updateObject()
    Updates a publication in the database.
    returns true on success, false on fail.
  
  bool deleteObject()
    Removes a publication from the database.
    returns true on success, false on fail.
  
  bool _clearData()
    Cleans up old publication data and creates a new Publication object.
    Returns true on success, false on fail.
    
  bool format($formatStyle, $data = '')
    Formats the data according to the specified format style.
    When no $data is passed, $this->data is used.
    $data should be of the publication class.
    The formatted data are stored in $this->data.
    Returns true on success, false on fail.
    
*/
class Publication_model extends Model {
  
  //$data can hold one single publication of the Publication class
  var $data         = '';
  
  //$authorList holds one Author_list model for retrieving author lists
  //for the current publication
  var $authorList   = '';
  
  //class constructor
  function Publication_model()
  {
    //call parent constructor to initialize the CI basic class functions
    parent::Model();

    //initialze $data with new publication object
    $this->data = new Publication;
    
    //load one authorlist model for getting a publication's authors and editors
    $this->load->model('author_list_model');
    $this->authorList = new Author_list_model;
  }
  
  function loadByID($pub_id)
  {
    //retrieve one publication row
    $Q = $this->db->query("SELECT * FROM publication WHERE pub_id = ".$this->db->escape($pub_id));
    if ($Q->num_rows() == 1)
    {
      //load the publication
      return $this->_loadFromRow($Q->row());
    }
    else
      return false;
  }
  
  function loadFromArray($pub_array)
  {
    //load publication, since an array handles the same as a row we call loadFromRow
    return $this->loadFromRow($pub_array);
  }
  
  function _loadFromRow($R)
  {
    //cleanup old publication data and create new Publication object
    if (!$this->_clearData())
      return false;
      
    //get all fields from the database and store in the Publication object
    
//TODO: CHECK MERGE SETTING FOR PUBLICATIONS
    //check if we have to merge this publication with a crossref entry
    $do_merge = false;
    if ($R->crossref != "")
    {
      //there is a crossref in this publication. Check if we already have a crossref_cache
      //the crossref_cache is initialized in the publication_list model and is only relevant
      //in lists.
      $has_cache = isset($this->crossref_cache);
      if ($has_cache)
      {
        //there is a cache, check if we can merge from the cache.
        //we signal this by setting the $merge_row
        if (array_key_exists($R->crossref, $this->crossref_cache))
        {
          $merge_row = $this->crossref_cache[$R->crossref];
          $do_merge  = true;
        }
      }
      
      //check if we found the publication in the cache, if not, retrieve from db.
      if (!isset($merge_row))
      {
        $Q = $this->db->query("SELECT * FROM publication WHERE bibtex_id = ".$this->db->escape($R->crossref));
        
        //if we retrieved one single row, we retrieve it and set the $do_merge flag
        if ($Q->num_rows() == 1)
        {
          $merge_row = $Q->row();
          
          //if we have a cache, store this row in the cache
          if ($has_cache)
          {
            $this->crossref_cache[$R->crossref] = $merge_row;
          }
          $do_merge     = true;
        }
      } 
    } //end of crossref retrieval. If we need to merge, this is now signaled in $do_merge
    
    
    if ($do_merge)
    {
      //copy the row to the publication object. If the original row is empty, retrieve the info
      //from the crossref merge row.
      foreach ($R as $key => $value)
      {
        if ($value != '')
        {
          $this->data->$key = $value;
        }
        else
        {
          $this->data->$key = $merge_row->$key;
        }
      }
    }
    else //no merge
    {
      //copy the row to the publication object
      foreach ($R as $key => $value)
      {
        $this->data->$key = $value;
      }
    }
    
//TODO: PERFORMANCE EVALUATION. HOW MUCH FASTER IS THE CODE WITH ONE QUERY FOR
//AUTHORS IN THE PUBLICATION MODEL, COMPARED TO THE QUERIES IN AUTHOR_LIST_MODEL?
//[WB] SMALL TEST: current method is 5-10% slower than method with single query
    
    //retrieve authors
    $this->authorList->loadForPublication($this->data->pub_id, 'N');
    $this->data->authors = $this->authorList->list;
    
    //retrieve editors
    $this->authorList->loadForPublication($this->data->pub_id, 'Y');
    $this->data->authors = $this->authorList->list;
    
    //cleanup authorlist
    $this->authorList->_clearList();

/*
    //retrieve authors and editors
    $this->db->select('*');
    $this->db->from('author, publicationauthor');
    $this->db->where('author.ID = publicationauthor.author');//', 'publicationauthor.author');
    $this->db->where('publicationauthor.pub_id', $this->data->pub_id);
    $this->db->orderby('publicationauthor.rank');
    
    $Q = $this->db->get();
    if ($Q->num_rows() > 0)
    {
      //get authors
      $this->authorList->_clearList();
      foreach ($Q->result() as $R)
      {
        if ($R->is_editor == 'N')
        {
          $this->authorList->_addFromRow($R);
        }
      }
      $this->data->authors = $this->authorList->list;
      
      //get editors
      $this->authorList->_clearList();
      foreach ($Q->result() as $R)
      {
        if ($R->is_editor == 'Y')
        {
          $this->authorList->_addFromRow($R);
        }
      }
      $this->data->editors = $this->authorList->list;
      
      //cleanup
      $this->authorList->_clearList();
    }
*/
  }
  
  function loadFromPost()
  {
    //We need the bibtex parsecreators for parsing names from input
    $this->load->library('PARSECREATORS.php');
    
    //we retrieve the following fields
    $fields = array('pub_id', 
                    'entered_by',
                    'specialchars',
                    'cleantitle',
                    'cleanjournal',
                    'actualyear',
                    'type',         //TODO: RENAME TO 'pub_type',
                    'bibtex_id',
                    'title',
                    'year',
                    'month',
                    'firstpage',
                    'lastpage',
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
                    'userfields',
                    'keywords',
                    'authors',
                    'editors,'
                   );
    
    //first: cleanup old data
    $this->_clearData();
    
    //then retrieve the data
    foreach ($fields as $key)
    {
      $this->data->$key = $this->input->post($key);
    }
    
    //parse the authors
    $nameparser = new PARSECREATORS;
    if ($this->data->authors)
    {
      $authors_array = $nameparser->parse(preg_replace('/[\r\n\t]/', ' and ', $this->data->authors));
      $this->authorList->_clearList();
      foreach ($authors_array as $author)
      {
        $this->authorList->_addToList($author['firstname'], $author['von'], $author['surname']);
      }
      
      $this->data->authors = $this->authorList->list;
    }
    
    //and the editors
    if ($this->data->editors)
    {
      $editors_array = $nameparser->parse(preg_replace('/[\r\n\t]/', ' and ', $this->data->editors));
      $this->authorList->_clearList();
      foreach ($editors_array as $author)
      {
        $this->authorList->_addToList($author['firstname'], $author['von'], $author['surname']);
      }
      $this->data->editors = $this->authorList->list;
    }
    
    //cleanup
    $this->authorList->_clearList();
  }
  
  
  function addObject()
  {
  }
  
  function updateObject()
  {
  }
  
  function deleteObject()
  {
  }
  
  function _clearData()
  {
    unset($this->data);
    $this->data = new Publication;
    
    return (isset($this->data));
  }
  
  function _format($formatStyle, $data = '')
  {
    //either load data from input, or get it from this object
    if ($data == '')
    {
      $data = $this->data;
    }
    else
    {
      $this->_clearData();
      $this->data = $data;
    }
    
//TO DISCUSS: CHECK FOR SPECIAL CHARACTERS OR ONLY CHECK SPECIALCHARS
    //when there are special characters, do the formatting
    if ($data->specialchars == 'TRUE')
    {  
      $fields = array(  'title',
                        'journal',
                        'booktitle',
                        'edition',
                        'series',
                        'publisher',
                        'location',
                        'institution',
                        'organization',
                        'school',
                        'address',
                        'howpublished',
                        'note',
                        'abstract',
                        'userfields'
                      );

//TODO: FORMATTING, FOR DIFFERENT FORMATTING STYLES
      foreach ($fields as $field)
      {
        $this->data->$field = $data->$field;
      }
    }

//TODO: KEYWORD FORMATTING?
    
    //format authors and editors
    $this->authorList->_format($formatStyle, $this->data->authors);
    $this->data->authors = $this->authorList->data;
    
    $this->authorList->_format($formatStyle, $this->data->editors);
    $this->data->editors = $this->authorList->data;
  }
  
  function validate()
  {
    //initialize validation message
    $this->validationMessage = "";
    
    //get fields array with required and conditional indications
    $fields = getPublicationFieldArray($this->data->type);  //TODO: RENAME TO pub_type
    
    //retrieve required fields from field array
    $required = array();
    foreach ($fields as $key => $value)
    {
      if ($value == 'required')
      {
        $required[] = $key;
      }
    }
    
    //and retrieve the conditional fields array
    $conditional = array();
    foreach ($fields as $key => $value)
    {
      if ($value == 'conditional')
      {
        $conditional[] = $key;
      }
    }
    
    //check required fields and do validation message
    foreach ($required as $field)
    {
      if (!$this->data->$field)
      {
        $this->validationMessage .= "The field ".$field." is required for this publication type.<br />\n";
      }
    }
    
    //check conditional fields
    if (count($conditional) > 0)
    {
      //there are max 2 conditional fields
      $bcondition = false;
      foreach ($conditional as $field)
      {
        if (!$bcondition && $this->data->$field)
        {
          $bcondition = true;
        }
      }
      if (!$bcondition)
      {
        $this->validationMessage .= "One of the fields: <b>".implode($conditional, '</b>or<b> ')."</b> is required.<br />\n";
      }
    }
    
    //complete validationMessage
    if ($this->validationMessage != "")
    {
      $this->validationMessage .= "Please correct this.<br />\n";
      return false;
    }
    else
    {
      return true;
    }
  }
}
?>