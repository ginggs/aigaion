<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class='publication'>
  <div class='header'>Import publications</div>
  <p>Paste the entries (BiBTeX, RIS or Refer) to import in the text area below and then press "Import". </p>
<?php
  //open the edit form
  $formAttributes     = array('ID' => 'import_form');
  echo form_open('import/commit', $formAttributes)."\n";
  echo form_hidden('submit_type', 'submit')."\n";
?>

  <table class='publication_edit_form' width='100%'>
    <tr>
      <td>
<?php
        echo form_textarea(array('name' => 'import_data', 'id' => 'import_data', 'rows' => '20', 'cols' => '60'));
?>
      </td>      
    </tr>
    
  </table>
<?php

  echo form_submit('publication_submit', 'Submit');//.'&nbsp;'.form_checkbox('markasread','markasread',False).' Mark imported entries as read.'."\n";
  echo form_close()."\n";
?>
</div>