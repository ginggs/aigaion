<?php
/**
views/groups/delete

Shows the confirm form for deleting a group.

Parameters:
    $group=>the Group object that is to be deleted
*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('groups/delete/'.$group->group_id.'/commit');
echo "Are you sure that you want to delete group \"".$group->name."\"?<p>\n";
echo form_submit('confirm','Confirm');
echo form_close();
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";

?>