
<!-- Single note display -->
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
foreach ($note->xref_ids as $xref_id) {
	$link = $bibtexidlinks[$xref_id];
	//check whether the xref is present in the session var (should be). If not, try to correct the issue.
	if ($link == "") {
		$Q = $this->db->query("SELECT bibtex_id FROM publication WHERE pub_id = ".$xref_id);
		if ($Q->num_rows() > 0) {
			$R = $Q->row();
			if (trim($R->bibtex_id) != "") {
				$bibtexidlinks[$R[$xref_id] ] = array($R->bibtex_id, "/\b(?<!\.)(".preg_quote($R->bibtex_id, "/").")\b/");
			}
		}
	}

	if ($link != "") {
		$text = preg_replace(
			$link[1],
			anchor('/publications/show/'.$xref_id,$link[0]),
			$text);
	}
}

echo "<div class='readernote'>
  <b>[".getAbbrevForUser($note->user_id)."]</b>: ";
$accesslevels = $this->accesslevels_lib->getAccessLevelSummary($note);
echo anchor('accesslevels/edit/note/'.$note->note_id,$accesslevels,array('title'=>'click to modify access levels'));
      
  echo $text;

//the block of edit actions: dependent on user rights
$userlogin  = getUserLogin();
$user       = $this->user_db->getByID($userlogin->userID());

if (    ($userlogin->hasRights('note_edit'))
     && 
        $this->accesslevels_lib->canEditObject($note)      
    ) 
{
    echo "<br/>".anchor('notes/delete/'.$note->note_id,'[delete]');
    echo "&nbsp;".anchor('notes/edit/'.$note->note_id,'[edit]');
}
?>
</div>
<!-- End of single note display -->
