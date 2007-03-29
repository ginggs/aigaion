<ul class='nosymbol'>
<?php
  foreach ($authorlist->list as $author)
  {
    echo "  <li>".anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))."</li>\n";
  }
?>
</ul>
