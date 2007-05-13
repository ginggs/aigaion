<?php
class Bookmarklist_db {

  var $CI = null;

  function Bookmarklist_db()
  {
    $this->CI = &get_instance();
  }

    function addPublication($pub_id)
    {
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage("Changing bookmarklist: insufficient rights<br>");
            return;
        }
        mysql_query("INSERT IGNORE INTO userbookmarklists (user_id,pub_id) VALUES (".getUserLogin()->userId().",".$pub_id.")");
    	if (mysql_error()) {
    		appendErrorMessage("Error changing bookmarklist<br>");
    	}

    }

    function removePublication($pub_id)
    {
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage("Changing bookmarklist: insufficient rights<br>");
            return;
        }
        mysql_query("DELETE FROM userbookmarklists WHERE user_id=".getUserLogin()->userId()." AND pub_id=".$pub_id);
    	if (mysql_error()) {
    		appendErrorMessage("Error changing bookmarklist<br>");
    	}

    }

    function clear() {
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage("Changing bookmarklist: insufficient rights<br>");
            return;
        }
        mysql_query("DELETE FROM userbookmarklists WHERE user_id=".getUserLogin()->userId());
    	if (mysql_error()) {
    		appendErrorMessage("Error changing bookmarklist<br>");
    	}
    }
    
    function addToTopic($topic) {
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist') || !$userlogin->hasRights('publication_edit')) {
            appendErrorMessage("Changing bookmarklist: insufficient rights<br>");
            return;
        }
        $pub_ids = array();
        foreach ($this->CI->publication_db->getForBookmarkList() as $publication) {
            $pub_ids[] = $publication->pub_id;
        }
        $topic->subscribePublicationSetUpRecursive($pub_ids);
        appendMessage("Bookmarked publications added to topic<br>");
    }

}
?>