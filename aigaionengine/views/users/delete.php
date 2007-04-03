<?php
/**
views/users/delete

Shows the confirm form for deleting a user.

Parameters:
    $user=>the User object that is to be deleted
*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('users/delete/'.$user->user_id.'/commit');
echo "Are you sure that you want to delete user \"".$user->login."\"?<p>\n";
echo form_submit('confirm','Confirm');
echo form_close();
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";

?>