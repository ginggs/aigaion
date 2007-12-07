<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/site/stats

Shows a block of site stats

Parameters:
    none

*/
	//get number of authors
    $authorCount = $this->author_db->getAuthorCount();
    
	//get number of maintopics
    $topicCount = $this->topic_db->getMainTopicCount();

	$publicationCount = $this->topic_db->getPublicationCountForTopic(1);
	$publicationReadCount = $this->topic_db->getReadPublicationCountForTopic(1);

	echo "
<p class='header1'>Aigaion statistics</p>
<ul>
<li>{$publicationCount} Publications ({$publicationReadCount} read)</li>
<li>{$authorCount} Authors</li>
<li>{$topicCount} Main topics</li>
</ul>
";
?>