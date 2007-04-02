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
<span class='header1'>Topic:
<?php 
    echo $name;
?>
</span>
<br>
Show subtopics?
<br>

<?php
    echo anchor('topics/edit/'.$topic->topic_id,'[edit]')."<br/>";
    echo $description;
?> 

</div>