<?php
/**
This file defines the class Login that is used to regulate the login to the site.
The UserLogin object is used to start, stop and query a user login.
The UserLogin object retrieves relevant information about the current login session (rights-info; 
whether the current login is anonymous; the preferences of the current user; etc) and provides 
methods to log in or out (user or anonymous account) given the right info.

Note: Creating, changing and deleting ACCOUNTS (as opposed to a 'current login session') is NOT done in this class! 

The UserLogin class uses some external information from the site config settings: 
    -whether anonymous login is allowed, and
    -the id of anonymous user
    
A note on the anonymous access:
    - To use the anonymous access facilities, you must enable it in the Site configuration page, and choose
      a user account that will be used to login the anonymous user.
    - If anonymous access is enabled, and you are not logged in as a 'normal' user, you will automatically
      be logged in as the anonymous user with all rights assigned to that anonymous user. A button will
      appear in the menu that allows you to login as a 'normal' user through the login screen.
    - If anonymous access is enabled, and you login with the anonymous user account _through the login screen_, 
      it is still considered to be an anonymous login (!)

The UserLogin class assumes that the connection to the database has already been made 

Access is through the getUserLogin() function in the login helper
*/


class UserLogin {
    
    //note : all 'var' should actually be 'private', but PHP 4 doesn't support that :(
    
    /* ================ Class variables ================ */
    
    /** True if the user is currently logged in, anonymous or non-anonymous */
    var $bIsLoggedIn = False; 
    /** True if the user is currently logged in as anonymous user */
    var $bIsAnonymous = False;
    /** The user name of the user currently logged in */
    var $sLoginName = "";
    /** The user ID of the user currently logged in */
    var $iUserId = "";
    /** feedback on any errors that occurred */
    var $sNotice = "";
    /** A list of some preference settings. */
    var $preferences = array();
    /** A list of the assigned rights for this user. */
    var $rights = array();
    /** The configured menu for this user. */
    var $theMenu = "";
    /* ================ Basic accessors ================ */
    
    function isLoggedIn() {
        return $this->bIsLoggedIn;
    }
    function isAnonymous() {
        return $this->bIsAnonymous;
    }
    function loginName() {
        return $this->sLoginName;
    }
    function userId() {
        return $this->iUserId;
    }    
    function notice() {
        $result = $this->sNotice;
        $this->sNotice = "";
        return $result;
    }
    function getMenu() {
        return $this->theMenu;
    }
    
    /* ================ Constructor ================ */
    
    /** Initially, the user is NOT logged in. */
    function UserLogin() {
        //...no construction stuff needed, really. everything happens when the user logs in.
    }
    
    /* ================ Access methods for user rights and preferences ================ */
    
    /** Initializes the cached rights from the database. Always called just after login. */
    function initRights() {
        $this->rights = array();
        $Q = mysql_query("SELECT * FROM userrights WHERE user_id={$this->iUserId}");
        while ($R=mysql_fetch_array($Q)) {
            $this->rights[] = $R["right_name"];
        }
    }
    
    /** die() if the currently logged in user does not have the given right. Uses hasRights($right) */
    function checkRights($right) 
    {
        if ($this->hasRights($right)) {
            return true;
        } else {
            echo "<div class='errormessage'>You do not have sufficient rights for the requested 
            operation or page. <br>Sorry for the inconvenience.</div>";
            die();
            return false;
        }
    }

    /** die() due to rights problems. This function is provided to fail rights in a uniform wawy,
    even in cases where the checking of the right was done without resorting to checkRights
    (for example because the condition is more than just one simple boolean check). */
    function failRights() {
        echo "<div class='errormessage'>You do not have sufficient rights for the requested 
        operation or page. <br>Sorry for the inconvenience.</div>";
        die();
        return false;
    }

