<?php
/** This class regulates the database access for Keywords. */
class Keyword_db {

  var $CI = null;

  function Keyword_db()
  {
    $this->CI = &get_instance();
  }

  function getByID($keyword_id)
  {
    //retrieve one publication row
    $Q = $this->CI->db->query("SELECT * FROM keywords WHERE keyword_id = ".$this->CI->db->escape($keyword_id));

    if ($Q->num_rows() > 0)
    {
      //load the publication
      $R = $Q->row();
      return array($R->keyword_id => $R->keyword);
    }
    else
      return null;
  }
  
  function getByKeyword($keyword)
  {
    $Q = $this->CI->db->query("SELECT * FROM keywords WHERE keyword = ".$this->CI->db->escape($keyword));

    if ($Q->num_rows() > 0)
    {
      //load the publication
      $R = $Q->row();
      return array($R->keyword_id => $R->keyword);
    }
    else
      return null;
  }
  
  function getKeywordsLike($keyword)
  {
    //select all keywords from the database that start with the characters as in $keyword
    $Q = $this->CI->db->query('SELECT * FROM keywords 
                           WHERE keyword LIKE "'.addslashes($keyword).'%" 
                           ORDER BY keyword');
    
    //retrieve results or fail
    $result = array();
    foreach ($Q->result() as $row)
    {
      $result[$row->keyword_id] = $row->keyword;
    }
    return $result;
  }
  
  function getKeywordsForPublication($pub_id)
  {
    $Q = $this->CI->db->query("SELECT keywords.* FROM keywords, publicationkeywordlink
                               WHERE publicationkeywordlink.pub_id = ".$this->CI->db->escape($pub_id)." 
                                 AND publicationkeywordlink.keyword_id = keywords.keyword_id
                               ORDER BY keywords.keyword");
    $result = array();

    if ($Q->num_rows() > 0)
    {
      foreach ($Q->result() as $R)
      {
        $result[$R->keyword_id] = $R->keyword;
      }
    }

    return $result;
  }
  
  function add($keyword)
  {
    $data = array('keyword' => $keyword);
    
    $this->CI->db->insert('keywords', $data);
    
    $keyword_id = $this->CI->db->insert_id();
    
    if ($keyword_id)
    {
      //load the publication
      return array($keyword_id => $keyword);
    }
    else
      return null;
  }
  
  //ensureKewordsInDatabase($keywords) checks if all keywords in the array 
  //are already in the database. If not, it will add them.
  function ensureKeywordsInDatabase($keywords)
  {
    if (!is_array($keywords))
      return null;
    
    $result = array();
    foreach($keywords as $keyword_id => $keyword)
    {
      $current      = $this->getByKeyword($keyword);
      if ($current == null)
        $current    = $this->add($keyword);
      
      $result[]     = $current;
    }
    
    return $result;
  }
  
  function review($keywords)
  {
    if (!is_array($keywords))
      return null;
    
    $result_message   = "";
    
    //get database keyword array
    $db_keywords = array();
    $Q = $this->CI->db->query("SELECT * FROM keywords ORDER BY keyword");
    if ($Q->num_rows() > 0)
    {
      foreach ($Q->result() as $R)
      {
        $db_keywords[$R->keyword_id] = strtolower($R->keyword);
      }
    }
    
    //check availability of the keywords in the database
    foreach ($keywords as $keyword)
    {
      $keyword_low  = strtolower($keyword);
      $keyword_id   = array_search($keyword_low, $db_keywords);
      
      //is the keyword already in the db?
      if (!is_numeric($keyword_id))
      {
        //not found in the database, so check for similar keywords
        $db_distances = array();
        foreach ($db_keywords as $keyword_id => $db_keyword)
        {
          $distance = levenshtein($db_keyword, $keyword_low);
          if ($distance < 3)
            $db_distances[$keyword_id] = $distance;
        }
        
        //sort while keeping key relationship
        asort($db_distances, SORT_NUMERIC);
        
        //are there similar keywords?
        if (count($db_distances) > 0)
        {
          $result_message .= "Found similar keywords for <b>&quot;".$keyword."&quot;</b>:<br/>\n";
          $result_message .= "<ul>\n";
          foreach($db_distances as $key => $value)
          {
            $result_message .= "<li>".$db_keywords[$key]."</li>\n";
          }
          $result_message .= "</ul>\n";
        }
        //when no similar keywords are found, we add the unknown keyword to the database
        else
        {
          $this->add($keyword);
        }
      }
    }
    if ($result_message != "")
    {
      $result_message .= "Please review the entered keywords.<br/>\n";
      return $result_message;
    }
    else
      return null;
  }
}
?>