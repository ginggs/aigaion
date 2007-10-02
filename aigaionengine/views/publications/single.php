<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
  $publicationfields = getPublicationFieldArray($publication->pub_type);
  if (!isset($categorize)) $categorize= False;
  
//some things are dependent on user rights.
//$accessLevelEdit is set to true iff the edit access level of the publication does not make it
//inaccessible to the logged user. Note: this does NOT yet garantuee atachemtn_edit or note_ediot or publication_edit rights
$accessLevelEdit = $this->accesslevels_lib->canEditObject($publication);
$userlogin  = getUserLogin();
$user       = $this->user_db->getByID($userlogin->userID());
    

?>
<div class='publication'>
  <div class='optionbox'><?php 
    if (    ($userlogin->hasRights('publication_edit'))
         && ($accessLevelEdit)
        )
        echo "[".anchor('publications/delete/'.$publication->pub_id, 'delete', array('title' => 'Delete this publication'))."]&nbsp;";
        echo "[".anchor('publications/edit/'.$publication->pub_id, 'edit', array('title' => 'Edit this publication'))."]";
        
        echo "&nbsp;<span id='bookmark_pub_".$publication->pub_id."'>[";
        if ($userlogin->hasRights('bookmarklist')) {
          if ($publication->isBookmarked) {
            echo $this->ajax->link_to_remote("UnBookmark",
                  array('url'     => site_url('/bookmarklist/removepublication/'.$publication->pub_id),
                        'update'  => 'bookmark_pub_'.$publication->pub_id
                        )
                  );
          } 
          else {
            echo $this->ajax->link_to_remote("Bookmark",
                  array('url'     => site_url('/bookmarklist/addpublication/'.$publication->pub_id),
                        'update'  => 'bookmark_pub_'.$publication->pub_id
                        )
                  );
          }
        }
        echo "]</span>";        
        echo  '&nbsp;['
           .anchor('export/publication/'.$publication->pub_id,'BiBTeX',array('target'=>'aigaion_export')).']';
        echo  '&nbsp;['
           .anchor('export/publication/'.$publication->pub_id.'/ris','RIS',array('target'=>'aigaion_export')).']';
?>
  </div>
  <div class='header'><?php echo $publication->title; ?>
<?php
    $accesslevels = "&nbsp;&nbsp;r:<img class='al_icon' src='".getIconurl('al_'.$publication->derived_read_access_level.'.gif')."'/> e:<img class='al_icon' src='".getIconurl('al_'.$publication->derived_edit_access_level.'.gif')."'/>";
    echo anchor('accesslevels/edit/publication/'.$publication->pub_id,$accesslevels,array('title'=>'click to modify access levels'));
    
?>    
  </div>
  <table class='publication_details' width='100%'>
    <tr>
      <td>Type of publication:</td>
      <td><?php echo $publication->pub_type; ?></td>
    </tr>
    <tr>
      <td>Citation:</td>
      <td><?php echo $publication->bibtex_id; ?></td>
    </tr>
<?php 
    foreach ($publicationfields as $key => $class):
      $pages = false;
      if ($key == "pages")
      {
        if ($publication->firstpage || $publication->lastpage)
          $pages = true;
      }   
      if ($publication->$key || $pages):
?>
    <tr>
      <td valign='top'><?php echo ucfirst($key); ?>:</td>
      <td valign='top'><?php 
        if ($key=='doi') {
            echo '<a target=_blank href="http://dx.doi.org/'.$publication->$key.'">'.$publication->$key.'</a>'; 
        } else if ($key == 'pages') {
          $pages = $publication->firstpage;
          if ($publication->lastpage) {
            if ($pages)
              $pages .= " - ".$publication->lastpage;
            else
              $pages = $publication->lastpage;
          }
          echo $pages;
        }
        else {
            echo $publication->$key; 
        }
      ?></td>
    </tr>
<?php
      endif;
    endforeach;

    $keywords = $publication->getKeywords();
    if (is_array($keywords))
    {
      $keywords = implode($keywords, ', ');
?>      
    <tr>
      <td valign='top'>Keywords:</td>
      <td valign='top'><?php echo $keywords ?></td>
    </tr>
<?php
    }

    if (count($publication->authors) > 0):
