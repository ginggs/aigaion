<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!-- Aigaion menu -->
<?php
  $userlogin = getUserLogin();
?>
<div id="menu_holder">
  <ul class="mainmenu">
    <li class="mainmenu-header">BROWSE</li>
    <li class="mainmenu"><?php echo anchor('topics', 'Topics'); ?></li>
    <li class="mainmenu"><?php echo anchor('publications', 'Publicationlist'); ?></li>
    <li class="mainmenu"><?php echo anchor('authors', 'Authors'); ?></li>
<?php

if ($userlogin->hasRights('bookmarklist')) {
?>
    <li class="mainmenu"><?php echo anchor('bookmarklist', 'My Bookmark List'); ?></li>
<?php
}

?>
    <li class="mainmenu"><?php echo anchor('export', 'Export BiBTeX'); ?></li>
<?php

if ($userlogin->hasRights('publication_edit'))
{
?>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">NEW DATA</li>
    <li class='mainmenu'><?php echo anchor('publications/add', 'New Publication'); ?></li>
    <li class='mainmenu'><?php echo anchor('authors/add', 'New Author'); ?></li>
<?php
} //New publication / author menu
if ($userlogin->hasRights('topic_edit'))
{
?>
    <li class='mainmenu'><?php echo anchor('topics/add', 'New Topic'); ?></li>
<?php
} 

$ACCOUNT_MENU = "";
if ($userlogin->hasRights('user_edit_self')) {
    $ACCOUNT_MENU .= "    <li class='mainmenu'>".anchor('users/edit/'.$userlogin->userId(), 'My Profile')."</li>\n";
}
if ($userlogin->hasRights('topic_subscription')) {
    $ACCOUNT_MENU .= "    <li class='mainmenu'>".anchor('users/topicreview/', 'Topic Review')."</li>\n";
}
if ($userlogin->hasRights('user_edit_all')) {
    $ACCOUNT_MENU .= "    <li class='mainmenu'>".anchor('users/manage', 'Manage All Accounts')."</li>\n";
}
if ($ACCOUNT_MENU != "") {
?>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">ACCOUNT</li>
<?
    echo $ACCOUNT_MENU;
}
?>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">HELP</li>
    <li class="mainmenu"><?php echo anchor('help/', 'Help'); ?></li>
    <li class="mainmenu"><?php echo anchor('help/viewhelp/about', 'About this site'); ?></li>
<?php
if ($userlogin->hasRights('database_manage')) {
?>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">SITE</li>
    <li class="mainmenu"><?php echo anchor('site/configure', 'Site Configuration'); ?></li>
    <li class="mainmenu"><?php echo anchor('site/maintenance', 'Site Maintenance'); ?></li>
<?php
}
?>
    <li class="mainmenu-spacer"></li>
<?php
	if ($userlogin->isAnonymous()) {
    $anonusers = $this->user_db->getAllAnonUsers();
    if (count($anonusers)>0) {
      //more than one anonymous user: show a dropdown where you can choose between the different guest users
?>	    
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">GUEST USER</li>
<?php
      $options = array();
      foreach ($anonusers as $anon) {
          $options[$anon->user_id] = $anon->login;
      }
      echo  "    <li class='mainmenu'>"
             .form_dropdown('anonlogin', 
                            $options, 
                            $userlogin->userId(),
                            "OnChange='var url=\"".site_url('/login/anonymous/')."\";window.document.location=(url+\"/\"+$(\"anonlogin\").value);' id='anonlogin'")
             ."</li>";
    }
        
?>	    
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">LOGIN</li>
<?php
    $this->load->helper('form');
    echo '<li>';
    echo form_open('login/dologin/'.$this->uri->uri_string());
?>
      <table class='loginbox'>
        <tr>
          <td>Name:</td>
        </tr>
        <tr>
          <td><input type=text name=loginName size=10></td>
        </tr>
        <tr>
          <td>Password:</td>
        </tr>
        <tr>
          <td><input type=password name=loginPass size=10></td>
        </tr>
        <tr>
          <td><input title='Remember me' name=remember type=checkbox><p align=right><input type=submit value='Login'></td>
        </tr>
      </table>
<?php
        echo form_close();
        echo '</li>';
	} else {
?>
    <li class="mainmenu-header">LOGGED IN:</li>
    <li class="mainmenu"><?php echo $userlogin->loginName(); ?></li>
    <li class="mainmenu"><?php echo anchor('login/dologout', 'Logout'); ?></li>
<?php
    }
?>
  </ul>
</div>
<!-- End of menu -->
