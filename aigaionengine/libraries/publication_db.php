<?php
/** This class regulates the database access for Publications. Several accessors are present that return a Publication or
array of Publications. */
class Publication_db {

  var $CI = null;

  function Publication_db()
  {
    $this->CI = &get_instance();
  }

  /** Return the Publication object with the given id, or null if insufficient rights */
  function getByID($pub_id)
  {
    //retrieve one publication row
    $Q = $this->CI->db->query("SELECT * FROM publication WHERE pub_id = ".$this->CI->db->escape($pub_id));

    if ($Q->num_rows() > 0)
    {
      //load the publication
      return $this->getFromRow($Q->row());
    }
    else
    return null;
  }
  
  /** Return the Publication object with the given bibtex_id, or null if insufficient rights */
  function getByBibtexID($bibtex_id)
  {
    //retrieve one publication row
    $Q = $this->CI->db->query("SELECT * FROM publication WHERE bibtex_id = ".$this->CI->db->escape($bibtex_id));

    if ($Q->num_rows() > 0)
    {
      //load the publication
      return $this->getFromRow($Q->row());
    }
    else
    return null;
  }
  
  

  function getFromArray($pub_array)
  {
    //load publication, since an array handles the same as a row we call getFromRow
    return $this->getFromRow($pub_array);
  }

