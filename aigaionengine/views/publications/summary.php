<?php 
$summaryfields = getPublicationSummaryFieldArray($publication->type); 
echo "<div class='publication_summary ".$even."' id='publicationsummary".$publication->pub_id."'>\n";
echo "<table width='100%'><tr><td>";
echo "  <span class='title'>".anchor('publications/show/'.$publication->pub_id, $publication->title, array('title' => 'View publication details'))."</span>";

$num_authors    = count($publication->authors);
$current_author = 1;

foreach ($publication->authors as $author)
{
  if (($current_author == $num_authors) & ($num_authors > 1))
  {
    echo " and ";
  }
  else
  {
    echo ", ";
  }
  
  echo "<span class='author'>".anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))."</span>";
  $current_author++;
}

foreach ($summaryfields as $key => $prefix)
{
  $val = trim($publication->$key);

  if ($val)
  {
    echo $prefix.$val;
  }
}

echo ".\n";
echo "</td><td width='8%' align='right'>";
echo "<nobr>".form_checkbox(array('name'=> 'publication', 'id' => 'pub_'.$publication->pub_id, 'checked' => false))."</nobr></div>\n";

echo "</td></tr>";
echo "<tr><td colspan=2>";
    $notes = $publication->getNotes();
    echo "<ul class='notelist'>";
    foreach ($notes as $note) {
        echo "<li>".$this->load->view('notes/summary',
                          array('note'   => $note),
                          true)."</li>";
    }
    echo "</ul>";
echo "</td></tr>";

echo "</table></div>\n"; //end of publication_summary div

?>


