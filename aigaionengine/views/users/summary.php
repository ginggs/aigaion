<div class="user-summary">
<?php
/**
views/users/summary

Shows a summary of a user: edit link, name, delete link, etc

Parameters:
    $user=>the User object that is to be summarized
*/
    echo anchor('users/edit/'.$user->user_id,'[edit]')."&nbsp;"
    .anchor('users/delete/'.$user->user_id,'[delete]')."&nbsp;"
    .$user->login." (".$user->firstname." ".$user->betweenname." ".$user->surname.")";

?>
</div>