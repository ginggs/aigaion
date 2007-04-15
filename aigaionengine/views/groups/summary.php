<div class="group-summary">
<?php
/**
views/groups/summary

Shows a summary of a group: edit link, name, delete link, etc

Parameters:
    $group=>the group object that is to be summarized
*/
    echo anchor('groups/edit/'.$group->group_id,'[edit]')."&nbsp;"
    .anchor('groups/delete/'.$group->group_id,'[delete]')."&nbsp;"
    .anchor('groups/topicreview/'.$group->group_id,'[topic subscription]')."&nbsp;"
    .$group->name;

?>
</div>