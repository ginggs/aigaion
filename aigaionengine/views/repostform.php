<?php
/** See formrepost_helper, login filter and login controller... */

$this->load->helper('form');
echo "<div class='editform'>";
echo form_open($this->latesession->get('FORMREPOST_uri'));
echo "The system detected that you were logged out while submitting a form named '".$this->latesession->get('FORMREPOST_formname')."'.
      The data in that form has <b>not</b> yet been submitted successfully to the database. 
      Press the button below to re-submit the form data.<br/><br/>";
echo form_submit('repost_form', 'Repost form');
foreach($this->latesession->get('FORMREPOST_post') as $field=>$val) {
    echo form_hidden($field,$val);
}
echo form_hidden('form_reposted','form_reposted');
echo form_close();
echo form_open('');
echo form_submit('cancel', 'Cancel form');
echo form_hidden('form_reposted','form_reposted');
echo form_close();
echo "</div>";

//note: if the form was a search form, we might as well immediately force a submit through javascript; searches don't need confirmation

//reset the session vars related to this formrepost, but not here because a refresh would then destroy this form
?>