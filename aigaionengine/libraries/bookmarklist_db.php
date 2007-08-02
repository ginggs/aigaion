<?php
class Bookmarklist_db {


  function Bookmarklist_db()
  {
  }

    function addPublication($pub_id)
    {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage("Changing bookmarklist: insufficient rights<br/>");
            return;
        }
        $CI->db->query("INSERT IGNORE INTO userbookmarklists (user_id,pub_id) VALUES (".$userlogin->userId().",".$pub_id.")");
    	if (mysql_error()) {
    		appendErrorMessage("Error changing bookmarklist<br/>");
    	}

    }

    function removePublication($pub_id)
    {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage("Changing bookmarklist: insufficient rights<br/>");
            return;
        }
        $CI->db->query("DELETE FROM userbookmarklists WHERE user_id=".$userlogin->userId()." AND pub_id=".$pub_id);
    	if (mysql_error()) {
    		appendErrorMessage("Error changing bookmarklist<br/>");
    	}

    }

    function clear() {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage("Changing bookmarklist: insufficient rights<br/>");
            return;
        }
        $CI->db->query("DELETE FROM userbookmarklists WHERE user_id=".$userlogin->userId());
    	if (mysql_error()) {
    		appendErrorMessage("Error changing bookmarklist<br/>");
    	}
    }
    
    function addToTopic($topic) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist') || !$userlogin->hasRights('publication_edit')) {
            appendErrorMessage("Changing bookmarklist: insufficient rights<br/>");
            return;
        }
        $pub_ids = array();
        foreach ($CI->publication_db->getForBookmarkList() as $publication) {
            $pub_ids[] = $publication->pub_id;
        }
        $topic->subscribePublicationSetUpRecursive($pub_ids);
        appendMessage("Bookmarked publications added to topic<br/>");
    }

}
?>