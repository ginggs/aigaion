<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/bookmarklist/setattaccesslevel

Shows the confirm form for setting access level of all attachments of publications on the bookmarklist

Parameters:

*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('bookmarklist/setattaccesslevel/commit');
echo form_hidden('accesslevel',$accesslevel);
echo "Are you sure that you want to set the read access level for all attachments of publications on the bookmarklist to '".$accesslevel."'? There is no undo!<p>\n";
echo form_submit('confirm','Confirm');
echo form_close();
echo form_open('bookmarklist');
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";
?>