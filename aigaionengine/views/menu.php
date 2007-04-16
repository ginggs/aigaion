<!-- Aigaion menu -->
<div id="menu_holder">
  <ul class="mainmenu">
    <li class="mainmenu"><?php echo anchor('topics', 'Home'); ?></li>
    <li class="mainmenu"><?php echo anchor('publications', 'Publicationlist'); ?></li>
    <li class="mainmenu"><?php echo anchor('authors', 'Authors'); ?></li>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu"><?php echo anchor('users/edit/'.getUserLogin()->userId(), 'My Profile'); ?></li>
    <li class="mainmenu"><?php echo anchor('users/topicreview/', 'Topic Review'); ?></li>
    <li class="mainmenu"><?php echo anchor('users/manage', 'Manage Accounts'); ?></li>
    <li class="mainmenu"><?php echo anchor('site/configure', 'Site Configuration'); ?></li>
    <li class="mainmenu"><?php echo anchor('site/maintenance', 'Site Maintenance'); ?></li>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu"><?php echo anchor('login/dologout', 'Logout'); ?></li>
  </ul>

</div>
<!-- End of menu -->
