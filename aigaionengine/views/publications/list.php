<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
//required parameters:
//$publications[] - array with publication objects
//
//optional parameters
//$order - Element for sub header content, defaults to 'year'
//$noBookmarkList - bool, show no bookmarking icons
//$noNotes - bool, show no notes
//


//first we get all necessary values required for displaying
  $userlogin  = getUserLogin();

  //Switch off the bookmarklist buttons by passing the parameter $noBookmarkList=True to this view, else default from rights
  if (isset($noBookmarkList) && ($noBookmarkList == true))
    $useBookmarkList = false;
  else
    $useBookmarkList = $userlogin->hasRights('bookmarklist');
    
  //note that when 'order' is set, this view supposes that the data is actually ordered in that way!
  //use 'none' or other nonexisting fieldname for no headers.
  if (!isset($order))
    $order = 'year';
  
  //retrieve the publication summary and list stype preferences (author first or title first etc)
  $summarystyle = $userlogin->getPreference('summarystyle');  
  $liststyle    = $userlogin->getPreference('liststyle');

  
  //this block of code is used to generate the multi-page-links. See the publications/showlist controller for how to use this - and make sure you set all parameters used there!
  $multipagelinks='';
  if (isset($multipage) && ($multipage == True))
  {
    $page = 0;
    $liststyle = $userlogin->getPreference('liststyle');
    if ($liststyle > 0) 
    {
      if (count($publications) > 0)
      {
        $multipagelinks.= '<center><div>';
        while ($page*$liststyle<count($publications)) 
        {
          $multipagelinks.= ' | ';
          $linktext = ($page*$liststyle+1).'-';
          if (($page+1)*$liststyle>count($publications)) {
              $linktext .= count($publications);
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
        $multipagelinks.= " |</div></center>\n<br/>\n";
      }
    }
  }
  
  //here the output starts
  echo "<div class='publication_list'>\n";
  if (isset($header) && ($header != '')) {
    echo "  <div class='header'>".$header."</div>\n";
  }
  echo $multipagelinks;
  
  $b_even = true;
  $subheader = '';
  $subsubheader = '';
  $pubno = 0;
  foreach ($publications as $publication)
  {
    $pubno++;
    if (isset($multipage) && ($multipage == True)) {
        if (($currentpage*$liststyle > $pubno) || (($currentpage+1)*$liststyle < $pubno))
            continue;
    }
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
        $newsubheader = "";
        if (strlen($publication->cleantitle)>0)
            $newsubheader = $publication->cleantitle[0];
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          echo '<div><br/></div><div class="header">'.strtoupper($subheader).'</div><div><br/></div>';
        }
        break;
      case 'author':
        $newsubheader = "";
        if (strlen($publication->cleanauthor)>0)
            $newsubheader = $publication->cleanauthor[0];
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          echo '<div><br/></div><div class="header">'.strtoupper($subheader).'</div><div><br/></div>';
        }
        break;
      case 'type':
        $newsubheader = $publication->pub_type;
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          if ($publication->pub_type!='Article')
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
      default:
        break;
    }
    
    $summaryfields = getPublicationSummaryFieldArray($publication->pub_type);

echo "
<div class='publication_summary ".$even."' id='publicationsummary".$publication->pub_id."'>
<table width='100%'>
  <tr>
    <td>";
$displayTitle = $publication->title;
//remove braces in list display
if (strpos($displayTitle,'$')===false) {
  $displayTitle = str_replace(array('{','}'),'',$displayTitle);
}

$num_authors    = count($publication->authors);

if ($summarystyle == 'title') {
    echo "<span class='title'>".anchor('publications/show/'.$publication->pub_id, $displayTitle, array('title' => 'View publication details'))."</span>";
}
    
$current_author = 1;

foreach ($publication->authors as $author)
{
  if (($current_author == $num_authors) & ($num_authors > 1)) {
    echo " and ";
  }
  else if ($current_author>1 || ($summarystyle == 'title')) {
    echo ", ";
  }

  echo  "<span class='author'>".anchor('authors/show/'.$author->author_id, $author->getName(), array('title' => 'All information on '.$author->cleanname))."</span>";
  $current_author++;
}

