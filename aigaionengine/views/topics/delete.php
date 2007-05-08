<?php
/**
views/topics/delete

Shows the confirm form for deleting a topic.

Parameters:
    $topic=>the topic object that is to be deleted
*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('topics/delete/'.$topic->topic_id.'/commit');
echo "Are you sure that you want to delete topic \"".$topic->name."\"?<p>\n";
echo form_submit('confirm','Confirm');
echo form_close();
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";

?>