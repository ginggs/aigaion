<div class='author'>
  <div class='header'><?php echo $author->data->cleanname ?></div>
  <table class='author_details'>
<?php
  $authorfields = array('firstname', 'von', 'surname', 'email', 'url', 'institute');
  foreach ($authorfields as $field)
  {
    if (trim($author->data->$field) != '')
    {
?>
      <tr>
        <td valign='top'><?php echo ucfirst($field); ?>:</td>
        <td valign='top'><?php echo $author->data->$field; ?></td>
      </tr>
<?php
    }
  }
?>
  </table>
</div>
<br />
<?php
  $this->load->view('publications/list', $publicationlist);
?>