<div id='singletopic-content-holder'>
<!-- Topic: HEADER AND DESCRIPTION -->
<?php
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
    <?php echo anchor('topics/edit/'.$topic->topic_id,'[edit]')."&nbsp;".anchor('topics/delete/'.$topic->topic_id,'[delete]')."<br/>\n<br/>"; ?>
</div>
<div class='header'>Topic:
<?php 
    echo $name;
?>
</div>
<?php 
    if ($topic->url != '') {
        $urlname = prep_url($topic->url);
        if (strlen($urlname)>15) {
            $urlname = substr($urlname,0,14)."...";
        }
        echo "URL: <a href='".prep_url($topic->url)."' target='_blank'>[".$urlname."]</a><br><br>";
    }
    if ($description)
        echo $description."<br/>";
?>


<?php
    
  if (isset($publicationlist))
    $this->load->view('publications/list', $publicationlist);
?>
</div>