<div class="rightsprofile-summary">
<?php
/**
views/rightsprofiles/summary

Shows a summary of a rightsprofile: edit link, name, delete link, etc

Parameters:
    $rightsprofile=>the Rightsprofile object that is to be summarized
*/
    echo anchor('rightsprofiles/edit/'.$rightsprofile->rightsprofile_id,'[edit]')."&nbsp;"
    .anchor('rightsprofiles/delete/'.$rightsprofile->rightsprofile_id,'[delete]')."&nbsp;"
    .$rightsprofile->name;
?>
</div>