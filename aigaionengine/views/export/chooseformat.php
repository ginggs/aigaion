<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
this view shows a form that asks you the format in which you want to export the data
It needs several view parameters:

header              Default: "Export all publications"
exportCommand       Default: "export/all/"; will be suffixed with type. May also be, e.g., "export/topic/12/"
*/
$this->load->helper('form');

if (!isset($header))$header="Export all publications";
if (!isset($exportCommand))$exportCommand="export/all/";
?>
<p class='header'><?php echo $header; ?></p>
<p>
  Please select the format in which you want to export the publications:<br/>
</p>
<?php
echo form_open($exportCommand.'bibtex');
echo "<div>".form_submit(array('name'=>'BiBTeX','title'=>'Export to BiBTeX'),'BiBTeX');
echo "</div>\n";
echo form_close();
echo '<br/>';
echo form_open($exportCommand.'ris');
echo "<div>".form_submit(array('name'=>'RIS','title'=>'Export to RIS'),'RIS');
echo "</div>\n";
echo form_close();
echo "<br/><hr/>";

$this->load->helper('osbib');
echo form_open($exportCommand.'formatted');
echo "<div>Format: ";
echo form_dropdown('format',array('html'=>'HTML','rtf'=>'RTF','plain'=>'TXT'),'html');//,'sxw'=>'Open Office'
echo " Style: ";
$style_options = array();
$styles = LOADSTYLE::loadDir(APPPATH."include/OSBib/styles/bibliography");
foreach ($styles as $style=>$longname) {
    $style_options[$style] = $style;
}
echo form_dropdown('style',$style_options);
//echo " Sort by: ";
//echo form_dropdown('sort',array('year'=>'Year','title'=>'Title','author'=>'Author','type'=>'Type/journal'),'html');
echo form_hidden('sort','nothing');
echo '&nbsp;'.form_submit(array('name'=>'Formatted','title'=>'Export formatted entries'),'Export');
echo "</div>";
echo form_close();


?>