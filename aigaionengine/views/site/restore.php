<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/site/restore

Shows a form for restoring the site backup.

*/
$this->load->helper('form');


echo "<div class='editform'>";
echo "<div class='errormessage'>UNSTABLE FUNCTION IN TESTING PHASE. DON'T USE THIS IF YOU DON'T HAVE A GOOD BACKUP OF YOUR LATEST DATA.<br><br>Most importantly, if you don't paste the full contents of an Aigaion 2 backup file here, the database will end up being corrupted.</div>";
echo form_open_multipart('site/restorefromsql');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','restorefromsql');

echo "<p class='header'>RESTORE DATABASE FROM SQL</p>";

?>
    <table width='100%'>
        <tr><td><label for='BACKUPDATA'>Paste here your SQL data</label></td>
            <td><?php echo form_textarea(array('name' => 'backup_data', 
                                               'id' => 'backup_data', 
                                               'cols' => '87', 
                                               'rows' => '30', 
                                               'alt' => 'backup data'), 
                                         ''); ?></td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'>
	        <p><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Paste backup sql here. Note: this will overwrite all data curently in this database! Don't do this if you do not have a backup file of the current status of the database!</p></td>
	    </tr>
	    

        <tr><td>
<?php
    echo form_submit('submit','Restore');
?>
        </td>
        </tr>
    </table>
<?php
echo form_close();
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
?>
</div>