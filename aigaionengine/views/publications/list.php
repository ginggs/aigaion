<div class='publication_list'>
<?php
  $userlogin  = getUserLogin();
  if (isset($header) && ($header != '')) {
?>
  <div class='header'><?php echo $header ?></div>
<?php
  }
  $multipagelinks='';
  //this block of code is used to display the multi-page-links. See the publications/showlist controller for how to use this - and make sure you set all parameters used there!
  if (isset($multipage) && ($multipage == True)) {
    $page=-1;
    $liststyle = $userlogin->getPreference('liststyle');
    if ($liststyle>0) {
        $multipagelinks.= '<center><div>';
        while ($page*$liststyle<$resultcount) {
            $page++;
            $multipagelinks.= ' | ';
            $linktext = ($page*$liststyle+1).'-'.(($page+1)*$liststyle);
            if ($page!=$currentpage) {
                $multipagelinks.= anchor($multipageprefix.$page,$linktext);
            } else {
                $multipagelinks.= $linktext;
            }
        }
        $multipagelinks.= ' |</div></center><br/>';
    }
  }
  echo $multipagelinks;
  $b_even = true;

  foreach ($publications as $publication)
  {
    if ($publication!=null) {
      $b_even = !$b_even;
    if ($b_even)
      $even = 'even';
    else
      $even = 'odd';
    
    $summaryfields = getPublicationSummaryFieldArray($publication->pub_type);

echo "<div class='publication_summary ".$even."' id='publicationsummary".$publication->pub_id."'>
<table width='100%'>
  <tr>
    <td>
      <span class='title'>".anchor('publications/show/'.$publication->pub_id, $publication->title, array('title' => 'View publication details'))."</span>";
$num_authors    = count($publication->authors);
$current_author = 1;

foreach ($publication->authors as $author)
{
  if (($current_author == $num_authors) & ($num_authors > 1)) {
    echo " and ";
  }
  else {
    echo ", ";
  }

  echo  "<span class='author'>".anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))."</span>";
  $current_author++;
}

foreach ($summaryfields as $key => $prefix) {
  $val = trim($publication->$key);
  if ($val) {
    echo $prefix.$val;
  }
}

echo "
    </td>
    <td width='8%' align='right' valign='top'>
      <nobr><span id='bookmark_pub_".$publication->pub_id."'>";
if ($userlogin->hasRights('bookmarklist')) {
  if ($publication->isBookmarked) {
    echo $this->ajax->link_to_remote("[UnBookmark]",
          array('url'     => site_url('/bookmarklist/removepublication/'.$publication->pub_id),
                'update'  => 'bookmark_pub_'.$publication->pub_id
                )
          );
  } 
  else {
    echo $this->ajax->link_to_remote("[Bookmark]",
          array('url'     => site_url('/bookmarklist/addpublication/'.$publication->pub_id),
                'update'  => 'bookmark_pub_'.$publication->pub_id
                )
          );
  }
}
echo "</span>";
$attachments = $publication->getAttachments();
if (count($attachments) != 0)
{
//  echo $this->load->view('attachments/icon_link',
//                         array('attachment'   => $attachments[0]),
//                         true);
    if ($attachments[0]->isremote) {
        echo "<a href='".prep_url($attachments[0]->location)."' target='_blank'><img title='Download ".htmlentities($attachments[0]->name,ENT_QUOTES)."' class='icon' src='".getIconUrl("attachment_html.gif")."'/></a>\n";
    } else {
        $iconUrl = getIconUrl("attachment.gif");
        $extension=strtolower(substr(strrchr($attachments[0]->location,"."),1));
        if (iconExists("attachment_".$extension.".gif")) {
            $iconUrl = getIconUrl("attachment_".$extension.".gif");
        }
        echo anchor('attachments/single/'.$attachments[0]->att_id,"<img class='icon' src='".$iconUrl."'/>" ,array('title'=>'Download '.$attachments[0]->name))."\n";
    }
}  
echo "</nobr>
    </td>
  </tr>";
$notes = $publication->getNotes();
if ($notes != null) {
echo "
  <tr>
    <td colspan=2>
      <ul class='notelist'>";
  foreach ($notes as $note) {
    echo "
        <li>".$this->load->view('notes/summary', array('note' => $note), true)."</li>";
  }
  echo "
      </ul>
    </td>
  </tr>";
}
echo "
</table>
</div>

"; //end of publication_summary div

    }
  }

echo $multipagelinks;
?>
</div>