<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/attachments/delete

Shows the confirm form for deleting an attachment.

Parameters:
    $attachment=>the Attachment object that is to be deleted

we assume that this view is not loaded if you don't have the appropriate read and edit rights (checked in the controllers)
*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('attachments/delete/'.$attachment->att_id.'/commit');
echo "Are you sure that you want to delete attachment \"".$attachment->name."\"?<p>\n";
echo form_submit('confirm','Confirm');
echo form_close();
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";

?>