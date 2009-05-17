<html><body>
<?php 
/** See documentation in iframebased.php
 */           

/** SET ALL VARIABLES */

#this token can be found in your database table `logintegration` 
$token=

#root of the aigaion site that we want to log in
$aigaionRoot = "http://localhost/aigaion2/index.php";
#fill in secret phrase here...
#this phrase must also be set in the Aigaion config 
$secretphrase = 
#sitename will be used to attach token to the right login call
$sitename = "testlogintegration";
#serial is used to separate different login calls originating from the same site occurring closely together
$serial = 1;

/** ... FORCE A LOGOUT OF THE USER WITH THE GIVEN TOKEN. */
/** This last point you'd probably do in the logout hook instead of immediately after successfully forcing the login. */


#Note: we need to store the $serial, $sitename and $token in the session of 
#this CMS because otherwise we couldn't logout the user like this.
$logouthashcode = md5(md5($sitename).md5($serial).md5($token).md5($secretphrase));

#force the logout and echo the results on screen
require_once(dirname(__FILE__) . "/iframebased/tokenlogout.php");
$logoutresult = tokenLogout($aigaionRoot,$sitename,$serial,$logouthashcode);
echo "<br>".$logoutresult;
?>
</body></html>