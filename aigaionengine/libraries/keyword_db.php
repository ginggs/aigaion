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
      return $R->keyword;
    }
    else
      return null;
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
        $result[] = $R->keyword;
      }
    }

    return $result;
  }
}
?>