    /** Return True iff the current user has certain (named) rights, false otherwise 
     *  (or if no user is logged in). */
    function hasRights($right) {
        //no logged user: no right.
        if (!$this->bIsLoggedIn) return False;
        if ($right=="") return true;
        if (in_array($right,$this->rights)) {
            return true;
        } else {
            return false;
        }
    }
   
    /** Initialize the preferences. Note: this method should also be called if the preferences
    have changed. */
    function initPreferences() {
        //right now, I just enumerate all relevant preferences from the user-table
        $nonprefs=array("password");
        $this->preferences = array();
        $Q = mysql_query("SELECT * FROM users WHERE user_id={$this->iUserId}");
        if ($Q) {
            if ($R = mysql_fetch_array($Q)) {
                //where needed, interpret setting as other than string
                foreach ($R as $key=>$val) {
                    if (!in_array($key ,$nonprefs)) {
                        //some preferences must be slightly transformed here...
                        if ($key=='theme') {
                            if (!themeExists($val)) {
                                appendErrorMessage("Theme '{$val}' no longer exists.<br/>");
                                $val = "default";
                            }
                        }
                        //store preference in object
                        $this->preferences[$key]=$val;
                    }
                }
            }
        }        
    }
    
    /** Return the value of a certain User Preference for the currently logged in user. */
    function getPreference($preferenceName) {
        if (array_key_exists($preferenceName,$this->preferences)) {
            return $this->preferences[$preferenceName]; 
        } else {
            return "";
        }
    }
    
    /* ================ login/logout methods ================ */
    
    /** This is the method that you call to perform the login
     *  If already logged in as non-anonymous, do nothing
     *  Else if login vars have been posted: login from POST vars
     *  Else if cookies are available: login from cookies
     *  Else if anonymous login allowed: login anonymously */
    function login() {
        //If already logged in as non-anonymous, do nothing
        if ($this->bIsLoggedIn && !$this->bIsAnonymous) return;
        //Else if login vars have been posted: login from POST vars
        $result = $this->loginFromPost();
        if ($this->bIsLoggedIn) {
            return;
        }
        if ($result == 1) {
            //report error and return
            $this->sNotice = "Unknown user or wrong password";
            return;
        }
        //Else if cookies are available: login from cookies
        $result = $this->loginFromCookie();
        if ($this->bIsLoggedIn) {
            return;
        }
        if ($result == 1) {
            //report error and return
            $this->sNotice = "Unknown user or wrong password in cookie";
            return;
        }
        //Else if anonymous login allowed: login anonymously 
        $result = $this->loginAnonymous();
        if ($this->bIsLoggedIn) {
            return;
        }
        //ah well, after this, the options are exhausted, you're not logged in!
        //no reason to report anything, either.
        return;
    }
        
    /** Attempts to login as user in POST variables
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password
     *      2 - no relevant POST vars available */
    function loginFromPost() {
        if (((isset($_POST["loginName"]))) && (isset($_POST['loginPass'])))    {
            #user logs in via login screen.
            //get username & pwd
            $loginName = $_POST["loginName"];
            $loginPwd = md5($_POST['loginPass']);
            $remember=False;
            if (isset($_POST['remember']))$remember=True;
            return $this->_login($loginName,$loginPwd,$remember);
        } else {
            return 2;
        }
    }
    
    /** Attempts to login as user given in cookies
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password
     *      2 - no relevant cookies available */
    function loginFromCookie() {
        if (isset($_COOKIE["loginname"])) {
            //user logs in via cookie
            $loginName = $_COOKIE["loginname"];
            $loginPwd = $_COOKIE["password"];
            return $this->_login($loginName,$loginPwd,True);
        } else {
            return 2;
        }
    }
    
