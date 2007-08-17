<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="user-summary">
<?php
/**
views/users/summary

Shows a summary of a user: edit link, name, delete link, etc

Parameters:
    $user=>the User object that is to be summarized
    
access rights: we presume that this view is not loaded when the user doesn't have the read rights.
as for the edit rights: they determine which edit links are shown.
    
*/
$userlogin  = getUserLogin();
    if ($userlogin->hasRights('user_edit_all') || ($userlogin->hasRights('user_edit_all')&&$user->user_id==$userlogin->userId()))
    {
        echo '['.anchor('users/edit/'.$user->user_id,'edit')."]&nbsp;[";
        echo anchor('users/delete/'.$user->user_id,'delete')."]&nbsp;";
        if ($userlogin->hasRights('topic_subscription')) {
            echo '['.anchor('users/topicreview/'.$user->user_id,'topic subscription')."]&nbsp;";
        }
    }
    echo $user->login." (".$user->firstname." ".$user->betweenname." ".$user->surname.")";
    if ($user->isAnonymous) {
        echo ' (guest user)';
    }

?>
</div>