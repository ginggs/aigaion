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
              
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."aigaiongeneral` (  `version` varchar(10) NOT NULL default '',  `releaseversion` varchar(10) NOT NULL,  PRIMARY KEY  (`version`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."aigaiongeneral` (`version`,`releaseversion`) VALUES  ('V2.0','2.0');");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."attachments` (  `pub_id` int(10) unsigned NOT NULL default '0',  `location` varchar(255) NOT NULL default '',  `note` varchar(255) NOT NULL default '',  `ismain` enum('TRUE','FALSE') NOT NULL default 'FALSE',  `user_id` int(11) NOT NULL default '0',  `mime` varchar(100) NOT NULL default '',  `name` varchar(255) NOT NULL default '',  `isremote` enum('TRUE','FALSE') NOT NULL default 'FALSE',  `att_id` int(10) unsigned NOT NULL auto_increment,  `read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `group_id` int(10) unsigned NOT NULL default '0',  `derived_read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  PRIMARY KEY  (`att_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."author` (  `author_id` int(10) unsigned NOT NULL auto_increment,  `surname` varchar(255) NOT NULL,  `von` varchar(255) NOT NULL default '',  `firstname` varchar(255) NOT NULL,  `email` varchar(255) NOT NULL,  `url` varchar(255) NOT NULL default '',  `institute` varchar(255) NOT NULL,  `specialchars` enum('FALSE','TRUE') NOT NULL default 'FALSE',  `cleanname` varchar(255) NOT NULL default '',  PRIMARY KEY  (`author_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."availablerights` (  `name` varchar(20) NOT NULL,  `description` varchar(255) NOT NULL,  PRIMARY KEY  (`name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."availablerights` (`name`,`description`) VALUES  ('attachment_read','read attachments'), ('attachment_edit','add, edit and delete attachments'), ('database_manage','manage the database'), ('note_read','read comments'), ('note_edit','add, edit and delete own comments'), ('publication_edit','add, edit and delete publications'), ('topic_subscription','change own topic subscriptions'), ('topic_edit','add, edit and delete topics'), ('user_edit_self','edit own profile (user rights not included)'), ('user_edit_all','edit all profiles (user rights not included)'), ('user_assign_rights','assign user rights'), ('bookmarklist','use a persistent bookmarklist'), ('read_all_override','read all attachments, publications, topics and notes, overriding access levels'), ('edit_all_override','edit all attachments, publications, topics and notes, overriding access levels');");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."changehistory` (  `version` varchar(20) NOT NULL,  `type` varchar(50) NOT NULL,  `description` text NOT NULL,  PRIMARY KEY  (`version`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."changehistory` (`version`,`type`,`description`) VALUES  ('1.99.0','bugfix,features,layout,security','Introduction of this table; first changehistory of Aigaion 2, still to be improved. This text will be modified before the real prerelease into some more informative description of Aigaion 2.0 Update contains all types: bugfix,features,layout,security. Note that the \'type\' column contains a comma separated list of things that may have changed in this release. ');");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."config` (  `setting` varchar(255) NOT NULL,  `value` mediumtext NOT NULL,  PRIMARY KEY  (`setting`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."config` (`setting`,`value`) VALUES  ('CFG_ADMIN','Admin'), ('CFG_ADMINMAIL','admin@... (mail server)'), ('ALLOWED_ATTACHMENT_EXTENSIONS','.doc,.gif,.htm,.html,.jpeg,.jpg,.pdf,.png,.tif,.tiff,.txt,.zip,.gz,.tar,.ps,.z'), ('ALLOW_ALL_EXTERNAL_ATTACHMENTS','FALSE'), ('WINDOW_TITLE','A Web Based Annotated Bibliography'), ('ALWAYS_INCLUDE_PAPERS_FOR_TOPIC','TRUE'), ('SHOW_TOPICS_ON_FRONTPAGE','FALSE'), ('SHOW_TOPICS_ON_FRONTPAGE_LIMIT','5'), ('SERVER_NOT_WRITABLE','FALSE'), ('CONVERT_LATINCHARS_IN','TRUE'), ('PUBLICATION_XREF_MERGE','FALSE'), ('BIBTEX_STRINGS_IN',''), ('ENABLE_ANON_ACCESS','FALSE'), ('ANONYMOUS_USER','');");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."grouprightsprofilelink` (  `group_id` int(10) NOT NULL,  `rightsprofile_id` int(10) NOT NULL,  PRIMARY KEY  (`group_id`,`rightsprofile_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."grouprightsprofilelink` (`group_id`,`rightsprofile_id`) VALUES  (2,1),(2,2),(2,3),(2,4),(3,3),(3,4),(4,2),(4,3),(4,4),(5,4);");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."keywords` (  `keyword_id` int(10) NOT NULL auto_increment,  `keyword` text NOT NULL,  PRIMARY KEY  (`keyword_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."notecrossrefid` (  `note_id` int(10) NOT NULL,  `xref_id` int(10) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."notes` (  `note_id` int(10) unsigned NOT NULL auto_increment,  `pub_id` int(10) unsigned NOT NULL default '0',  `user_id` int(11) NOT NULL default '0',  `rights` enum('public','private') NOT NULL default 'public',  `text` mediumtext,  `read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `group_id` int(10) unsigned NOT NULL default '0',  `derived_read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  PRIMARY KEY  (`note_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."publication` (  `pub_id` int(10) unsigned NOT NULL auto_increment,  `user_id` int(10) unsigned NOT NULL default '0',  `year` varchar(12) NOT NULL default '0000',  `actualyear` varchar(12) NOT NULL default '0000',  `title` mediumtext NOT NULL,  `bibtex_id` varchar(255) NOT NULL,  `report_type` varchar(255) NOT NULL default '',  `pub_type` enum('Article','Book','Booklet','Inbook','Incollection','Inproceedings','Manual','Mastersthesis','Misc','Phdthesis','Proceedings','Techreport','Unpublished') default NULL,  `survey` tinyint(1) NOT NULL default '0',  `mark` int(11) NOT NULL default '5',  `series` varchar(64) NOT NULL default '',  `volume` varchar(16) NOT NULL default '',  `publisher` varchar(127) NOT NULL default '',  `location` varchar(127) NOT NULL default '',  `issn` varchar(32) NOT NULL default '',  `isbn` varchar(32) NOT NULL default '',  `firstpage` varchar(10) NOT NULL default '0',  `lastpage` varchar(10) NOT NULL default '0',  `journal` varchar(255) NOT NULL default '',  `booktitle` varchar(255) NOT NULL default '',  `number` varchar(255) NOT NULL default '',  `institution` varchar(255) NOT NULL default '',  `address` varchar(255) NOT NULL default '',  `chapter` varchar(10) NOT NULL default '0',  `edition` varchar(255) NOT NULL default '',  `howpublished` varchar(255) NOT NULL default '',  `month` varchar(255) NOT NULL default '',  `organization` varchar(255) NOT NULL default '',  `school` varchar(255) NOT NULL default '',  `note` mediumtext NOT NULL,  `abstract` mediumtext NOT NULL,  `url` varchar(255) NOT NULL default '',  `doi` varchar(255) NOT NULL default '',  `crossref` varchar(255) NOT NULL,  `namekey` varchar(255) NOT NULL,  `userfields` mediumtext NOT NULL,  `specialchars` enum('FALSE','TRUE') NOT NULL default 'FALSE',  `cleanjournal` varchar(255) NOT NULL default '',  `cleantitle` varchar(255) NOT NULL default '',  `read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `group_id` int(10) unsigned NOT NULL default '0',  `derived_read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  PRIMARY KEY  (`pub_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."publicationauthorlink` (  `pub_id` int(10) unsigned NOT NULL default '0',  `author_id` int(10) unsigned NOT NULL default '0',  `rank` int(10) unsigned NOT NULL default '1',  `is_editor` enum('Y','N') NOT NULL default 'N',  PRIMARY KEY  (`pub_id`,`author_id`,`is_editor`),  KEY `pub_id` (`pub_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."publicationkeywordlink` (  `pub_id` int(10) NOT NULL,  `keyword_id` int(10) NOT NULL,  PRIMARY KEY  (`pub_id`,`keyword_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."rightsprofilerightlink` (  `rightsprofile_id` int(10) NOT NULL,  `right_name` varchar(20) NOT NULL,  PRIMARY KEY  (`rightsprofile_id`,`right_name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."rightsprofilerightlink` (`rightsprofile_id`,`right_name`) VALUES  (1,'database_manage'), (1,'edit_all_override'), (1,'read_all_override'), (1,'user_assign_rights'), (1,'user_edit_all'), (2,'attachment_edit'), (2,'note_edit'), (2,'publication_edit'), (2,'topic_edit'), (2,'user_edit_self'), (3,'attachment_read'), (3,'bookmarklist'), (3,'note_read'), (3,'topic_subscription');");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."rightsprofiles` (  `rightsprofile_id` int(10) NOT NULL auto_increment,  `name` varchar(20) NOT NULL,  PRIMARY KEY  (`rightsprofile_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."rightsprofiles` (`rightsprofile_id`,`name`) VALUES  (1,'admin_rights'), (2,'editor_rights'), (3,'reader_rights'), (4,'guest_rights');");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."topicpublicationlink` (  `topic_id` int(10) unsigned NOT NULL default '0',  `pub_id` int(10) unsigned NOT NULL default '0',  PRIMARY KEY  (`topic_id`,`pub_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."topics` (  `topic_id` int(10) NOT NULL auto_increment,  `name` varchar(50) default NULL,  `description` mediumtext,  `url` varchar(255) NOT NULL default '',  `user_id` int(10) unsigned NOT NULL default '0',  `read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `group_id` int(10) unsigned NOT NULL default '0',  `derived_read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  PRIMARY KEY  (`topic_id`),  KEY `name` (`name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."topics` (`topic_id`,`name`,`description`,`url`,`user_id`,`read_access_level`,`edit_access_level`,`group_id`,`derived_read_access_level`,`derived_edit_access_level`) VALUES  (1,'Top','No description. This topic is in itself not relevant, it is just a \'topmost parent\' for the topic hierarchy.','',0,'public','intern',0,'public','intern'); ");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."topictopiclink` (  `source_topic_id` int(10) NOT NULL default '0',  `target_topic_id` int(10) NOT NULL default '0',  PRIMARY KEY  (`source_topic_id`),  KEY `target_topic_id` (`target_topic_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Hierarchy of topics; typed relations';");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."userbookmarklists` (  `user_id` int(10) NOT NULL,  `pub_id` int(10) NOT NULL,  PRIMARY KEY  (`user_id`,`pub_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."usergrouplink` (  `user_id` int(10) NOT NULL,  `group_id` int(10) NOT NULL,  PRIMARY KEY  (`user_id`,`group_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."userpublicationmark` (  `pub_id` int(10) NOT NULL default '0',  `user_id` int(11) NOT NULL default '0',  `mark` enum('1','2','3','4','5') NOT NULL default '3',  `read` enum('y','n') NOT NULL default 'y',  PRIMARY KEY  USING BTREE (`pub_id`,`user_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."userrights` (  `user_id` int(10) NOT NULL,  `right_name` varchar(20) NOT NULL,  PRIMARY KEY  (`right_name`,`user_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."userrights` (`user_id`,`right_name`) VALUES  (1,'attachment_edit'), (1,'attachment_read'), (1,'database_manage'), (1,'note_edit'), (1,'note_edit_all'), (1,'note_edit_self'), (1,'note_read'), (1,'publication_edit'), (1,'topic_edit'), (1,'topic_subscription'), (1,'user_assign_rights'), (1,'user_edit_all'), (1,'user_edit_self'), (1,'bookmarklist');");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."users` (  `user_id` int(10) NOT NULL auto_increment,  `theme` varchar(255) NOT NULL default 'darkdefault',  `newwindowforatt` enum('TRUE','FALSE') NOT NULL default 'FALSE',  `summarystyle` varchar(255) NOT NULL default 'author',  `authordisplaystyle` varchar(5) NOT NULL default 'vlf',  `liststyle` smallint(6) NOT NULL default '0',  `login` varchar(20) NOT NULL default '',  `password` varchar(255) NOT NULL default '',  `initials` varchar(10) default NULL,  `firstname` varchar(20) default NULL,  `betweenname` varchar(10) default NULL,  `surname` varchar(100) default NULL,  `csname` varchar(10) default NULL,  `abbreviation` varchar(10) NOT NULL default '',  `email` varchar(30) NOT NULL default '',  `u_rights` tinyint(2) NOT NULL default '0',  `lastreviewedtopic` int(10) NOT NULL default '1',  `type` enum('group','anon','normal') NOT NULL default 'normal',  `lastupdatecheck` int(10) unsigned NOT NULL default '0',  `exportinbrowser` enum('TRUE','FALSE') NOT NULL default 'TRUE',  `utf8bibtex` enum('TRUE','FALSE') NOT NULL default 'FALSE',  PRIMARY KEY  (`user_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."users` (`user_id`,`theme`,`newwindowforatt`,`summarystyle`,`authordisplaystyle`,`liststyle`,`login`,`password`,`initials`,`firstname`,`betweenname`,`surname`,`abbreviation`,`email`,`lastreviewedtopic`,`type`,`lastupdatecheck`,`exportinbrowser`,`utf8bibtex`) VALUES  (1,'default','TRUE','title','fvl',50,'admin','21232f297a57a5a743894a0e4a801fc3','AA','Admin','the','Admin','ADM','',1,'normal',0,'TRUE','FALSE');");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."users` (`user_id`,`surname`,`abbreviation`,`type`) VALUES  (2,'admins','adm_grp','group'),(3,'readers','read_grp','group'),(4,'editors','ed_grp','group'),(5,'guests','gue_grp','group');");
        _query("CREATE TABLE `".AIGAION2_DB_PREFIX."usertopiclink` (  `collapsed` int(2) NOT NULL default '0',  `user_id` int(10) NOT NULL default '0',  `topic_id` int(10) NOT NULL default '0',  `star` int(2) NOT NULL default '0',  PRIMARY KEY  (`user_id`,`topic_id`),  KEY `topic_id` (`topic_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        _query("INSERT INTO  `".AIGAION2_DB_PREFIX."usertopiclink` (`collapsed`,`user_id`,`topic_id`,`star`) VALUES  (0,1,1,0); ");
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
            <TD><input type=submit name=Submit value='Install' size=50></TD>
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