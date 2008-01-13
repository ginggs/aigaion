<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$userlogin = getUserLogin();
$this->load->helper('publication');

//$resulttabs will be 'title'=>'resultdisplay'.
//later on, display will take care of surrounding divs, and show-and-hide-scripts for the tabs
$resulttabs = array();
foreach ($searchresults as $type=>$resultList) {
    switch ($type) {
        case 'authors':
            $authordisplay = "<ul>";
            foreach ($resultList as $author) {
                $authordisplay .= '<li>'.anchor('authors/show/'.$author->author_id,$author->getName()).'</li>';
            }
            $authordisplay .= "</ul>";
            $resulttabs['Authors: '.count($resultList)] = $authordisplay;
            break;
        case 'topics':
            $topicdisplay = "<ul>";
            foreach ($resultList as $topic) {
                $topicdisplay .= '<li>'.anchor('topics/single/'.$topic->topic_id,$topic->name).'</li>';
            }
            $topicdisplay .= "</ul>";
            $resulttabs['Topics: '.count($resultList)] = $topicdisplay;
            break;
        case 'keywords':
            $keyworddisplay = "<ul>";
            foreach ($resultList as $kw) {
                $keyworddisplay .= '<li>'.anchor('keywords/single/'.$kw->keyword_id,$kw->keyword).'</li>';
            }
            $keyworddisplay .= "</ul>";
            $resulttabs['Keywords: '.count($resultList)] = $keyworddisplay;
            break;
/*        case 'publications_titles':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= anchor('publications/show/'.$publication->pub_id,$publication->title);
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs['Publications: '.count($resultList)] = $pubdisplay;
            //option below displays the publciations as list, but I don't want the headers and everything... maybe make an option in that view that 
            //determines whether headers are displayed?
            //$resulttabs['Publications: '.count($resultList)] = $this->load->view('publications/list', array('publications'=>$resultList), true);
            break;
        case 'publications_bibtex':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= anchor('publications/show/'.$publication->pub_id,$publication->bibtex_id.': '.$publication->title);
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs['BibTeX ID: '.count($resultList)] = $pubdisplay;
            break;
        case 'publications_notes':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= anchor('publications/show/'.$publication->pub_id,$publication->title);
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs['Notes: '.count($resultList)] = $pubdisplay;
            break;
  */
        default:
            break;
    }
  
}


if (count($resulttabs)==0)
{
  echo "<div class='message'>No search results found for query: <b>".htmlentities($query)."</b></div>\n";
}
else
{
  echo "<div class='message'>Search results for query: <b>".htmlentities($query)."</b></div>\n";
} 
//show all relevant result tabs
foreach ($resulttabs as $title=>$tabdisplay) {
    echo '<div class="header">'.$title.' matches</div>';
    echo $tabdisplay;
}

$types = array();
$resultHeaders = array();
$result_div_ids = array();
foreach ($searchresults as $title=>$content)
{
  if (substr($title, 0, strlen("publication")) == "publication")
  {
    $type = substr($title, strlen("publication") + 2);
    $types[] = $type;
    $resultHeaders[$type] = ucfirst($type)." (".count($content).")";
    $result_div_ids[$type] = "result_".$type;
    $result_views[$type] = $this->load->view('publications/list', array('publications' => $content, 'order' => 'year'), true);
  }
}

if (count($types) > 0)
{
  echo "<div class='header'>Publication matches</div>\n";
  $cells = "";
  $divs  = "";
  $hideall = "";
  foreach ($types as $type)
  {
    $cells .= "<td><div class='header'><a onclick=\"";
    foreach ($types as $type2)
    {
      if ($type2 == $type)
        $cells .= $this->ajax->show($result_div_ids[$type2])."; ";
      else
        $cells .= $this->ajax->hide($result_div_ids[$type2])."; ";
    }
    
    $cells .= "\">".$resultHeaders[$type]."</a></div></td>\n";
    $divs .= "<div id='".$result_div_ids[$type]."'>\n".$result_views[$type]."\n</div>\n\n";
    $hideall .= $this->ajax->hide($result_div_ids[$type])."; ";
    
  }
  $showfirst = $this->ajax->show($result_div_ids[$types[0]])."; ";
?>  
  <table>
    <tr>
<?php
    echo $cells;
?>
    </tr>
  </table>
<?php
  echo $divs;
  echo "<script>".$hideall.$showfirst."</script>";
}
/*
$content['publications']    = $this->publication_db->getForTopic('1',$order);
        $content['order'] = $order;
        
        $output = $this->load->view('header', $headerdata, true);
        $output .= $this->load->view('publications/list', $content, true);
        */
?>