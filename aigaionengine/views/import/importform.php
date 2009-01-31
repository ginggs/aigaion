<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php

$importTypes = $this->import_lib->getAvailableImportTypes();


if (!isset($content)||($content==null)) 
{
   $content = '';
}
?>
<div class='publication'>
  <div class='header'>Import publications</div>
  <p>Paste the entries (<?php echo implode(', ',$importTypes); ?>) to import in the text area below and then press "Import". </p>
<?php
  //open the edit form
  $formAttributes     = array('ID' => 'import_form');
  echo form_open('import/submit', $formAttributes)."\n";
  echo form_hidden('submit_type', 'submit')."\n";
  echo form_hidden('formname','import');
?>

  <table class='publication_edit_form' width='100%'>
    <tr>
      <td>
<?php
        echo form_textarea(array('name' => 'import_data', 'id' => 'import_data', 'rows' => '20', 'cols' => '60', 'value'=>$content));
?>
      </td>      
    </tr>
    
  </table>
<?php
  $importTypes["auto"] = "auto";
  echo form_submit('publication_submit', 'Import')
       .'&nbsp;<span title="Select the format of the data entered in the form above, or \'auto\' to let Aigaion automatically detect the format.">Format: '.form_dropdown('format',$importTypes,'auto')."</span>\n"
       .'&nbsp;'.form_checkbox('markasread','markasread',False).' Mark imported entries as read.'."\n";
  echo form_close()."\n";
?>
</div>