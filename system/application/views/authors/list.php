<div class='author_list'>
  <div class='author_list_header'><?php echo $header ?></div>
  <ul class='nosymbol'>
<?php
  foreach ($authors as $author)
  {
?>
    <li><?php echo anchor('authors/show/'.$author->ID, $author->getName(), array('title' => 'All information on '.$author->getName())); ?></li>
<?php
  }
?>
  </ul>
</div>