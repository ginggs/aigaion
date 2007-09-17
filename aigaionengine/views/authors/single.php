<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class='author'>
  <div class='optionbox'><?php echo "[".anchor('authors/delete/'.$author->author_id, 'delete', array('title' => 'Delete this author'))."]&nbsp[".anchor('authors/edit/'.$author->author_id, 'edit', array('title' => 'Edit this author'))."]";
    echo  '&nbsp;['
           .anchor('export/author/'.$author->author_id,'BiBTeX',array('target'=>'aigaion_export')).']';
    echo  '&nbsp;['
           .anchor('export/author/'.$author->author_id.'/ris','RIS',array('target'=>'aigaion_export')).']';
    
    ?>
  </div>
  <div class='header'><?php echo $author->getName() ?></div>
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