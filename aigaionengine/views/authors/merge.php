<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
  $authorfields   = array('firstname', 'von', 'surname', 'email', 'url', 'institute');
  $formAttributes = array('ID' => 'author_'.$author->author_id.'_edit');
?>
<div class='author'>
  <div class='header'>Merge authors</div>
  Merges the source author with the target author.
  The source author will be deleted, all publications will be transferred to the target author.
<?php
    //open the edit form
    echo form_open('authors/mergecommit', $formAttributes)."\n";
    echo form_hidden('author_id',   $author->author_id)."\n";
    echo form_hidden('simauthor_id',   $simauthor->author_id)."\n";
?>
  <table>
    <tr><td>
    <table class='author_edit_form' width='100%'>
        <tr>
        <td colspan=2><p class='header2'>Target author</p></td>
        <td><p class='header2'></p></td>
        <td colspan=2><p class='header2'>Source author</p></td>
        </tr>
<?php
        foreach ($authorfields as $field):
?>
        <tr>
        <td valign='top'><?php echo ucfirst($field); ?>:</td>
        <td valign='top'><?php echo form_input(array('name' => $field, 'id' => $field, 'size' => '30', 'alt' => $field), $author->$field);?></td>
        <td valign='top'><?php echo $this->ajax->button_to_function('<<', "$('".$field."').value=$('sim".$field."').value;");?></td>
        <td valign='top'><?php echo ucfirst($field); ?>:</td>
        <td valign='top'><?php echo form_input(array('name' => 'sim'.$field, 'id' => 'sim'.$field, 'size' => '30', 'alt' => $field), $simauthor->$field);?></td>
        </tr>
<?php
        endforeach;
?>
    </table>
    </td></tr>
    <tr><td colspan='2'>
      <?php echo form_submit('merge_submit', 'Merge')."\n"; ?>
    </td></tr>
  </table>

<?php
    
  echo form_close()."\n";
echo form_open('authors/show/'.$author->author_id);
echo form_submit('cancel','Cancel');
echo form_close();

?>
</div>