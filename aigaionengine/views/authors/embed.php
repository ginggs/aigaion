<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
//this view is meant as an example of how you can embed an per-author-publication-listing in another page.
//See also the controller authors/embed

$userlogin = getUserLogin();

?>
<div class='author'>
  <div class='header'><?php echo $author->getName() ?></div>
<table width='100%'>
<tr>
    <td  width='100%'>
      <table class='author_details'>
<?php
      $authorfields = array('firstname', 'von', 'surname', 'email', 'url', 'institute');
      foreach ($authorfields as $field)
      {
        if (trim($author->$field) != '')
        {
?>
          <tr>
            <td valign='top'><?php echo ucfirst($field); ?>:</td>
            <td valign='top'><?php echo $author->$field; ?></td>
          </tr>
<?php
        }
      }
?>
      </table>
    </td>
    <td>
<?php 
echo '<div style="border:1px solid black;padding-right:0.2em;margin:0.2em;">';
	echo "
<ul>";
echo  "<li><nobr>["
      .anchor('export/author/'.$author->author_id,'BiBTeX',array('target'=>'aigaion_export'))."]</nobr></li>
       <li><nobr>["
      .anchor('export/author/'.$author->author_id.'/ris','RIS',array('target'=>'aigaion_export'))."]</nobr></li>
       <li><nobr>["
      .anchor('authors/embed/'.$author->author_id.'/type','Order on type/journal')."]</nobr></li>
       <li><nobr>["
      .anchor('authors/embed/'.$author->author_id.'/title','Order alphabetically on title')."]</nobr></li>
       <li><nobr>["
      .anchor('authors/embed/'.$author->author_id.'/author','Order alphabetically on author')."]</nobr></li>
       <li><nobr>["
      .anchor('authors/embed/'.$author->author_id.'/year','Order on year')."]</nobr></li>
</ul>
";

echo '</div>';
?>
    </td>
</tr>
</table>

  <br/>
</div>