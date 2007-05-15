<div class='publication_list'>
  <div class='header'><?php echo $header ?></div>
<?php
  $even = true;
  foreach ($publications as $publication)
  {
    if ($publication!=null) {
        $even = !$even;
        $publicationData['publication'] = $publication;
        if ($even)
          $publicationData['even']      = 'even';
        else
          $publicationData['even']      = 'odd';
          
        $this->load->view('publications/summary', $publicationData);
    }
  }
?>
</div>