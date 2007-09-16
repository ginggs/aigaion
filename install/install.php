<?php
include ('database.php');
include ('sec.php');
//split script on POST user/paswd var.
$pwd='';
if (isset($_POST['aigaion2_pwd']))
    $pwd = $_POST['aigaion2_pwd'];
$user='';
if (isset($_POST['aigaion2_user']))
    $user = $_POST['aigaion2_user'];
if ( $pwd !='' 
    && 
     $user != '' 
    && 
     defined('AIGAION_INSTALL_USERNAME')
    &&
     (AIGAION_INSTALL_USERNAME!='')
    &&
     defined('AIGAION_INSTALL_PWD')
    &&
     (AIGAION_INSTALL_PWD!='')
    &&
     $pwd == AIGAION_INSTALL_PWD
    &&
     $user == AIGAION_INSTALL_USERNAME) {
    //correct password was provided - do migration
    if ( 
         !defined('AIGAION2_DB_HOST')
        ||
         (AIGAION2_DB_HOST=='')
        ||
         !defined('AIGAION2_DB_USER')
        ||
         (AIGAION2_DB_USER=='')
        ||
         !defined('AIGAION2_DB_PWD')
        ||
         !defined('AIGAION2_DB_NAME')
        ||
         (AIGAION2_DB_NAME=='')
        ||
         !defined('AIGAION2_DB_PREFIX')
        )
        die('Please define all appropriate parameters for the migration.');
    
        
        #
        # connect to aigaion 2 database, execute install query
        #
        
        //Connect to the database, feedback html when an error occurs.
        $theDatabase = mysql_connect(AIGAION2_DB_HOST,
                                     AIGAION2_DB_USER,
                                     AIGAION2_DB_PWD);
        if ($theDatabase)
        {
            if (!mysql_select_db(AIGAION2_DB_NAME)) {
                die("Aigaion 2.0 migration script: database connection to new database failed<br>
                Error: Aigaion did not succeed in selecting the correct 
                database. Please check the database settings in your migration script.");
            }
        } else {
            die("Aigaion: database connection to new database failed<br>
            Error: Aigaion did not succeed in connecting to the database 
            server. Please check the database settings in config.php.");
        }        
      
insert database creation statements here...

} else {
    //no or incorrect pwd - show form
    ?>
    <form action='install.php' method='post'>
        <table bgcolor="F7F7F7" cellspacing="3" cellpadding="3" style="border:1px solid black" width="400"  style='width:395px;'>
            
            <TR>
            <TD>Name:</TD>
            <TD><input type=text name=aigaion2_user size=50></TD>
            </TR>
            
            <TR>
            <TD>Password:</TD>
            <TD><input type=password name=aigaion2_pwd size=50></TD>
            </TR>        
  
            <TR>
            <TD></TD>
            <TD><input type=submit name=Submit value='Migrate' size=50></TD>
            </TR>        
        
        </table>
    </form>
    <?php
}

function _query($q) {
    $res = mysql_query($q);
    if (mysql_error())
        echo mysql_error().'<br/>';
    return $res;
}
?>