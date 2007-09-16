<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for accessing maintenance functions
| -------------------------------------------------------------------
|
|   Provides access to maintenance checks.
|
|	Usage:
|       //load this helper:
|       $this->load->helper('maintenance'); 
|       //perform a check and get the result
|       $report = checkAttachments(); 
|       $report = checkTopics(); 
|       $report = checkPasswords(); 
|       $report = checkCleanNames();
|
  
*/
/*
        check whether anyone has an empty password or a password that is the same as the username
*/
    function checkPasswords() {
		$result = "<tr><td colspan=2><p class='header1'>Passwords check</p></td></tr>\n";

		$result .= "<tr><td>Check all users...</td>";
        $checkResult = "";
        $CI = &get_instance();
		#for every user:
		foreach ($CI->user_db->getAllUsers() as $user) {
		    #check empty passwords
		    if ($user->password==md5('')) {
		        $checkResult .= 'User '.$user->login.' has an empty password!<br>';
		    }
		    #check name=pwd
		    if ($user->password==md5($user->login)) {
		        $checkResult .= 'User '.$user->login.' has the user name for password!<br>';
		    }
		}
		if ($checkResult != "")
		{
			$result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
			$result .= "<div class='message'>";
			$result .= "The following users have a wrong password:<br/>\n";
			$result .= $checkResult."</div>\n";
			$result .= "</td></tr>\n";
		}
		else
			$result .= "<td><b>OK</b></td></tr>\n";

	    return $result;
    }
    function checkAttachments() {
		$result = "<tr><td colspan=2><p class='header1'>Attachments check</p></td></tr>\n";

		#check attachments where file on server is missing
		$result .= "<tr><td>Check missing attachments...</td>";
		$checkResult = checkMissingFiles();
		if ($checkResult != "")
		{
			$result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
			$result .= "<div class='message'>";
			$result .= "The following files could not be found in the attachment directory.<br/>\n";
			$result .= $checkResult."</div>\n";
			$result .= "</td></tr>\n";
		}
		else
			$result .= "<td><b>OK</b></td></tr>\n";

		#check for orphaned attachments
		$result .= "<tr><td>Check orphaned attachments...</td>";
		$checkResult = checkAttachmentPublicationLinks(); //remove attachments of Publications no longer in the database
		if ($checkResult > 0)
		{
			$result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
			$result .= "<div class='message'>";
			$result .= $checkResult." references to attachments that no longer belong to a publication have been removed.</div>\n";
			$result .= "</td></tr>\n";
		}
		else
			$result .= "<td><b>OK</b></td></tr>\n";

		#check for unknown files
		$result .= "<tr><td>Check unknown files...</td>";
		$checkResult = checkUnknownFiles();
		if ($checkResult != "")
		{
			$result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
			$result .= "<div class='message'>";
			$result .= "The following files are on the server, but do not belong to a publication in the database:<br/>\n";
			$result .= $checkResult."</div>\n";
			$result .= "</td></tr>\n";
		}
		else
			$result .= "<td><b>OK</b></td></tr>\n";
	    return $result;
    }
    function checkTopics() {
  	    $result = "<tr><td colspan=2><p class='header1'>Topic tree check</p></td></tr>\n";

		//remove deleted topics from topictopiclink table
		$result .= "<tr><td>Check orphaned topictopiclinks...</td>";
		$checkResult = checkTopicTopicLinks();
		if ($checkResult > 0)
		{
			$result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
			$result .= "<div class='message'>";
			$result .= $checkResult." topictopiclinks, of which the topic couldn't be found, have been removed.</div>\n";
			$result .= "</td></tr>\n";
		}
		else
			$result .= "<td><b>OK</b></td></tr>\n";

		//remove topicpublicationlinks where topic is deleted
		$result .= "<tr><td>Check orphaned topicpublicationlinks...</td>";
		$checkResult = checkTopicPublicationLinks();
		if ($checkResult > 0)
		{
			$result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
			$result .= "<div class='message'>";
			$result .= $checkResult." topicpublicationlinks, of which the corresponding publication could not be found, have been removed.</div>\n";
			$result .= "</td></tr>\n";
		}
		else
			$result .= "<td><b>OK</b></td></tr>\n";


		//check for parentless topics
		$result .= "<tr><td>Check topics without parent...</td>";
		$checkResult = checkTopicParents();
		if ($checkResult != "")
		{
			$result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
			$result .= "<div class='message'>";
			$result .= "The following topics had no parent. Their parent is set to the top topic.<br/>\n";
			$result .= $checkResult."</div>\n";
			$result .= "</td></tr>\n";
		}
		else
			$result .= "<td><b>OK</b></td></tr>\n";

		//check for empty topics
		$result .= "<tr><td>Checking for empty topics...</td>";
//		$checkResult = checkEmptyTopics();
//		if ($checkResult != "")
//		{
//			$result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
//			$result .= "<div class='message'>";
//			$result .= "The following topics have no assigned publications.<br/>\n";
//			$result .= $checkResult."</div>\n";
//			$result .= "</td></tr>\n";
//		}
//		else
			$result .= "<td  class=errortext><b>NOT IMPLEMENTED</b></td></tr>\n";
        return $result;
    }
    function checkCleanNames() {
        $CI = &get_instance();
        $CI->load->helper('utf8_to_ascii');
  	    $result = "<tr><td colspan=2><p class='header1'>Reinit searchable names and titles</p></td></tr>\n";

        $result .= "<tr><td>Checking... ";
        # check clean names of authors (author.cleanname)
        $authorcount = 0;
        foreach ($CI->author_db->getAllAuthors() as $author) { //all authors are accessible to all users...
            $oldcleanname = $author->cleanname;
            $author->cleanname = utf8_to_ascii($author->getName('lvf'));
            if ($author->cleanname!=$oldcleanname) {
                $author->update();
                $authorcount++;
            }
        }
        if ($authorcount > 0) {
            $result .= "<br/>Fixed searchable names of ".$authorcount." authors.";
        }
        # check clean titles of publications and journals (publication.cleantitle, publication.cleanjournal)
        $pubcount = 0;
        $Q = $CI->db->get('publication');
        foreach ($Q->result() as $row) { //not all publications are accessible to all users... so go directly to sql
            $oldcleantitle = $row->cleantitle;
            $oldcleanjournal = $row->cleanjournal;
            $cleantitle = utf8_to_ascii($row->title);
            $cleanjournal = utf8_to_ascii($row->journal);
            if ($oldcleanjournal!=$cleanjournal || $oldcleantitle!=$cleantitle) {
                $CI->db->update('publication',array('cleantitle'=>$cleantitle, 'cleanjournal'=>$cleanjournal),array('pub_id'=>$row->pub_id));
                $pubcount++;
            }
        }
        if ($pubcount > 0) {
            $result .= "<br/>Fixed searchable names of ".$pubcount." publications.";
        }
        $result .= "</td><td><b>OK</b></td></tr>\n";
        return $result;
    }

