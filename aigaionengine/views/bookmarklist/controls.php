<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/bookmarklist/controls

Shows the controls for using the bookmarklist

access rights: we presume that this view is not loaded when the user doesn't have the bookmarklist rights.
Some controls may be shown only dependent on other rights, though.

*/
$this->load->helper('form');
$userlogin = getUserLogin();
?>
<p class='header'>Bookmark list controls</p>

<?php     
//add to topic only if you are allowed to edit publications. Note that
//for some publicatibns in the bookmarklist the operation might still fail if the access levels are wrong.
//In that case the user will be notified after the (failed) attempts
if ($userlogin->hasRights('publication_edit')) {
    echo form_open('bookmarklist/addtotopic');
    $user = $this->user_db->getByID($userlogin->userId());
    $config = array('onlyIfUserSubscribed'=>True,
                    'includeGroupSubscriptions'=>True,
                    'user'=>$user);
    echo $this->load->view('topics/optiontree',
                       array('topics'   => $this->topic_db->getByID(1,$config),
                            'showroot'  => False,
                            'depth'     => -1,
                            'selected'  => -1,
                            'dropdownname' => 'topic_id',
                            'header'    => 'Add bookmarked to topic...'
                            ),  
                       true)."\n";
    echo form_submit(array('name'=>'addtotopic','title'=>'Add all bookmarked publications to the selected topic'),'Add all to topic');
    echo form_close();
    echo "<br/>";
    
    echo form_open('bookmarklist/removefromtopic');
    $user = $this->user_db->getByID($userlogin->userId());
    $config = array('onlyIfUserSubscribed'=>True,
                    'includeGroupSubscriptions'=>True,
                    'user'=>$user);
    echo $this->load->view('topics/optiontree',
                       array('topics'   => $this->topic_db->getByID(1,$config),
                            'showroot'  => False,
                            'depth'     => -1,
                            'selected'  => -1,
                            'dropdownname' => 'topic_id',
                            'header'    => 'Remove bookmarked from topic...'
                            ),  
                       true)."\n";
    echo form_submit(array('name'=>'removefromtopic','title'=>'Remove all bookmarked publications from the selected topic'),'Remove all from topic');
    echo form_close();
    echo "<br/>";
    
    if ($userlogin->hasRights('topic_edit')) {
        echo form_open('bookmarklist/maketopic');
        echo form_submit(array('name'=>'maketopic','title'=>'Make a new topic from the bookmarked publications'),'Make into new topic');
        echo form_close();
    }

}
?>
<?php
//<br/>
    //echo form_open('export/bookmarklist/');
    //echo form_submit(array('name'=>'Export','title'=>'Export publicatinos on the bookmarklist'),'Export');
    //echo form_close();
?>
<br/>
<?php
    echo form_open('bookmarklist/clear');
    echo form_submit(array('name'=>'clear','title'=>'Clear the bookmarklist'),'Clear bookmarklist');
    echo form_close();
?>
<br/>
<?php
if ($userlogin->hasRights('publication_edit')) {
    echo form_open('bookmarklist/deleteall');
    echo form_submit(array('name'=>'deleteall','title'=>'Delete all publications on the bookmarklist from the database'),'Delete all bookmarked publications');
    echo form_close();
}
?>
<br/>
<?php

if ($userlogin->hasRights('publication_edit')) {
    echo 'Set read access level for all bookmarked publications:';
    echo form_open('bookmarklist/setpubaccesslevel');
    echo form_dropdown('accesslevel',array('public'=>'public','intern'=>'intern','private'=>'private'),'intern');
    echo form_submit(array('name'=>'setpubaccesslevel','title'=>'Set the read  access levels for all publications on the bookmarklist'),'Set publication access level');
    echo form_close();
}
?>
<br/>
<?php
if ($userlogin->hasRights('publication_edit')) {
    echo 'Set read access level for all attachments of bookmarked publications:';
    echo form_open('bookmarklist/setattaccesslevel');
    echo form_dropdown('accesslevel',array('public'=>'public','intern'=>'intern','private'=>'private'),'intern');
    echo form_submit(array('name'=>'setattaccesslevel','title'=>'Set the read access levels for all attachments of publications on the bookmarklist'),'Set attachment access level');
    echo form_close();
}
?>

<br/>
<?php

if ($userlogin->hasRights('publication_edit')) {
    echo 'Set edit access level for all bookmarked publications:';
    echo form_open('bookmarklist/seteditpubaccesslevel');
    echo form_dropdown('editaccesslevel',array('public'=>'public','intern'=>'intern','private'=>'private'),'intern');
    echo form_submit(array('name'=>'seteditpubaccesslevel','title'=>'Set the edit  access levels for all publications on the bookmarklist'),'Set publication edit access level');
    echo form_close();
}
?>
<br/>
<?php
if ($userlogin->hasRights('publication_edit')) {
    echo 'Set edit access level for all attachments of bookmarked publications:';
    echo form_open('bookmarklist/seteditattaccesslevel');
    echo form_dropdown('editaccesslevel',array('public'=>'public','intern'=>'intern','private'=>'private'),'intern');
    echo form_submit(array('name'=>'seteditattaccesslevel','title'=>'Set the edit access levels for all attachments of publications on the bookmarklist'),'Set attachment edit access level');
    echo form_close();
}
?>
<br/>
