<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$userlogin = getUserLogin();
?>
<div class='keyword'>
<?php 
// echo "<div class='optionbox'>";
// echo "[".anchor('keywords/delete/'.$keyword->keyword_id, 'delete', array('title' => 'Delete this keyword'))."]&nbsp[".anchor('keywords/edit/'.$keyword->keyword_id, 'edit', array('title' => 'Edit this keyword'))."]";
// echo "</div>";
  ?>
  <div class='header'><?php echo $keyword->keyword ?></div>
<table width='100%'>
<tr>
    <td>
<?php 
echo '<div style="border:1px solid black;padding-right:0.2em;margin:0.2em;">';
	echo "
<ul>";
    if ($userlogin->hasRights('bookmarklist')) {
      echo  '<li><nobr>['
           .anchor('bookmarklist/addkeyword/'.$keyword->keyword_id,'BookmarkAll')
           .']</nobr></li><li><nobr>['
           .anchor('bookmarklist/removekeyword/'.$keyword->keyword_id,'UnBookmarkAll').']</nobr></li>';
    }
echo  "
</ul>
";

echo '</div>';
?>
    </td>
</tr>
</table>

  <br/>
</div>