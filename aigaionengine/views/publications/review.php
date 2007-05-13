<?php
  $publicationfields  = getPublicationFieldArray($publication->pub_type);
  $formAttributes     = array('ID' => 'publication_'.$publication->pub_id.'_review');
?>
<div class='publication'>
  <div class='header'>Review publication</div>
    <table class='publication_review_form'>
<?php
  //open the edit form
  echo form_open('publications/commit', $formAttributes)."\n";
  echo form_hidden('pub_id',      $publication->pub_id)."\n";
  echo form_hidden('user_id',     $publication->user_id)."\n";
  echo form_hidden('submit_type', 'review')."\n";
  echo form_hidden('pub_type',    $publication->pub_type)."\n";
  echo form_hidden('title',       $publication->title)."\n";
  echo form_hidden('bibtex_id',   $publication->bibtex_id)."\n";
  foreach ($publicationfields as $key => $class):
    echo form_hidden($key,        $publication->$key)."\n";
  endforeach;

  //keyword review
  if ($review['keywords'] != null)
  {
    echo "<div class='errormessage'>".$review['keywords']."</div>\n";

    $keywords = $publication->keywords;
    if (is_array($keywords))
      $keywords = implode($keywords, ', ');
    else
    {
      $keywords = "";
      
      $key    = 'keywords';
      $class  = 'optional';
?>      
    <tr>
      <td valign='top'>Keywords:</td>
      <td valign='top'><?php echo "<span title='".$class." field'>".form_input(array('name' => $key, 'id' => $key, 'size' => '45', 'alt' => $class, 'autocomplete' => 'off', 'class' => $class), $keywords);?></span></td>
    </tr>
<?php
    }
  }
  else
    echo form_hidden('keywords', implode($publication->keywords, ', '))."\n";
    
  
/*
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
        <?php echo $this->ajax->auto_complete_field('editors', $options = array('url' => base_url().'index.php/publications/li_keywords/', 'update' => 'editor_autocomplete', 'tokens'=> '\n', 'frequency' => '0.01'))."\n";?>
      </td>
    </tr>
*/
?>
  </table>
<?php
      
  echo form_submit('publication_submit', 'Submit')."\n";
  echo form_close()."\n";
?>
</div>