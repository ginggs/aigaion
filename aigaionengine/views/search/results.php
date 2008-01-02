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
                $keyworddisplay .= '<li>'.anchor('keywords/single/'.$kw[0],$kw[1]).'</li>';
            }
            $keyworddisplay .= "</ul>";
            $resulttabs['Keywords: '.count($resultList)] = $keyworddisplay;
            break;
        case 'publications_content':
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
        case 'publications_bibtex_id':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= anchor('publications/show/'.$publication->pub_id,$publication->bibtex_id.': '.$publication->title);
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs['BiBTeX ID: '.count($resultList)] = $pubdisplay;
            break;
        case 'publications_note':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= anchor('publications/show/'.$publication->pub_id,$publication->title);
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs['Notes: '.count($resultList)] = $pubdisplay;
            break;
        default:
            break;
    }
}
if (count($resulttabs)==0){
    echo 'no results for query<br/>';
}
//show all relevant result tabs
foreach ($resulttabs as $title=>$tabdisplay) {
    echo '<p class="header1">'.$title.'</p>';
    echo $tabdisplay;
}

?>