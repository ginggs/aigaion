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
  if ($description)
      echo $description."<br/>";
?>
<br/>
Show subtopics?<br />

<?php
    
  if (isset($publicationlist))
    $this->load->view('publications/list', $publicationlist);
?>
</div>