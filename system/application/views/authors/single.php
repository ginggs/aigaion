<div class='author'>
  <div class='author_name'><?php echo $author->getName(); ?></div>
  <table class='author_details'>
<?php
  $author_details = $author->getDetailArray();
  foreach ($author_details as $key => $value)
  {
    if (trim($value) != "")
    {
?>
      <tr>
        <td valign='top'><?php echo $key; ?></td>
        <td valign='top'><?php echo $value; ?></td>
      </tr>
<?php
    }
  }
?>
  </table>
</div>
<?php
  $this->load->view('publications/list', $publications);
?>