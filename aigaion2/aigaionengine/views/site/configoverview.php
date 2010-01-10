<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/site/configoverview

a page with links to all configuration settings

Parameters:
    $siteconfig     the site config object

we assume that this view is not loaded if you don't have the appropriate database_manage rights
*/
$this->load->helper('translation');

echo "<p class='header'>".__('Aigaion site configuration')."</p>";

echo __("Choose one of the links to change settings")."<br/>\n";
echo "<br/>\n";

//note, we can have some overview of current settings here as well!

//interface&appearance
echo anchor('site/configform/display',__('General display settings'))."<br/>\n";

//content, inpupt, and output
echo anchor('site/configform/inputoutput',__('Input and output settings'))."<br/>\n";
echo anchor('site/configform/attachments',__('Attachment settings'))."<br/>\n";
echo anchor('site/configform/customfields',__('Custom fields'))."<br/>\n";
//echo anchor('site/configform/authorsettings',__('authorsettings'))."<br/>\n";

//login, users, and access levels
echo anchor('site/configform/login',__('Login settings'))."<br/>\n";
echo anchor('site/configform/userdefaults',__('Default user preferences'))."<br/>\n";
echo anchor('site/configform/accesslevels',__('Default accesslevels'))."<br/>\n";

//integration&embedding
echo anchor('site/configform/siteintegration',__('Site integration settings'))."<br/>\n";

