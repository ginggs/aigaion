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
$result = '';
$this->load->helper('export');
foreach ($nonxrefs as $pub_id=>$publication) {
    $result .= getRISForPublication($publication);
}
foreach ($xrefs as $pub_id=>$publication) {
    $result .= getRISForPublication($publication);
}

$userlogin = getUserLogin();
if ($userlogin->getPreference('exportinbrowser')=='TRUE') {
    echo '<pre>';
    echo $result;
    echo '</pre>';
} else {
    // Load the download helper and send the file to your desktop
    $this->output->set_header("Content-type: application/ris");
    $this->output->set_header("Cache-Control: cache, must-revalidate");
    $this->output->set_header("Pragma: public");
    $this->load->helper('download');
    force_download(AIGAION_DB_NAME."_export_".date("Y_m_d").'.ris', $result);
} 


?>