<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/** This class regulates the database access for Authors. Several accessors are present that return an Author or 
array of Authors. */
class Author_db {
  
  
    function Author_db()
    {
    }

  function getByID($author_id)
  {
        $CI = &get_instance();
    //retrieve one author row	  
    $Q = $CI->db->getwhere('author',array('author_id' =>$author_id));
    if ($Q->num_rows() == 1) 
    {
      //load the author
      return $this->getFromRow($Q->row());
    }
    else 
      return false;
  }
  
  function getByExactName($firstname = "", $von = "", $surname = "")
  {
        $CI = &get_instance();
    //check if there is input, if not fail
    if (!($firstname || $von || $surname))
      return false;
      
    //prepare the name
    $name = $surname;
    if ($von != '')
      ($name != '') ? $name .= ", ".$von : $name = $von;
    
    if ($firstname != '')
      ($name != '') ? $name .= ", ".$firstname : $name = $firstname;
    
    //strip out any special characters
    $name = stripBibCharsFromString($name);
    $name = stripQuotesFromString($name);
    
    //do the query
    $Q = $CI->db->getwhere('author',array('cleanname' => $name));
  
    //only when a single result is found, load the result. Else fail
    if ($Q->num_rows() == 1)
      return $this->getFromRow($Q->row());
    else
      return null;
  }
  
  function setByName($firstname = "", $von = "", $surname = "")
  {
        $CI = &get_instance();
    //check if there is input, if not fail
    if (!($firstname || $von || $surname))
      return null;
    
    //pack into array
    $authorArray = array("firstname" => $firstname, "von" => $von, "surname" => $surname);
    
    //load from array
    return $this->getFromArray($authorArray);
  }
  
  function getFromArray($authorArray)
  {
    return $this->getFromRow($authorArray);
  }
  
  function getFromRow($R)
  {
    $author = new Author;
    foreach ($R as $key => $value)
    {
        $author->$key = $value;
    }
    return $author;
  }
  
  function getFromPost()
  {
        $CI = &get_instance();
    //create the array with variables to retrieve
    $fields = array('author_id',
                    //'specialchars', no! specialchars var is not set in edit form.
                    'cleanname',
                    'firstname',
                    'von',
                    'surname',
                    'email',
                    'url',
                    'institute'
                   );
    
    $author = new Author;
    
    //retrieve all fields
    foreach ($fields as $key)
    {
      $author->$key = $CI->input->post($key);
    }
    return $author;
  }
  
  function getAuthorCount() {
  	$CI = &get_instance();
  	$CI->db->select("COUNT(*)");
    $Q = $CI->db->get("author");
    $R = $Q->row_array();
    return $R['COUNT(*)'];

  }
  function add($author)
  {
        $CI = &get_instance();
    //fields that are to be submitted
    $fields = array('specialchars',
                    'cleanname',
                    'firstname',
                    'von',
                    'surname',
                    'email',
                    'url',
                    'institute'
                   );
    
    //check for specialchars
    $specialfields = array('firstname', 'von', 'surname', 'institute');
    foreach ($specialfields as $field)
    {
      if (findSpecialCharsInString($author->$field))
        $author->specialchars = 'TRUE';
    }
    
    //create cleanname
    $cleanname = stripBibCharsFromString($author->getName('lvf'));
    $cleanname = stripQuotesFromString($cleanname);
    $author->cleanname = $cleanname;
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field)
      $data[$field] = $author->$field;
    
    //insert into database using active record helper
    $CI->db->insert('author', $data);
    
