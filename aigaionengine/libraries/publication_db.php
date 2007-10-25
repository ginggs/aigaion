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
    $Q = $CI->db->getwhere('publication',array('pub_id'=>$pub_id));

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
    $Q = $CI->db->getwhere('publication',array('bibtex_id'=>$bibtex_id));

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
        $Q = $CI->db->getwhere('publication',array('bibtex_id'=>$R->crossref));

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
    $Q = $CI->db->getwhere('userbookmarklists',array('user_id'=>$userlogin->userId(),'pub_id'=>$R->pub_id));
    if ($Q->num_rows()>0) {
        $publication->isBookmarked = True;
    }
    
    return $publication;
  }

  function getFromPost($suffix = "")
  {
    $CI = &get_instance();
    //we retrieve the following fields
    $fields = array('pub_id',
    'user_id',
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
      $publication->$key = trim($CI->input->post($key.$suffix));
    }

    //parse the keywords
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
        $CI->load->helper('bibtexutf8');
        $CI->load->helper('utf8_to_ascii');
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
                    'cleanauthor',
                    'cleanjournal',
                    'actualyear',
                    'mark', //always 0 by default; mark value will only be changed in a separate method so it doesn't need to get a value ehre or in the update method
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
      //remove bibchars
        $publication->$field = bibCharsToUtf8FromString($publication->$field);
    }

    //create cleantitle and cleanjournal
    $publication->cleantitle    = utf8_to_ascii($publication->title);
    $publication->cleanjournal    = utf8_to_ascii($publication->journal);
    $publication->cleanauthor = ""; //will be filled later, after authors have been done
    
    //get actual year
    if (trim($publication->year) == '')
    {
      if (trim($publication->crossref) != '')
      {
        $xref_pub = $CI->publication_db->getByBibtexID($publication->crossref);
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

    //start building up clean author value
    $publication->cleanauthor = "";
    
    //add authors
    if (is_array($publication->authors)) {
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
        $publication->cleanauthor .= ' '.$author->cleanname;
      }
    }
    
    //add editors
    if (is_array($publication->editors)) {
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
        $publication->cleanauthor .= ' '.$author->cleanname;
      }
    }
    
    //update cleanauthor value
    $CI->db->where('pub_id', $publication->pub_id);
    $CI->db->update('publication', array('cleanauthor'=>trim($publication->cleanauthor)));
    
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
        $CI->load->helper('bibtexutf8');
        $CI->load->helper('utf8_to_ascii');
    //check access rights (by looking at the original publication in the database, as the POST
    //data might have been rigged!)
    $userlogin  = getUserLogin();
    $oldpublication = $this->getByID($publication->pub_id);
    if (    ($oldpublication == null) 
         ||
            (!$userlogin->hasRights('publication_edit'))
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
    ); //'mark' doesn't need to get updated as that is done through other methods.
  
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
      //remove bibchars
        $publication->$field = bibCharsToUtf8FromString($publication->$field);
    }
    
    //create cleantitle and cleanjournal
    $publication->cleantitle    = utf8_to_ascii($publication->title);
    $publication->cleanjournal    = utf8_to_ascii($publication->journal);
    
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
  
    //insert into database using active record helper. 
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
    
    //start building up clean author value
    $publication->cleanauthor = "";
    
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
        $publication->cleanauthor .= ' '.$author->cleanname;
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
        $publication->cleanauthor .= ' '.$author->cleanname;
      }
    }

    //update cleanauthor value
    $CI->db->where('pub_id', $publication->pub_id);
    $CI->db->update('publication', array('cleanauthor'=>trim($publication->cleanauthor)));


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

    /** delete given object. where necessary cascade. Checks for edit and read rights on this object and all cascades
    in the _db class before actually deleting. */
    function delete($publication) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        //collect all cascaded to-be-deleted-id's: none
        //check rights
        //check, all through the cascade, whether you can read AND edit that object
        if (!$userlogin->hasRights('publication_edit')
            ||
            !$CI->accesslevels_lib->canEditObject($publication)
            ) {
            //if not, for any of them, give error message and return
            appendErrorMessage('Cannot delete publication: insufficient rights');
            return false;
        }
        if (empty($publication->pub_id)) {
            appendErrorMessage('Cannot delete publication: erroneous ID');
            return false;
        }
        //no delete for object with children. check through tables, not through object
        #NOTE: if we want to allow delete of publications with notes and attachments, we should make sure
        #that current user can edit/delete all those notes and attachments!
        $Q = $CI->db->getwhere('attachments',array('pub_id'=>$publication->pub_id));
        if ($Q->num_rows()>0) {
            //check if you can delete attachments 
            foreach ($Q->result() as $row) {
                $attachment = $CI->attachment_db->getByID($row->att_id);
                if ($attachment == null) {
                    appendErrorMessage('Cannot delete publication: it contains some attachments that you do not have permission to delete<br/>');
                    return false;
                }
                if (!$CI->accesslevels_lib->canEditObject($attachment)) {
                    appendErrorMessage('Cannot delete publication: it contains some attachments that you do not have permission to delete<br/>');
                    return false;
                }
            }
        }
        $Q = $CI->db->getwhere('notes',array('pub_id'=>$publication->pub_id));
        if ($Q->num_rows()>0) {
            //check if you can delete notes
            foreach ($Q->result() as $row) {
                $note = $CI->note_db->getByID($row->note_id);
                if ($note == null) {
                    appendErrorMessage('Cannot delete publication: it contains some notes that you do not have permission to delete<br/>');
                    return false;
                }
                if (!$CI->accesslevels_lib->canEditObject($note)) {
                    appendErrorMessage('Cannot delete publication: it contains some notes that you do not have permission to delete<br/>');
                    return false;
                }
            }
        }
        $Q = $CI->db->getwhere('attachments',array('pub_id'=>$publication->pub_id));
        if ($Q->num_rows()>0) {
            //do actual delete of attachments, AFTER you know it is OK to proceed with delete
            foreach ($Q->result() as $row) {
                $attachment = $CI->attachment_db->getByID($row->att_id);
                $attachment->delete();
            }
        }
        $Q = $CI->db->getwhere('notes',array('pub_id'=>$publication->pub_id));
        if ($Q->num_rows()>0) {
            //do actual delete of notes, AFTER you know it is OK to proceed with delete
            foreach ($Q->result() as $row) {
                $note = $CI->note_db->getByID($row->note_id);
                $note->delete();
            }
        }
        //otherwise, delete all dependent objects by directly accessing the rows in the table 
        $CI->db->delete('publication',array('pub_id'=>$publication->pub_id));
        //delete links
        $CI->db->delete('topicpublicationlink',array('pub_id'=>$publication->pub_id));
        $CI->db->delete('publicationauthorlink',array('pub_id'=>$publication->pub_id));
        $CI->db->delete('publicationkeywordlink',array('pub_id'=>$publication->pub_id));
        $CI->db->delete('userbookmarklists',array('pub_id'=>$publication->pub_id));
        $CI->db->delete('userpublicationmark',array('pub_id'=>$publication->pub_id));
        //add the information of the deleted rows to trashcan(time, data), in such a way that at least manual reconstruction will be possible
        return true;
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

  function getForTopic($topic_id,$order='')
  {
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type, cleanjournal, actualyear, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor';
        break;
    }
    $CI = &get_instance();
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
    WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
    AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id
    ORDER BY ".$orderby);

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
  function getUnassigned($order='')
  {
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type, cleanjournal, actualyear, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor';
        break;
    }
    $CI = &get_instance();
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    ///////////////////
    //DR: this query is copied from another method - needs to be modified to retrieve all unassigned papers.
    ///////////////////
