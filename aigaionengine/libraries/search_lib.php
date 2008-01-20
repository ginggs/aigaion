<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class provides calls for performing searchs both complex and simple.

Note, esp. for the simple search:
- search input is debibtexxed
- search input is transliterated from utf8 to ascii, as search takes place in clean fields

*/
class Search_lib {

  function Search_lib()
  {
  }

  /** A simple search on all types of data using a single string of query.
  Returns an array map ('type'=>$resultArray) like this:
  ('authors'=>$arrayOfAuthors,
  'topics'=>$arrayOfTopics,
  'keywords'=>$arrayOfKeywords,
  'publications_content'=>$arrayOfPubs,
  'publications_note'=>$arrayOfPubs,
  'publications_bibtex_id'=>$arrayOfPubs,
  ) */
  function simpleSearch($query) {
    $result = array();
    
    $keywordArray = $this->queryToKeywords($query);
    if (count($keywordArray)==0) {
      return $result;
    }

    $authorResult = $this->findAuthorsLike($keywordArray);
    if ($authorResult != null)
      $result['authors'] = $authorResult;
    unset($authorResult);
    
    $topicResult = $this->findTopicsLike($keywordArray);
    if ($topicResult != null)
      $result['topics'] = $topicResult;
    unset($topicResult);
    
    
    $keywordResult = $this->findKeywordsLike($keywordArray);
    if ($keywordResult != null)
      $result['keywords'] = $keywordResult;
    unset($keywordResult);
    
    
    $publicationResult = $this->findPublicationsLike($keywordArray);
    if ($publicationResult != null)
      $result['publications_titles'] = $publicationResult;
    unset($publicationResult);
    
    
    $publicationResult = $this->findPublicationsCiteIDLike($keywordArray);
    if ($publicationResult != null)
      $result['publications_bibtex'] = $publicationResult;
    unset($publicationResult);
    
    
    $publicationResult = $this->findPublicationsNotesLike($keywordArray);
    if ($publicationResult != null)
      $result['publications_notes'] = $publicationResult;
    unset($publicationResult);
    
    
    $publicationResult = $this->findPublicationsAbstractsLike($keywordArray);
    if ($publicationResult != null)
      $result['publications_abstracts'] = $publicationResult;
    unset($publicationResult);
    
    //return result
    return $result;
  }

  //find author hits on 'like' clause for cleanname
  //DR note: here we could also enforce that the author should publish on a subscribed topic. Would make things a lot slower,
  //but I think people will want this.
  function findAuthorsLike($query)
  {
    $CI = &get_instance();
    
    if (!is_array($query))
      $keywordArray = $this->queryToKeywords($query);
    else
      $keywordArray = $query;
      
    $authorQ = $CI->db->query("SELECT * FROM ".AIGAION_DB_PREFIX."author WHERE ".$this->keywordsToLikeQuery($keywordArray,'cleanname')." ORDER BY cleanname;");
    if ($authorQ->num_rows()>0) {
      $arrayOfAuthors = array();
      foreach ($authorQ->result() as $R) {
        $arrayOfAuthors[] = $CI->author_db->getFromRow($R); //create author from row
      }
      return $arrayOfAuthors;
    }
    else
      return null;
  }
  
  //find topic hits on 'like' clause for cleanname
  function findTopicsLike($query)
  {
    $CI = &get_instance();
    
    if (!is_array($query))
      $keywordArray = $this->queryToKeywords($query);
    else
      $keywordArray = $query;
   
    //find topic hits on 'like' clause for cleanname
    $userlogin = getUserLogin();
    $user = $CI->user_db->getByID($userlogin->userId());
    //by default: only search for subscribed topics
    $config = array('onlyIfUserSubscribed'=>True,
    'user'=>$user,
    'includeGroupSubscriptions'=>True
    );
    $topicQ = $CI->db->query("SELECT * FROM ".AIGAION_DB_PREFIX."topics WHERE ".$this->keywordsToLikeQuery($keywordArray,'cleanname')." ORDER BY name;");
    if ($topicQ->num_rows()>0) {
      $arrayOfTopics = array();
      foreach ($topicQ->result() as $R) {
        $next = $CI->topic_db->getFromRow($R,$config); //create topic from row
        if ($next != null)
        $arrayOfTopics[] = $next;
      }
      return $arrayOfTopics;
    }
    else
      return null;
  }

