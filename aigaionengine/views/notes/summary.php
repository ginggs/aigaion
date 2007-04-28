<!-- Single attachment displays -->
<?php
/**
views/notes/summary

Shows a summary of a note: who entered it, what is the text, and some edit buttons etc

Parameters:
    $note=>the Note object that is to be shown
*/

echo "<div class='readernote'><b>[User ".$note->user_id."]</b>: " . $note->text;
echo "<br>".anchor('notes/delete/'.$note->note_id,'[delete]');
echo "&nbsp;".anchor('notes/edit/'.$note->note_id,'[edit]');
echo "&nbsp;[some link for setting access levels]";
echo "</div>\n";
?>
<!-- End of single attachment displays -->