//    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
//    WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
//    AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id
//    ORDER BY ".$orderby);

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
  function getForAuthor($author_id,$order='')
  {
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type, cleanjournal, actualyear, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor';
        break;
    }
    $CI = &get_instance();
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."publicationauthorlink
    WHERE ".AIGAION_DB_PREFIX."publicationauthorlink.author_id = ".$CI->db->escape($author_id)."
    AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id
    ORDER BY ".$orderby);

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
  function getForBookmarkList($order='')
  {
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type, cleanjournal, actualyear, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor';
        break;
    }
    $CI = &get_instance();
    $userlogin = getUserLogin();
    
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."userbookmarklists
    WHERE ".AIGAION_DB_PREFIX."userbookmarklists.user_id=".$CI->db->escape($userlogin->userId())."
    AND   ".AIGAION_DB_PREFIX."userbookmarklists.pub_id=".AIGAION_DB_PREFIX."publication.pub_id
    ORDER BY ".$orderby);

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
            $CI->db->update('publication', $updatefields, array('pub_id'=>$R->pub_id));
    		if (mysql_error()) {
    		    appendErrorMessage("Failed to update the bibtex-id in publication ".$R->pub_id."<br/>");
        	}
        }
    }

    /** returns all accessible publications, as a map (id=>publication) */
    function getAllPublicationsAsMap() {
        $CI = &get_instance();
        $result = array();
        $CI->db->orderby('bibtex_id');
        $Q = $CI->db->get('publication');
        foreach ($Q->result() as $R) {
            $next = $this->getFromRow($R);
            if ($next != null) {
                $result[$next->pub_id] = $next;
            }
        }
        return $result;
    }
    /** returns all accessible publications from a topic, as a map (id=>publication), for export purposes */
    function getForTopicAsMap($topic_id) {
        $CI = &get_instance();
        $result = array();
        $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
        WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
        AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id
        ORDER BY bibtex_id");
    
        foreach ($Q->result() as $row)
        {
          $next = $this->getFromRow($row);
          if ($next != null)
          {
            $result[$next->pub_id] = $next;
          }
        }
        return $result;
    }
  /** Return a list of publications for the bookmark list of the logged user, as a map (id=>publication), for export purposes  */
  function getForBookmarkListAsMap()
  {
    $CI = &get_instance();
    $userlogin = getUserLogin();
    
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."userbookmarklists
    WHERE ".AIGAION_DB_PREFIX."userbookmarklists.user_id=".$CI->db->escape($userlogin->userId())."
    AND   ".AIGAION_DB_PREFIX."userbookmarklists.pub_id=".AIGAION_DB_PREFIX."publication.pub_id
    ORDER BY bibtex_id");

    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[$next->pub_id] = $next;
      }
    }
    return $result;
  }    
  function getForAuthorAsMap($author_id)
  {
    $CI = &get_instance();
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."publicationauthorlink
    WHERE ".AIGAION_DB_PREFIX."publicationauthorlink.author_id = ".$CI->db->escape($author_id)."
    AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id
    ORDER BY bibtex_id");

    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[$next->pub_id] = $next;
      }
    }

    return $result;
  }
  
  
    /** splits the given publication map (id=>publication) into two maps [normal,xref],
    where xref is the map with all crossreffed publications (including those not present
    in the original map), and normal is the map with all other publications. 
    If $merge is true, all crossref entries will additionally be merged into their referring entries.
    */
    function resolveXref($publicationMap, $merge=false) {
        $normal=$publicationMap;
        $xref=array();
        foreach ($publicationMap as $pub_id=>$publication) {
            //$publication null? then it was apparently a crossref that was moved to the xref array; skip
            if ($publication==null) {
                continue;
            }
            //has crossref? 
            if (trim($publication->crossref)!='') {
                //get publication for crossref
                $xrefpub = $this->getByBibtexID($publication->crossref);
                if ($xrefpub!=null) {
                    //  find crossref in xref; 
                    if (!array_key_exists($xrefpub->pub_id,$xref)) {
                        if (array_key_exists($xrefpub->pub_id,$normal)) {
                            //  if not exists in xref find in and move from $normal to $xref; 
                            $xref[$xrefpub->pub_id]=$normal[$xrefpub->pub_id];
                            $normal[$xrefpub->pub_id] = null;
                        } else {
                            //  if not there either get from database and add to $xref
                            $xref[$publication->crossref] = $xrefpub;
                        }
                        if ($merge) {
                            appendMessage('resolveXref: merge xref into publication!<br/>');
                        }
                    }
                } //else: don't do a thing; leave the publication in $normal where it was put in the first place
            }
        }
        //finally, remove all entries from $normal that were set to null
        $finalnormal=array();
        foreach ($normal as $pub_id=>$publication) {
            if ($publication!=null) {
                $finalnormal[$pub_id] = $publication;
            }
        }
        return array($finalnormal,$xref);
    }
    
    /*
    reviewTitle($publication) -> checks for duplicate titles.
    */
    function reviewTitle($publication) {
      $CI = &get_instance();
      $CI->load->helper('bibtexutf8');
      $CI->load->helper('utf8_to_ascii');
      
      $publication->cleantitle = bibCharsToUtf8FromString($publication->title);
      $publication->cleantitle = utf8_to_ascii($publication->cleantitle);

      $Q = $CI->db->query("SELECT DISTINCT cleantitle FROM ".AIGAION_DB_PREFIX."publication
                           WHERE cleantitle = ".$CI->db->escape($publication->cleantitle));
  
      $num_rows = $Q->num_rows();
      if ($num_rows > 0)
      {
        return "A publication with the same title exists. Please make sure that the publication you are importing is not already in the database.";
      }
      else return null;
    }
    
    /*
    reviewBibtexID($publication) -> checks for duplicate cite_id. If the publication ID is set, one duplicate is allowed.
    */
    function reviewBibtexID($publication) {
      $CI = &get_instance();
      $Q = $CI->db->query("SELECT pub_id FROM ".AIGAION_DB_PREFIX."publication
                           WHERE bibtex_id = ".$CI->db->escape($publication->bibtex_id));
  
      $num_rows = $Q->num_rows();
      if ($num_rows > 0)
      {
        foreach ($Q->result() as $row)
        {
          if ($row->pub_id != $publication->pub_id)
          {
            $message = "The cite id is not unique, please choose another cite id.";
            
            $Q = $CI->db->query("SELECT bibtex_id FROM ".AIGAION_DB_PREFIX."publication
                                 WHERE bibtex_id LIKE ".$CI->db->escape($publication->bibtex_id."%"));
            $num_rows = $Q->num_rows();
            
            if ($num_rows > 1)
            {
              $list = "";
              foreach ($Q->result() as $row)
              {
                $list .= "<li>".$row->bibtex_id."</li>\n";
              }
              if ($list != "")
                $message .= "<br/>\nSimilar cite ids:<br/><ul>\n".$list."</ul>\n";
              
              return $message;  
            }
          }
        }
      }
      return null;
    }
    
    /** return the mark given to the publication by the user, or '' if the publication was read but not marked, 
    or -1 if the publication wasn't read */
    function getUserMark($pub_id,$user_id) {
        $CI = &get_instance();
        $Q = $CI->db->getwhere('userpublicationmark',array('pub_id'=>$pub_id,'user_id'=>$user_id));
        if ($Q->num_rows()==0) {
            return -1;
        }
        $R = $Q->row();
        if ($R->read == 'n') {
            return -1;
        }
        return $R->mark;
    }
    function read($mark,$oldmark,$pub_id,$user_id) {
        $CI = &get_instance();
        //set proper mark for user
        $Q = $CI->db->delete("userpublicationmark",array('pub_id'=>$pub_id,'user_id'=>$user_id));
        $Q = $CI->db->query("INSERT INTO ".AIGAION_DB_PREFIX."userpublicationmark 
                                (`user_id`,`pub_id`,`hasread`,`mark`)
                                VALUES
                                (".$user_id.",".$pub_id.",'y','".$mark."')");
        //and now fix total mark
        if ($oldmark<0) {
            $oldmark = 0;
        }
        //subtract old mark from current mark for given publication
        $CI->db->select('mark');
        $Q = $CI->db->getwhere('publication',array('pub_id'=>$pub_id));
        $R = $Q->row();
        $Q = $CI->db->update('publication',array('mark'=>($R->mark-$oldmark+$mark)),array('pub_id'=>$pub_id));
    }
    function unread($oldmark,$pub_id,$user_id) {
        $CI = &get_instance();
        //set proper mark for user
        $Q = $CI->db->query("UPDATE ".AIGAION_DB_PREFIX."userpublicationmark 
                                SET `hasread`='n' 
                              WHERE pub_id=".$pub_id." 
                                    AND user_id=".$user_id);
        //and now fix total mark
        if ($oldmark>0) {
            //subtract old mark from current mark for given publication
            $CI->db->select('mark');
            $Q = $CI->db->getwhere('publication',array('pub_id'=>$pub_id));
            $R = $Q->row();
            $Q = $CI->db->update('publication',array('mark'=>($R->mark-$oldmark)),array('pub_id'=>$pub_id));
        }
    }
}
?>