  //find keywords hits on 'like' clause for keyword
  function findKeywordsLike($query)
  {
    $CI = &get_instance();
    
    if (!is_array($query))
      $keywordArray = $this->queryToKeywords($query);
    else
      $keywordArray = $query;
  
  //find keywords hits on 'like' clause for keyword
    $keywordQ = $CI->db->query("SELECT * FROM ".AIGAION_DB_PREFIX."keywords WHERE ".$this->keywordsToLikeQuery($keywordArray,'keyword')." ORDER BY keyword;");
    if ($keywordQ->num_rows()>0) {
      $arrayOfKeywords = array();
      foreach ($keywordQ->result() as $R) {
        $kw->keyword_id = $R->keyword_id;
        $kw->keyword = $R->keyword;
        $arrayOfKeywords[] = $kw;
        unset($kw);
      }
      return $arrayOfKeywords;
    }
    else
      return null;
  }
  
  //find publication hits on 'like' clause for cleantitle, bibtex_id, cleanjournal
  //DR note: here we could also enforce that the publicaiton should be in a subscribed topic. Would make things a lot slower,
  //but I think people will want this.
  function findPublicationsLike($query)
  {
    $CI = &get_instance();
    
    if (!is_array($query))
      $keywordArray = $this->queryToKeywords($query);
    else
      $keywordArray = $query;

    $pubQ = $CI->db->query("SELECT * FROM ".AIGAION_DB_PREFIX."publication WHERE "
    .$this->keywordsToLikeQuery($keywordArray,'cleantitle')
    .' OR '
    .$this->keywordsToLikeQuery($keywordArray,'cleanjournal')
    ." ORDER BY actualyear DESC, cleantitle;");
    if ($pubQ->num_rows()>0) {
      $arrayOfPubs = array();
      foreach ($pubQ->result() as $R) {
        $next = $CI->publication_db->getFromRow($R); //create publication from row
        if ($next != null)
        $arrayOfPubs[] = $next;
      }
      return $arrayOfPubs;
    }
    else
      return null;
  }
  
  function findPublicationsCiteIDLike($query)
  {
    $CI = &get_instance();
    
    if (!is_array($query))
      $keywordArray = $this->queryToKeywords($query);
    else
      $keywordArray = $query;

    $pubQ = $CI->db->query("SELECT * FROM ".AIGAION_DB_PREFIX."publication WHERE "
    .$this->keywordsToLikeQuery($keywordArray,'bibtex_id')
    ." ORDER BY actualyear DESC, cleantitle;");
    if ($pubQ->num_rows()>0) {
      $arrayOfPubs = array();
      foreach ($pubQ->result() as $R) {
        $next = $CI->publication_db->getFromRow($R); //create publication from row
        if ($next != null)
        $arrayOfPubs[] = $next;
      }
      return $arrayOfPubs;
    }
    else
      return null;
  }
  
  function findPublicationsNotesLike($query)
  {
    $CI = &get_instance();
    
    if (!is_array($query))
      $keywordArray = $this->queryToKeywords($query);
    else
      $keywordArray = $query;

    $pubQ = $CI->db->query("SELECT * FROM ".AIGAION_DB_PREFIX."notes WHERE "
    .$this->keywordsToLikeQuery($keywordArray,'text'));
    if ($pubQ->num_rows()>0) {
      $arrayOfPubs = array();
      foreach ($pubQ->result() as $R) {
        $note = $CI->note_db->getByID($R->note_id);
        //the user may not have access to the note, so check if null is returned
        if ($note != null)
        {
          $next = $CI->publication_db->getByID($note->pub_id);
          if ($next != null)
          $arrayOfPubs[] = $next;
        }
      }
      return $arrayOfPubs;
    }
    else
      return null;
  }
  
