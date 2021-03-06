<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/authors/delete

Shows the confirm form for deleting an author.

Parameters:
    $author=>the author object that is to be deleted
*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('authors/delete/'.$author->author_id.'/commit');
echo sprintf(__('Are you sure that you want to delete the author "%s"?'), $author->getName())."<p>\n";
echo form_submit('confirm',__('Confirm'));
echo form_close();
echo form_open('authors/show/'.$author->author_id);
echo form_submit('cancel',__('Cancel'));
echo form_close();
echo "</div>";
?>