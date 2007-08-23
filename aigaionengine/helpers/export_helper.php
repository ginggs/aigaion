<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for export functions.
| -------------------------------------------------------------------
|
|   This helper contains functions to export to BiBTeX and RIS
|
|	Usage:
|       //load this helper:
|       $this->load->helper('export'); 
|       //get export text for an entry
|       $bibtex = getBibtexForPublication($publication);
|       $ris    = getRISForPublication($publication);
|
*/

    /** returns formatted bibtex for this publication object. Does not do any crossref merging. */
    function getBibtexForPublication($publication) {
        $CI = &get_instance();
        $CI->load->helper('specialchar');
        $CI->load->helper('string');
        $CI->load->helper('publication');
        $fields = array();
        $maxfieldname=0;
        //open entry
        $result = '@'.strtoupper($publication->pub_type).'{'.$publication->bibtex_id.",\n";
        //collect authors
        $authors = "";
        $first = true;
        foreach ($publication->authors as $author) {
            if (!$first) $authors .= " and ";
            $first = false;
            $authors .= $author->getName('lvf');
        }
        $fields['author']=$authors;
        //collect editors
        $editors = "";
        $first = true;
        foreach ($publication->editors as $editor) {
            if (!$first) $editors .= " and ";
            $first = false;
            $editors .= $editor->getName('lvf');
        }
        $fields['editor']=$editors;
        //collect keywords
        $keywords = "";
        $first = true;
        foreach ($publication->getKeywords() as $keyword) {
            if (!$first) $keywords .= ",";
            $first = false;
            $keywords .= $keyword;
        }
        $fields['keywords']=$keywords;
        //parse fstpage - lastpage into pages
        $pages = "";
        if (($publication->firstpage != "0") || ($publication->lastpage != "0")) {
        	if ($publication->firstpage != "0") {
        		$pages = $publication->firstpage;
        	}
        	if (($publication->firstpage != $publication->lastpage)&& ($publication->lastpage != "0")) {
        		if ($pages != "") {
        			$pages .= "--";
        		}
        		$pages .= $publication->lastpage;
        	}
        }
        $fields['pages']=$pages;
        //key is named 'namekey' in the database
        $fields['key'] = $publication->namekey;
        //month is a number in the database...
        $months = getMonthsEng();
        if (array_key_exists($publication->month,$months)) {
            $fields['month'] = $months[$publication->month];
        }
        //initial maxfieldname: the longest of the above collected fields
        $maxfieldname = 8;
        //process user fields
        //see old bibtex export for how to export userfields? Directly DUMP user fields? (but what about layout :( )
        $done = array('author',
                      'editor',
                      'keywords',
                      'pub_type',
                      'bibtex_id',
                      'userfields',
                      'firstpage',
                      'lastpage',
                      'namekey',
                      'month');
        $omitifzero = array('chapter','year');
        //now add all other fields that are relevant for exporting
        foreach (getFullFieldArray() as $field) {
            if (!in_array($field,$done) && (trim($publication->$field)!='')) {
                if (!in_array($field,$omitifzero)||($publication->$field!='0'&&$publication->$field!='0000')) {
                    $fields[$field]=$publication->$field;
                    $maxfieldname = max(strlen($field),$maxfieldname);
                }
            }
        }
        
        
        //process fields array, converting to bibtex special chars as you go along.
        //maxfieldname determines the adjustment of the field names
        $spaces = repeater(' ',$maxfieldname);
        foreach ($fields as $name=>$value) {
            if ($value!='') {
                $result .= "  ".substr($spaces.$name,-$maxfieldname)." = {".latinToBibCharsFromString($value)."},\n";
            }
        }
        
        //hmmm -- could have done better layout here for userfields
        if (trim($publication->userfields)!='') {
            $result .= $publication->userfields."\n";
        }
        
        //close entry
        $result .= "}\n";    
        return $result;
    }  

    /** returns formatted RIS for this publication object. Does not do any crossref merging. */
    function getRISForPublication($publication) {
        $CI = &get_instance();
        $CI->load->helper('specialchar');
        $CI->load->helper('string');
        $CI->load->helper('publication');
        
        $result = '';
       	$result .= "TY  - ".getRISEntryType($publication->pub_type)."\n";
	    if ($publication->bibtex_id != "") 
		    $result .= "ID  - ".$publication->bibtex_id."\n";
	
        $result .= getRISExportLine('T1',$publication->title);
    	foreach ($publication->authors as $author) 
	    	$result .= getRISExportLine('A1', $author->getName('lvf'));
    	foreach ($publication->editors as $editor) 
	    	$result .= getRISExportLine('ED', $editor->getName('lvf'));
        $result .= getRISExportLine('JA',$publication->journal);
        $result .= getRISExportLine('TI',$publication->booktitle);
        $result .= getRISExportLine('T3',$publication->series);
        if ($publication->year!='0000')
            $result .= getRISExportLine('Y1',$publication->year);
        $result .= getRISExportLine('VL',$publication->volume);
        $result .= getRISExportLine('M1',$publication->report_type);
        $result .= getRISExportLine('IS',$publication->number);
    	if ($publication->firstpage != "0")
    		$result .= getRISExportLine("SP", $publication->firstpage);
    	if ($publication->lastpage != "0")
    		$result .= getRISExportLine("EP", $publication->lastpage);
        $result .= getRISExportLine('U1',$publication->edition);
        if ($publication->chapter != 0)
            $result .= getRISExportLine('U2',$publication->chapter);
        $result .= getRISExportLine('PB',$publication->publisher);
        $result .= getRISExportLine('T2',$publication->school);
        $result .= getRISExportLine('T2',$publication->organization);
        $result .= getRISExportLine('T2',$publication->institution);
        $result .= getRISExportLine('CY',$publication->location);
        $result .= getRISExportLine('AD',$publication->address);
        $result .= getRISExportLine('SN',$publication->isbn);
        $result .= getRISExportLine('SN',$publication->issn);
        $result .= getRISExportLine('N1',$publication->note);
        $result .= getRISExportLine('UR',$publication->url);
    	foreach($publication->getKeywords() as $keyword)
    		$result .= getRISExportLine("KW", trim($keyword));
        $result .= getRISExportLine('N1',$publication->note);
        $result .= getRISExportLine('N2',$publication->abstract);

    	if (trim($publication->userfields) != "") {
    		$field = strtok($userfields,",");
    		while (strlen($field) > 0)
    		{
    			$result .= "M1  - ".$field."\n";
    			$field = strtok(",");
    		}
    	}
        
        //close entry
        $result .= "ER  -\n";
        return $result;
    }          
    function getRISExportLine($field,$value) {
    	//U1: edition
    	//U2: chapter
    	$result = "";
    	if (trim($value) != "")
    	{
    	  if ($field == "U1") $value = "Edition: ".$value;
    	  if ($field == "U2") $value = "Chapter: ".$value;
    	  $result .= $field."  - ".urldecode($value)."\n";
    	}
    	return $result;
    }
        
    function getRISEntryType($bibEntryType)
    {
    	$return = "";
    	switch($bibEntryType) {
    		case "Article":
    			$return = "JOUR";
    		break;
    		case "Book":
    			$return = "BOOK";
    		break;
    		case "Booklet":
    			$return = "BOOK";
    		break;
    		case "Inbook":
    			$return = "CHAP";
    		break;
    		case "Incollection":
    			$return = "MGZN";
    		break;
    		case "Inproceedings":
    			$return = "CONF";
    		break;
    		case "Manual":
    			$return = "GEN ";
    		break;
    		case "Mastersthesis":
    			$return = "RPRT";
    		break;
    		case "Misc":
    			$return = "GEN ";
    		break;
    		case "Phdthesis":
    			$return = "THES";
    		break;
    		case "Proceedings":
    			$return = "CONF";
    		break;
    		case "Techreport":
    			$return = "RPRT";
    		break;
    		case "Unpublished":
    			$return = "UNPB";
    		break;
    		default:
    		break;
    	}
    	return $return;
    }
?>