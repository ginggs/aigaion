<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Filters configuration
| -------------------------------------------------------------------
|
| Note: The filters will be applied in the order that they are defined
|
| Example configuration:
|
| $filter['auth'] = array('exclude', array('login/*', 'about/*'));
| $filter['cache'] = array('include', array('login/index', 'about/*', 'register/form,rules,privacy'));
|
*/

/** By default, when no user is logged in, control is passed to the login form.
    Add controllers for which this should not happen to the exclude array. */
$filter['login'][] = array(
	'exclude', array('login/*','version/*'), array('action'=>'redirect')
);
/** For some controllers, failure of the login check should simply result in the
    display of a div with an error message defined in the login/fail view.
    Add controllers for which this should happen to the include array below. */
$filter['login'][] = array(
	'include', array(), array('action'=>'fail')
);
?>