    /** Attempts to login as the anonymous user
     *  returns one of following:
     *      0 - success
     *      1 - no anonymous user allowed 
     *      2 - no or incorrect anonymous account defined */
    function loginAnonymous() {
        if (getConfigurationSetting("ENABLE_ANON_ACCESS")!="TRUE") return 1; //no anon accounts allowed
        $loginID = getConfigurationSetting("ANONYMOUS_USER");
        $res = mysql_query("SELECT * FROM users WHERE user_id='".$loginID."'");
        if ($res) {
            if ($row = mysql_fetch_array($res)) {
                $loginName = $row["login"];
                $loginPwd = $row["password"];
                if ($this->_login($loginName,$loginPwd,False)==0) { //never remember anon login :)
                    $this->bIsAnonymous=True;
                    return 0; // success
                }
            }
        }
        return 2; //no or incorrect anonymous account defined
    }
    
    /** Attempts to login as the given user. Called by the other login methods.
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password */
    function _login($userName, $pwdHash, $remember) {
        //check username / password in user-table
        $Q = mysql_query("SELECT * FROM users WHERE login='".$userName."'");
        if (!$Q || !($R = mysql_fetch_array($Q)) ) {
            return 1; //no such user error
        }
        if ($pwdHash != $R["password"]) {
            //not a successful login: reset all class vars, return error
            //reset class vars
            $this->bIsLoggedIn = False; 
            $this->bIsAnonymous = False;
            $this->sLoginName = "";
            $this->iUserId = "";                
            return 1; //password error
        } else { 
            clearErrorMessage();
            clearMessage();
            //successful login: perform login, store cookies, etc
            //check if people changed the default account!
            if ($userName == "admin") {
                if ($pwdHash == md5("admin")) {
                    appendErrorMessage("Your admin account password still has the default 
                                        value, please change it on the 'profile' page.<br/>");
                }
            }

            //login OK
            $this->bIsLoggedIn = True;
            $this->sLoginName = $R["login"];
            $this->iUserId = $R["user_id"];                
            
            //make sure that the anonymous user is ALWAYS logged in as anonymous user
            if (   (getConfigurationSetting("ENABLE_ANON_ACCESS")=="TRUE")
                && ($this->iUserId==getConfigurationSetting("ANONYMOUS_USER"))
                ) {
                $this->bIsAnonymous=True;
            }

            //store cookies after login was checked
            if ($remember)
            {
                setcookie("loginname", $R['login']   ,(30*24*60*60)+time());
                setcookie("password",  $R["password"],(30*24*60*60)+time());
            }
                        
            #set a welcome message/advertisement after login
            appendMessage("
                <table>\n<tr><td>
                This site is powered by Aigaion 
                - A PHP/Web based management system for shared and annotated bibliographies.
                For more information visit <a href='http://www.aigaion.nl/' target='_blank'>Aigaion.nl</a>.
                </td><td>
                <a target=_blank 
                   href='http://aigaion.sourceforge.net'>
                   <img src='http://sourceforge.net/sflogo.php?group_id=109910&type=1' 
                        width='88' 
                        height='31' 
                        border='0' 
                        alt='SourceForge.hetLogo'/>
                </a>
                </td></tr>\n</table>
              ");

            #init rights and preferences
            $this->initRights();

            #SO. Here, if login was successful, we will check the database structure once.
//            include_once(APPPATH."/schema/checkschema.php");
//            if (!checkDatabase())
//            {
//                echo "<div class=errormessage>ERROR: DATABASE STRUCTURE CANNOT BE UPDATED TO NEW VERSION. PLEASE CONTACT ADMIN</div>";
//                $this->bIsLoggedIn = False;
//                $this->sLoginName = "";
//                $this->iUserId = "";
//                die();
//            }
            
            $this->initPreferences();
            return 0;
        } 
    }

    /** Logout any active session. */
    function logout() {
        //session_destroy();
        //reset class vars
        $this->bIsLoggedIn = False; 
        $this->bIsAnonymous = False;
        $this->sLoginName = "";
        $this->iUserId = "";
        //Delete cookie values
        setcookie("loginname",FALSE);
        setcookie("password",FALSE);
    }

}



?>