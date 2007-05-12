<!-- Single attachment displays -->
<?php
/**
views/notes/summary

Shows a summary of a note: who entered it, what is the text, and some edit buttons etc

Parameters:
    $note=>the Note object that is to be shown
    
appropriate read rights are assumed. Edit block depends on other rights.
*/

echo "<div class='readernote'><b>[User ".$note->user_id."]</b>: " . $note->text;

//the block of edit actions: dependent on user rights
$userlogin = getUserLogin();
if (    ($userlogin->hasRights('note_edit_self'))
     && 
        (!$userlogin->isAnonymous() || ($note->edit_access_level=='public'))
     &&
        (    ($note->edit_access_level != 'private') 
          || ($userlogin->userId() == $note->user_id) 
          || ($userlogin->hasRights('note_edit_all'))
         )                
    ) 
{
    echo "<br>".anchor('notes/delete/'.$note->note_id,'[delete]');
    echo "&nbsp;".anchor('notes/edit/'.$note->note_id,'[edit]');
}
echo "</div>\n";
?>
<!-- End of single attachment displays -->
