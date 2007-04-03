<div class="group-summary">
<?php
/**
views/groups/summary

Shows a summary of a group: edit link, name, delete link, etc

Parameters:
    $group=>the group object that is to be summarized
*/
    echo anchor('group/edit/'.$group->group_id,'[edit]')."&nbsp;"
    .anchor('group/delete/'.$group->group_id,'[delete]')."&nbsp;"
    .$group->name;

?>
</div>