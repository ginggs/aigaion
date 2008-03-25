<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
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

//echo 'userlogin loaded';
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
    
    var $theUser = null;

    /** this var is set to True if the user just logged out. This fact needs to be remembered 
    because otherwise we run the risk of immedately loggin the user in again through the cookies 
    (cookies are not deleted properly in PHP4 when using a CI redirect after deleting the cookies :( ) */
    var $bJustLoggedOut = False;
        
    /* ================ Basic accessors ================ */
    
    /** This method is called for every controller access, thanks to the login_filter.
    This is also where we check for the schema updates.... so if the Aigaion engine is replaced
    with a new version, every user will get logged out upon the next page access. */
    function isLoggedIn() {
        if ($this->bIsLoggedIn) {
            //check schema
            if (checkSchema()) { 
                return True; //OK? return true;
            } else {
                $this->logout();
                $this->sNotice = "You have been logged out because the Aigaion Engine is in the 
                                  process of being updated.<br/> If you are a user with 
                                  database_manage rights, please login to complete the update. 
                                  <br/>";
            }
        }            
        return False;
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
    function user() {
        return theUser;
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
        $CI = &get_instance();
        $this->rights = array();
        $Q = $CI->db->getwhere('userrights',array('user_id'=>$this->iUserId));
        foreach ($Q->result() as $R) {
            $this->rights[] = $R->right_name;
        }
    }
    
    /** die() if the currently logged in user does not have the given right. Uses hasRights($right) */
    function checkRights($right) 
    {
        if ($this->hasRights($right)) {
            return true;
        } else {
            echo "<div class='errormessage'>You do not have sufficient rights for the requested 
            operation or page. <br/>Sorry for the inconvenience.</div>";
            die();
            return false;
        }
    }

    /** die() due to rights problems. This function is provided to fail rights in a uniform wawy,
    even in cases where the checking of the right was done without resorting to checkRights
    (for example because the condition is more than just one simple boolean check). */
    function failRights() {
        echo "<div class='errormessage'>You do not have sufficient rights for the requested 
        operation or page. <br/>Sorry for the inconvenience.</div>";
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
        $CI = &get_instance();
        //right now, I just enumerate all relevant preferences from the user-table
        $nonprefs=array("password");
        $this->preferences = array();
        $Q = $CI->db->getwhere('users',array('user_id'=>$this->iUserId));
        if ($Q->num_rows()>0) {
            $R = $Q->row_array();
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
     *  Else if external login in use: try to login from external module
     *  Else if external login not in use and login vars have been posted: login from POST vars
     *  Else if external login not in use and cookies are available: login from cookies
     *  Else if anonymous login allowed: login anonymously */
    function login() {
        //If already logged in as non-anonymous, do nothing
        if ($this->bIsLoggedIn && !$this->bIsAnonymous) return;
        //Else maybe we can login from external module?
        if (getConfigurationSetting("USE_EXTERNAL_LOGIN") == 'TRUE') {
            $result = $this->loginFromExternalSystem();
            if ($this->bIsLoggedIn) {
                return;
            }
            if ($result == 1) {
                //report error and return
                $this->sNotice = "Unknown user or wrong password from external login module";
                //return; don't return, but rather attempt to do the anonymous login later on
            }
            if ($result == 2) {
                //report error and return
                $this->sNotice = "No login info available...";
                //return; don't return, but rather attempt to do the anonymous login later on
            }
        } else {
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

    /** Attempts to login as user specified by some external module (e.g. provided by a CMS)
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password
     *      2 - no relevant login info available */
    function loginFromExternalSystem() {
        if (getConfigurationSetting("USE_EXTERNAL_LOGIN") != 'TRUE') {
            return 2;
        }
        $loginName = '';
        $loginGroups = array();
        $CI = &get_instance();
        //depending on the external login settings, choose module and obtain the loginName of the logged user in the external module
        switch (getConfigurationSetting("EXTERNAL_LOGIN_MODULE")) {
            case "Httpauth":
                $CI->load->library('login_httpauth');
                //attempt to get loginname from external system
                $loginInfo = $CI->login_httpauth->getLoginInfo();
                $loginName = $loginInfo['login'];
                $loginGroups = $loginInfo['groups'];
                break;
            case "LDAP":
                appendErrorMessage('testing for ldap login...');
                $CI->load->library('login_ldap');
                $CI->load->library('authldap');
                //attempt to get loginname from external system
                $loginInfo = $CI->login_ldap->getLoginInfo();
                $loginName = $loginInfo['login'];
                $loginGroups = $loginInfo['groups'];
                appendErrorMessage('<br/>LDAP login says: '.$loginName.'<br/>');
                break;
            //case "drupal":
                //$CI->load->library('login_drupal');
                //attempt to get loginname from external system.
                //this module probably needs some extra info, such as the URL of the DRUPAL site?
                //
                //$loginName = $CI->login_drupal->getLoginName();
                //$loginGroups = $CI->login_drupal->getLoginGroups();
                //break;
        }
        if ($loginName == '') {
            //no login info could be found
            return 1;
        }
        
        //login name was found. Now try to login that person
        $Q = $CI->db->getwhere('users',array('login'=>$loginName));
        if ($Q->num_rows()>0) { //user found
            $row = $Q->row();
            $loginPwd = $row->password;
            if ($this->_login($loginName,$loginPwd,False)==0) { //never remember external login; that's a task for the external module
                //$this->sNotice = 'logged from httpauth';
                //appendErrorMessage('<br/>LDAP login says: known user, logged in');
                return 0; // success
            }
        } 
        //appendErrorMessage('<br/>LDAP login says: unknown user, make?');
        if (getConfigurationSetting("CREATE_MISSING_USERS") == 'TRUE') {
            //appendErrorMessage('<br/>LDAP login says: unknown user, make!');
            //no such user found. Make user on the fly. Don't use the user_db class for this, as 
            // we would run into problems with the checkrights performed in user_db->add(...)
            $chars = "abcdefghijkmnopqrstuvwxyz023456789";
            srand((double)microtime()*1000000);
            $i = 0;
            $pass = '' ;
            while ($i <= 7) {
                $num = rand() % 33;
                $tmp = substr($chars, $num, 1);
                $pass = $pass . $tmp;
                $i++;
            }            
            $group_ids = array();
            foreach ($loginGroups as $groupname) {
                $groupQ = $CI->db->getwhere('users',array('type'=>'group','abbreviation'=>$groupname));
                if ($groupQ->num_rows()>0) {
                    $R = $groupQ->row();
                    $group_ids[] = $R->user_id;
                } else {
                    //group must also be created...
                    $CI->db->insert("users", array('surname'=>$groupname,'abbreviation'=>$groupname,'type'=>'group'));
                    $new_id = $CI->db->insert_id();
                    //subscribe group to top topic
                    $CI->db->insert('usertopiclink', array('user_id' => $new_id, 'topic_id' => 1)); 
                    $group_ids[] = $new_id;
                }
            }
            //appendErrorMessage('<br/>LDAP login says: now add user...');
            //add user....
            $CI->db->insert("users",     array('initials'           => '',
                                               'firstname'          => '',
                                               'betweenname'        => '',
                                               'surname'            => $loginName,
                                               'email'              => '',
                                               'lastreviewedtopic'  => 1,
                                               'abbreviation'       => '',
                                               'login'              => $loginName,
                                               'password'           => md5($pass),
                                               'type'               => 'normal',
                                               'theme'              => 'default',
                                               'summarystyle'       => 'author',
                                               'authordisplaystyle' => 'fvl',
                                               'liststyle'          => '0',
                                               'newwindowforatt'    => 'FALSE',
                                               'exportinbrowser'    => 'TRUE',
                                               'utf8bibtex'         => 'FALSE'
                                               )
                              );
            $new_id = $CI->db->insert_id();
            //add group links, and rightsprofiles for these groups, to the user
            foreach ($group_ids as $group_id) {
                $CI->db->insert('usergrouplink',array('user_id'=>$new_id,'group_id'=>$group_id));
                $group = $CI->group_db->getByID($group_id);
                foreach ($group->rightsprofile_ids as $rightsprofile_id) {
                    $rightsprofile = $CI->rightsprofile_db->getByID($rightsprofile_id);
                    foreach ($rightsprofile->rights as $right) {
                        $CI->db->delete('userrights',array('user_id'=>$new_id,'right_name'=>$right));
                        $CI->db->insert('userrights',array('user_id'=>$new_id,'right_name'=>$right));
                    }
                    
                }
            }
            //subscribe new user to top topic
            $CI->db->insert('usertopiclink', array('user_id' => $new_id, 'topic_id' => 1)); 
            //after adding the new user, log in as that new user
            if ($this->_login($loginName,md5($pass),False)==0) { //never remember external login; that's a task for the external module
                //$this->sNotice = 'logged from httpauth';
                appendMessage('Created missing user: '.$loginName.' as member of groups: '.implode(',',$loginGroups));
                return 0; // success
            } else {
                echo "Serious error: a new user was created and could not be logged in. ".md5($pass)." ";die();
            }
        } else {
            return 1;
        }
        return 2;
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
        if ($this->bJustLoggedOut) {
            $this->bJustLoggedOut = False;  
            return 2;
        }
        if (isset($_COOKIE["loginname"])) {
            //user logs in via cookie
            $loginName = $_COOKIE["loginname"];
            $loginPwd = $_COOKIE["password"];
            return $this->_login($loginName,$loginPwd,True);
        } else {
            return 2;
        }
    }
        
    /** Attempts to login as the given anonymous user
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password (no or incorrect anonymous account defined)
     *      2 - no login info available */
    function loginAnonymous($user_id = -1) {
        $CI = &get_instance();
        if (getConfigurationSetting("ENABLE_ANON_ACCESS")!="TRUE") return 1; //no anon accounts allowed
        if ($user_id==-1) {
            $user_id = getConfigurationSetting("ANONYMOUS_USER");
        }
        $Q = $CI->db->getwhere('users',array('user_id'=>$user_id,'type'=>'anon'));
        if ($Q->num_rows()>0) {
            $row = $Q->row();
            $loginName = $row->login;
            $loginPwd = $row->password;
            if ($this->_login($loginName,$loginPwd,False)==0) { //never remember anon login :)
                $this->bIsAnonymous=True;
                return 0; // success
            }
        }
        return 1; //no or incorrect anonymous account defined
    }
    
    /** Attempts to login as the given user. Called by the other login methods.
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password */
    function _login($userName, $pwdHash, $remember) {
        $CI = &get_instance();
        //check username / password in user-table
        $Q = $CI->db->getwhere('users',array('login'=>$userName));
        if ($Q->num_rows()<=0) {
            return 1; //no such user error
        }
        $R = $Q->row();
        if ($pwdHash != $R->password) {
            //not a successful login: reset all class vars, return error
            //reset class vars
            $this->bIsLoggedIn = False; 
            $this->bIsAnonymous = False;
            $this->sLoginName = "";
            $this->iUserId = "";                
            $CI->latesession->set('USERLOGIN', $this);
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
            $this->sLoginName = $R->login;
            $this->iUserId = $R->user_id;       
            $this->bIsAnonymous = False;
            $this->bJustLoggedOut = False;  
            
            //create the User object for this logged user
            $CI = &get_instance();
            $this->theUser = $CI->user_db->getByID($this->iUserId);

            //make sure that the anonymous user is ALWAYS logged in as anonymous user
            if (   (getConfigurationSetting("ENABLE_ANON_ACCESS")=="TRUE")
                && ($this->theUser->isAnonymous)
                ) {
                $this->bIsAnonymous=True;
            }

            //store cookies after login was checked
            if ($remember)
            {
                setcookie("loginname", $R->login   ,(3*24*60*60)+time());
                setcookie("password",  $R->password,(3*24*60*60)+time());
            }

            #init rights and preferences
            $this->initRights();
            

            #set a welcome message/advertisement after login
            appendMessage("
                <table>\n<tr><td>
                This site is powered by Aigaion 
                - A PHP/Web based management system for shared and annotated bibliographies.
                For more information visit <a href='http://www.aigaion.nl/' class='open_extern'>www.aigaion.nl</a>.
                </td><td>
                <a href='http://aigaion.sourceforge.net' class='open_extern'>
                   <img src='http://sourceforge.net/sflogo.php?group_id=109910&type=1' 
                        width='88' 
                        height='31' 
                        border='0' 
                        alt='SourceForge.hetLogo'/>
                </a>
                </td></tr>\n</table>
              ");

            #SO. Here, if login was successful, we will check the database structure once.
            $this->initPreferences();
            $CI->latesession->set('USERLOGIN', $this);
            if (!checkSchema()) { //checkSchema will also attempt to login...
                $this->logout();
                $this->sNotice = "You have been logged out because the Aigaion Engine is in the 
                                  process of being updated.<br/> If you are a user with 
                                  database_manage rights, please login to complete the update. 
                                  <br/>";
                return 2;
            }
            
            #once every day (i.e. depending on when last up-to-date-check was performed), for
            #database_manage users, an up-to-date-check is performed
            #do this AFTER possible updating of the database ;-)
            if (!$this->bIsAnonymous && $this->hasRights('database_manage') && ($this->theUser->lastupdatecheck+48*60*60 < time())) {
                $CI->load->helper('checkupdates');
	            $checkresult = "Checking for updates... ";
	            $updateinfo = checkUpdates();
	            if ($updateinfo == '') {
    		        $checkresult .= '<b>OK</b><br/>';
        			$checkresult .= 'This installation of Aigaion is up-to-date';
	            } else {
        			$checkresult .= '<span class="errortext">ALERT</span><br/>';
        			$checkresult .= $updateinfo;
    	        }
    	        appendMessage($checkresult);
                $CI->db->update('users',array('lastupdatecheck'=>time()),array('user_id'=>$this->iUserId));
            }
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
        $this->rights = array();
        $this->preferences = array();
        $this->bJustLoggedOut = True;
        
        //Delete cookie values
        setcookie("loginname",FALSE);
        setcookie("password",FALSE);
        $CI = &get_instance();
        $CI->latesession->set('USERLOGIN', $this);
    }

}



?>