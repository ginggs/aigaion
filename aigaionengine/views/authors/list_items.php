<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<ul class='nosymbol'>
<?php
  foreach ($authorlist as $author)
  {
    echo "  <li>".anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))."</li>\n";
  }
?>
</ul>
