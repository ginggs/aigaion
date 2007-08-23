<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
$publicationfields  = getPublicationFieldArray($publication->pub_type);
$formAttributes     = array('ID' => 'publication_'.$publication->pub_id.'_review');
?>
<div class='publication'>
  <div class='header'>Review publication</div>
<?php
    //open the edit form
    echo form_open('publications/commit', $formAttributes)."\n";
    echo form_hidden('edit_type',   $review['edit_type'])."\n";
    echo form_hidden('pub_id',      $publication->pub_id)."\n";
    echo form_hidden('user_id',     $publication->user_id)."\n";
    echo form_hidden('submit_type', 'review')."\n";
    echo form_hidden('pub_type',    $publication->pub_type)."\n";
    echo form_hidden('title',       $publication->title)."\n";
    foreach ($publicationfields as $key => $class):
    echo form_hidden($key,        $publication->$key)."\n";
    endforeach;
?>    
    <table class='publication_review_form' width='100%'>
<?php
    //cite id review
    if ($review['bibtex_id'] != null)
    {
      $key    = 'bibtex_id';
      $class  = 'required';
?>
      <tr>
        <td colspan = 2>
          <div class='errormessage'><?php echo $review['bibtex_id']; ?></div>
        </td>
      </tr>
      <tr>
        <td valign='top'>Citation:</td>
        <td valign='top'><?php echo "<span title='".$class." field'>".form_input(array('name' => $key, 'id' => $key, 'size' => '45', 'alt' => $class, 'autocomplete' => 'off', 'class' => $class), $publication->bibtex_id);?></span></td>
      </tr>
<?php
    }
    //keyword review
    if ($review['keywords'] != null)
    {
?>
      <tr>
        <td colspan = 2>
          <div class='errormessage'><?php echo $review['keywords']; ?></div>
        </td>
      </tr>
<?php
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
        <td valign='top'><?php echo "<span title='".$class." field'>".form_input(array('name' => $key, 'id' => $key, 'size' => '45', 'alt' => $class, 'autocomplete' => 'off', 'class' => $class), $keywords);?></span></td>
      </tr>
<?php
    }
    else
    {
      if (is_array($publication->keywords))
      echo form_hidden('keywords', implode($publication->keywords, ', '))."\n";
      else
      echo form_hidden('keywords', '')."\n";
    }

    //author review
    $authors = array();
    if (is_array($publication->authors))
    {
      foreach ($publication->authors as $author)
      {
        $authors[] = $author->getName();
      }
    }

    if ($review['authors'] != null)
    {
?>
      <tr>
        <td colspan = 2>
          <div class='errormessage'><?php echo $review['authors']; ?></div>
        </td>
      </tr>
      <tr>
        <td valign='top'>Authors:</td>
        <td>
<?php
          echo form_textarea(array('name' => 'authors', 'id' => 'authors', 'rows' => '5', 'cols' => '42', 'value' => implode($authors, "\n")));
?>
          <div name='author_autocomplete' id='author_autocomplete' class='autocomplete'>
          </div>
          <?php echo $this->ajax->auto_complete_field('authors', $options = array('url' => base_url().'index.php/publications/li_keywords/', 'update' => 'author_autocomplete', 'tokens'=> '\n', 'frequency' => '0.01'))."\n";?>
        </td>
      </tr>
<?php
    }
    else
    echo form_hidden('authors', implode($authors, "\n"))."\n";

    //editor review
    $editors = array();
    if (is_array($publication->editors))
    {
      foreach ($publication->editors as $author)
      {
        $editors[] = $author->getName();
      }
    }

    if ($review['editors'] != null)
    {
?>
      <tr>
        <td colspan = 2>
          <div class='errormessage'><?php echo $review['editors']; ?></div>
        </td>
      </tr>
      <tr>
        <td valign='top'>editors:</td>
        <td>
<?php
          echo form_textarea(array('name' => 'editors', 'id' => 'editors', 'rows' => '5', 'cols' => '42', 'value' => implode($editors, "\n")));
?>
          <div name='editor_autocomplete' id='editor_autocomplete' class='autocomplete'>
          </div>
          <?php echo $this->ajax->auto_complete_field('editors', $options = array('url' => base_url().'index.php/publications/li_keywords/', 'update' => 'editor_autocomplete', 'tokens'=> '\n', 'frequency' => '0.01'))."\n";?>
        </td>
      </tr>
<?php
    }
    else
      echo form_hidden('editors', implode($editors, "\n"))."\n";
?>
  </table>

<?php
  echo form_submit('publication_submit', 'Submit')."\n";
  echo form_close()."\n";
?>
</div>