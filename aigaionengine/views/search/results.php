<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$userlogin = getUserLogin();
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
        case 'publications_content':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= $publication->title;
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs['Publications: '.count($resultList)] = $pubdisplay;
            break;
        case 'publications_bibtex_id':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= $publication->bibtex_id.': '.$publication->title;
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs['BiBTeX ID: '.count($resultList)] = $pubdisplay;
            break;
        case 'publications_note':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= $publication->title;
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs['Notes: '.count($resultList)] = $pubdisplay;
            break;
        default:
            break;
    }
}

//show all relevant result tabs
foreach ($resulttabs as $title=>$tabdisplay) {
    echo '<p class="header1">'.$title.'</p>';
    echo $tabdisplay;
}

?>