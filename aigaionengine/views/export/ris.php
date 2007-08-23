<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/**
views/export/ris

displays ris for given publications

input parameters:
nonxref: map of [id=>publication] for non-crossreffed-publications
xref: map of [id=>publication] for crossreffed-publications
header: not used here.

*/
if (!isset($header)||($header==null))$header='';

//no header
echo '<pre>';
$this->load->helper('export');
foreach ($nonxrefs as $pub_id=>$publication) {
    echo getRISForPublication($publication);
}
foreach ($xrefs as $pub_id=>$publication) {
    echo getRISForPublication($publication);
}
echo '</pre>';


?>