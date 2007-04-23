<?php
  $publicationfields = getPublicationFieldArray($publication->data->type);
  if (!isset($categorize)) $categorize= False;
?>
<div class='publication'>
  <div class='optionbox'><?php echo "[".anchor('publications/edit/'.$publication->data->pub_id, 'edit', array('title' => 'Edit this publication'))."]</div>";?>
  <div class='header'><?php echo $publication->data->title; ?></div>
  <table class='publication_details'>
    <tr>
      <td>Type of publication:</td>
      <td><?php echo $publication->data->type; ?></td>
    </tr>
    <tr>
      <td>Citation:</td>
      <td><?php echo $publication->data->bibtex_id; ?></td>
    </tr>
<?php 
    foreach ($publicationfields as $key => $class):
      if ($publication->data->$key):
?>
    <tr>
      <td valign='top'><?php echo ucfirst($key); ?>:</td>
      <td valign='top'><?php echo $publication->data->$key; ?></td>
    </tr>
<?php
      endif;
    endforeach;

    if (count($publication->data->authors) > 0):
?>
    <tr>
      <td valign='top'>Authors</td>
      <td valign='top'>
        <span class='authorlist'>
<?php     foreach ($publication->data->authors as $author)
          {
            echo anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))."<br />\n";
          }
?>
        </span>
      </td>
    </tr>
<?php 
    endif;
    if (count($publication->data->editors) > 0):
?>
    <tr>
      <td valign='top'>Editors</td>
      <td valign='top'>
        <span class='authorlist'>
<?php     foreach ($publication->data->editors as $author)
          {
            echo anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))."<br />\n";
          }
?>
        </span>
      </td>
    </tr>
<?php 
    endif;
?>

    <tr>
      <td colspan='2' valign='top'>
        <div class='optionbox'>
<?php 
        echo anchor('attachments/add/'.$publication->data->pub_id,'[add attachment]');
?>
        </div>
        <div class='header'>Attachments</div>
      </td>
    </tr>
    <tr>
        <td colspan='2' valign='top'>
<?php
    $attachments = $publication->data->getAttachments();
    echo "<ul>";
    foreach ($attachments as $attachment) {
        echo "<li>".$this->load->view('attachments/summary',
                          array('attachment'   => $attachment),
                          true)."</li>";
    }
    echo "</ul>";
?>
        </td>
    </tr>

    <tr>
      <td colspan='2' valign='top'>
        <div class='optionbox'>
<?php 
        if ($categorize == True) {
            echo anchor('publications/show/'.$publication->data->pub_id,'[finish categorization]');
        } else {
            echo anchor('publications/show/'.$publication->data->pub_id.'/categorize','[categorize publication]');
        } 
?>
        </div>
        <div class='header'>Topics</div>
      </td>
    </tr>
    <tr>
      <td colspan='2' valign='top'>
<?php     
        if ($categorize == True) {
            echo "<div class='message'>Click on a topic name to change it's subscription status.</div>";
            $root = $this->topic_db->getByID(1, array('onlyIfUserSubscribed'=>True,
                                                      'userId'=>getUserLogin()->userId(),
                                                      'includeGroupSubscriptions'=>True,
                                                      'publicationId'=>$publication->data->pub_id
                                                            ));
            $this->load->vars(array('subviews'  => array('topics/publicationsubscriptiontreerow'=>array())));
        } else {
            $root = $this->topic_db->getByID(1, array('onlyIfUserSubscribed'=>True,
                                                      'userId'=>getUserLogin()->userId(),
                                                      'includeGroupSubscriptions'=>True,
                                                      'onlyIfPublicationSubscribed'=>True,
                                                      'publicationId'=>$publication->data->pub_id
                                                            ));
            $this->load->vars(array('subviews'  => array('topics/maintreerow'=>array())));
        }
        echo "<div id='topictree-holder'>\n<ul class='topictree-list'>\n"
                    .$this->load->view('topics/tree',
                                      array('topics'   => $root->getChildren(),
                                            'showroot'  => True,
                                            'collapseAll'  => $categorize,
                                            'depth'     => -1
                                            ),  
                                      true)."</ul>\n</div>\n";
?>
      </td>
    </tr>
  </table>
</div>