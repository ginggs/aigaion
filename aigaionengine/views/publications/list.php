<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class='publication_list'>
<?php
  //note that when 'order' is set, this view supposes that the data is actually ordered in that way! Otherwise the headers won't work :)
  if (!isset($order))$order='year';
  
  $userlogin  = getUserLogin();
  if (isset($header) && ($header != '')) {
?>
  <div class='header'><?php echo $header ?></div>
<?php
  }
  $multipagelinks='';
  //this block of code is used to display the multi-page-links. See the publications/showlist controller for how to use this - and make sure you set all parameters used there!
  if (isset($multipage) && ($multipage == True)) {
    $page=0;
    $liststyle = $userlogin->getPreference('liststyle');
    if ($liststyle>0) {
        $multipagelinks.= '<center><div>';
        while ($page*$liststyle<$resultcount) {
            $multipagelinks.= ' | ';
            $linktext = ($page*$liststyle+1).'-';
            if (($page+1)*$liststyle>$resultcount) {
                $linktext .= $resultcount;
            } else {
                $linktext .= (($page+1)*$liststyle);
            }
            if ($page!=$currentpage) {
                $multipagelinks.= anchor($multipageprefix.$page,$linktext);
            } else {
                $multipagelinks.= '<b>'.$linktext.'</b>';
            }
            $page++;
        }
        $multipagelinks.= ' |</div></center><br/>';
    }
  }
  echo $multipagelinks;
  $b_even = true;
  
  $subheader = '';
  $subsubheader = '';
  
  foreach ($publications as $publication)
  {
    if ($publication!=null) {
      $b_even = !$b_even;
    if ($b_even)
      $even = 'even';
    else
      $even = 'odd';
   
    //check whether we should display a new header/subheader, depending on the $order parameter
    switch ($order) {
      case 'year':
        $newsubheader = $publication->actualyear;
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          echo '<div><br/></div><div class="header">'.$subheader.'</div><div><br/></div>';
        }
        break;
      case 'title':
        $newsubheader = $publication->cleantitle[0];
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          echo '<div><br/></div><div class="header">'.strtoupper($subheader).'</div><div><br/></div>';
        }
        break;
      case 'type':
        $newsubheader = $publication->pub_type;
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          echo '<div><br/></div><div class="header">Publications of type '.$subheader.'</div><div><br/></div>';
        }
        if ($publication->pub_type=='Article') {
            $newsubsubheader = $publication->cleanjournal;
            if ($newsubsubheader!=$subsubheader) {
              $subsubheader = $newsubsubheader;
              echo '<div><br/></div><div class="header">'.$publication->journal.'</div><div><br/></div>';
            }
        } else {
            $newsubsubheader = $publication->actualyear;
            if ($newsubsubheader!=$subsubheader) {
              $subsubheader = $newsubsubheader;
              echo '<div><br/></div><div class="header">'.$subsubheader.'</div><div><br/></div>';
            }
        }
        break;
      case 'recent':
        break;
    }
    
    $summaryfields = getPublicationSummaryFieldArray($publication->pub_type);

echo "<div class='publication_summary ".$even."' id='publicationsummary".$publication->pub_id."'>
<table width='100%'>
  <tr>
    <td>";
if ($userlogin->getPreference('summarystyle') == 'title')    
    echo " <span class='title'>".anchor('publications/show/'.$publication->pub_id, $publication->title, array('title' => 'View publication details'))."</span>, ";
    
$num_authors    = count($publication->authors);
$current_author = 1;

foreach ($publication->authors as $author)
{
  if (($current_author == $num_authors) & ($num_authors > 1)) {
    echo " and ";
  }
  elseif ($current_author >1) {
    echo ", ";
  }

  echo  "<span class='author'>".anchor('authors/show/'.$author->author_id, $author->getName(), array('title' => 'All information on '.$author->cleanname))."</span>";
  $current_author++;
}

if ($userlogin->getPreference('summarystyle') == 'author')    
    echo ", <span class='title'>".anchor('publications/show/'.$publication->pub_id, $publication->title, array('title' => 'View publication details'))."</span> ";


foreach ($summaryfields as $key => $prefix) {
  if ($key == 'pages') {
    $pages = "";
    if (($publication->firstpage != "0") || ($publication->lastpage != "0")) {
      if ($publication->firstpage != "0") {
        $pages = $publication->firstpage;
      }
      if (($publication->firstpage != $publication->lastpage)&& ($publication->lastpage != "0")) {
        if ($pages != "") {
            $pages .= "-";
        }
        $pages .= $publication->lastpage;
      }
    }
    $val = $pages;
  } else {
    $val = trim($publication->$key);
  }
  $postfix='';
  if (is_array($prefix)) {
    $postfix = $prefix[1];
    $prefix = $prefix[0];
  }
  if ($val) {
    echo $prefix.$val.$postfix;
  }
}
echo "
    </td>
    <td width='8%' align='right' valign='top'>
      <span id='bookmark_pub_".$publication->pub_id."'>";
if ($userlogin->hasRights('bookmarklist')) {
  if ($publication->isBookmarked) {
    echo '<span title="Click to UnBookmark publication">'
         .$this->ajax->link_to_remote("<img border=0 src='".getIconUrl('bookmarked.gif')."'>",
          array('url'     => site_url('/bookmarklist/removepublication/'.$publication->pub_id),
                'update'  => 'bookmark_pub_'.$publication->pub_id
                )
          ).'</span>';
  } 
  else {
    echo '<span title="Click to Bookmark publication">'
         .$this->ajax->link_to_remote("<img border=0 src='".getIconUrl('nonbookmarked.gif')."'>",
          array('url'     => site_url('/bookmarklist/addpublication/'.$publication->pub_id),
                'update'  => 'bookmark_pub_'.$publication->pub_id
                )
          ).'</span>';
  }
}
echo "</span><br/>";
$attachments = $publication->getAttachments();
if (count($attachments) != 0)
{
    if ($attachments[0]->isremote) {
        echo "<a href='".prep_url($attachments[0]->location)."' target='_blank'><img title='Download ".htmlentities($attachments[0]->name,ENT_QUOTES)."' class='icon' src='".getIconUrl("attachment_html.gif")."'/></a>\n";
    } else {
        $iconUrl = getIconUrl("attachment.gif");
        //might give problems if location is something containing UFT8 higher characters! (stringfunctions)
        //however, internal file names were created using transliteration, so this is not a problem
        $extension=strtolower(substr(strrchr($attachments[0]->location,"."),1));
        if (iconExists("attachment_".$extension.".gif")) {
            $iconUrl = getIconUrl("attachment_".$extension.".gif");
        }
        echo anchor('attachments/single/'.$attachments[0]->att_id,"<img class='icon' src='".$iconUrl."'/>" ,array('title'=>'Download '.$attachments[0]->name))."\n";
    }
}  
if (trim($publication->doi)!='') {
    echo "<br/>[<a title='Click to follow Digital Object Identifier link to online publication' target='_blank' href='http://dx.doi.org/".$publication->doi."'>DOI</a>]";
}

echo "
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