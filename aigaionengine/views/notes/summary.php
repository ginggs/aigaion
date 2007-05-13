<!-- Single attachment displays -->
<?php
/**
views/notes/summary

Shows a summary of a note: who entered it, what is the text, and some edit buttons etc

Parameters:
    $note=>the Note object that is to be shown
    
appropriate read rights are assumed. Edit block depends on other rights.
*/
//get text, replace links
$text = auto_link($note->text);

//replace bibtex cite_ids that appear in the text with a link to the publication
$link = "";
$bibtexidlinks = getBibtexIdLinks();
foreach ($note->xref_ids as $xref) {
	$link = $bibtexidlinks[$xref];
	//check whether the xref is present in the session var (should be). If not, try to correct the issue.
	if ($link == "") {
		$Q = mysql_query("SELECT bibtex_id FROM publication WHERE pub_id = ".$xref);
		if (mysql_num_rows($Q) > 0) {
			$R = mysql_fetch_array($Q);
			if (trim($R['bibtex_id']) != "") {
				$bibtexidlinks[$R[$xref] ] = array($R['bibtex_id'], "/\b(?<!\.)(".preg_quote($R['bibtex_id'], "/").")\b/");
			}
		}
	}

	if ($link != "") {
		$text = preg_replace(
			$link[1],
			anchor('/publications/show/'.$xref,$link[0]),
			$text);
	}
}
echo "<div class='readernote'><b>[User ".$note->user_id."]</b>: " . $text;

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
     &&
        (    ($note->edit_access_level != 'group') 
          || (in_array($note->group_id,$this->user_db->getByID($userlogin->userId())->group_ids) ) 
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
