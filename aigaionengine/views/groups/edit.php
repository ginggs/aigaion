<?php
/**
views/groups/edit

Shows a form for editing or adding groups.

Parameters:
    $group=>the group object to be edited
    
If $group is null, the edit for will be restyled as an 'add new group' form

*/
$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('groups/commit');
$isAddForm = False;
if (!isset($group)||($group==null)) {
    $isAddForm = True;
    echo form_hidden('action','add');
    $group = new Group;
} else {
    echo form_hidden('action','edit');
    echo form_hidden('group_id',$group->group_id);
}

if ($isAddForm) {
    echo "<p class='header2'>Create a new group</p>";
} else {
    echo "<p class='header2'>Edit group settings</p>";
}

echo "
    <table width='100%'>
        
        <tr><td colspan='2'>
        <hr><b>Group details:</b><hr>
        </td></tr>

        <tr>
        <td>Name</td>
        <td>"
        .form_input(array('name'=>'name',
                          'size'=>'15',
                          'value'=>$group->name))."
        </td>
        </tr>
        <tr>
        <td>Abbreviation (about three characters)</td>
        <td>"
        .form_input(array('name'=>'abbreviation',
                          'size'=>'5',
                          'value'=>$group->abbreviation))."
        </td>
        </tr>

        <tr><td colspan='2'><hr>
        <b>Note: an interface for assigning rights profiles to groups (as default rights for new users) will be added here.</b>
        </td></tr>        
        
        <tr><td colspan='2'><hr>
        <b>Note: an interface for modifying the group topic subscription will be added here.</b>
        </td></tr>        

        <tr>
        <td colspan=2><hr></td>
        </tr>
        <tr><td>";
if ($isAddForm) {
    echo form_submit('submit','Add');
} else {
    echo form_submit('submit','Change');
}
echo "
        </td>
        </tr>
    </table>
     ";
echo form_close();
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";

?>

