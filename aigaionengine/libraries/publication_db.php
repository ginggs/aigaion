<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/** This class regulates the database access for Publications. Several accessors are present that return a Publication or
array of Publications. */
class Publication_db {


  function Publication_db()
  {
  }

  /** Return the Publication object with the given id, or null if insufficient rights */
  function getByID($pub_id)
  {
        $CI = &get_instance();
    //retrieve one publication row
    $Q = $CI->db->query("SELECT * FROM publication WHERE pub_id = ".$CI->db->escape($pub_id));

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
        $CI = &get_instance();
    //retrieve one publication row
    $Q = $CI->db->query("SELECT * FROM publication WHERE bibtex_id = ".$CI->db->escape($bibtex_id));

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
    $CI = &get_instance();
    $publication = new Publication;
    foreach ($R as $key => $value)
    {
      $publication->$key = $value;
    }
    
    $userlogin  = getUserLogin();
    //check rights; if fail: return null
    if ( !$CI->accesslevels_lib->canReadObject($publication))return null;
    

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
        $Q = $CI->db->query("SELECT * FROM publication WHERE bibtex_id = ".$CI->db->escape($R->crossref));

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
    $publication->authors = $CI->author_db->getForPublication($R->pub_id, 'N');
    $publication->editors = $CI->author_db->getForPublication($R->pub_id, 'Y');

    //check if this publication was bookmarked by the logged user
    $Q = $CI->db->query("SELECT * FROM userbookmarklists WHERE user_id = ".$userlogin->userId()." AND pub_id=".$R->pub_id);
    if ($Q->num_rows()>0) {
        $publication->isBookmarked = True;
    }
    
    return $publication;
  }

