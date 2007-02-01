<div class='publication_list'>
  <div class='publication_list_header'><?php echo $header ?></div>
<?php
  $publicationData = array();
  $even = true;
  foreach ($publications as $publication)
  {
    $even = !$even;
    $publicationData['publication'] = $publication;
    if ($even)
      $publicationData['even']      = 'even';
    else
      $publicationData['even']      = 'odd';
      
    $this->load->view('publications/summary', $publicationData);
  }
?>
</div>