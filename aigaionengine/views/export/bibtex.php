<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/**
views/export/bibtex

displays bibtex for given publications

input parameters:
nonxref: map of [id=>publication] for non-crossreffed-publications
xref: map of [id=>publication] for crossreffed-publications

*/
if (!isset($header)||($header==null))$header='';
echo '<pre>';
echo "%Aigaion2 BiBTeX export from ".getConfigurationSetting("WINDOW_TITLE")."\n";
echo "%".date('l dS \of F Y h:i:s A')."\n";
echo "%".$header."\n\n";

$this->load->helper('export');
foreach ($nonxrefs as $pub_id=>$publication) {
    echo getBibtexForPublication($publication)."\n";
}
if (count($xrefs)>0) echo "\n\n%crossreffed publications: \n";
foreach ($xrefs as $pub_id=>$publication) {
    echo getBibtexForPublication($publication)."\n";
}

echo '</pre>';

?>