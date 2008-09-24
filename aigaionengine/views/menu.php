<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!-- Aigaion menu -->
<?php
  //view parameter: if $sortPrefix is set, the sort options will be shown in the menu as links to $sortPrefix.'title' etc
  //view parameter: if $exportCommand is set, the export block will include an export command for the browse list
  //view parameter: if $exportName is set, this determines the text for the exportCommand menu option
  
  $userlogin = getUserLogin();
  $this->lang->load('menu',$userlogin->getPreference('language'));
?>
<div id="menu_holder">
  <ul class="mainmenu">
    <li class="mainmenu-header"><?php echo $this->lang->line('menu_show_header'); ?></li>
    <ul class="mainmenu">
    <li class="mainmenu"><?php echo anchor('topics', $this->lang->line('menu_show_mytopics')); ?></li>
    <?php
    if ($userlogin->hasRights('bookmarklist')) 
    {
      ?>
      <li class="mainmenu"><?php echo anchor('bookmarklist', $this->lang->line('menu_show_bookmarklist')); ?></li>
      <?php
    }
    ?>
    <li class="mainmenu"><?php echo anchor('topics/all', $this->lang->line('menu_show_alltopics')); ?></li>
    <li class="mainmenu"><?php echo anchor('publications', $this->lang->line('menu_show_pubs')); ?></li>
    <li class="mainmenu"><?php echo anchor('authors', $this->lang->line('menu_show_authors')); ?></li>
    <li class="mainmenu"><?php echo anchor('publications/unassigned', $this->lang->line('menu_show_unassigned')); ?></li>
    <li class="mainmenu"><?php echo anchor('publications/showlist/recent', $this->lang->line('menu_show_recent')); ?></li>

    <?php
    //the export option is slightly dependent on the view parameter 'exportCommand'
    //
    ?>
</ul>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header"><?php echo $this->lang->line('menu_export_header'); ?></li>
    <ul class="mainmenu">
    <li class="mainmenu"><?php echo anchor('export', 'Export all publications'); ?></li>
    <?php
    if (isset($exportCommand)&&($exportCommand!=''))
    {
      ?>
      <li class="mainmenu"><?php echo anchor($exportCommand, $exportName); ?></li>
      <?php
    }
    ?>
    </ul>

    <?php
    //the sort options are only available if the view is called with a 'sortPrefix' option that is not ''
    //
    if (!isset($sortPrefix)||($sortPrefix==''))
    {
      //$sortPrefix = 'publications/showlist/';
      //<li class="mainmenu-spacer"></li>
      //<li class="mainmenu-header">BROWSE ALL BY</li>
    } else {
      ?>
      <li class="mainmenu-spacer"></li>
      <li class="mainmenu-header"><?php echo $this->lang->line('menu_sort_header'); ?></li>
      <ul class="mainmenu">
      <li class="mainmenu"><?php echo anchor($sortPrefix.'author', $this->lang->line('menu_sort_author')); ?></li>
      <li class="mainmenu"><?php echo anchor($sortPrefix.'title',  $this->lang->line('menu_sort_title')); ?></li>
      <li class="mainmenu"><?php echo anchor($sortPrefix.'type',   $this->lang->line('menu_sort_type')); ?></li>
      <li class="mainmenu"><?php echo anchor($sortPrefix.'year',   $this->lang->line('menu_sort_year')); ?></li>
      <li class="mainmenu"><?php echo anchor($sortPrefix.'recent', $this->lang->line('menu_sort_recent')); ?></li>
      </ul>
      <?php
    }
    ?>
    <?php

    //you need the proper userrrights to create new items
    if ($userlogin->hasRights('publication_edit'))
    {
      ?>  
      <li class="mainmenu-spacer"></li>
      <ul class="mainmenu">
      <li class="mainmenu-header"><?php echo $this->lang->line('menu_create_header'); ?></li>
      <li class='mainmenu'><?php echo anchor('publications/add', $this->lang->line('menu_create_pub')); ?></li>
      <li class='mainmenu'><?php echo anchor('authors/add', $this->lang->line('menu_create_author')); ?></li>
      <?php
        if ($userlogin->hasRights('topic_edit'))
        {
          ?>
          <li class='mainmenu'><?php echo anchor('topics/add', $this->lang->line('menu_create_topic')); ?></li>
          <?php
        } 
      ?>
      <li class='mainmenu'><?php echo anchor('import', $this->lang->line('menu_create_import')); ?></li>
      </ul>
      <?php
    }

?>

    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header"><?php echo $this->lang->line('menu_system_header'); ?></li>
    <ul class="mainmenu">
    <li class="mainmenu"><?php echo anchor('help/', $this->lang->line('menu_system_help')); ?></li>
    <li class="mainmenu"><?php echo anchor('help/viewhelp/about', $this->lang->line('menu_system_about')); ?></li>
<?php
if ($userlogin->hasRights('database_manage')) {
?>
    <li class="mainmenu"><?php echo anchor('site/configure', $this->lang->line('menu_system_config')); ?></li>
    <li class="mainmenu"><?php echo anchor('site/maintenance', $this->lang->line('menu_system_maintenance')); ?></li>
<?php
}
if ($userlogin->hasRights('user_edit_all')) {
    echo "    <li class='mainmenu'>".anchor('users/manage', $this->lang->line('menu_system_usermanage'))."</li>\n";
}
?>
    </ul>

    <li class="mainmenu-spacer"></li>
<?php
  if ($userlogin->isAnonymous()) {
    $anonusers = $this->user_db->getAllAnonUsers();
?>	    
      <li class="mainmenu-spacer"></li>
      <li class="mainmenu-header"><?php echo $this->lang->line('menu_guest_header'); ?></li>
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
    echo "    <li class='mainmenu'>".anchor('users/edit/'.$userlogin->userId(), $this->lang->line('menu_logged_profile'))."</li>\n";
}
if ($userlogin->hasRights('topic_subscription')) {
    echo "    <li class='mainmenu'>".anchor('users/topicreview/', $this->lang->line('menu_logged_subscribe'))."</li>\n";
}
        
?>	    
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header"><?php echo $this->lang->line('menu_login_header'); ?></li>
<?php
    $this->load->helper('form');
    echo '<li>';
    echo form_open('login/dologin'.$this->uri->uri_string());
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
    <li class="mainmenu-header"><?php echo $this->lang->line('menu_logged_header'); ?></li>
    <li class="mainmenu"><?php echo $userlogin->loginName(); ?></li>
<?php
if ($userlogin->hasRights('user_edit_self')) {
    echo "    <li class='mainmenu'>".anchor('users/edit/'.$userlogin->userId(), $this->lang->line('menu_logged_profile'))."</li>\n";
}
if ($userlogin->hasRights('topic_subscription')) {
    echo "    <li class='mainmenu'>".anchor('users/topicreview/', $this->lang->line('menu_logged_subscribe'))."</li>\n";
}
?>
    <li class="mainmenu"><?php echo anchor('login/dologout', $this->lang->line('menu_logged_logout')); ?></li>
<?php
  }
?>
  </ul>
<br/><br/>
<div style='float:bottom;font-size:90%;'>
<?php 
$this->load->helper('language');
foreach (getLanguages() as $lang=>$display) {
    echo anchor('userlanguage/set/'.$lang.'/'.implode('/',$this->uri->segment_array()),$display).'<br/>';
}
?>
</div>
</div>

<!-- End of menu -->
