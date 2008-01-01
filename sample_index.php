<?php

/*==== OPTIONAL SETTINGS */
# URL where to store attachments. Default: root_url/attachments
# Only uncomment and fill this line if your directory for storing attachments on server 
# is different from the default
//define('AIGAION_ATTACHMENT_URL', 'http://url/for/attachments/'); 
# Directory where to store attachments. Default: this directory/attachments
# Only uncomment and fill this line if your directory for storing attachments on server 
# is different from the default
//define('AIGAION_ATTACHMENT_DIR', '/Path/for/attachments'); 
# Table prefix for database. 
# By default, no table prefix is defined. If your tables have been defined 
# with a table prefix, uncomment the following line and fill in the prefix:
//define('AIGAION_DB_PREFIX', '');

/*==== MANDATORY SETTINGS */
#Root URL of this instance Aigaion, WITH trailing slash
define('AIGAION_ROOT_URL','http://localhost/aigaion2root/');
#Unique ID of this site, to keep it separate from other installations that use same engine 
#NOTE: use only alphanumeric characters, no spaces, and at least one letter. Otherwise Aigaion won't work at all.
define('AIGAION_SITEID', 'AigaionInstance1');
# Host where database runs
define('AIGAION_DB_HOST', 'localhost');
# Database user
define('AIGAION_DB_USER', 'username');
# Database password
define('AIGAION_DB_PWD', 'userpass');
# Name of the standard database
define('AIGAION_DB_NAME', 'aigaion');

#We need to know where your aigaion - engine is located. WITH trailing slash.
#By default this is http://localhost/aigaion2root/aigaionengine/
define('APPURL','http://localhost/aigaion2root/aigaionengine/');

/*
|---------------------------------------------------------------
| PHP ERROR REPORTING LEVEL
|---------------------------------------------------------------
|
| By default Aigaion runs with error reporting set to ALL.  For security
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
| This variable must contain the name of your code igniter "system" folder.
| Include the path if the folder is not in the same  directory
| as this file.
| This is normally only changed when you are sharing the same Aigaion 2 code base
| between several instances of Aigaion 2
|
| NO TRAILING SLASH!
|
*/
	$system_folder = "./codeigniter";

/*
|---------------------------------------------------------------
| APPLICATION FOLDER NAME
|---------------------------------------------------------------
|
| Points to the folder of the aigaion engine. If not relative from the directory
| in which this file is located, use a path.
| This is normally only changed when you are sharing the same Aigaion 2 code base
| between several instances of Aigaion 2
|
| If you want to use a relative path, always include ./ or ../
| E.g. like this: ./aigaionengine
|
| This is normally only changed when you are sharing the same Aigaion 2 code base
| between several instances of Aigaion 2
|
| NO TRAILING SLASH!
|
*/
	$application_folder = "./aigaionengine";


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