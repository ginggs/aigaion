<?php
/*
Helper functions for processing months...
*/

/* In: month field from database
out: month field in bibtex format, assuming that it will be exported with additional braces around it! (that means that the export may look like this: }#nov#{    ) */
function formatMonthBibtex($month)
{
    $output = $month;
    //replace braced quotes by AIGSTR
    $output = preg_replace("/\\{\\\"\\}/",AIGSTR,$output);
    //replace remaining quotes "..." by }#...#{ 
    $output = preg_replace("/\\\"([^\\\"]*)\\\"/","}#$1#{",$output);
    //replace AIGSTR by unbraced quotes
    $output = preg_replace("/".AIGSTR."/","\"",$output);
    return $output;
}

/* In: month field from database
out: month field in bibtex format, assuming that it will be shown in an edit form */
function formatMonthBibtexForEdit($month)
{
    $output = formatMonthBibtex("{".$month."}");
//    appendMessage($output."<br>");
    //remove intial }# if any
    $output = preg_replace("/^\\{\\}\\#/","",$output);
//    appendMessage($output."<br>");
    //remove sufgfix #{ if any
    $output = preg_replace("/\\#\\{\\}\z/","",$output);
//    appendMessage($output."<br>");
    if ($output=="{}")$output="";
    return $output;
}
/* In: month field from database.
Out: month field formatted in text format, for display on screen or for export to RIS / RTF / etc */
function formatMonthText($month) 
{
    $output = $month;
    //replace braced quotes by AIGSTR
    $output = preg_replace("/\\{\\\"\\}/",AIGSTR,$output);
    //replace month quotes "..." by month names
    foreach (getMonthsInternalNoQuotes() as $abbrv=>$full)
    {
        $output = preg_replace("/\\\"".$abbrv."\\\"/",$full,$output);
    }
    //replace REMAINOING (UNKNOWN MACROS) by the macro name if it is an unknown macro...
    $output = preg_replace("/\\\"([^\\\"]*)\\\"/","$1",$output);
    //replace AIGSTR by unbraced quotes
    $output = preg_replace("/".AIGSTR."/","\"",$output);
    return $output;
}

//move to somewhere else?
function getMonthsEng() 
{
    return array("","January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
}
function getMonthsInternal() 
{
    return array(""=>"","\"jan\""=>"January", "\"feb\""=>"February", "\"mar\""=>"March", "\"apr\""=>"April", "\"may\""=>"May", "\"jun\""=>"June", "\"jul\""=>"July", "\"aug\""=>"August", "\"sep\""=>"September", "\"oct\""=>"October", "\"nov\""=>"November", "\"dec\""=>"December");
}
function getMonthsInternalHtmlQuotes() 
{
    return array(""=>"","&quot;jan&quot;"=>"January", "&quot;feb&quot;"=>"February", "&quot;mar&quot;"=>"March", "&quot;apr&quot;"=>"April", "&quot;may&quot;"=>"May", "&quot;jun&quot;"=>"June", "&quot;jul&quot;"=>"July", "&quot;aug&quot;"=>"August", "&quot;sep&quot;"=>"September", "&quot;oct&quot;"=>"October", "&quot;nov&quot;"=>"November", "&quot;dec&quot;"=>"December");
}
function getMonthsInternalNoQuotes() 
{
    return array(""=>"","jan"=>"January", "feb"=>"February", "mar"=>"March", "apr"=>"April", "may"=>"May", "jun"=>"June", "jul"=>"July", "aug"=>"August", "sep"=>"September", "oct"=>"October", "nov"=>"November", "dec"=>"December");
}
function getMonthsArray() {
  return array( '0'  => '',
                '1'  => 'January',
                '2'  => 'February',
                '3'  => 'March',
                '4'  => 'April',
                '5'  => 'May',
                '6'  => 'June',
                '7'  => 'July',
                '8'  => 'August',
                '9'  => 'September',
                '10' => 'October',
                '11' => 'November',
                '12' => 'December');
}
?>