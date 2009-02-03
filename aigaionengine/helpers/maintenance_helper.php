<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for accessing maintenance functions
| -------------------------------------------------------------------
|
|   Provides access to maintenance checks.
|
|    Usage:
|       //load this helper:
|       $this->load->helper('maintenance'); 
|       //perform a check and get the result
|       $report = checkAttachments(); 
|       $report = checkTopics(); 
|       $report = checkPasswords(); 
|       $report = checkCleanNames();
|       $report = checkNotes();
|       $report = checkAuthors();
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
          if (($user->type!='external') && ($user->password_invalidated!= 'TRUE')) {
            #check empty passwords
            if ($user->password==md5('')) {
                $checkResult .= 'User '.$user->login.' has an empty password!<br>';
            }
            #check name=pwd
            if ($user->password==md5($user->login)) {
                $checkResult .= 'User '.$user->login.' has the user name for password!<br>';
            }
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
    function checkNotes() {
        $result = "<tr><td colspan=2><p class='header1'>Notes checks</p></td></tr>\n";

        $result .= "<tr><td>Checking note crossreference consistency...</td>";
        $checkResult = checkNoteXrefIDs();
        if ($checkResult != "")
        {
            $result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
            $result .= "<div class='message'>";
            $result .= $checkResult." notes had inconsistent note crossref IDs.</div>\n";
            $result .= "</td></tr>\n";
        }
        else
            $result .= "<td class=errortext><b>NOT YET IMPLEMENTED</b></td></tr>\n";

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
    function checkAuthors() {
        $result = "<tr><td colspan=2><p class='header1'>Author check</p></td></tr>\n";

        #check for empty author names
        $result .= "<tr><td>Checking empty author names...</td>";
        $checkResult = checkEmptyAuthorNames();
        if ($checkResult != "")
        {
            $result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
            $result .= "<div class='message'>";
            $result .= "The following authors have an empty name:<br/>\n";
            $result .= $checkResult."</div>\n";
            $result .= "</td></tr>\n";
        }
        else
            $result .= "<td><b>OK</b></td></tr>\n";

        #check for similar author names
        $result .= "<tr><td>Checking similar author names...</td>";
        //uncomment this block to turn this check on. Warning: It'll probably time-out.
//        $checkResult = checkSimilarAuthors();
//        if ($checkResult != "")
//        {
//            $result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
//            $result .= "<div class='message'>";
//            $result .= "The following authors are very similar:<br/>\n";
//            $result .= $checkResult."<br/>\n";
//            $result .= "Click on a pair to merge the authors</div>\n";
//            $result .= "</td></tr>\n";
//        }
//        else
            $result .= "<td class=errortext><b>TURNED OFF BECAUSE IT TENDS TO TIME-OUT</b></td></tr>\n";

        #check authorpublication links
        $result .= "<tr><td>Checking orphaned authorpublicationlinks...</td>\n";
        $checkResult = checkAuthorPublicationLinks();
        if ($checkResult > 0)
        {
            $result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
            $result .= "<div class='message'>";
            $result .= $checkResult." authorpublicationlinks whose corresponding authors could not be found, were removed.</div>\n";
            $result .= "</td></tr>\n";
        }
        else
            $result .= "<td><b>OK</b></td></tr>\n";

        #check for nonpublishing authors
        $result .= "<tr><td>Checking for authors that do not publish...</td>\n";
        $checkResult = checkNonPublishingAuthors();
        if ($checkResult != "")
        {
            $result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
            $result .= "<div class='message'>";
            $result .= "The following authors have no publications listed:<br/>\n";
            $result .= $checkResult;
            $result .= '<br/>'.anchor('site/maintenance/deletenonpublishingauthors','Delete all non-publishing authors')."</div>\n";
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
        $checkResult = checkEmptyTopics();
        if ($checkResult != "")
        {
            $result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
            $result .= "<div class='message'>";
            $result .= "The following topics have no assigned publications.<br/>\n";
            $result .= $checkResult."</div>\n";
            $result .= "</td></tr>\n";
        }
        else
            $result .= "<td><b>OK</b></td></tr>\n";

        //check that all publications are subscribed to topic ancestors
        $result .= "<tr><td>Checking publications in ancestor topics...</td>";
        $leafs = array();
        getLeafTopicIds($leafs);
        $checkResult = checkTopicPublicationAncestors($leafs);
        if ($checkResult > 0)
        {
            $result .= "<td><span class=errortext>ALERT</span></td></tr>\n<tr><td colspan=2>";
            $result .= "<div class='message'>";
            $result .= $checkResult." publications did not appear in ancestor topics.</div>\n";
            $result .= "</td></tr>\n";
        }
        else
            $result .= "<td><b>OK</b></td></tr>\n";
          
        return $result;
    }
    function checkCleanNames() {
        $CI = &get_instance();
        $CI->load->helper('cleanname');
        $result = "<tr><td colspan=2><p class='header1'>Reinit searchable names and titles</p></td></tr>\n";

        $result .= "<tr><td>Checking... ";
        # check clean names of authors (author.cleanname)
        $authorcount = 0;
        foreach ($CI->author_db->getAllAuthors() as $author) { //all authors are accessible to all users...
            $oldcleanname = $author->cleanname;
            $author->cleanname = authorCleanName($author);
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
            //check clean title
            $oldcleantitle = $row->cleantitle;
            $oldcleanjournal = $row->cleanjournal;
            $oldcleanauthor = $row->cleanauthor;
            $cleantitle = cleanTitle($row->title);
            $cleanjournal = cleanTitle($row->journal);
            //check clean author? that's tricky. We cannot get to the publication object, so some code from publication.update needs to be replicated here :(
            $cleanauthor = getCleanAuthor($row);
            if ($oldcleanjournal!=$cleanjournal || $oldcleantitle!=$cleantitle || $oldcleanauthor!=$cleanauthor) {
                $CI->db->update('publication',array('cleantitle'=>$cleantitle, 'cleanjournal'=>$cleanjournal, 'cleanauthor'=>$cleanauthor),array('pub_id'=>$row->pub_id));
                $pubcount++;
            }
            
        }
        if ($pubcount > 0) {
            $result .= "<br/>Fixed searchable names of ".$pubcount." publications.";
        }
        # check clean names of topics (topics.cleanname)
        $topiccount = 0;
        $Q = $CI->db->get('topics');
        foreach ($Q->result() as $row) { //not all topics are accessible to all users... so go directly to sql
            $oldcleanname = $row->cleanname;
            $cleanname = cleanTitle($row->name);
            if ($oldcleanname!=$cleanname) {
                $CI->db->update('topics',array('cleanname'=>$cleanname),array('topic_id'=>$row->topic_id));
                $topiccount++;
            }
        }
        if ($topiccount > 0) {
            $result .= "<br/>Fixed searchable names of ".$topiccount." topics.";
        }
        # check clean names of keywords (keywords.cleankeyword)
        $CI->load->helper('utf8_to_ascii');
        $keywordcount = 0;
        $Q = $CI->db->get('keywords');
        foreach ($Q->result() as $row) { 
            $oldcleankeyword = $row->cleankeyword;
            $cleankeyword =  utf8_to_ascii($row->keyword);
            if ($oldcleankeyword!=$cleankeyword) {
                $CI->db->update('keywords',array('cleankeyword'=>$cleankeyword),array('keyword_id'=>$row->keyword_id));
                $keywordcount++;
            }
        }
        if ($keywordcount > 0) {
            $result .= "<br/>Fixed searchable names of ".$keywordcount." keywords.";
        }
        $result .= "</td><td><b>OK</b></td></tr>\n";
        return $result;
    }
    /** repair the 'total marks' for all publications */
    function checkPublicationMarks() {
        $markcount = 0;
        $CI = &get_instance();
        $Q = $CI->db->get('publication');
        $result = "<tr><td colspan=2><p class='header1'>Check total publication marks</p></td></tr>\n";

        $result .= "<tr><td>Checking... ";
        foreach ($Q->result() as $R) {
            $oldmark = $R->mark;
            $newmark = $CI->publication_db->recalcTotalMark($R->pub_id);
            if ($oldmark != $newmark) $markcount++;
        }
        if ($markcount > 0) {
            $result .= "<br/>Fixed total topic mark of ".$markcount." publications.";
        }
        $result .= "</td><td><b>OK</b></td></tr>\n";
        return $result;        
    }

    /** return a proper clean author for the given publication row without creating publication object */
    function getCleanAuthor($row) {
        //check clean author? that's tricky. We cannot get to the publication object, so some code from publication.update needs to be replicated here :(
        $CI = &get_instance();
        $authors = $CI->author_db->getForPublication($row->pub_id, 'N');
        $editors = $CI->author_db->getForPublication($row->pub_id, 'Y');
        $cleanauthor = "";
        //add authors
        if (is_array($authors)) {
          foreach ($authors as $author)
          {
            $cleanauthor .= ' '.$author->cleanname;
          }
        }
        if (is_array($editors)) {
          foreach ($editors as $author)
          {
            $cleanauthor .= ' '.$author->cleanname;
          }
        }
        return trim($cleanauthor);
        
    }

/*
        Checks the filesystem for files that are listed in the database, but are not on disk.
        returns a <ul> with missing files.
*/
function checkMissingFiles()
{
    $CI = &get_instance();
    //check for each entry the file
    $Q = $CI->db->get_where("attachments",array('isremote'=>'FALSE'));
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
            $Q = $CI->db->get_where('attachments',array('location'=>$file));
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
    $CI->db->distinct();
    $CI->db->select('source_topic_id');
    $Q = $CI->db->get_where('topictopiclink',array('source_topic_id != ' => '1'));
    foreach ($Q->result() as $row) {
        if (!in_array($row->source_topic_id, $topic_ids))
            $topic_ids[] = $row->source_topic_id;
    }
    $CI->db->distinct();
    $CI->db->select('target_topic_id');
    $Q = $CI->db->get_where('topictopiclink',array('target_topic_id != ' => '1'));
    foreach ($Q->result() as $row) {
        if (!in_array($row->target_topic_id, $topic_ids))
            $topic_ids[] = $row->target_topic_id;
    }
    foreach ($topic_ids as $topic_id) {
        $Q = $CI->db->get_where('topics',array('topic_id'=>$topic_id));
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
    $Q = $CI->db->get_where('topics',array('topic_id !='=>'1'));
    foreach ($Q->result() as $R) {
        $Q2 = $CI->db->get_where('topictopiclink',array('source_topic_id'=>$R->topic_id));
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
    $CI = &get_instance();
    $result = "";
    $report = "";
    $Q = $CI->db->query(
            "SELECT DISTINCT ".AIGAION_DB_PREFIX."topics.topic_id, ".AIGAION_DB_PREFIX."topics.name
            FROM ".AIGAION_DB_PREFIX."topics LEFT JOIN ".AIGAION_DB_PREFIX."topicpublicationlink 
                                                    ON (".AIGAION_DB_PREFIX."topics.topic_id = ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id)
            WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id IS NULL");
    if ($Q->num_rows($Q) > 0) {
        foreach ($Q->result() as $R) {
            $report .= "<li>".anchor('topics/single/'.$R->topic_id,$R->name)."</li>\n";
        }
    }

    if ($report != "")
        $result .= "<ul>\n".$report."</ul>\n";

    return $result;
}

/** starting from the given leaf topics, slowly workway upwards to check whether all publications 
that appear in a topic also appear in its ancestor topics */
function checkTopicPublicationAncestors($topics = array())
{
    $nrOfFixes = 0;
    $parentTopics = array();
    if (count($topics) > 0) {
        foreach($topics as $child) {
            $childPublications = array();
            $parentPublications = array();
            //fetch parent
            $parent = 1;
            $Q = mysql_query("SELECT target_topic_id FROM ".AIGAION_DB_PREFIX."topictopiclink WHERE source_topic_id = ".$child);
            if (mysql_num_rows($Q) > 0) {
                $R = mysql_fetch_array($Q);
                $parent = $R['target_topic_id'];
            }

            //fetch child publications
            $Q = mysql_query("SELECT pub_id FROM ".AIGAION_DB_PREFIX."topicpublicationlink WHERE topic_id = ".$child);
            if (mysql_num_rows($Q) > 0) {
                while ($R = mysql_fetch_array($Q)) {
                    $childPublications[] = $R['pub_id'];
                }
            }

            //fetch parent publications
            $Q = mysql_query("SELECT pub_id FROM ".AIGAION_DB_PREFIX."topicpublicationlink WHERE topic_id = ".$parent);
            if (mysql_num_rows($Q) > 0) {
                while ($R = mysql_fetch_array($Q)) {
                    $parentPublications[] = $R['pub_id'];
                }
            }

            //find the missing publications
            $missingPubs = array_diff($childPublications, $parentPublications);

            //missing pubs: add to topicpublication
            foreach ($missingPubs as $pub_id) {
                $Q = mysql_query("INSERT INTO ".AIGAION_DB_PREFIX."topicpublicationlink (topic_id, pub_id) VALUES ('".$parent."', '".$pub_id."')");
                $nrOfFixes++;
            }

            //add the parent topic to the parentTopics array for the next iteration.
            if (!in_array($parent, $parentTopics) && ($parent != 1))
                $parentTopics[] = $parent;

            unset($childPublications);
            unset($parentPublications);
        }
    }
    if (count($parentTopics) > 0)
        $nrOfFixes += checkTopicPublicationAncestors($parentTopics);

    return $nrOfFixes;
}

function getLeafTopicIds(&$leafs, $root = 1)
{
    $CI = &get_instance();
    $Q = $CI->db->query("SELECT source_topic_id FROM ".AIGAION_DB_PREFIX."topictopiclink WHERE target_topic_id = ".$root);
    if ($Q->num_rows($Q) > 0) {
        foreach ($Q->result() as $R) {
            getLeafTopicIds($leafs, $R->source_topic_id);
        }
    } else { # we have a leaf, so just return it.
        $leafs[] = $root;
    }
}

function checkNoteXrefIDs()
{
    $count = 0;
    //note: this one should still be reimplemented.
    return $count;
}

function checkAuthorPublicationLinks()
{
	$count = 0;
	$Q = mysql_query("SELECT ".AIGAION_DB_PREFIX."publicationauthorlink.author_id 
	                    FROM ".AIGAION_DB_PREFIX."publicationauthorlink 
	                      LEFT JOIN ".AIGAION_DB_PREFIX."author 
	                             ON (".AIGAION_DB_PREFIX."publicationauthorlink.author_id = ".AIGAION_DB_PREFIX."author.author_id) 
	                    WHERE ".AIGAION_DB_PREFIX."author.author_id IS NULL");
	if (mysql_num_rows($Q) > 0)
	{
		while ($R = mysql_fetch_array($Q))
		{
			$Q2 = mysql_query("DELETE FROM ".AIGAION_DB_PREFIX."publicationauthorlink WHERE author_id = '".$R['author_id']."'");
			$count++;
		}
	}
	return $count;
}

function checkEmptyAuthorNames()
{
    $CI = &get_instance();
	$result = "";
	$report = "";
	foreach ($CI->author_db->getAllAuthors() as $author) {
	    if ($author->surname=='' && $author->firstname=='') {
            $report .= "<li>".anchor('authors/show/'.$author->author_id,'Author #'.$author->author_id)."</li>\n";	        
	    }
	}
    if ($report != '') {
		$result = "<ul>\n".$report."</ul>\n";
	}
	return $result;
}


function checkSimilarAuthors()
{
    $CI = &get_instance();
	$report = "";
	$result = "";
	foreach ($CI->author_db->getAllAuthors() as $author) {
        $similar = $author->getSimilarAuthors();
        if (count($similar)>0) {
            foreach ($similar as $simauth) {
                echo '<li>'
                    .anchor('authors/show/'.$author->author_id, $author->getName(), array('title' => 'Click to show details'))
                    .anchor('authors/show/'.$simauth->author_id, $simauth->getName(), array('title' => 'Click to show details'))
    		        .'('.anchor('authors/merge/'.$author->author_id.'/'.$simauth->author_id, 'merge', array('title' => 'Click to merge')).")</li>\n";
    		}
        }
    }	

	if ($report != "")
		$result .= "<ul>\n".$report."</ul>\n";

	return $result;
}


function checkNonPublishingAuthors()
{
    $CI = &get_instance();
	$result = "";
	$report = "";

	$Q = mysql_query(
			"SELECT ".AIGAION_DB_PREFIX."author.*
			FROM ".AIGAION_DB_PREFIX."author
			LEFT JOIN ".AIGAION_DB_PREFIX."publicationauthorlink 
			       ON (".AIGAION_DB_PREFIX."author.author_id = ".AIGAION_DB_PREFIX."publicationauthorlink.author_id)
			WHERE ".AIGAION_DB_PREFIX."publicationauthorlink.author_id IS NULL
			ORDER BY cleanname");
	if (mysql_num_rows($Q) > 0) {

		while ($R = mysql_fetch_array($Q)) {
		    $author = $CI->author_db->getByID($R['author_id']);
			$report .= "<li>".anchor('authors/show/'.$author->author_id,$author->getName())."</li>\n";
		}

		$result .= "<ul>\n".$report."</ul>\n";
	}

	return $result;
}

/** Delete all authors without publications. No other checks, no feedback other than the number of deleted authors. */
function deleteNonPublishingAuthors() {
    $CI = &get_instance();
    $Q = mysql_query(
		"SELECT ".AIGAION_DB_PREFIX."author.*
		FROM ".AIGAION_DB_PREFIX."author
		LEFT JOIN ".AIGAION_DB_PREFIX."publicationauthorlink 
		       ON (".AIGAION_DB_PREFIX."author.author_id = ".AIGAION_DB_PREFIX."publicationauthorlink.author_id)
		WHERE ".AIGAION_DB_PREFIX."publicationauthorlink.author_id IS NULL
		ORDER BY cleanname");
    $num = 0;
	if (mysql_num_rows($Q) > 0) {
		while ($R = mysql_fetch_array($Q)) {
		    $author = $CI->author_db->getByID($R['author_id']);
			$author->delete();
			$num++;
		}
	}
    return "Deleted ".$num." authors who do not have any publications.<br/>";
}


?>