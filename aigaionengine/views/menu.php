<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!-- Aigaion menu -->
<?php
  //view parameter: if $sortPrefix is set, the sort options will be shown in the menu as links to $sortPrefix.'title' etc
  //view parameter: if $exportCommand is set, the export block will include an export command for the browse list
  //view parameter: if $exportName is set, this determines the text for the exportCommand menu option
  
  $userlogin = getUserLogin();
?>
<div id="menu_holder">
  <ul class="mainmenu">
    <li class="mainmenu-header">BROWSE</li>
    <li class="mainmenu"><?php echo anchor('topics', 'My Topics'); ?></li>
    <?php
    if ($userlogin->hasRights('bookmarklist')) 
    {
      ?>
      <li class="mainmenu"><?php echo anchor('bookmarklist', 'My Bookmarks'); ?></li>
      <?php
    }
    ?>
    <li class="mainmenu"><?php echo anchor('topics/all', 'All Topics'); ?></li>
    <li class="mainmenu"><?php echo anchor('publications', 'All Publications'); ?></li>
    <li class="mainmenu"><?php echo anchor('authors', 'All Authors'); ?></li>

    <?php
    //the export option is slightly dependent on the view parameter 'exportCommand'
    //
    ?>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">EXPORT</li>
    <li class="mainmenu"><?php echo anchor('export', 'Export all publications'); ?></li>
    <?php
    if (isset($exportCommand)&&($exportCommand!=''))
    {
      ?>
      <li class="mainmenu"><?php echo anchor($exportCommand, $exportName); ?></li>
      <?php
    }
    ?>

    <?php
    //the sort options are only available if the view is called with a 'sortPrefix' option that is not ''
    //
    if (!isset($sortPrefix)||($sortPrefix==''))
    {
      $sortPrefix = 'publications/showlist/';
      ?>
      <li class="mainmenu-spacer"></li>
      <li class="mainmenu-header">BROWSE ALL BY</li>
      <?php
    } else {
      ?>
      <li class="mainmenu-spacer"></li>
      <li class="mainmenu-header">SORT BY</li>
      <?php
    }
    ?>
    <li class="mainmenu"><?php echo anchor($sortPrefix.'author', 'Author'); ?></li>
    <li class="mainmenu"><?php echo anchor($sortPrefix.'title',  'Title'); ?></li>
    <li class="mainmenu"><?php echo anchor($sortPrefix.'type',   'Type/journal'); ?></li>
    <li class="mainmenu"><?php echo anchor($sortPrefix.'year',   'Year'); ?></li>
    <li class="mainmenu"><?php echo anchor($sortPrefix.'recent', 'Recently added'); ?></li>
    <?php

    //you need the proper userrrights to create new items
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
    if ($userlogin->hasRights('publication_edit'))
    {
      ?>
      <li class='mainmenu'><?php echo anchor('import', 'Import'); ?></li>
      <?php
    }

?>

    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">SITE</li>
    <li class="mainmenu"><?php echo anchor('help/', 'Help'); ?></li>
    <li class="mainmenu"><?php echo anchor('help/viewhelp/about', 'About this site'); ?></li>
<?php
if ($userlogin->hasRights('database_manage')) {
?>
    <li class="mainmenu"><?php echo anchor('site/configure', 'Site Configuration'); ?></li>
    <li class="mainmenu"><?php echo anchor('site/maintenance', 'Site Maintenance'); ?></li>
<?php
}
if ($userlogin->hasRights('user_edit_all')) {
    echo "    <li class='mainmenu'>".anchor('users/manage', 'Manage All Accounts')."</li>\n";
}

?>

    <li class="mainmenu-spacer"></li>
<?php
  if ($userlogin->isAnonymous()) {
    $anonusers = $this->user_db->getAllAnonUsers();
?>	    
      <li class="mainmenu-spacer"></li>
      <li class="mainmenu-header">GUEST USER</li>
<?php
    if (count($anonusers)>1) {
      //more than one anonymous user: show a dropdown where you can choose between the different guest users
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
    } else {
      echo "<li class='mainmenu'>".$anonusers[0]->login."</li>";
    }

//probably no-one would ever assign these two rights to the anon user, but nevertheless....:
if ($userlogin->hasRights('user_edit_self')) {
    echo "    <li class='mainmenu'>".anchor('users/edit/'.$userlogin->userId(), 'My Profile')."</li>\n";
}
if ($userlogin->hasRights('topic_subscription')) {
    echo "    <li class='mainmenu'>".anchor('users/topicreview/', 'Topic Subscribe')."</li>\n";
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
<?php
if ($userlogin->hasRights('user_edit_self')) {
    echo "    <li class='mainmenu'>".anchor('users/edit/'.$userlogin->userId(), 'My Profile')."</li>\n";
}
if ($userlogin->hasRights('topic_subscription')) {
    echo "    <li class='mainmenu'>".anchor('users/topicreview/', 'Topic Subscribe')."</li>\n";
}
?>
    <li class="mainmenu"><?php echo anchor('login/dologout', 'Logout'); ?></li>
<?php
  }
?>
  </ul>
</div>
<!-- End of menu -->