  function findPublicationsAbstractsLike($query)
  {
    $CI = &get_instance();
    
    if (!is_array($query))
      $keywordArray = $this->queryToKeywords($query);
    else
      $keywordArray = $query;

    $pubQ = $CI->db->query("SELECT * FROM ".AIGAION_DB_PREFIX."publication WHERE "
    .$this->keywordsToLikeQuery($keywordArray,'abstract')
    ." ORDER BY actualyear DESC, cleantitle;");
    if ($pubQ->num_rows()>0) {
      $arrayOfPubs = array();
      foreach ($pubQ->result() as $R) {
        $next = $CI->publication_db->getFromRow($R); //create publication from row
        if ($next != null)
        $arrayOfPubs[] = $next;
      }
      return $arrayOfPubs;
    }
    else
      return null;
  }


  /** returns a list of keyword strings from a 'quick search query'.
  Main point is that it 1) separates on space and 2) allows 'exact match phrases' using quotes and
  3) it converts the keywords to de-bibtexxed transliterated utf8_to_ascii values. */
  function queryToKeywords($query)
  {
    $CI = &get_instance();
    $CI->load->helper('bibtexutf8');
    $CI->load->helper('utf8_to_ascii');

    //DR: current implementation uses substr, not entirely UTF8-safe!
    $keywordArray = array();
    $tmpKeywordArray = explode(' ', $query);
    $bGroup = false;
    $groupKeyword = "";
    $groupSeparator = "";
    foreach ($tmpKeywordArray as $keyword)
    {
      $keyword = trim($keyword);

      if ((substr($keyword, 0,1) == '"') | (substr($keyword, 0,1) == "'")) {
        $groupSeparator = substr($keyword, 0,1);
        if (strlen($keyword) > 1):
        $keyword = substr($keyword, 1, (strlen($keyword)-1));
        elseif ($bGroup == true):
        $keyword = " ".$groupSeparator;
        else:
        $keyword = " ";
        endif;

        $bGroup = true;
      }

      if ($bGroup) {
        if (substr($keyword, -1, 1) == $groupSeparator) {
          $bGroup = false;
          $keyword = substr($keyword, 0, strlen($keyword) - 1);
          $groupKeyword .= $keyword;
          if (trim($groupKeyword)!="")
          $keywordArray[] = utf8_to_ascii(bibCharsToUtf8FromString($groupKeyword));
          $groupKeyword = "";
        }
        else {
          $groupKeyword .= $keyword." ";
        }
      }
      else {
        if (trim($keyword) != "")
        $keywordArray[] = utf8_to_ascii(bibCharsToUtf8FromString($keyword));
      }
    }
    return $keywordArray;
  }

  /** Constructs a compound LIKE sql phrase for use in the WHERE clause, like this:
  ($fieldname LIKE '%$keyword1% OR $fieldname LIKE '%$keyword2% ... ) */
  function keywordsToLikeQuery($keywords, $fieldname) {
    $CI = &get_instance();
    $result = "";
    foreach ($keywords as $keyword) {
      if ($result != "") $result .= ' OR ';
      {
        $prefix_wildcard = $suffix_wildcard = "";
/*      //explicit anchoring to match word beginning with ^ and word en also with ^
        if (strlen($keyword) > 2)
        {
          if (substr($keyword, 0, 1) == "^")
          {
            $prefix_wildcard = '[[:<:]]';
            $keyword = substr($keyword, 1);
          }
          if (substr($keyword, -1) == "^")
          {
            $suffix_wildcard = '[[:>:]]';
            $keyword = substr($keyword, 0, -1);
          }
*/
        //nonexplicit anchoring: match only word beginnings, * for open ends
        if (strlen($keyword) > 2)
        {
          $prefix_wildcard = '[[:<:]]';
          
          if (substr($keyword, -1) != "*")
            $suffix_wildcard = '[[:>:]]';
          else
            $keyword = substr($keyword, 0, -1);
        }
        $result .= $fieldname." REGEXP '".$prefix_wildcard.mysql_real_escape_string($keyword).$suffix_wildcard."' ";
      }
    }
    return '('.$result.')';
  }
}
?>