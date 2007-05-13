<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for some BiBTeX related functions. 
| -------------------------------------------------------------------
|
|   Currently, this helper only caches the bibtex id mappings, used for e.g.
|   crossreferencing in notes.
|
|	Usage:
|       //load this helper:
|       $this->load->helper('bibtex'); 

|
*/

    function getBibtexIdLinks() {
        $CI = &get_instance();
        $bibtexidlinks = $CI->latesession->get('BIBTEX_ID_LINKS');
        if (!isset($bibtexidlinks)||($bibtexidlinks==null)) {
            $bibtexidlinks = refreshBibtexIdLinks();
        }
        return $bibtexidlinks;
    }  
    
    function refreshBibtexIdLinks() {
        $CI = &get_instance();
        $bibtexidlinks = array();
        $Q = mysql_query("SELECT pub_id, bibtex_id FROM publication");
        while ($R = mysql_fetch_array($Q))
        {
            if ($R['bibtex_id'] != "")
                $bibtexidlinks[$R['pub_id'] ] = array($R['bibtex_id'], "/\b(?<!\.)(".preg_quote($R['bibtex_id'], "/").")\b/");
            else
                $bibtexidlinks[$R['pub_id'] ] = "";
        }
        $CI->latesession->set('BIBTEX_ID_LINKS',$bibtexidlinks);
        return $bibtexidlinks;
    }
?>