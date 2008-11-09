<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
  $authorfields   = array('firstname'=>'First name(s)', 'von'=>'von-part', 'surname'=>'Last name(s)', 'jr'=>'jr-part', 'email'=>'Email', 'url'=>'URL', 'institute'=>'Institute');
  $formAttributes = array('ID' => 'author_'.$author->author_id.'_edit');
?>
<div class='author'>
  <div class='header'><?php echo ucfirst($edit_type); ?> author</div>
<?php
  //open the edit form
  echo form_open('authors/commit', $formAttributes)."\n";
  echo form_hidden('edit_type',   $edit_type)."\n";
  echo form_hidden('author_id',   $author->author_id)."\n";
  echo form_hidden('formname','author');
  if (isset($review))
    echo form_hidden('submit_type', 'review');
  else
    echo form_hidden('submit_type', 'submit')."\n";
?>
  <table class='author_edit_form' width='100%'>
<?php
    if (isset($review)):
?>    
    <tr>
      <td colspan = 2>
        <div class='errormessage'><?php echo $review['author']; ?></div>
      </td>
    </tr>
<?php
    endif;
    foreach ($authorfields as $field=>$display):
?>
    <tr>
      <td valign='top'><?php echo $display; ?>:</td>
      <td valign='top'><?php echo form_input(array('name' => $field, 'id' => $field, 'size' => '45', 'alt' => $field), $author->$field);?></td>
    </tr>
<?php
    endforeach;
?>
  </table>
<?php
if ($edit_type=='edit') {
  echo form_submit('publication_submit', 'Change')."\n";
} else {
  echo form_submit('publication_submit', 'Add')."\n";
}
  echo form_close()."\n";
if ($edit_type=='edit') {
  echo form_open('authors/show/'.$author->author_id);
} else {
  echo form_open('');
}
  echo form_submit('Cancel', 'Cancel');
  echo form_close()."\n";
?>
</div>