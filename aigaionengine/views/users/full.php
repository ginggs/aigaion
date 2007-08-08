<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="userview-holder">
<?php
/**
views/users/full

Shows the full information of the user

Parameters:
    $user=>the User object that is to be displayed

we assume that this view is not loaded if you don't have the appropriate read and edit rights
*/
    echo "FULL INFO FOR ".$user->login;

?>
</div>
