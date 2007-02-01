<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title><?php $title; ?></title>
    <link href="<?php echo base_url(); ?>themes/default/css/boxes.css"    rel="stylesheet" type="text/css" media="screen,projection,tv" />
    <link href="<?php echo base_url(); ?>themes/default/css/menu.css"     rel="stylesheet" type="text/css" media="screen,projection,tv" />
    <link href="<?php echo base_url(); ?>themes/default/css/general.css"  rel="stylesheet" type="text/css" media="screen,projection,tv" />
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
      </div>
      <!-- End of header -->

      <?php
        //load menu
        $this->load->view('menu');
      ?>

      <!-- Aigaion main content -->
      <div id="content_holder">
