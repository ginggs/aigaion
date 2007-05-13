<?php
  $publicationfields  = getPublicationFieldArray($publication->pub_type);
  $formAttributes     = array('ID' => 'publication_'.$publication->pub_id.'_edit');
?>
<div class='publication'>
  <div class='header'><?php echo ucfirst($edit_type); ?> publication</div>
<?php
  //open the edit form
  echo form_open('publications/commit', $formAttributes)."\n";
  echo form_hidden('edit_type',   $edit_type)."\n";
  echo form_hidden('pub_id',      $publication->pub_id)."\n";
  echo form_hidden('user_id',     $publication->user_id)."\n";
  echo form_hidden('submit_type', 'submit')."\n";
?>
  <table class='publication_edit_form' width='100%'>
    <tr>
      <td>Type of publication:</td>
      <td><?php echo form_dropdown('pub_type', getPublicationTypes(), $publication->pub_type, 'onchange="this.form.submit_type.value=\'type_change\'; this.form.submit();"'); ?>
    </tr>
    <tr>
      <td>Title:</td>
      <td><?php echo form_input(array('name' => 'title', 'id' => 'title', 'size' => '90'), $publication->title); ?></td>
    </tr>
    <tr>
      <td>Citation:</td>
      <td><?php echo form_input(array('name' => 'bibtex_id', 'id' => 'bibtex_id', 'size' => '45'), $publication->bibtex_id); ?></td>
    </tr>
<?php 
    //show all publication fields that are not hidden
    //at the end of this table, we show all hidden fields as hidden form elements
    foreach ($publicationfields as $key => $class):
      if ($class != 'hidden'):
?>
    <tr>
      <td valign='top'><?php echo ucfirst($key); ?>:</td>
      <td valign='top'><?php echo "<span title='".$class." field'>".form_input(array('name' => $key, 'id' => $key, 'size' => '45', 'alt' => $class, 'autocomplete' => 'off', 'class' => $class), $publication->$key);?></span></td>
    </tr>
<?php
      endif; //class != hidden
    endforeach;

    $keywords = $publication->keywords;
    if (is_array($keywords))
      $keywords = implode($keywords, ', ');
    else
      $keywords = "";
      
      $key    = 'keywords';
      $class  = 'optional';
?>      
    <tr>
      <td valign='top'>Keywords:</td>
      <td valign='top'><?php echo "<span title='".$class." field'>".form_input(array('name' => $key, 'id' => $key, 'size' => '45', 'alt' => $class, 'autocomplete' => 'off', 'class' => $class), $keywords);?></span>
      <div name='keyword_autocomplete' id='keyword_autocomplete' class='autocomplete'>
      </div>
      <?php echo $this->ajax->auto_complete_field('keywords', $options = array('url' => base_url().'index.php/keywords/li_keywords/', 'update' => 'keyword_autocomplete', 'tokens'=> ',', 'frequency' => '0.01'))."\n";?>
      </td>
    </tr>
<?php

?>
    <tr>
      <td valign='top'>Authors:</td>
      <td>
<?php
        $authors = array();
        if (is_array($publication->authors))
        {
          foreach ($publication->authors as $author)
          {
            $authors[] = $author->getName();
          }
        }

        echo form_textarea(array('name' => 'authors', 'id' => 'authors', 'rows' => '5', 'cols' => '42', 'value' => implode($authors, "\n")));
        ?>
        <div name='author_autocomplete' id='author_autocomplete' class='autocomplete'>
        </div>
        <?php echo $this->ajax->auto_complete_field('authors', $options = array('url' => base_url().'index.php/authors/li_authors/', 'update' => 'author_autocomplete', 'tokens'=> '\n', 'frequency' => '0.01'))."\n";?>
      </td>
    </tr>
    <tr>
      <td valign='top'>Editors:</td>
      <td>
<?php
        $editors = array();
        if (is_array($publication->editors))
        {
          foreach ($publication->editors as $author)
          {
            $editors[] = $author->cleanname;
          }
        }

        echo form_textarea(array('name' => 'editors', 'id' => 'editors', 'rows' => '5', 'cols' => '42', 'value' => implode($editors, "\n")));
        ?>
        <div name='editor_autocomplete' id='editor_autocomplete' class='autocomplete'>
        </div>
        <?php echo $this->ajax->auto_complete_field('editors', $options = array('url' => base_url().'index.php/authors/li_authors/', 'update' => 'editor_autocomplete', 'tokens'=> '\n', 'frequency' => '0.01'))."\n";?>
      </td>
    </tr>
  </table>
<?php
  foreach ($publicationfields as $key => $class):
    if ($class == 'hidden'):
      echo form_hidden($key, $publication->$key)."\n";
    endif;
  endforeach;
      

  echo form_submit('publication_submit', 'Submit')."\n";
  echo form_close()."\n";
?>
</div>