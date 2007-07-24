<body onload="javascript:document.loginForm.loginName.focus();">
  <div id="login_holder">
<?php
    $userlogin=getUserLogin();
    $notice = $userlogin->notice();
    if ($notice!="") {
      echo "    <table width='40em'><tr><td><div class='errormessage'>".$notice."</div></td></tr></table>\n";
    }
    $err = getErrorMessage();
    if ($err != "") {
        echo "<div class='errormessage' width='40em'>".$err."</div>";
        clearErrorMessage();
    }

    //the login form is NOT shown if 'external login module' is activated
    if ((getConfigurationSetting("USE_EXTERNAL_LOGIN") != 'TRUE') || (getConfigurationSetting("EXTERNAL_LOGIN_MODULE")!='Httpauth')) {
        $formAttributes = array('id' => 'loginForm');
        echo form_open_multipart('login/dologin/'.implode('/',$segments),$formAttributes);
    ?>
        <table cellspacing="3" cellpadding="3" width="40em">
          <tr>
            <td colspan='2'><div class='header'>Welcome to the Aigaion bibliography system, please login</div></td>
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
              echo '<p align="right">';
              echo form_submit('submitlogin', 'Login');
              echo '</p>';
            ?></td>
          </tr>
          <tr>
            <td colspan='2'>
              If you want a password, please mail to
              <?php echo getConfigurationSetting("CFG_ADMIN"); ?><br/>
              <?php echo getConfigurationSetting("CFG_ADMINMAIL"); ?>
            </td>
          </tr>
          <tr>
            <td colspan='2'>
              For more information about the Aigaion bibliography system visit
              <a href="http://aigaion.nl/" target="_blank"> Aigaion.nl</a>.
            </td>
          </tr>
        </table>
    <?php
        echo form_close();
    }
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
  <a href="http://aigaion.nl/" target="_blank"> Aigaion.nl</a>.
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