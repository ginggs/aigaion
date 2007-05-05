<div class='author'>
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