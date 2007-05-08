<?php
/**
views/rightsprofiles/delete

Shows the confirm form for deleting a rightsprofile.

Parameters:
    $rightsprofile=>the Rightsprofile object that is to be deleted
*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('rightsprofiles/delete/'.$rightsprofile->rightsprofile_id.'/commit');
echo "Are you sure that you want to delete rightsprofile \"".$rightsprofile->name."\"?<p>\n";
echo form_submit('confirm','Confirm');
echo form_close();
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";

?>