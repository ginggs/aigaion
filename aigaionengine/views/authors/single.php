<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$userlogin = getUserLogin();
?>
<div class='author'>
  <div class='optionbox'><?php echo "[".anchor('authors/delete/'.$author->author_id, 'delete', array('title' => 'Delete this author'))."]&nbsp[".anchor('authors/edit/'.$author->author_id, 'edit', array('title' => 'Edit this author'))."]";
    
    ?>
  </div>
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
    if ($userlogin->hasRights('bookmarklist')) {
      echo  '<li><nobr>['
           .anchor('bookmarklist/addauthor/'.$author->author_id,'BookmarkAll')
           .']</nobr></li><li><nobr>['
           .anchor('bookmarklist/removeauthor/'.$author->author_id,'UnBookmarkAll').']</nobr></li>';
    }
echo  "<li><nobr>["
      .anchor('export/author/'.$author->author_id,'BiBTeX',array('target'=>'aigaion_export'))."]</nobr></li>
       <li><nobr>["
      .anchor('export/author/'.$author->author_id.'/ris','RIS',array('target'=>'aigaion_export'))."]</nobr></li>
       <li><nobr>["
      .anchor('authors/show/'.$author->author_id.'/type','Order on type/journal')."]</nobr></li>
       <li><nobr>["
      .anchor('authors/show/'.$author->author_id.'/title','Order alphabetically on title')."]</nobr></li>
       <li><nobr>["
      .anchor('authors/show/'.$author->author_id.'/author','Order alphabetically on author')."]</nobr></li>
       <li><nobr>["
      .anchor('authors/show/'.$author->author_id.'/year','Order on year')."]</nobr></li>
</ul>
";

echo '</div>';
?>
    </td>
</tr>
</table>
<?php
    $similar = $author->getSimilarAuthors();
    if (count($similar)>0) {
        echo "<div class='message'>Found authors with very similar names.
              You can choose to merge the following authors with this author 
              by clicking on the merge link.<br/>\n";
        foreach ($similar as $simauth) {
            echo anchor('authors/show/'.$simauth->author_id, $simauth->getName(), array('title' => 'Click to show details'))."\n";
		    echo '('.anchor('authors/merge/'.$author->author_id.'/'.$simauth->author_id, 'merge', array('title' => 'Click to merge')).")<br />\n";
		}
		echo "</div>\n";
    }
?>

  <br/>
</div>