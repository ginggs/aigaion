<?php
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
    $user = $this->user_db->getByID(getUserLogin()->userId());
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
}
?>
<br>
[export to BiBTeX]
<br>
[export to WhaTEveR]
<br>
<?php
//make topic only if you are allowed to edit topics. 
if ($userlogin->hasRights('topic_edit')) {
?>
    [make new topic from bookmarked publications]
<?php
}
?>
<br>
<br>