<div class='publication_list'>
  <div class='header'><?php echo $publicationlist->header ?></div>
<?php
  $even = true;
  foreach ($publicationlist->list as $publication)
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