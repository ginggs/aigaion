<div class='publication'>
  <div class='publication_title'><?php echo $publication->getTitle(); ?></div>
  <table class='publication_details'>
    <tr>
      <td valign='top'>Authors</td>
      <td valign='top'>
        <div class='publication_authors'>
<?php
          
          foreach ($publication->authors as $author)
          {
            echo anchor('authors/show/'.$author->ID, $author->getName(), array('title' => 'All information on '.$author->getName()))."<br />\n";
          }
?>
        </div>
      </td>
    </table>
</div>