/*
        Checks the filesystem for files that are listed in the database, but are not on disk.
        returns a <ul> with missing files.
*/
function checkMissingFiles()
{
    $CI = &get_instance();
    //check for each entry the file
    $Q = $CI->db->getwhere("attachments",array('isremote'=>'FALSE'));
    $found = FALSE;
    $report = "";
    $result = "";
    foreach ($Q->result() as $R)
    {
        $curfile = $R->location;
        $checklocation = AIGAION_ATTACHMENT_DIR.'/'.$curfile;
        if (!file_exists($checklocation))
        {
            $found = TRUE;
            //report link to publication
            $publication = $CI->publication_db->getByID($R->pub_id);
            if ($publication==null) {//in table, but not linked: just remove
                $CI->db->query("DELETE FROM attachments WHERE att_id=".$R->att_id);
                $report .= "<li>Removed: ".$R->name." (file not on server and not linked to a publication).</li>\n";
            } else {
                $report .= "<li>".$R->name." (".anchor('publications/show/'.$publication->pub_id,$publication->title).")</li>\n";
            }
        }
    }
    if ($found)
        $result .= "<ul>\n".$report."</ul>\n";

    return $result;
}

/*
        Checks the filesystem for files that are in the document directory but not listed in the
        database as being an attachment.
        returns a <ul> with links to found files.
*/
function checkUnknownFiles()
{
    $CI = &get_instance();
    $bFound = FALSE;
    $report = "";
    $result = "";
    if ($handle = opendir(AIGAION_ATTACHMENT_DIR)) {
        /* This is the correct way to loop over the directory. */
        while (false !== ($file = readdir($handle)))
        {
            if ($file=='CVS'||$file=='.svn'||$file=='.'||$file=='..'||$file=='_README.txt'||$file=='index.php'||$file=='custom_logo.jpg'||$file=='aisearch.src'||$file=='export.bib')
                continue;
            $Q = $CI->db->getwhere('attachments',array('location'=>$file));
            if ($Q->num_rows() <= 0)
            {
                $bFound = TRUE;
                $report .= "<li>".$file."</li>\n";
            }
        }
        closedir($handle);
    }
    else
        $result .= "Could not open documents directory.<br/>\n";

    if ($bFound)
    {
        $result .= "<ul>\n".$report."</ul>\n";
    }
    return $result;
}

 
/*
        checks for links between publicationss and attachments where the publication does not exist anymore.
        silently deletes invalid links and attachments.      
*/
function checkAttachmentPublicationLinks()
{
    $CI = &get_instance();
    $count = 0;

    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."attachments.*
                             FROM ".AIGAION_DB_PREFIX."attachments LEFT JOIN ".AIGAION_DB_PREFIX."publication 
                                              ON (".AIGAION_DB_PREFIX."attachments.pub_id = ".AIGAION_DB_PREFIX."publication.pub_id) 
                             WHERE ".AIGAION_DB_PREFIX."publication.pub_id IS NULL");
    foreach ($Q->result() as $R) 
    {
        if ($R->isremote!="TRUE") {
            unlink(AIGAION_ATTACHMENT_DIR.'/'.$R->location);
        }
        $CI->db->delete('attachments',array('att_id'=>$R->att_id));
        $count++;
    }
    return $count;
}



