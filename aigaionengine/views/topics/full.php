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
    if ($userlogin->hasRights('bookmarklist')) {
      echo  '&nbsp;['
           .anchor('bookmarklist/addtopic/'.$topic->topic_id,'BookmarkAll')
           .']&nbsp;['
           .anchor('bookmarklist/removetopic/'.$topic->topic_id,'UnBookmarkAll').']';
    }
    echo  '&nbsp;['
           .anchor('export/topic/'.$topic->topic_id,'BiBTeX',array('target'=>'aigaion_export')).']';
    echo "<br/><br/>\n";
    ?>
</div>
<div class='header'>Topic:
<?php 
    echo $name;
    $accesslevels = "&nbsp;&nbsp;r:<img class='al_icon' src='".getIconurl('al_'.$topic->derived_read_access_level.'.gif')."'/> e:<img class='al_icon' src='".getIconurl('al_'.$topic->derived_edit_access_level.'.gif')."'/>";
    echo anchor('accesslevels/edit/topic/'.$topic->topic_id,$accesslevels,array('title'=>'click to modify access levels'));

?>
</div>
<?php 
    if ($topic->url != '') {
        $urlname = prep_url($topic->url);
        if (strlen($urlname)>21) {
            $urlname = substr($urlname,0,20)."...";
        }
        echo "URL: <a href='".prep_url($topic->url)."' target='_blank'>[".$urlname."]</a><br/><br/>\n";
    }
    if ($description)
        echo "<p>".$description."</p>\n";
?>


<?php
    
  if (isset($publications))
    $this->load->view('publications/list', $publications);
?>
</div>