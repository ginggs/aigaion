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

$result = '';

$result .= "%Aigaion2 BiBTeX export from ".getConfigurationSetting("WINDOW_TITLE")."\n";
$result .= "%".date('l dS \of F Y h:i:s A')."\n";
$result .= "%".$header."\n\n";

$this->load->helper('export');
foreach ($nonxrefs as $pub_id=>$publication) {
    $result .= getBibtexForPublication($publication)."\n";
}
if (count($xrefs)>0) $result .= "\n\n%crossreffed publications: \n";
foreach ($xrefs as $pub_id=>$publication) {
    $result .= getBibtexForPublication($publication)."\n";
}
$userlogin = getUserLogin();
if ($userlogin->getPreference('exportinbrowser')=='TRUE') {
    echo '<pre>';
    echo $result;
    echo '</pre>';
} else {
    // Load the download helper and send the file to your desktop
    $this->output->set_header("Content-type: application/bibtex");
    $this->output->set_header("Cache-Control: cache, must-revalidate");
    $this->output->set_header("Pragma: public");
    $this->load->helper('download');
    force_download(AIGAION_DB_NAME."_export_".date("Y_m_d").'.bib', $result);
}

?>