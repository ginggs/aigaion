<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/**
this view shows a form that asks you the format in which you want to export the data
It needs several view parameters:

header              Default: "Export all publications"
exportCommand       Default: "export/all/"; will be suffixed with type. May also be, e.g., "export/topic/12/"
*/
if (!isset($header))$header="Export all publications";
if (!isset($exportCommand))$exportCommand="export/all/";
echo '<p class="header">'.$header.'</p>';
echo 'Click on the format in which you want to export the publications:<br/><br/>';
$this->load->helper('form');
echo form_open($exportCommand.'bibtex',array('target'=>'aigaion_export'));
echo form_submit(array('name'=>'BiBTeX','title'=>'Export to BiBTeX'),'BiBTeX');
echo form_close();
echo '<br/>';
echo form_open($exportCommand.'ris',array('target'=>'aigaion_export'));
echo form_submit(array('name'=>'RIS','title'=>'Export to RIS'),'RIS');
echo form_close();
?>