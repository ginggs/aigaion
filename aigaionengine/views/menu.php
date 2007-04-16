<!-- Aigaion menu -->
<div id="menu_holder">
  <ul class="mainmenu">
    <li class="mainmenu-header">BROWSE</li>
    <li class="mainmenu"><?php echo anchor('topics', 'Home'); ?></li>
    <li class="mainmenu"><?php echo anchor('publications', 'Publicationlist'); ?></li>
    <li class="mainmenu"><?php echo anchor('authors', 'Authors'); ?></li>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">ACCOUNT</li>
    <li class="mainmenu"><?php echo anchor('users/edit/'.getUserLogin()->userId(), 'My Profile'); ?></li>
    <li class="mainmenu"><?php echo anchor('users/topicreview/', 'Topic Review'); ?></li>
    <li class="mainmenu"><?php echo anchor('users/manage', 'Manage All Accounts'); ?></li>
    <li class="mainmenu"><?php echo anchor('login/dologout', 'Logout'); ?></li>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">HELP</li>
    <li class="mainmenu"><?php echo anchor('help/', 'Help'); ?></li>
    <li class="mainmenu"><?php echo anchor('help/view/about', 'About this site'); ?></li>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">SITE</li>
    <li class="mainmenu"><?php echo anchor('site/configure', 'Site Configuration'); ?></li>
    <li class="mainmenu"><?php echo anchor('site/maintenance', 'Site Maintenance'); ?></li>
  </ul>

</div>
<!-- End of menu -->
