<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class='publication'>
  <div class='header'>Import publications</div>
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

  echo form_submit('publication_submit', 'Submit')."\n";
  echo form_close()."\n";
?>
</div>