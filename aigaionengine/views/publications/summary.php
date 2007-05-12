<?php 
$summaryfields = getPublicationSummaryFieldArray($publication->pub_type); 
echo "
    <div class='publication_summary ".$even."' 
         id='publicationsummary".$publication->pub_id."'>
      <table width='100%'>
        <tr>
            <td>
                <span class='title'>
                ".anchor('publications/show/'.$publication->pub_id, $publication->title, array('title' => 'View publication details'))."
                </span>
";
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
  
  echo  "<span class='author'>"
       .anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))
       ."</span>";
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

echo "
            </td>
            <td width='8%' align='right' valign='top'>

                <nobr>
                <span id='bookmark_pub_".$publication->pub_id."'>";
if (getUserLogin()->hasRights('bookmarklist')) {
    if ($publication->isBookmarked) {
        echo $this->ajax->link_to_remote("[UnBookmark]", 
                         array('url' => site_url('/bookmarklist/removepublication/'.$publication->pub_id), 
                               'update' => 'bookmark_pub_'.$publication->pub_id
                               )
                         );
    } else {
        echo $this->ajax->link_to_remote("[Bookmark]", 
                         array('url' => site_url('/bookmarklist/addpublication/'.$publication->pub_id), 
                               'update' => 'bookmark_pub_'.$publication->pub_id
                               )
                         );
    }
}
echo "
                </span>
                </nobr>

            </td>
        </tr>
        <tr>
            <td colspan=2>";
        
$notes = $publication->getNotes();
echo "<ul class='notelist'>";
foreach ($notes as $note) {
    echo "<li>".$this->load->view('notes/summary',
                      array('note'   => $note),
                      true)."</li>";
}
echo "</ul>";
echo "

            </td>
        </tr>

    </table>
</div>
"; //end of publication_summary div

?>


