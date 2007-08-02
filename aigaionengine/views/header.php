<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $title; ?></title>
    <link href="<?php echo getCssUrl("positioning.css"); ?>" rel="stylesheet" type="text/css" media="screen,projection,tv" />
    <link href="<?php echo getCssUrl("styling.css"); ?>"     rel="stylesheet" type="text/css" media="screen,projection,tv" />
    <link href="<?php echo getCssUrl("topics.css"); ?>"     rel="stylesheet" type="text/css" media="screen,projection,tv" />
    <link href="<?php echo getCssUrl("accesslevels.css"); ?>"     rel="stylesheet" type="text/css" media="screen,projection,tv" />
    <link href="<?php echo getCssUrl("help.css"); ?>"     rel="stylesheet" type="text/css" media="screen,projection,tv" />
<?php
    if (!isset($javascripts))
      $javascripts = array();
    elseif (!is_array($javascripts))
      $javascripts = array($javascripts);
    foreach ($javascripts as $jsName):
?>
    <script src="<?php echo APPURL."javascript/".$jsName; ?>" type="text/javascript"></script>
<?php
    endforeach;
?>
    <script type="text/javascript">
      //<![CDATA[
      base_url = '<?= base_url();?>index.php/';
      //]]>
    </script>

  </head>
  <body>
    <div id="main_holder">
      <!-- Aigaion header: Logo, simple search form -->
      <div id="header_holder">
        <div id='quicksearch'>
          <?php
          echo form_open('search/quicksearch')."\n";
          echo form_input(array('name' => 'searchstring', 'size' => '25'));
          echo form_submit('submit_search', 'search');
          echo form_close();
          ?>
        </div>  
        <?php
        if (getConfigurationSetting('USE_UPLOADED_LOGO')=='TRUE') {
            //echo '<img border=0 style="height:100%;" src="'.AIGAION_ATTACHMENT_URL.'/custom_logo.jpg">';
        }
        ?>
        &nbsp;<?php
            echo anchor('','Aigaion 2.0','id="page_title"');
        ?>
        
      </div>
      <!-- End of header -->

      <?php
        //load menu
        $this->load->view('menu');
      ?>

      <!-- Aigaion main content -->
      <div id="content_holder">
      
      
      <!-- I think that here we want to have the (error) messages: -->
      <?php
            $err = getErrorMessage();
            $msg = getMessage();
            if ($err != "") {
                echo "<div class='errormessage'>".$err."</div>";
                clearErrorMessage();
            }
            if ($msg != "") {
                echo "<div class='message'>".$msg."</div>";
                clearMessage();
            }      
        ?>
        <!---->
