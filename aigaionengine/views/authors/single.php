<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class='author'>
  <div class='optionbox'><?php echo "[".anchor('authors/delete/'.$author->author_id, 'delete', array('title' => 'Delete this author'))."]&nbsp[".anchor('authors/edit/'.$author->author_id, 'edit', array('title' => 'Edit this author'))."]</div>";?>
  <div class='header'><?php echo $author->getName() ?></div>
  <table class='author_details'>
<?php
  $authorfields = array('firstname', 'von', 'surname', 'email', 'url', 'institute');
  foreach ($authorfields as $field)
  {
    if (trim($author->$field) != '')
    {
?>
      <tr>
        <td valign='top'><?php echo ucfirst($field); ?>:</td>
        <td valign='top'><?php echo $author->$field; ?></td>
      </tr>
<?php
    }
  }
?>
  </table>
  <br/>
</div>