/*
		checks for topics that appear in the topictopiclink table but that are not available anymore.
		returns the number of deleted links.
*/
function checkTopicTopicLinks()
{
    $CI = &get_instance();
	$topic_ids = array();
	$count = 0;
	$CI->db->select('DISTINCT source_topic_id');
	$Q = $CI->db->getwhere('topictopiclink',array('source_topic_id != "1"'));
	foreach ($Q->result() as $row) {
		if (!in_array($row->source_topic_id, $topic_ids))
			$topic_ids[] = $row->source_topic_id;
	}
	$CI->db->select('DISTINCT target_topic_id');
	$Q = $CI->db->getwhere('topictopiclink',array('target_topic_id != "1"'));
	foreach ($Q->result() as $row) {
		if (!in_array($row->target_topic_id, $topic_ids))
			$topic_ids[] = $row->target_topic_id;
	}
	foreach ($topic_ids as $topic_id) {
		$Q = $CI->db->getwhere('topics',array('topic_id'=>$topic_id));
		if ($Q->num_rows()==0) {
			$CI->db->delete('topictopiclink',array('source_topic_id'=>$topic_id));
			$CI->db->delete('topictopiclink',array('target_topic_id'=>$topic_id));
			$count++;
		}
	}
	return $count;
}

/*
		checks for TopicPublication where the pub or topic does not exist anymore.
		returns the number of deleted links.
*/
function checkTopicPublicationLinks()
{
    $CI = &get_instance();
	$count = 0;
	$Q = $CI->db->query(
			"SELECT DISTINCT ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id
			FROM ".AIGAION_DB_PREFIX."topicpublicationlink
			LEFT JOIN ".AIGAION_DB_PREFIX."publication ON (".AIGAION_DB_PREFIX."topicpublicationlink.pub_id = ".AIGAION_DB_PREFIX."publication.pub_id)
			WHERE ".AIGAION_DB_PREFIX."publication.pub_id IS NULL");
    foreach ($Q->result() as $R) {
		$CI->db->delete('topicpublicationlink',array('pub_id'=>$R->pub_id));
		$count++;
	}
    return $count;
}


/*
		checks for topics that have no parents, sets parent to top if no parent.
		returns a <ul> with parentless topics.
*/
function checkTopicParents()
{
    $CI = &get_instance();
	$result = "";
	$report = "";
	$CI->db->select('topic_id,name');
	$Q = $CI->db->getwhere('topics','topic_id<>1');
	foreach ($Q->result() as $R) {
		$Q2 = $CI->db->getwhere('topictopiclink',array('source_topic_id'=>$R->topic_id));
		if ($Q2->num_rows() == 0) { //we found a parentless topic
			$Q3 = $CI->db->insert('topictopiclink',array('source_topic_id'=>$R->topic_id,'target_topic_id'=>'1'));
			$config = array();
			$topic = $CI->topic_db->getByID($R->topic_id,$config);
			$report .= "<li>".anchor('topics/single/'.$topic->topic_id, $topic->name)."</li>\n";
		}
	}
	if ($report != "")
		$result .= "<ul>\n".$report."</ul>\n";

	return $result;
}


/*
		checks for topics that are empty.
		returns a <ul> with empty topics.
*/
function checkEmptyTopics()
{
	$result = "";
//	$report = "";
//	$Q = mysql_query(
//			"SELECT DISTINCT topic.ID, topic.name
//			FROM topic LEFT JOIN topicpublication ON (topic.ID = topicpublication.topic_id)
//			WHERE topicpublication.topic_id IS NULL");
//	if (mysql_num_rows($Q) > 0) {
//		while ($R = mysql_fetch_array($Q)) {
//			$report .= "<li>".getLinkToTopicPage($R['ID'], $R['name'])."</li>\n";
//		}
//	}
//
//	if ($report != "")
//		$result .= "<ul>\n".$report."</ul>\n";

	return $result;
}




?>