  function getFromPost()
  {
        $CI = &get_instance();
    //we retrieve the following fields
    $fields = array('pub_id',
    'user_id',
    //'specialchars', DR: you shouldn't get this one from post, as it is not present in the post data. It is calculated anew every add or update.
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
    'editors'
    );

    $publication = new Publication;


    foreach ($fields as $key)
    {
      $publication->$key = $CI->input->post($key);
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
    if ($publication->authors)
    {
      $authors_array    = $CI->parsecreators->parse(preg_replace('/[\r\n\t]/', ' and ', $publication->authors));
      $authors          = array();
      foreach ($authors_array as $author)
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
    }

    //parse the editors
    if ($publication->editors)
    {
      $authors_array    = $CI->parsecreators->parse(preg_replace('/[\r\n\t]/', ' and ', $publication->editors));
      $authors          = array();
      foreach ($authors_array as $author)
      {
        $author_db      = $CI->author_db->getByExactName($author['firstname'], $author['von'], $author['surname']);
        if ($author_db != null)
        {
          $authors[]      = $author_db;
        }
        else
        {
          $author_db     = $CI->author_db->setByName($author['firstname'], $author['von'], $author['surname']);
          $authors[]  = $author_db;
        }
      }

      $publication->editors = $authors;
    }
    return $publication;
  }

    /** Return an array of Publication objects that crossref the given publication. 
    Will return only accessible publications (i.e. wrt access_levels). This method can therefore
    not be used to e.g. update crossrefs for a changed bibtex id. */
    function getXRefPublicationsForPublication($bibtex_id) {
        $CI = &get_instance();
        $result = array();
        if (trim($bibtex_id)=='')return $result;
        $Q = $CI->db->getwhere('publication', array('crossref' => $bibtex_id));
        foreach ($Q->result() as $row) {
            $next  =$this->getByID($row->pub_id);
            if ($next != null) {
                $result[] = $next;
            }
        }
        return $result;
    }

  
  function add($publication)
  {
        $CI = &get_instance();
        //check access rights (!)
    $userlogin = getUserLogin();
    if (    (!$userlogin->hasRights('publication_edit'))
        ) 
    {
        appendErrorMessage('Add publication: insufficient rights.<br/>');
        return;
    }        
    
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
    
    $data['user_id'] = $userlogin->userId();
  

    //insert into database using active record helper
    $CI->db->insert('publication', $data);
    
    //update this publication's pub_id
    $publication->pub_id = $CI->db->insert_id();
    
    
    //check whether Keywords are already available, if not, add them to the database
    //keywords are in an array, the keys are the keyword_id.
    //If no key the keyword still has to be added.
    if (is_array($publication->keywords)) //we bypass the ->getKeywords() function here, it would try to retrieve from DB.
    {
      $publication->keywords  = $CI->keyword_db->ensureKeywordsInDatabase($publication->keywords);
    
      foreach ($publication->keywords as $keyword_id => $keyword)
      {
        $data = array('pub_id' => $publication->pub_id, 'keyword_id' => $keyword_id);
        $CI->db->insert('publicationkeywordlink', $data);
      }
    }
    
    //add authors
    if (is_array($publication->authors))
      $publication->authors   = $CI->author_db->ensureAuthorsInDatabase($publication->authors);
      
    $rank = 1;
    foreach ($publication->authors as $author)
    {
      $data = array('pub_id'    => $publication->pub_id,
                    'author_id' => $author->author_id,
                    'rank'      => $rank,
                    'is_editor' => 'N');
      $CI->db->insert('publicationauthorlink', $data);
      $rank++;
    }
    
    //add editors
    if (is_array($publication->editors))
      $publication->editors   = $CI->author_db->ensureAuthorsInDatabase($publication->editors);
    
    $rank = 1;
    foreach ($publication->editors as $author)
    {
      $data = array('pub_id'    => $publication->pub_id,
                    'author_id' => $author->author_id,
                    'rank'      => $rank,
                    'is_editor' => 'Y');
      $CI->db->insert('publicationauthorlink', $data);
      $rank++;
    }
    
    //subscribe to topic 1
    $data = array('pub_id'      => $publication->pub_id,
                  'topic_id'    => 1);
    $CI->db->insert('topicpublicationlink', $data);

    //also fix bibtex_id mappings
	refreshBibtexIdLinks();
    $CI->accesslevels_lib->initPublicationAccessLevels($publication);
    return $publication;
  }
  
  function update($publication)
  {
        $CI = &get_instance();
    //check access rights (by looking at the original publication in the database, as the POST
    //data might have been rigged!)
    $userlogin  = getUserLogin();
    $oldpublication = $this->getByID($publication->pub_id);
    if (    ($oldpublication == null) 
         ||
            (!$userlogin->hasRights('publciation_edit'))
         || 
            (!$CI->accesslevels_lib->canEditObject($oldpublication))
        ) 
    {
        appendErrorMessage('Edit publication: insufficient rights. publication_db.update<br/>');
        return $oldpublication;
    }

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

    //[DR:] line below commented out: the user id should not change when updating! the owner always stays the same!
    //$data['user_id'] = $userlogin->userId();
  
  
    //insert into database using active record helper
    $CI->db->where('pub_id', $publication->pub_id);
    $CI->db->update('publication', $data);
    
    
    //remove old keyword links
    $CI->db->delete('publicationkeywordlink', array('pub_id' => $publication->pub_id)); 
    
    //check whether Keywords are already available, if not, add them to the database
    //keywords are in an array, the keys are the keyword_id.
    //If no key the keyword still has to be added.
    if (is_array($publication->keywords)) //we bypass the ->getKeywords() function here, it would try to retrieve from DB.
    {
      $publication->keywords  = $CI->keyword_db->ensureKeywordsInDatabase($publication->keywords);
    
      foreach ($publication->keywords as $keyword_id => $keyword)
      {
        $data = array('pub_id' => $publication->pub_id, 'keyword_id' => $keyword_id);
        $CI->db->insert('publicationkeywordlink', $data);
      }
    }
    
    //remove old author and editor links
    $CI->db->delete('publicationauthorlink', array('pub_id' => $publication->pub_id)); 
    
    //add authors
    if (is_array($publication->authors))
    {
      $publication->authors   = $CI->author_db->ensureAuthorsInDatabase($publication->authors);
      
      $rank = 1;
      foreach ($publication->authors as $author)
      {
        $data = array('pub_id'    => $publication->pub_id,
                      'author_id' => $author->author_id,
                      'rank'      => $rank,
                      'is_editor' => 'N');
        $CI->db->insert('publicationauthorlink', $data);
        $rank++;
      }
    }
    
    //add editors
    if (is_array($publication->editors))
    {
      $publication->editors   = $CI->author_db->ensureAuthorsInDatabase($publication->editors);
    
      $rank = 1;
      foreach ($publication->editors as $author)
      {
        $data = array('pub_id'    => $publication->pub_id,
                      'author_id' => $author->author_id,
                      'rank'      => $rank,
                      'is_editor' => 'Y');
        $CI->db->insert('publicationauthorlink', $data);
        $rank++;
      }
    }

    //changed bibtex_id?
    if ($oldpublication->bibtex_id != $publication->bibtex_id) {
        //fix all crossreffing notes
        $CI->note_db->changeAllCrossrefs($publication->pub_id, $publication->bibtex_id);
        //fix all crossreffing pubs
        $this->changeAllCrossrefs($publication->pub_id, $oldpublication->bibtex_id, $publication->bibtex_id);
		refreshBibtexIdLinks();
    }
    
    
    return $publication;
  }

  function validate($publication)
  {
        $CI = &get_instance();
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

  function getForTopic($topic_id,$page=0)
  {
    $limit = '';
    if ($page>-1) {
        $userlogin = getUserLogin();
        $liststyle= $userlogin->getPreference('liststyle');
        if ($liststyle>0) {
            $limit = ' LIMIT '.$liststyle.' OFFSET '.($page*$liststyle);
        }
    }
    $CI = &get_instance();
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $CI->db->query("SELECT DISTINCT publication.* FROM publication, topicpublicationlink
    WHERE topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
    AND publication.pub_id = topicpublicationlink.pub_id
    ORDER BY actualyear, cleantitle".$limit);

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
  function getCountForTopic($topic_id) {
    $CI = &get_instance();
    
    $Q = $CI->db->query("SELECT DISTINCT publication.* FROM publication, topicpublicationlink
    WHERE topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
    AND publication.pub_id = topicpublicationlink.pub_id");
    return $Q->num_rows();
  }  
  
  function getForAuthor($author_id,$page=0)
  {
    $limit = '';
    if ($page>-1) {
        $userlogin = getUserLogin();
        $liststyle= $userlogin->getPreference('liststyle');
        if ($liststyle>0) {
            $limit = ' LIMIT '.$liststyle.' OFFSET '.($page*$liststyle);
        }
    }
    $CI = &get_instance();
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $CI->db->query("SELECT DISTINCT publication.* FROM publication, publicationauthorlink
    WHERE publicationauthorlink.author_id = ".$CI->db->escape($author_id)."
    AND publication.pub_id = publicationauthorlink.pub_id
    ORDER BY actualyear, cleantitle".$limit);

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
  function getCountForAuthor($author_id) {
    $CI = &get_instance();
    
    $Q = $CI->db->query("SELECT DISTINCT publication.* FROM publication, publicationauthorlink
    WHERE publicationauthorlink.author_id = ".$CI->db->escape($author_id)."
    AND publication.pub_id = publicationauthorlink.pub_id");
    return $Q->num_rows();
  }  
  /** Return a list of publications for the bookmark list of the logged user */
  function getForBookmarkList($page=0)
  {
    $userlogin = getUserLogin();
    $limit = '';
    if ($page>-1) {
        $liststyle= $userlogin->getPreference('liststyle');
        if ($liststyle>0) {
            $limit = ' LIMIT '.$liststyle.' OFFSET '.($page*$liststyle);
        }
    }
    $CI = &get_instance();
    
    $Q = $CI->db->query("SELECT DISTINCT publication.* FROM publication, userbookmarklists
    WHERE userbookmarklists.user_id=".$userlogin->userId()."
    AND   userbookmarklists.pub_id=publication.pub_id
    ORDER BY actualyear, cleantitle".$limit);

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
  function getCountForBookmarkList() {
    $CI = &get_instance();
    
    $Q = $CI->db->query("SELECT DISTINCT publication.* FROM publication, userbookmarklists
    WHERE userbookmarklists.user_id=".$userlogin->userId()."
    AND   userbookmarklists.pub_id=publication.pub_id");
    return $Q->num_rows();
  }
    /** change the crossref of all affected publications to reflect a change of the bibtex_id of the given publication.
    Note: this method does NOT make use of getByID($pub_id), because one should also change the referring 
    crossref field of all publications that are inaccessible through getByID($pub_id) due to access level 
    limitations. */
    function changeAllCrossrefs($pub_id, $old_bibtex_id, $new_bibtex_id) 
    {
        $CI = &get_instance();
        if (trim($old_bibtex_id) == '')return;
        $Q = $CI->db->getwhere('publication',array('crossref'=>$old_bibtex_id));
        //update is done here, instead of using the update function, as some of the affected publications
        // may not be accessible for this user
        foreach ($Q->result() as $R) {
            $updatefields =  array('crossref'=>$new_bibtex_id);
            $CI->db->query(
                $CI->db->update_string("publication",
                                             $updatefields,
                                             "pub_id=".$R->pub_id)
                                  );
    		if (mysql_error()) {
    		    appendErrorMessage("Failed to update the bibtex-id in publication ".$R->pub_id."<br/>");
        	}
        }
    }

}
?>