?>
    <tr>
      <td valign='top'>Authors</td>
      <td valign='top'>
        <span class='authorlist'>
<?php     foreach ($publication->authors as $author)
          {
            echo anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))."<br />\n";
          }
?>
        </span>
      </td>
    </tr>
<?php 
    endif;
    if (count($publication->editors) > 0):
?>
    <tr>
      <td valign='top'>Editors</td>
      <td valign='top'>
        <span class='authorlist'>
<?php     foreach ($publication->editors as $author)
          {
            echo anchor('authors/show/'.$author->author_id, $author->cleanname, array('title' => 'All information on '.$author->cleanname))."<br />\n";
          }
?>
        </span>
      </td>
    </tr>
<?php 
    endif;

    $crossrefpubs = $this->publication_db->getXRefPublicationsForPublication($publication->bibtex_id);
    if (count($crossrefpubs)>0):
?>
    <tr>
      <td valign='top'>Crossref by</td>
      <td valign='top'>
<?php
        foreach ($crossrefpubs as $crossrefpub) {
            $linkname = $crossrefpub->bibtex_id;
            if ($linkname == '') {
                $linkname = $crossrefpub->title;
            }
            echo anchor('/publications/show/'.$crossrefpub->pub_id, $linkname)."<br/>";
        }
?>
      </td>
    </tr>
<?php
    endif;
?>
    <tr>
      <td valign='top'>Added by:</td>
      <td valign='top'>
<?php
        echo '<b>['.getAbbrevForUser($publication->user_id).']</b>';
?>
      </td>
    </tr>
    
    <tr>
      <td colspan='2' valign='top'>
        <div class='optionbox'>
<?php 
    if (    ($userlogin->hasRights('attachment_edit'))
         && ($accessLevelEdit)
        )
        echo '['.anchor('attachments/add/'.$publication->pub_id,'add attachment').']';
?>
        </div>
        <div class='header'>Attachments</div>
      </td>
    </tr>
    <tr>
        <td colspan='2' valign='top'>
<?php
    $attachments = $publication->getAttachments();
    echo "<ul class='attachmentlist'>";
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
    if (    ($userlogin->hasRights('note_edit'))
         && ($accessLevelEdit)
        )
        echo '['.anchor('notes/add/'.$publication->pub_id,'add note').']';
?>
        </div>
        <div class='header'>Notes</div>
      </td>
    </tr>
    <tr>
        <td colspan='2' valign='top'>
<?php
    $notes = $publication->getNotes();
    echo "<ul class='notelist'>";
    foreach ($notes as $note) {
        echo "<li>".$this->load->view('notes/summary',
                          array('note'   => $note),
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
    
    if (    ($userlogin->hasRights('publication_edit'))
         && ($accessLevelEdit)
        ) 
    {
        if ($categorize == True) {
            echo '['.anchor('publications/show/'.$publication->pub_id,'finish categorization').']';
        } else {
            echo '['.anchor('publications/show/'.$publication->pub_id.'/categorize','categorize publication').']';
        } 
    }
?>
        </div>
        <div class='header'>Topics</div>
      </td>
    </tr>
    <tr>
      <td colspan='2' valign='top'>
<?php     
        if (    ($userlogin->hasRights('publication_edit'))
             && ($accessLevelEdit)
             && ($categorize == True)
            ) 
        {
        
            echo "<div class='message'>Click on a topic name to change its subscription status.</div>";
            $user = $this->user_db->getByID($userlogin->userId());
            $config = array('onlyIfUserSubscribed'=>True,
                              'user'=>$user,
                              'includeGroupSubscriptions'=>True,
                              'publicationId'=>$publication->pub_id
                                    );
            $root = $this->topic_db->getByID(1, $config);
            $this->load->vars(array('subviews'  => array('topics/publicationsubscriptiontreerow'=>array())));
        } else {
            $user = $this->user_db->getByID($userlogin->userId());
            $config = array('onlyIfUserSubscribed'=>True,
                              'user'=>$user,
                              'includeGroupSubscriptions'=>True,
                              'onlyIfPublicationSubscribed'=>True,
                              'publicationId'=>$publication->pub_id
                                    );
            $root = $this->topic_db->getByID(1, $config);
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