    //update this author's author_id
    $author->author_id = $CI->db->insert_id();
    return $author;
  }
  
  function update($author)
  {
        $CI = &get_instance();
    //fields that are to be updated
    $fields = array('specialchars',
                    'cleanname',
                    'firstname',
                    'von',
                    'surname',
                    'email',
                    'url',
                    'institute'
                   );
    
    //check for specialchars
    $specialfields = array('firstname', 'von', 'surname', 'institute');
    foreach ($specialfields as $field)
    {
      if (findSpecialCharsInString($author->$field))
        $author->specialchars = 'TRUE';
    }
    
    //create cleanname
    $cleanname = stripBibCharsFromString($author->getName('lvf'));
    $cleanname = stripQuotesFromString($cleanname);
    $author->cleanname = $cleanname;
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field)
      $data[$field] = $author->$field;
    
    //update database using active record helper
    $CI->db->where('author_id', $author->author_id);
    $CI->db->update('author', $data);

    return $author;
  }
  
    /** delete given object. where necessary cascade. Checks for edit and read rights on this object and all cascades
    in the _db class before actually deleting. */
    function delete($author) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        //collect all cascaded to-be-deleted-id's: none
        //check rights
        //check, all through the cascade, whether you can read AND edit that object
        if (!$userlogin->hasRights('publication_edit')) {
            //if not, for any of them, give error message and return
            appendErrorMessage('Cannot delete author: insufficient rights');
            return;
        }
        if (empty($author->author_id)) {
            appendErrorMessage('Cannot delete author: erroneous ID');
            return;
        }
        //no delete for authors with publications. check through tables, not through object
        $Q = $CI->db->getwhere('publicationauthorlink',array('author_id'=>$author->author_id));
        if ($Q->num_rows()>0) {
            appendErrorMessage('Cannot delete author: still has publications (possibly invisible...)<br/>');
            return false;
        }
        //otherwise, delete all dependent objects by directly accessing the rows in the table 
        $CI->db->delete('authors',array('author_id'=>$author->author_id));
        //delete links
        $CI->db->delete('publicationauthorlink',array('author_id'=>$author->author_id));
        //add the information of the deleted rows to trashcan(time, data), in such a way that at least manual reconstruction will be possible
    }    
      
  function validate($author)
  {
        $CI = &get_instance();
    $validate_conditional = array();
    
    //we require at least the first or the surname
    $validate_conditional[] = 'firstname';
    $validate_conditional[] = 'surname';

    $validation_message   = '';
    $conditional_field_text = '';
    $conditional_validation = false;
    foreach ($validate_conditional as $key)
    {
      if (trim($author->$key) != '')
      {
        $conditional_validation = true;
      }
      $conditional_field_text .= $key.", ";
    }
    if (!$conditional_validation)
    {
      $validation_message .= "One of the fields ".$conditional_field_text." is required.<br/>\n";
    }
  
    if ($validation_message != '')
    {
      appendErrorMessage("Changes not committed:<br/>\n".$validation_message);
      return false;
    }
    else
      return true;
  }

  function deleteAuthor($author)
  {
        $CI = &get_instance();
    //only delete a valid object
    if ($author->author_id == 0)
      return false;
      
    //remove all links to this author
/*
TODO:
- remove publicationauthorlinks
- remove other (new?) authorlinks
*/
    //remove the actual author
    $this->db->where('author_id', $author->author_id);
    $this->db->delete('author');
    
    //if the delete was succesful, only one single row is affected
    //please note: mysql returns 0 affected rows, CI has a work-around
    //in the db class.
    if ($this->db->affected_rows() == 1)
      return true;
    else
      return false;
  }
  
  function getAllAuthors()
  {
        $CI = &get_instance();
    $result = array();
    
    //get all authors from the database, order by cleanname
    $CI->db->orderby('cleanname');
    $Q = $CI->db->get('author');
    
    //retrieve results or fail
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
  
  function getAuthorsLike($cleanname)
  {
        $CI = &get_instance();
    //select all authors from the database where the cleanname begins with the characters
    //as given in $cleanname
    $CI->db->orderby('cleanname');
    $CI->db->like('cleanname',$cleanname);
    $Q = $CI->db->get('author');
    
    //retrieve results or fail
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
  
  function getForPublication($pub_id, $is_editor = 'N')
  {
        $CI = &get_instance();
    $result = array();
    
    //retrieve authors and editors
    $Q = $CI->db->query("SELECT * FROM ".AIGAION_DB_PREFIX."author, ".AIGAION_DB_PREFIX."publicationauthorlink 
                           WHERE ".AIGAION_DB_PREFIX."author.author_id = ".AIGAION_DB_PREFIX."publicationauthorlink.author_id
                           AND ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id = ".$CI->db->escape($pub_id)."
                           AND ".AIGAION_DB_PREFIX."publicationauthorlink.is_editor = ".$CI->db->escape($is_editor)."
                           ORDER BY ".AIGAION_DB_PREFIX."publicationauthorlink.rank");
    
    //retrieve results or fail                       
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
  
  function ensureAuthorsInDatabase($authors)
  {
        $CI = &get_instance();
    if (!is_array($authors))
      return null;
      
    $result = array();
    foreach ($authors as $author)
    {
      $current      = $this->getByExactName($author->firstname, $author->von, $author->surname);
      if ($current == null)
        $current    = $this->add($author);
      
      $result[] = $author;
    }
    return $result;
  }
  
  function review($authors)
  {
        $CI = &get_instance();
    if (!is_array($authors))
      return null;
    
    $result_message   = "";
    
    //get database author array
    $CI->db->select('author_id, cleanname');
    $CI->db->orderby('cleanname');
    $Q = $CI->db->get('author');
    
    $db_cleanauthors = array();
    //retrieve results or fail                       
    foreach ($Q->result() as $R)
    {
      $db_cleanauthors[$R->author_id] = strtolower($R->cleanname);
    }
    
    
    //check availability of the authors in the database
    foreach ($authors as $author)
    {
      if ($this->getByExactName($author->firstname, $author->von, $author->surname) == null)
      {
        //no exact match, or more than one authors exist in the database
        
        //check on cleanname
        //create cleanname
        $cleanname = stripBibCharsFromString($author->getName('lvf'));
        $cleanname = strtolower(stripQuotesFromString($cleanname));
        $author->cleanname = $cleanname;
        
        $db_distances = array();
        foreach ($db_cleanauthors as $author_id => $db_author)
        {
          $distance = levenshtein($db_author, $cleanname);
          if (($distance < 3) && ($author_id != $author->author_id))
            $db_distances[$author_id] = $distance;
        }
        
        //sort while keeping key relationship
        asort($db_distances, SORT_NUMERIC);
        
        //are there similar keywords?
        if (count($db_distances) > 0)
        {
          $result_message .= "Found similar authors for <b>&quot;".$author->getName('lvf')."&quot;</b>:<br/>\n";
          $result_message .= "<ul>\n";
          foreach($db_distances as $key => $value)
          {
            $author = $this->getByID($key);
            $result_message .= "<li>".$author->getName('lvf')."</li>\n";
          }
          $result_message .= "</ul>\n";
        }
      }
    }
    if ($result_message != "")
    {
      $result_message .= "Please review the entered authors.<br/>\n";
      return $result_message;
    }
    else
      return null;
  }
}
?>