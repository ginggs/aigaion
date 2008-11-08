<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<body onload="$('loginName').focus();">
  <div id="login_holder">
<?php
    $userlogin=getUserLogin();
    $notice = $userlogin->notice();
    if ($notice!="") {
      echo "    
      <table width='100%'>
        <tr>
            <td><div class='errormessage'>".$notice."</div></td>
        </tr>
      </table>\n";
    }
    $err = getErrorMessage();
    if ($err != "") {
        echo "<div class='errormessage' width='100%'>".$err."</div>";
        clearErrorMessage();
    }
    $formtitle = "Welcome to the Aigaion bibliography system, please login";
    if ($this->latesession->get('FORMREPOST')==True) {
        echo "<div class='errormessage' width='100%'>You just submitted a form
               named '".$this->latesession->get('FORMREPOST_formname')."', but it seems that you have 
               been logged out. To proceed with submitting the information, please 
               log in again, then confirm that you want to re-submit the data. </div>";
        $formtitle = "Login to proceed with form submission";
    }

    //the login form is NOT shown if 'external login module' is activated
    //however, external login is killed, for now!
    //if (getConfigurationSetting("USE_EXTERNAL_LOGIN") != 'TRUE') {
        $formAttributes = array('id' => 'loginForm');
        echo form_open_multipart('login/dologin/'.implode('/',$segments),$formAttributes);
    ?>
        <table cellspacing="3" cellpadding="3" width="100%">
          <tr>
            <td colspan='2'><div class='header'><?php echo $formtitle; ?></div></td>
          </tr>
          <tr>
            <td>Name:</td>
            <td><?php
              $data = array(
              'name'        => 'loginName',
              'id'          => 'loginName',
              'maxlength'   => '100',
              'size'        => '50'
              );
              echo form_input($data);
            ?></td>
          </tr>
          <tr>
            <td>Password:</td>
            <td><?php
              $data = array(
              'name'        => 'loginPass',
              'id'          => 'loginPass',
              'maxlength'   => '100',
              'size'        => '50'
              );
              echo form_password($data);
            ?></td>
          </tr>
          <tr>
            <td></td>
            <td><?php
              $data = array(
              'name'        => 'remember',
              'id'          => 'remember',
              'title'       => 'Remember me',
              'checked'     => FALSE
              );
              echo form_checkbox($data);
              echo '&nbsp;Remember me.';
              echo '<p class="alignright">';
              echo form_submit('submitlogin', 'Login');
              echo '</p>';
            ?></td>
          </tr>
          <tr>
            <td colspan='2'>
              If you want a password, please mail to <a href='mailto: "<?php echo getConfigurationSetting("CFG_ADMIN"); ?>" <?php echo '<'.getConfigurationSetting("CFG_ADMINMAIL").'>'; ?>?subject=Registration request for <?php echo getConfigurationSetting("WINDOW_TITLE")?> Aigaion database'><?php echo getConfigurationSetting("CFG_ADMIN"); ?></a>
              <br/>
              
            </td>
          </tr>
          <tr>
            <td colspan='2'>
              For more information about the Aigaion bibliography system visit <a href="http://www.aigaion.nl/" class="external">Aigaion.nl</a>.
            </td>
          </tr>
        </table>
    <?php
        echo form_close();
    //} else {
//        echo "<div class='message' width='40em'>This Aigaion 2.0 database uses external login modules. 
//              Login is not possible through the login form.
//              [".anchor('/','CLICK HERE TO TRY AGAIN')."]<br/>
//              <br/>
//              (If you want to turn of the external login module, use your MySQL database program to edit the 
//              '".AIGAION_DB_PREFIX."config' table. Find the setting 'USE_EXTERNAL_LOGIN', set it to 'FALSE',
//              close your browser windows, restart your browser and try to login through the normal login form.)
//              </div>";
    //}
    ?>
  </div>
  
</body>
  <?php
  /*
  <?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
  ?>
  <html>
  <head>
  <title> <?php echo getConfigurationSetting("WINDOW_TITLE"); ?> | Please login</title>
  <link REL="StyleSheet" HREF="<?php echo getCssUrl("boxes.css"); ?>" TYPE="text/css"/>
  <link REL="StyleSheet" HREF="<?php echo getCssUrl("general.css"); ?>" TYPE="text/css"/>
  </head>
  <body onload="javascript:document.loginForm.loginName.focus();">
  <center>
  <?php
  $userlogin=getUserLogin();
  $notice = $userlogin->notice();
  if ($notice!="") {
  ?><table><tr><td><div class="errormessage"><?php echo $notice; ?></div></td></tr></table><?php
  }
  ?>

  <?php
  $this->load->helper('form');
  $formAttributes = array('id' => 'loginForm');
  echo form_open_multipart('login/dologin/'.implode('/',$segments),$formAttributes);
  ?>
  <table bgcolor="F7F7F7"
  cellspacing="3"
  cellpadding="3"
  style="border:1px solid black"
  width="400"
  style='width:395px;'>

  <tr>
  <td>Name:</td>
  <td><?php
  $data = array(
  'name'        => 'loginName',
  'id'          => 'loginName',
  'maxlength'   => '100',
  'size'        => '50'
  );
  echo form_input($data);
  ?></td>
  </tr>

  <tr>
  <td>Password:</td>
  <td><?php
  $data = array(
  'name'        => 'loginPass',
  'id'          => 'loginPass',
  'maxlength'   => '100',
  'size'        => '50'
  );
  echo form_password($data);
  ?></td>
  </tr>

  <tr>
  <td></td>
  <td><?php
  $data = array(
  'name'        => 'remember',
  'id'          => 'remember',
  'title'       => 'Remember me',
  'checked'     => FALSE
  );
  echo form_checkbox($data);
  echo '&nbsp;Remember me.';
  echo '<p align=right>';
  echo form_submit('submitlogin', 'Login');
  echo '</p>';
  ?></td>
  <td>
  </tr>

  <tr>
  <td colspan=2>
  If you want a password, please mail to
  <?php echo getConfigurationSetting("CFG_ADMIN"); ?><br/>
  <?php echo getConfigurationSetting("CFG_ADMINMAIL"); ?>
  </td>
  </tr>

  <tr>
  <td colspan=2>
  For more information about the Aigaion bibliography system visit
  <a href="http://www.aigaion.nl/" class='open_extern'> www.aigaion.nl</a>.
  </td>
  </tr>
  </TABLE>
  <?php
  echo form_close();
  ?>
  </center>
  </body>
  </html>
  <?php
  ?>
  */
  ?>