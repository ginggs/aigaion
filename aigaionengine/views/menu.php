<!-- Aigaion menu -->
<div id="menu_holder">
  <ul class="mainmenu">
    <li class="mainmenu"><?php echo anchor('topics', 'Home'); ?></li>
    <li class="mainmenu"><?php echo anchor('publications', 'Publicationlist'); ?></li>
    <li class="mainmenu"><?php echo anchor('authors', 'Authors'); ?></li>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu"><?php echo anchor('users/edit/'.getUserLogin()->userId(), 'My Profile'); ?></li>
    <li class="mainmenu"><?php echo anchor('users/manage', 'Manage Accounts'); ?></li>
    <li class="mainmenu"><?php echo anchor('configuration', 'Site Configuration'); ?></li>
  </ul>

</div>
<!-- End of menu -->
