<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
$publicationFields  = getFullFieldArray();
$importCount        = count($publications);
$formAttributes     = array('ID' => 'import_review');
echo form_open('import/commit',   $formAttributes)."\n";
echo form_hidden('import_count',  $importCount)."\n";
$mark = '';
if ($markasread) $mark = 'markasread'; //commit controller expects not a boolean, but the value 'markasread'
echo form_hidden('markasread',  $mark)."\n";
$b_even = true;

echo "<div class='publication'>\n";
echo "  <div class='header'>Review publications</div>\n";

for ($i = 0; $i < $importCount; $i++)
{

  $b_even = !$b_even;
  if ($b_even)
  $even = 'even';
  else
  $even = 'odd';

    echo "<div class='publication_summary ".$even."' id='publicationsummary".$i."'>\n";
    echo "<table width='100%'>\n";
    //open the edit form
    echo form_hidden('pub_type_'.$i,    $publications[$i]->pub_type)."\n";
    //bibtex_id

    ?>
    <tr>
      <td colspan = 2><?php
        echo form_checkbox(array('name' => 'do_import_'.$i, 'id' => 'import_'.$i, 'value' => 'CHECKED'));
        echo "Import: <b>".$publications[$i]->title."</b>\n"; 
        
        if ($reviews[$i]['title'] != null)
          echo "<div class='errormessage'>".$reviews[$i]['title']."</div>\n";
        ?>
        </td>
    </tr>
    <?php
    if ($reviews[$i]['bibtex_id'] != null)
    {
    ?>
    <tr>
      <td colspan = 2><div class='errormessage'><?php echo $reviews[$i]['bibtex_id'] ?></div></td>
    </tr>
    <?php
    }
    ?>
    <tr>
      <td>Citation:</td>
      <td><?php echo form_input(array('name' => 'bibtex_id_'.$i, 'id' => 'bibtex_id_'.$i, 'size' => '45'), $publications[$i]->bibtex_id); ?></td>
    </tr>
    <?php

    if ($reviews[$i]['authors'] != null)
    {

      ?>
      <tr>
        <td colspan = 2><div class='errormessage'><?php echo $reviews[$i]['authors'] ?></div></td>
      </tr>
      <tr>
        <td valign='top'>Authors:</td>
        <td>
          <?php
          $authors = array();
          if (is_array($publications[$i]->authors))
          {
            foreach ($publications[$i]->authors as $author)
            {
              $authors[] = $author->getName();
            }
          }

          echo form_textarea(array('name' => 'authors_'.$i, 'id' => 'authors_'.$i, 'rows' => '5', 'cols' => '42', 'value' => implode($authors, "\n")));
          echo "<div name='author_autocomplete_".$i."' id='author_autocomplete_".$i."' class='autocomplete'></div>\n";
          echo $this->ajax->auto_complete_field('authors_'.$i, $options = array('url' => base_url().'index.php/authors/li_authors/authors_'.$i, 'update' => 'author_autocomplete_'.$i, 'tokens'=> '\n', 'frequency' => '0.01'))."\n";
          ?>
        </td>
      </tr>
      <?php
    }
    else
    {
      //authors
      $authors = array();
      if (is_array($publications[$i]->authors))
      {
        foreach ($publications[$i]->authors as $author)
        {
          $authors[] = $author->getName();
        }
        echo form_hidden('authors_'.$i, implode($authors, "\n"))."\n";
      }
      else
        echo form_hidden('authors_'.$i, '')."\n";
    }

    if ($reviews[$i]['editors'] != null)
    {

      ?>
      <tr>
        <td colspan = 2><div class='errormessage'><?php echo $reviews[$i]['editors'] ?></div></td>
      </tr>
      <tr>
        <td valign='top'>Editors:</td>
        <td>
          <?php
          $authors = array();
          if (is_array($publications[$i]->editors))
          {
            foreach ($publications[$i]->editors as $author)
            {
              $authors[] = $author->getName();
            }
          }

          echo form_textarea(array('name' => 'editors_'.$i, 'id' => 'editors_'.$i, 'rows' => '5', 'cols' => '42', 'value' => implode($authors, "\n")));
          echo "<div name='editor_autocomplete_".$i."' id='editor_autocomplete_".$i."'class='autocomplete'></div>\n";
          echo $this->ajax->auto_complete_field('editors_'.$i, $options = array('url' => base_url().'index.php/authors/li_authors/editors_'.$i, 'update' => 'editor_autocomplete_'.$i, 'tokens'=> '\n', 'frequency' => '0.01'))."\n";
          ?>
        </td>
      </tr>
      <?php

    }
    else
    {
      //editors
      $editors = array();
      if (is_array($publications[$i]->editors))
      {
        foreach ($publications[$i]->editors as $editor)
        {
          $editors[] = $editor->getName();
        }
        echo form_hidden('editors_'.$i, implode($editors, "\n"))."\n";
      }
      else
        echo form_hidden('editors_'.$i, '')."\n";
    }


    if ($reviews[$i]['keywords'] != null)
    {
      $keywords = $publications[$i]->keywords;
      if (is_array($keywords))
      $keywords = implode($keywords, ', ');
      else
      $keywords = "";

      ?>
      <tr>
        <td colspan = 2><div class='errormessage'><?php echo $reviews[$i]['keywords'] ?></div></td>
      </tr>
      <tr>
        <td valign='top'>Keywords:</td>
        <td valign='top'>
          <?php
          echo form_input(array('name' => 'keywords_'.$i, 'id' => 'keywords_'.$i, 'size' => '45', 'alt' => 'keywords', 'autocomplete' => 'off'), $keywords);
          echo "<div name='keyword_autocomplete_".$i."' id='keyword_autocomplete_".$i."' class='autocomplete'></div>\n";
          echo $this->ajax->auto_complete_field('keywords_'.$i, $options = array('url' => base_url().'index.php/keywords/li_keywords/keywords_'.$i, 'update' => 'keyword_autocomplete_'.$i, 'tokens'=> ',', 'frequency' => '0.01'))."\n";
          ?>
        </td>
      </tr>
    <?php
    }
    foreach ($publicationFields as $field)
    {
      if ($field != "keywords")
      echo form_hidden($field."_".$i,     $publications[$i]->$field)."\n";
      else if ($reviews[$i]['keywords'] == null)
      {
        if (is_array($publications[$i]->keywords))
        echo form_hidden('keywords_'.$i, implode($publications[$i]->keywords, ', '))."\n";
        else
        echo form_hidden('keywords_'.$i, $publications[$i]->keywords)."\n";
      }
    }
    echo form_hidden("actualyear_".$i,     $publications[$i]->actualyear)."\n"; //don't forget to remember this one... as during import, actualyear is determined in parser_import.php
    echo form_hidden("old_bibtex_id_".$i,     $publications[$i]->bibtex_id)."\n"; //don't forget to remember this one... when the bibtexID is changed in the edit box, we need to know whether we should change any crossrefs (later on in controller import.php#commit() )

    ?>
  </table>
  </div>
  <?php

} //end for each publication

echo form_submit('publication_submit', 'Submit')."\n";
echo form_close()."\n";
?>
</div>
