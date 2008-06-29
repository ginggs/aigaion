<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
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
        ) {
		echo "[".anchor('publications/delete/'.$publication->pub_id, 'delete', array('title' => 'Delete this publication'))."]&nbsp;";
		echo "[".anchor('publications/edit/'.$publication->pub_id, 'edit', array('title' => 'Edit this publication'))."]";
	}

        if ($userlogin->hasRights('bookmarklist')) {
          echo "&nbsp;<span id='bookmark_pub_".$publication->pub_id."'>";
          if ($publication->isBookmarked) {
            echo '['.$this->ajax->link_to_remote("UnBookmark",
                  array('url'     => site_url('/bookmarklist/removepublication/'.$publication->pub_id),
                        'update'  => 'bookmark_pub_'.$publication->pub_id
                        )
                  ).']';
          }
          else {
            echo '['.$this->ajax->link_to_remote("Bookmark",
                  array('url'     => site_url('/bookmarklist/addpublication/'.$publication->pub_id),
                        'update'  => 'bookmark_pub_'.$publication->pub_id
                        )
                  ).']';
          }
          echo "</span>";
        }
        echo  '&nbsp;['
           .anchor('export/publication/'.$publication->pub_id.'/bibtex','BiBTeX',array('target'=>'aigaion_export')).']';
        echo  '&nbsp;['
           .anchor('export/publication/'.$publication->pub_id.'/ris','RIS',array('target'=>'aigaion_export')).']';
?>
  </div>
  <div class='header'><?php echo $publication->title; ?>
<?php
    $accesslevels = "&nbsp;&nbsp;r:<img class='rights_icon' src='".getIconurl('rights_'.$publication->derived_read_access_level.'.gif')."'/> e:<img class='rights_icon' src='".getIconurl('rights_'.$publication->derived_edit_access_level.'.gif')."'/>";
    echo anchor('accesslevels/edit/publication/'.$publication->pub_id,$accesslevels,array('title'=>'click to modify access levels'));

