<html><body>
<?php 
/** This file shows the appropriate calls for "dependent Aigaion logins".
 *  
 *  Take the following situation:
 *  You have a CMS system to which your users log in. When they are logged in 
 *  to the CMS, you want them also to be automatically logged in in the 
 *  Aigaion database,
 *  with the same username. When they log out of the CMS, you potentially want 
 *  them also to be logged out of Aigaion again. This file shows how it works. 
 *  
 *  The basic idea is as follows. Something like this file is supposed to be part of your
 *  CMS, e.g. as a login / logout hook. 
 *  - first, we will get a login token directly from the Aigaion server.
 *    This token needs to be passed from the client to the Aigaion server to 
 *    show that the client has the right to be logged in. This token must be 
 *    used within 15 seconds or it will expire. Because this CMS may have to 
 *    login several users at the same time from different clients, we will
 *    sent an serial INT, too, to distinguish these calls from each other. It is
 *    the responsibility of the CMS system to make sure the same serial INT is 
 *    not used twice (store it in database, use a large enough random number, 
 *    ...). To get a token, you need to pass the following info to Aigaion:
 *      - sitename of this CMS
 *      - serial INTeger
 *      - and whether the login session may also be forced to logout from this 
 *        CMS at a later time (if not, Aigaion will not check every page access
 *        later on, which makes things a bit more efficient)     
 *  - Then, we will construct an iframe to be included client-side in the HTML
 *    of the CMS. This iframe will make a call to the Aigaion server that logs
 *    the user in. This iframe will have to include some information for that:
 *      - the sitename for which the login token was originally
 *        aquired, 
 *      - the serial INT, and the user name of the user who is supposed 
 *        to be logged in from the client computer. 
 *      - and a hashcode consiting among other things of the token and the 
 *        shared secret phrase  
 *    This information is encrypted in a certain way.
 *  - Then we will show the iframe in the CMS html on the clients computer. The
 *    effect is that the user will be logged in in Aigaion, too.
 *  - then, if we want to force a logout of that particular user from Aigaion, 
 *    for example because he logged out from this CMS, we will call the Aigaion 
 *    server to tell it that the user who used this token to login should be 
 *    logged out from Aigaion at the first subsequent attempt to access an 
 *    Aigaion page. To achieve this, Aigaion needs the following info 
 *    (encrypted, of course):
 *      - the sitename
 *      - the serial Integer with which the token was associated
 *      - and a hashcode consiting among other things of the token and the 
 *        shared secret phrase (not the same hash code as was used for login!)
 *     
 *  To actually make this example file work, take the following steps.
 *  
 *  - in your Aigaion configuration screen, set the shared secret phrase for 
 *    the Logintegration options
 *  - in this file, set the same shared secret phrase
 *  - in this file, set the $aigaionRoot to the site where we want to log 
 *    the user in 
 *  - note that we assume that this file was called because the user just 
 *    logged in to the CMS, and we know the user name is stored in $uname.
 *    For testing, set uname with a valid username. 
 *  - Set some $sitename (not important what it is, as long as its not empty)
 */           

/** SET ALL VARIABLES */

#root of the aigaion site that we want to log in
$aigaionRoot = "http://localhost/aigaion2/index.php";
#fill in secret phrase here...
#this phrase must also be set in the Aigaion config 
$secretphrase = 
#the account name of the user that Aigaion should consider to be validly logged in
$uname = 
#sitename will be used to attach token to the right login call
$sitename = "testlogintegration";
#serial is used to separate different login calls originating from the same site occurring closely together
$serial = 1;

/** GET TOKEN */
require_once(dirname(__FILE__) . "/iframebased/gettoken.php");
//if you call this with False as last parameter, the login session in Aigaion that will be started cannot be logged out using the trick below. Saves ona bit of checking.
$token = getToken($aigaionRoot,$sitename,$serial,True);

/** GENERATE THE IFRAME THAT SHOULD BE INCLUDED IN THE HTML OUTPUT TO FORCE THE AIGAION LOGIN, USING THE TOKEN */
$loginhashcode =  md5(md5($uname).md5($token).md5($secretphrase)); 
echo "The following iframe takes care of loggin in as the specified user. Normally, you'd probably hide the iframe altogether.<br><iframe src='".$aigaionRoot."/logintegration/login/".$sitename."/".$loginhashcode."/".$serial."/".$uname."'></iframe><br>";

/** ... AND IMMEDIATELY FORCE A LOGOUT OF THIS SAME USER. */
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