if ($summarystyle == 'author') {
    if ($num_authors > 0) {
        echo ', ';
    }
    echo "<span class='title'>".anchor('publications/show/'.$publication->pub_id, $displayTitle, array('title' => 'View publication details'))."</span>";
}


foreach ($summaryfields as $key => $prefix) {
  if ($key == 'pages') {
    $pages = "";
    if (($publication->firstpage != "0") || ($publication->lastpage != "0")) {
      if ($publication->firstpage != "0") {
        $pages = $publication->firstpage;
      }
      if (($publication->firstpage != $publication->lastpage)&& ($publication->lastpage != "0") && ($publication->lastpage != "")) {
        if ($pages != "") {
            $pages .= "-";
        }
        $pages .= $publication->lastpage;
      }
    }
    $val = $pages;
  } else {
    $val = utf8_trim($publication->$key);
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

if (!(isset($noNotes) && ($noNotes == true)))
{
  $notes = $publication->getNotes();
  if ($notes != null) {
  echo "<br/>
        <ul class='notelist'>";
    foreach ($notes as $note) {
      echo "
          <li>".$this->load->view('notes/summary', array('note' => $note), true)."</li>";
    }
    echo "
        </ul>";
  }
}
echo "
    </td>
    <td width='8%' align='right' valign='top'>
      <span id='bookmark_pub_".$publication->pub_id."'>";
      
if ($useBookmarkList) {
  if ($publication->isBookmarked) {
    echo '<span title="Click to UnBookmark publication">'
         .$this->ajax->link_to_remote("<img class='large_icon' src='".getIconUrl('bookmarked.gif')."'>",
          array('url'     => site_url('/bookmarklist/removepublication/'.$publication->pub_id),
                'update'  => 'bookmark_pub_'.$publication->pub_id
                )
          ).'</span>';
  } 
  else {
    echo '<span title="Click to Bookmark publication">'
         .$this->ajax->link_to_remote("<img class='large_icon' src='".getIconUrl('nonbookmarked.gif')."'>",
          array('url'     => site_url('/bookmarklist/addpublication/'.$publication->pub_id),
                'update'  => 'bookmark_pub_'.$publication->pub_id
                )
          ).'</span>';
  }
}
echo "</span>";
$attachments = $publication->getAttachments();
if (count($attachments) != 0)
{
    if ($attachments[0]->isremote) {
        echo "<a href='".prep_url($attachments[0]->location)."' class='open_extern'><img class='large_icon' title='Download ".htmlentities($attachments[0]->name,ENT_QUOTES)."' src='".getIconUrl("attachment_html.gif")."'/></a>\n";
    } else {
        $iconUrl = getIconUrl("attachment.gif");
        //might give problems if location is something containing UFT8 higher characters! (stringfunctions)
        //however, internal file names were created using transliteration, so this is not a problem
        $extension=strtolower(substr(strrchr($attachments[0]->location,"."),1));
        if (iconExists("attachment_".$extension.".gif")) {
            $iconUrl = getIconUrl("attachment_".$extension.".gif");
        }
        $params = array('title'=>'Download '.$attachments[0]->name);
        if ($userlogin->getPreference('newwindowforatt')=='TRUE')
            $params['class'] = 'open_extern';
        echo '<br/>'.anchor('attachments/single/'.$attachments[0]->att_id,"<img class='large_icon' src='".$iconUrl."'/>" ,$params)."\n";
    }
}  
if (utf8_trim($publication->doi)!='') {
    echo "<br/>[<a title='Click to follow Digital Object Identifier link to online publication' class='open_extern' href='http://dx.doi.org/".$publication->doi."'>DOI</a>]";
}
if (utf8_trim($publication->url)!='') {
    echo "<br/>[<a title='".prep_url($publication->url)."' class='open_extern' href='".prep_url($publication->url)."'>URL</a>]";
}

echo "
    </td>
  </tr>";

echo "
</table>
</div>

"; //end of publication_summary div

    }
  }

echo $multipagelinks;
?>
</div>