<!-- Aigaion menu -->
<div id="menu_holder">
  <ul class="mainmenu">
    <li class="mainmenu-header">BROWSE</li>
    <li class="mainmenu"><?php echo anchor('topics', 'Topics'); ?></li>
    <li class="mainmenu"><?php echo anchor('publications', 'Publicationlist'); ?></li>
    <li class="mainmenu"><?php echo anchor('authors', 'Authors'); ?></li>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">ACCOUNT</li>
    <li class="mainmenu"><?php echo anchor('users/edit/'.getUserLogin()->userId(), 'My Profile'); ?></li>
    <li class="mainmenu"><?php echo anchor('users/topicreview/', 'Topic Review'); ?></li>
    <li class="mainmenu"><?php echo anchor('users/manage', 'Manage All Accounts'); ?></li>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">HELP</li>
    <li class="mainmenu"><?php echo anchor('help/', 'Help'); ?></li>
    <li class="mainmenu"><?php echo anchor('help/view/about', 'About this site'); ?></li>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header">SITE</li>
    <li class="mainmenu"><?php echo anchor('site/configure', 'Site Configuration'); ?></li>
    <li class="mainmenu"><?php echo anchor('site/maintenance', 'Site Maintenance'); ?></li>
    <li class="mainmenu-spacer"></li>
<?php
	if (getUserLogin()->isAnonymous()) {
	    
	    $this->load->helper('form');
        
        //here one could add a dropdown menu for switching to other anonymous users

	    echo form_open('login/dologin/'.$this->uri->uri_string());
?>
            <li><table class='loginbox'>

                <TR>
                <TD>Name:</TD>
                </TR>
                <TR>
                <TD><input type=text name=loginName size=10></TD>
                </TR>

                <TR>
                <TD>Password:</TD>
                </TR>
                <TR>
                <TD><input type=password name=loginPass size=10></TD>
                </TR>

                <TR>
                <TD><input title='Remember me' name=remember type=checkbox><p align=right><input type=submit value='Login'></TD>
                </TR>
            </TABLE></li>
<?php
        echo form_close();
	} else {
?>
    	<li class="mainmenu-header">LOGGED IN:</li>
    	<li class="mainmenu"><?php echo getUserLogin()->loginName(); ?></li>
        <li class="mainmenu"><?php echo anchor('login/dologout', 'Logout'); ?></li>
<?php
    }
?>
      </ul>

</div>
<!-- End of menu -->
