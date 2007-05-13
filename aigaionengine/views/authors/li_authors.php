<ul>
<?php
  foreach ($authors as $author)
  {
    echo "  <li>".$author->getName()."</li>\n";
  }
?>
</ul>