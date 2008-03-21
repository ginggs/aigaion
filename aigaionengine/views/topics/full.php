<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id='singletopic-content-holder'>
<!-- Topic: HEADER AND DESCRIPTION -->
<?php

    $userlogin  = getUserLogin();
    $user       = $this->user_db->getByID($userlogin->userID());


    if ($topic->name=="") {
        $name = "Topic #".$topic->topic_id;
    } else {
        $name = $topic->name;
    }
    if ($topic->description != null) {
        $description = $topic->description;
    } else {
        $description = "-no description-";
    }

$parent = $topic->getParent();
echo anchor('topics/single/'.$parent->topic_id,$parent->name);
echo '<br/>&nbsp;&nbsp;<img class="icon" src="'.getIconUrl('small_arrow.gif').'" alt="icon"/><br/>';
?>
<div class='optionbox'>
    <?php 
    if (($userlogin->hasRights('topic_edit'))
         && $this->accesslevels_lib->canEditObject($topic)      
        ) 
    {
        echo '['.anchor('topics/edit/'.$topic->topic_id,'edit')."]&nbsp;[".anchor('topics/delete/'.$topic->topic_id,'delete').']'; 
    }
    echo "\n";
    ?>
</div>
<div class='header'>Topic:
<?php 
    echo $name;
    $accesslevels = "&nbsp;&nbsp;r:<img class='rights_icon' src='".getIconurl('rights_'.$topic->derived_read_access_level.'.gif')."' alt='rights icon'/> e:<img class='rights_icon' src='".getIconurl('rights_'.$topic->derived_edit_access_level.'.gif')."' alt='rights_icon'/>";
    if (($userlogin->hasRights('topic_edit')) && $this->accesslevels_lib->canEditObject($topic)) 
    {
    echo anchor('accesslevels/edit/topic/'.$topic->topic_id,$accesslevels,array('title'=>'click to modify access levels'));
    }

?>
</div>

<table class='fullwidth'>
<tr>
    <td class='fullwidth'>
<?php
$this->load->vars(array('subviews'  => array('topics/simpletreerow'=>array())));
echo "<div id='topictree-holder'>\n<ul class='topictree-list'>\n"
            .$this->load->view('topics/tree',
                              array('topics'   => $topic,
                                    'showroot'  => False,
                                    'depth'     => 2
                                    ),  
                              true)."</ul>\n</div>\n";

    if ($topic->url != '') {
        $this->load->helper('utf8');
        $urlname = prep_url($topic->url);
        if (utf8_strlen($urlname)>21) {
            $urlname = utf8_substr($urlname,0,30)."...";
        }
        echo "URL: <a  title='".prep_url($topic->url)."' href='".prep_url($topic->url)."' class='open_extern'>".$urlname."</a><br/><br/>\n";
    }
    if ($description)
        echo "<p>".$description."</p>\n";
?>
    </td>
    <td>
      <div class="topicstats">
<?php 

	//Get statistics for this topic
  $authorCount          = $this->topic_db->getAuthorCountForTopic($topic->topic_id);
  $topicCount           = count($topic->getChildren());
	$publicationCount     = $this->topic_db->getPublicationCountForTopic($topic->topic_id);
	$publicationReadCount = $this->topic_db->getReadPublicationCountForTopic($topic->topic_id);

	echo "<ul>
<li class='nobr'>{$publicationCount} Publications ({$publicationReadCount} read)</li>
<li class='nobr'>{$authorCount} Authors [".anchor('authors/fortopic/'.$topic->topic_id,'view', 'title="view authors for topic"')."]</li>
<li class='nobr'>{$topicCount} Subtopics [".anchor('topics/add/'.$topic->topic_id,'create new', 'title="create new subtopic"')."]</li>\n";

  if ($userlogin->hasRights('bookmarklist')) {
    echo "<li class='nobr'>[".anchor('bookmarklist/addtopic/'.$topic->topic_id,'BookmarkAll')."]</li>\n";
    echo "<li class='nobr'>[".anchor('bookmarklist/removetopic/'.$topic->topic_id,'UnBookmarkAll')."]</li>\n";
  }
  echo "</ul>\n";
?>
      </div>
   </td>
</tr>
</table>

<?php
    
  if (isset($publications))
    $this->load->view('publications/list', $publications);
?>

</div> 