  /** Return the Publication object stored in the given database row, or null if insufficient rights. */
  function getFromRow($R)
  {
    $userlogin = getUserLogin();
    //check rights; if fail: return null
    if ($userlogin->isAnonymous() && $R->read_access_level!='public') {
      return null;
    }
    if (    ($R->read_access_level=='private')
         && ($userlogin->userId() != $R->user_id)) {
      return null;
    }
    if (   ($R->read_access_level=='group')
        && (!in_array($R->group_id,$this->CI->user_db->getByID($userlogin->userId())->group_ids) ) 
        ) {
      return null;
    }    
    //rights were OK; read data

    $publication = new Publication;
    foreach ($R as $key => $value)
    {
      $publication->$key = $value;
    }

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
        $Q = $this->CI->db->query("SELECT * FROM publication WHERE bibtex_id = ".$this->CI->db->escape($R->crossref));

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
          $publication->$key = $value;
        }
        else
        {
          $publication->$key = $merge_row->$key;
        }
      }
    }
    else //no merge
    {
      //copy the row to the publication object
      foreach ($R as $key => $value)
      {
        $publication->$key = $value;
      }
    }

    //TODO: PERFORMANCE EVALUATION. HOW MUCH FASTER IS THE CODE WITH ONE QUERY FOR
    //AUTHORS IN THE PUBLICATION MODEL, COMPARED TO THE QUERIES IN AUTHOR_LIST_MODEL?
    //[WB] SMALL TEST: current method is 5-10% slower than method with single query

    ////////////// End of crossref merge //////////////


    //retrieve authors and editors
    $publication->authors = $this->CI->author_db->getForPublication($R->pub_id, 'N');
    $publication->editors = $this->CI->author_db->getForPublication($R->pub_id, 'Y');

    //check if this publication was bookmarked by the logged user
    $Q = $this->CI->db->query("SELECT * FROM userbookmarklists WHERE user_id = ".getUserLogin()->userId()." AND pub_id=".$R->pub_id);
    if ($Q->num_rows()>0) {
        $publication->isBookmarked = True;
    }
    
    return $publication;
  }

  function getFromPost()
  {
    //we retrieve the following fields
    $fields = array('pub_id',
    'user_id',
    'specialchars',
    'cleantitle',
    'cleanjournal',
    'actualyear',
    'pub_type',
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

    $publication = new Publication;

    foreach ($fields as $key)
    {
      $publication->$key = $this->CI->input->post($key);
    }
    
    //parse the keywords
    if ($publication->keywords)
    {
      $keywords = preg_replace('/ *([^,]+)/',
  						                 "###\\1",
  						                 $publication->keywords);
  						
      $keywords = explode('###', $keywords);
      
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
    
    //parse the authors
    $parser = new $this->CI->parsecreators;
    if ($publication->authors)
    {
      $authors_array    = $parser->parse(preg_replace('/[\r\n\t]/', ' and ', $publication->authors));
      $authors          = array();
      foreach ($authors_array as $author)
      {
        $author_db      = $this->CI->author_db->getByExactName($author['firstname'], $author['von'], $author['surname']);
        if ($author_db  != null)
        {
          $authors[]    = $author_db;
        }
        else
        {
          $author_db    = $this->CI->author_db->setByName($author['firstname'], $author['von'], $author['surname']);
          $authors[]  = $author_db;
        }
      }

      $publication->authors = $authors;
    }

    //parse the editors
    if ($publication->editors)
    {
      $authors_array  = $parser->parse(preg_replace('/[\r\n\t]/', ' and ', $publication->editors));
      $authors        = array();
      foreach ($authors_array as $author)
      {
        $author       = $this->CI->author_db->getByExactName($author['firstname'], $author['von'], $author['surname']);
        if ($author  != null)
        $authors[]  = $author;
        else
        {
          $author     = $this->CI->author_db->setByName($author['firstname'], $author['von'], $author['surname']);
          $authors[]  = $author;
        }
      }

      $publication->editors = $authors;
    }
    return $publication;
  }
  
  function add($publication)
  {
    //insert all publication data in the publication table
    $fields = array(
                    'pub_type',
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
                    'cleantitle',
                    'cleanjournal',
                    'actualyear',
                    'specialchars'
    );
  
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
  
  
  
    //check for specialchars
    foreach ($specialfields as $field)
    {
      if (findSpecialCharsInString($publication->$field))
        $publication->specialchars = 'TRUE';
    }
    
    //create cleantitle and cleanjournal
    $cleantitle                 = stripBibCharsFromString($publication->title);
    $publication->cleantitle    = stripQuotesFromString($cleantitle);
    $cleanjournal               = stripBibCharsFromString($publication->journal);
    $publication->cleanjournal  = stripQuotesFromString($cleanjournal);
    
    //get actual year
    if (trim($publication->year) == '')
    {
      if (trim($publication->crossref) != '')
      {
        $xref_pub = $this->publication_db->getByBibtexID($publication->crossref);
        $publication->actualyear = $xref_pub->year;
      }
    }
    else
    {
      $publication->actualyear = $publication->year;
    }
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field)
      $data[$field] = $publication->$field;
    
    $data['user_id'] = getUserLogin()->userId();
  
    /* fields set to default value by database: 
      'read_access_level'
      'edit_access_level'
    */
    
        //insert into database using active record helper
    $this->CI->db->insert('publication', $data);
    
    //update this publication's pub_id
    $publication->pub_id = $this->CI->db->insert_id();
    
    
    //check whether Keywords are already available, if not, add them to the database
    //keywords are in an array, the keys are the keyword_id.
    //If no key the keyword still has to be added.
    if (is_array($publication->keywords)) //we bypass the ->getKeywords() function here, it would try to retrieve from DB.
    {
      $publication->keywords  = $this->CI->keyword_db->ensureKeywordsInDatabase($publication->keywords);
    
      foreach ($publication->keywords as $keyword_id => $keyword)
      {
        $data = array('pub_id' => $publication->pub_id, 'keyword_id' => $keyword_id);
        $this->CI->db->insert('publicationkeywordlink', $data);
      }
    }
    
    //add authors
    if (is_array($publication->authors))
      $publication->authors   = $this->CI->author_db->ensureAuthorsInDatabase($publication->authors);
      
    $rank = 1;
    foreach ($publication->authors as $author)
    {
      $data = array('pub_id'    => $publication->pub_id,
                    'author_id' => $author->author_id,
                    'rank'      => $rank,
                    'is_editor' => 'N');
      $this->CI->db->insert('publicationauthorlink', $data);
      $rank++;
    }
    
    //add editors
    if (is_array($publication->editors))
      $publication->editors   = $this->CI->author_db->ensureAuthorsInDatabase($publication->editors);
    
    $rank = 1;
    foreach ($publication->editors as $author)
    {
      $data = array('pub_id'    => $publication->pub_id,
                    'author_id' => $author->author_id,
                    'rank'      => $rank,
                    'is_editor' => 'Y');
      $this->CI->db->insert('publicationauthorlink', $data);
      $rank++;
    }
    
    //subscribe to topic 1
    $data = array('pub_id'      => $publication->pub_id,
                  'topic_id'    => 1);
    $this->CI->db->insert('topicpublicationlink', $data);
    
    return $publication;
  }
  
  function update($publication)
  {
    
  }

  function validate($publication)
  {
    $validate_required    = array();
    $validate_conditional = array();
    $fields               = getPublicationFieldArray($publication->pub_type);
    foreach ($fields as $field => $value)
    {
      if ($value == 'required')
      {
        $validate_required[$field] = 'required';
      }
      else if ($value == 'conditional')
      {
        $validate_conditional[$field] = 'conditional';
      }
    }
    
    $validation_message   = '';
    foreach ($validate_required as $key => $value)
    {
      if (trim($publication->$key) == '')
      {
        $validation_message .= "The ".$key." field is required.<br/>\n";
      }
    }
    
    if (count($validate_conditional) > 0)
    {
      $conditional_validation = false;
      $conditional_field_text = '';
      
      foreach ($validate_conditional as $key => $value)
      {
        if (trim($publication->$key) != '')
        {
          $conditional_validation = true;
        }
        $conditional_field_text .= $key.", ";
      }
      if (!$conditional_validation)
      {
        $validation_message .= "One of the fields ".$conditional_field_text." is required.<br/>\n";
      }
    }
    
    if ($validation_message != '')
    {
      appendErrorMessage("Changes not committed:<br/>\n".$validation_message);
      return false;
    }
    else
      return true;
  }


///////publication list functions

  function getForTopic($topic_id)
  {
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $this->CI->db->query("SELECT DISTINCT publication.* FROM publication, topicpublicationlink
    WHERE topicpublicationlink.topic_id = ".$this->CI->db->escape($topic_id)."
    AND publication.pub_id = topicpublicationlink.pub_id
    ORDER BY actualyear, cleantitle");

    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }

    unset($this->crossref_cache);
    return $result;
  }
  
  function getForAuthor($author_id)
  {
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $this->CI->db->query("SELECT DISTINCT publication.* FROM publication, publicationauthorlink
    WHERE publicationauthorlink.author_id = ".$this->CI->db->escape($author_id)."
    AND publication.pub_id = publicationauthorlink.pub_id
    ORDER BY actualyear, cleantitle");

    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }

    unset($this->crossref_cache);
    return $result;
  }
  
  /** Return a list of publications for the bookmark list of the logged user */
  function getForBookmarkList()
  {
    $user_id = getUserLogin()->userId();
    $Q = $this->CI->db->query("SELECT DISTINCT publication.* FROM publication, userbookmarklists
    WHERE userbookmarklists.user_id=".$user_id."
    AND   userbookmarklists.pub_id=publication.pub_id
    ORDER BY actualyear, cleantitle");

    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }
    return $result;
  }
  

}
?>