<div class='publication_summary <?php echo $even ?>' id='publicationsummary<?php echo $publication->pub_id; ?>'>
  <div class='publication_title'><?php echo anchor('publications/show/'.$publication->pub_id, $publication->getTitle(), array('title' => 'View publication details')); ?></div>
<?php
foreach ($publication->authors as $author)
{
?>
  <div class='publication_author'><?php echo anchor('authors/show/'.$author->ID, $author->getName(), array('title' => 'All information on '.$author->getName())); ?><br /></div>
<?php
}
?>
</div>
