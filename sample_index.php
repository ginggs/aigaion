<?php

/*==== OPTIONAL SETTINGS */
# URL where to store attachments. Default: root_url/attachments
//define('AIGAION_ATTACHMENT_URL', 'http://url/for/attachments/'); 
# Directory where to store attachments. Default: this directory/attachments
//define('AIGAION_ATTACHMENT_DIR', '/Path/for/attachments'); 
# Table prefix for database. Default ''
//define('AIGAION_DB_PREFIX', '');

/*==== MANDATORY SETTINGS */
#Root URL of this instance Aigaion, WITH trailing slash
define('AIGAION_ROOT_URL','http://localhost/aigaion2root/');
#Unique ID of this site, to keep it separate from other installations that use same engine 
define('AIGAION_SITEID', 'AigaionInstance1');
# Host where database runs
define('AIGAION_DB_HOST', 'localhost');
# Database user
define('AIGAION_DB_USER', 'username');
# Database password
define('AIGAION_DB_PWD', 'userpass');
# Name of the standard database
define('AIGAION_DB_NAME', 'aigaion');

#If your instance of the system is NOT located in the application directory,
#you must specify the URL to the application here.
#NOTE: EXPLAIN THIS MORE CAREFULLY< WHY AND WHEN THIS WOULD HAPPEN!
define('APPURL','http://localhost/aigaion2root/aigaionengine/');

/*
|---------------------------------------------------------------
| PHP ERROR REPORTING LEVEL
|---------------------------------------------------------------
|
| By default CI runs with error reporting set to ALL.  For security
| reasons you are encouraged to change this when your site goes live.
| For more info visit:  http://www.php.net/error_reporting
|
*/
	error_reporting(E_ALL);

/*
|---------------------------------------------------------------
| SYSTEM FOLDER NAME
|---------------------------------------------------------------
|
| This variable must contain the name of your "system" folder.
| Include the path if the folder is not in the same  directory
| as this file.
|
| NO TRAILING SLASH!
|
*/
	$system_folder = "codeigniter";

/*
|---------------------------------------------------------------
| APPLICATION FOLDER NAME
|---------------------------------------------------------------
|
| If you want this front controller to use a different "application"
| folder then the default one you can set its name here. The folder 
| can also be renamed or relocated anywhere on your server.
| For more info please see the user guide:
| http://www.codeigniter.com/user_guide/general/managing_apps.html
|
|
| NO TRAILING SLASH!
|
*/
	$application_folder = "aigaionengine";


/*
|===============================================================
| END OF USER CONFIGURABLE SETTINGS
|===============================================================
*/


/*
|---------------------------------------------------------------
| SET THE SERVER PATH
|---------------------------------------------------------------
|
| Let's attempt to determine the full-server path to the "system"
| folder in order to reduce the possibility of path problems.
|
*/
if (function_exists('realpath') AND @realpath(dirname(__FILE__)) !== FALSE)
{
	$system_folder = str_replace("\\", "/", realpath(dirname(__FILE__))).'/'.$system_folder;
}

/*
|---------------------------------------------------------------
| DEFINE APPLICATION CONSTANTS
|---------------------------------------------------------------
|
| EXT		- The file extension.  Typically ".php"
| FCPATH	- The full server path to THIS file
| SELF		- The name of THIS file (typically "index.php)
| BASEPATH	- The full server path to the "system" folder
| APPPATH	- The full server path to the "application" folder
|
*/
define('EXT', '.'.pathinfo(__FILE__, PATHINFO_EXTENSION));
define('FCPATH', __FILE__);
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH', $system_folder.'/');

if (is_dir($application_folder))
{
	define('APPPATH', $application_folder.'/');
}
else
{
	if ($application_folder == '')
	{
		$application_folder = 'application';
	}

	define('APPPATH', BASEPATH.$application_folder.'/');
}

/*
|---------------------------------------------------------------
| DEFINE E_STRICT
|---------------------------------------------------------------
|
| Some older versions of PHP don't support the E_STRICT constant
| so we need to explicitly define it otherwise the Exception class 
| will generate errors.
|
*/
if ( ! defined('E_STRICT'))
{
	define('E_STRICT', 2048);
}

/*
|---------------------------------------------------------------
| LOAD THE FRONT CONTROLLER
|---------------------------------------------------------------
|
| And away we go...
|
*/
require_once BASEPATH.'codeigniter/CodeIgniter'.EXT;
?>