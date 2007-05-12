<div id='singletopic-content-holder'>
<!-- Topic: HEADER AND DESCRIPTION -->
<?php
    $userlogin = getUserLogin();

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
            (!$userlogin->isAnonymous() || ($topic->edit_access_level=='public'))
         &&
            (    ($topic->edit_access_level != 'private') 
              || ($userlogin->userId() == $topic->user_id) 
              || ($userlogin->hasRights('topic_edit_all'))
             )                
         &&
            (    ($topic->edit_access_level != 'group') 
              || (in_array($topic->group_id,$this->user_db->getByID($userlogin->userId())->group_ids) ) 
              || ($userlogin->hasRights('topic_edit_all'))
             )                
        ) 
    {
        echo anchor('topics/edit/'.$topic->topic_id,'[edit]')."&nbsp;".anchor('topics/delete/'.$topic->topic_id,'[delete]')."<br/>\n<br/>"; 
    }
    ?>
</div>
<div class='header'>Topic:
<?php 
    echo $name;
?>
</div>
<?php 
    if ($topic->url != '') {
        $urlname = prep_url($topic->url);
        if (strlen($urlname)>21) {
            $urlname = substr($urlname,0,20)."...";
        }
        echo "URL: <a href='".prep_url($topic->url)."' target='_blank'>[".$urlname."]</a><br><br>";
    }
    if ($description)
        echo $description."<br/>";
?>


<?php
    
  if (isset($publications))
    $this->load->view('publications/list', $publications);
?>
</div>