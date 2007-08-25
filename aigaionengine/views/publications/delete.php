<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/**
views/publications/delete

Shows the confirm form for deleting a publication.

Parameters:
    $publication=>the publication object that is to be deleted
*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('publications/delete/'.$publication->pub_id.'/commit');
echo "Are you sure that you want to delete the publication '".$publication->title."'?<p>\n";
echo form_submit('confirm','Confirm');
echo form_close();
echo form_open('publications/show/'.$publication->pub_id);
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";
?>