<?php
  $publicationfields = getPublicationFieldArray($publication->data->type);
?>
<div class='publication'>
  <div class='optionbox'><?php echo "[".anchor('publications/edit/'.$publication->data->pub_id, 'edit', array('title' => 'Edit this publication'))."]</div>";?>
  <div class='header'><?php echo $publication->data->title; ?></div>
  <table class='publication_details'>
    <tr>
      <td>Type of publication:</td>
      <td><?php echo $publication->data->type; ?></td>
    </tr>
    <tr>
      <td>Citation:</td>
      <td><?php echo $publication->data->bibtex_id; ?></td>
    </tr>
<?php 
    foreach ($publicationfields as $key => $class):
      if ($publication->data->$key):
?>
    <tr>
      <td valign='top'><?php echo ucfirst($key); ?>:</td>
      <td valign='top'><?php echo $publication->data->$key; ?></td>
    </tr>
<?php
      endif;
    endforeach;

    if (count($publication->data->authors) > 0):
?>
    <tr>
      <td valign='top'>Authors</td>
      <td valign='top'>
        <span class='authorlist'>
<?php     foreach ($publication->data->authors as $author)
          {
            echo anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))."<br />\n";
          }
?>
        </span>
      </td>
    </tr>
<?php 
    endif;
    if (count($publication->data->editors) > 0):
?>
    <tr>
      <td valign='top'>Editors</td>
      <td valign='top'>
        <span class='authorlist'>
<?php     foreach ($publication->data->editors as $author)
          {
            echo anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))."<br />\n";
          }
?>
        </span>
      </td>
    </tr>
<?php 
    endif;
?>
  </table>
</div>