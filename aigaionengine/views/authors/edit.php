<?php
  $authorfields   = array('firstname', 'von', 'surname', 'email', 'url', 'institute');
  $formAttributes = array('ID' => 'author_'.$author->author_id.'_edit');
?>
<div class='author'>
  <div class='header'><?php echo ucfirst($edit_type); ?> author</div>
<?php
  //open the edit form
  echo form_open('authors/commit', $formAttributes)."\n";
  echo form_hidden('edit_type',   $edit_type)."\n";
  echo form_hidden('author_id',   $author->author_id)."\n";
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
    foreach ($authorfields as $field):
?>
    <tr>
      <td valign='top'><?php echo ucfirst($field); ?>:</td>
      <td valign='top'><?php echo form_input(array('name' => $field, 'id' => $field, 'size' => '45', 'alt' => $field), $author->$field);?></td>
    </tr>
<?php
    endforeach;
?>
  </table>
<?php
  echo form_submit('publication_submit', 'Submit')."\n";
  echo form_close()."\n";
?>
</div>