/*
//TEST OF NEW READ/EDIT RIGHTS INTERFACE
    $read_icon = $this->accesslevels_lib->getReadAccessLevelIcon($publication);
    $edit_icon = $this->accesslevels_lib->getEditAccessLevelIcon($publication);

    $readrights = $this->ajax->link_to_remote($read_icon,
                  array('url'     => site_url('/accesslevels/toggle/publication/'.$publication->pub_id.'/read'),
                        'update'  => 'publication_rights_'.$publication->pub_id
                       )
                  );
    $editrights = $this->ajax->link_to_remote($edit_icon,
                  array('url'     => site_url('/accesslevels/toggle/publication/'.$publication->pub_id.'/edit'),
                        'update'  => 'publication_rights_'.$publication->pub_id
                       )
                  );

    echo "<span id='publication_rights_".$publication->pub_id."'><span title='publication read / edit rights'>".$readrights.$editrights."</span></span>";

*/
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
    $capitalfields = getCapitalFieldArray();
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
      <td valign='top'><?php
        if ($key=='namekey') {
            echo 'Key <span title="This is the bibtex `key` field, used to define sorting keys">(?)</span>'; //stored in the databse as namekey, it is actually the bibtex field 'key'
        } else {
            if (in_array($key,$capitalfields)) {
                echo strtoupper($key);
            } else  {
                echo ucfirst($key);
            }
        }
      ?>:</td>
      <td valign='top'><?php
        if ($key=='doi') {
            echo '<a href="http://dx.doi.org/'.$publication->$key.'" class="open_extern">'.$publication->$key.'</a>';
        } else if ($key=='url') {
            $this->load->helper('utf8');
            $urlname = prep_url($publication->url);
            if (utf8_strlen($urlname)>21) {
                $urlname = utf8_substr($urlname,0,30)."...";
            }
            echo "<a title='".prep_url($publication->url)."' href='".prep_url($publication->url)."' class='open_extern'>".$urlname."</a>\n";
        } else if ($key == 'month') {
          if ($publication->month != "" && $publication->month > 0 && $publication->month <= 12 ) {
            $months = getMonthsEng();
            echo $months[$publication->month];
          }
        } else if ($key == 'pages') {
          $pages = $publication->firstpage;
          if ($publication->lastpage) {
            if ($pages)
              $pages .= " - ".$publication->lastpage;
            else
              $pages = $publication->lastpage;
          }
          echo $pages;
        } elseif ($key == 'crossref') {
            $xref_pub = $this->publication_db->getByBibtexID($publication->$key);
            if ($xref_pub != null) {
                echo '<i>'.anchor('publications/show/'.$xref_pub->pub_id,$publication->$key).':</i>';
                //and then the summary of the crossreffed pub. taken from views/publications/list
                $summaryfields = getPublicationSummaryFieldArray($xref_pub->pub_type);
                echo "<div class='message'>
                      <span class='title'>".anchor('publications/show/'.$xref_pub->pub_id, $xref_pub->title, array('title' => 'View publication details'))."</span>";

                //authors of crossref
                $num_authors    = count($xref_pub->authors);
                $current_author = 1;

                foreach ($xref_pub->authors as $author)
                {
                  if (($current_author == $num_authors) & ($num_authors > 1)) {
                    echo " and ";
                  }
                  else {
                    echo ", ";
                  }

                  echo  "<span class='author'>".anchor('authors/show/'.$author->author_id, $author->getName('vlf'), array('title' => 'All information on '.$author->cleanname))."</span>";
                  $current_author++;
                }

                //editors of crossref
                $num_editors    = count($xref_pub->editors);
                $current_editor= 1;

                foreach ($xref_pub->editors as $editor)
                {
                  if (($current_editor == $num_editors) & ($num_editors > 1)) {
                    echo " and ";
                  }
                  else {
                    echo ", ";
                  }

                  echo  "<span class='author'>".anchor('authors/show/'.$editor->author_id, $editor->getName('vlf'), array('title' => 'All information on '.$editor->cleanname))."</span>";
                  $current_editor++;
                }
                if ($num_editors>1) {
                    echo ' (eds)';
                } elseif ($num_editors>0) {
                    echo ' (ed)';
                }
                foreach ($summaryfields as $key => $prefix) {
                  $val = trim($xref_pub->$key);
                  $postfix='';
                  if (is_array($prefix)) {
                    $postfix = $prefix[1];
                    $prefix = $prefix[0];
                  }
                  if ($val) {
                    echo $prefix.$val.$postfix;
                  }
                }
                echo "</div>"; //end of publication_summary div for crossreffed publication
            } else {
                echo $publication->$key;
            }
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
      $keyword_string = "";
      foreach ($keywords as $keyword)
      {
        $keyword_string .= anchor('keywords/single/'.$keyword->keyword_id, $keyword->keyword).", ";
      }
      $keyword_string = substr($keyword_string, 0, -2);
?>
    <tr>
      <td valign='top'>Keywords:</td>
      <td valign='top'><?php echo $keyword_string ?></td>
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
            echo anchor('authors/show/'.$author->author_id, $author->getName('vlf'), array('title' => 'All information on '.$author->cleanname))."<br />\n";
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
            echo anchor('authors/show/'.$author->author_id, $author->getName('vlf'), array('title' => 'All information on '.$author->cleanname))."<br />\n";
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
      <td valign='top'>Total mark:</td>
      <td valign='top'>
<?php
        echo $publication->mark;
?>
      </td>
    </tr>
<?php
    if ($userlogin->hasRights('note_edit')) {
      $this->load->helper('form');
?>
      <tr>
        <td valign='top'>Your mark:</td>
        <td valign='top'>
<?php
          echo form_open('publications/read/'.$publication->pub_id);

          $mark = $publication->getUserMark();
          if ($mark==-1) {//not read
            echo form_submit('read','Read/Add mark');
          } else {
            echo form_submit('read','Update mark');
          }
          echo '1';
          for ($i = 1; $i < 6; $i++)
          {
            echo form_radio('mark',$i,$i==$mark);
          }
          echo '5&nbsp;';
          if ($mark==-1) {//not read
            echo form_close();
          } else {
            echo form_close();
            echo form_open('publications/unread/'.$publication->pub_id);
            echo form_submit('unread','Unread');
            echo form_close();
          }
?>
        </td>
      </tr>
<?php
    }
?>
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