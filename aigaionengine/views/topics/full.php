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
        echo '['.anchor('topics/edit/'.$topic->topic_id,$this->lang->line('main_edit'))."]&nbsp;[".anchor('topics/delete/'.$topic->topic_id,$this->lang->line('main_delete')).']'; 
    }
    echo "\n";
    ?>
</div>
<div class='header'>
<?php 
    echo $this->lang->line('main_topic').": ";
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
        
echo "<p class=header2>Subtopics:</p>\n";        
$this->load->vars(array('subviews'  => array('topics/simpletreerow'=>array())));

echo "<div id='topictree-holder'>\n<ul class='topictree-list'>\n"
            .$this->load->view('topics/tree',
                              array('topics'   => $topic,
                                    'showroot'  => False,
                                    'depth'     => 2
                                    ),  
                              true)."<li></li></ul>\n</div>\n";
?>
    </td>
    <td>
<?php 
  $topicstatBlock = "";
	//Get statistics for this topic
  $authorCount          = $this->topic_db->getAuthorCountForTopic($topic->topic_id);
  $topicCount           = count($topic->getChildren());
	$publicationCount     = $this->topic_db->getPublicationCountForTopic($topic->topic_id);
	$publicationReadCount = $this->topic_db->getReadPublicationCountForTopic($topic->topic_id);

if ($publicationCount ==1) 
	$topicstatBlock .= "
<ul>
<li class='nobr'>{$publicationCount} ".$this->lang->line('main_publication')." ({$publicationReadCount} read)</li>";
else 
	$topicstatBlock .= "
<ul>
<li class='nobr'>{$publicationCount} ".$this->lang->line('main_publications')." ({$publicationReadCount} read)</li>";
if ($authorCount ==1)
$topicstatBlock .="
<li class='nobr'>{$authorCount} ".$this->lang->line('main_author')." [".anchor('authors/fortopic/'.$topic->topic_id,'view', 'title="view author for topic"')."]</li>";
else 
$topicstatBlock .="<li class='nobr'>{$authorCount} ".$this->lang->line('main_authors')." [".anchor('authors/fortopic/'.$topic->topic_id,'view', 'title="view authors for topic"')."]</li>";
if ($topicCount>0)
{
  if ($topicCount==1)
  $topicstatBlock .=
  "
  <li class='nobr'>{$topicCount} Sub".$this->lang->line('main_topic')." ";
  else
  $topicstatBlock .= "<li class='nobr'>{$topicCount} Sub".$this->lang->line('main_topics')." ";
} else
{
  $topicstatBlock .= "<li class='nobr'>No sub".$this->lang->line('main_topics')." ";
}
if ($userlogin->hasRights('topic_edit')) {
  $topicstatBlock .= "[".anchor('topics/add/'.$topic->topic_id,'create new', 'title="create new subtopic"')."]";
}
$topicstatBlock .= "</li>\n";
  if ($userlogin->hasRights('bookmarklist')) {
    $topicstatBlock .= "<li class='nobr'>[".anchor('bookmarklist/addtopic/'.$topic->topic_id,'BookmarkAll')."]</li>\n";
    $topicstatBlock .= "<li class='nobr'>[".anchor('bookmarklist/removetopic/'.$topic->topic_id,'UnBookmarkAll')."]</li>\n";
  }
  $topicstatBlock .= "</ul>\n";
  
if ($topicstatBlock != '') 
{
	echo "
	<div class='topicstats'>
	".$topicstatBlock."
  </div>";
}
?>
      
   </td>
</tr>
</table>

<?php
    
  if (isset($publications))
    $this->load->view('publications/list', $publications);
?>

</div> 