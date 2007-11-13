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
echo '<br/>&nbsp;&nbsp;<img class="icon" src="'.getIconUrl('small_arrow.gif').'"/><br/>';
?>
<div class='optionbox'>
    <?php 
    if (    ($userlogin->hasRights('topic_edit'))
         && 
            $this->accesslevels_lib->canEditObject($topic)      
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
    $accesslevels = "&nbsp;&nbsp;r:<img class='al_icon' src='".getIconurl('al_'.$topic->derived_read_access_level.'.gif')."'/> e:<img class='al_icon' src='".getIconurl('al_'.$topic->derived_edit_access_level.'.gif')."'/>";
    echo anchor('accesslevels/edit/topic/'.$topic->topic_id,$accesslevels,array('title'=>'click to modify access levels'));

?>
</div>

<table width='100%'>
<tr>
    <td  width='100%'>
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
            $urlname = utf8_substr($urlname,0,20)."...";
        }
        echo "URL: <a href='".prep_url($topic->url)."' target='_blank'>[".$urlname."]</a><br/><br/>\n";
    }
    if ($description)
        echo "<p>".$description."</p>\n";
?>
    </td>
    <td>
<?php 
//echo '&nbsp;&nbsp;&nbsp;&nbsp;<img class="icon" src="'.getIconUrl('small_arrow.gif').'"/>';
echo '<div style="border:1px solid black;padding-right:0.2em;margin:0.2em;">';
	//get number of authors
    $authorCount = $this->topic_db->getAuthorCountForTopic($topic->topic_id);
    
	//get number of maintopics
    $topicCount = count($topic->getChildren());

	$publicationCount = $this->topic_db->getPublicationCountForTopic($topic->topic_id);
	$publicationReadCount = $this->topic_db->getReadPublicationCountForTopic($topic->topic_id);

	echo "
<ul>
<li><nobr>{$publicationCount} Publications ({$publicationReadCount} read)</nobr></li>
<li><nobr>{$authorCount} Authors </nobr><br/><nobr>[".anchor('authors/fortopic/'.$topic->topic_id,'view authors on this topic')."]</nobr></li>
<li><nobr>{$topicCount} Subtopics </nobr><br/><nobr>[".anchor('topics/add/'.$topic->topic_id,'create new subtopic')."]</nobr></li>";
    if ($userlogin->hasRights('bookmarklist')) {
      echo  '<li><nobr>['
           .anchor('bookmarklist/addtopic/'.$topic->topic_id,'BookmarkAll')
           .']</nobr></li><li><nobr>['
           .anchor('bookmarklist/removetopic/'.$topic->topic_id,'UnBookmarkAll').']</nobr></li>';
    }
//echo  "<li><nobr>["
//      .anchor('export/topic/'.$topic->topic_id,'Export')."]</nobr></li>
echo  "
</ul>
";

echo '</div>';
?>
    </td>
</tr>
</table>

<?php
    
  if (isset($publications))
    $this->load->view('publications/list', $publications);
?>

</div> 