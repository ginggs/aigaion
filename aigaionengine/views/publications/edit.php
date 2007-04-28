<?php
  $publicationfields = getPublicationFieldArray($publication->data->type);
?>
<div class='publication'>
  <div class='header'>Edit publication</div>
<?php
  echo form_open('publications/commit')."\n";
  echo form_hidden('pub_id', $publication->data->pub_id)."\n";
  echo form_hidden('user_id', $publication->data->user_id)."\n";
?>
  <table class='publication_edit_form'>
    <tr>
      <td>Type of publication:</td>
      <td><?php echo form_dropdown('type', getPublicationTypes(), $publication->data->type); ?></td>
    </tr>
    <tr>
      <td>Title:</td>
      <td><?php echo form_input(array('name' => 'title', 'id' => 'title', 'size' => '90'), $publication->data->title); ?></td>
    </tr>
    <tr>
      <td>Citation:</td>
      <td><?php echo form_input(array('name' => 'bibtex_id', 'id' => 'bibtex_id', 'size' => '45'), $publication->data->bibtex_id); ?></td>
    </tr>
<?php 
    foreach ($publicationfields as $key => $class):
?>
    <tr>
      <td valign='top'><?php echo ucfirst($key); ?>:</td>
      <td valign='top'><?php echo form_input(array('name' => $key, 'id' => $key, 'size' => '45', 'autocomplete' => 'off', 'class' => $class), $publication->data->$key)."\n";
      
      if ($key == 'keywords'): ?>
        <div name='keyword_autocomplete' id='keyword_autocomplete' class='autocomplete'>
        </div>
        <?php echo $this->ajax->auto_complete_field('keywords', $options = array('url' => base_url().'index.php/publications/li_keywords/', 'update' => 'keyword_autocomplete', 'tokens'=>',', 'frequency' => '0.01'))."\n";?>
      </td>
    </tr>
<?php
      endif;
    endforeach;
?>
    <tr>
      <td valign='top'>Authors:</td>
      <td>
<?php
        $authors = array();
        if (count($publication->data->authors) > 0)
        {
          foreach ($publication->data->authors as $author)
          {
            $authors[] = $author->getName();
          }
        }?>
        <?php 

        echo form_textarea(array('name' => 'authors', 'id' => 'authors', 'rows' => '5', 'cols' => '42', 'value' => implode($authors, "\n")));
        ?>
        <div name='author_autocomplete' id='author_autocomplete' class='autocomplete'>
        </div>
        <?php echo $this->ajax->auto_complete_field('authors', $options = array('url' => base_url().'index.php/publications/li_keywords/', 'update' => 'author_autocomplete', 'tokens'=> '\n', 'frequency' => '0.01'))."\n";?>
      </td>
    </tr>
    <tr>
      <td valign='top'>Editors:</td>
      <td>
<?php
        $editors = array();
        if (count($publication->data->editors) > 0)
        {
          foreach ($publication->data->editors as $author)
          {
            $editors[] = $author->cleanname;
          }
        }

        echo form_textarea(array('name' => 'editors', 'id' => 'editors', 'rows' => '5', 'cols' => '42', 'value' => implode($editors, "\n")));
        ?>
        <div name='editor_autocomplete' id='editor_autocomplete' class='autocomplete'>
        </div>
        <?php echo $this->ajax->auto_complete_field('editors', $options = array('url' => base_url().'index.php/publications/li_keywords/', 'update' => 'editor_autocomplete', 'tokens'=> '\n', 'frequency' => '0.01'))."\n";?>
      </td>
    </tr>
  </table>
<?php
  echo form_submit('publication_summit', 'Submit')."\n";
  echo form_close()."\n";
?>
</div>