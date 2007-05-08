<?php
/**
views/groups/edit

Shows a form for editing or adding groups.

Parameters:
    $group=>the group object to be edited
    
If $group is null, the edit for will be restyled as an 'add new group' form
if $group is not null, but $action == 'add', the edit form will be restyled as a
pre filled 'add new group' form
*/
$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('groups/commit');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','group');
$isAddForm = False;
if (!isset($group)||($group==null)||(isset($action)&&$action=='add')) {
    $isAddForm = True;
    echo form_hidden('action','add');
    if (!isset($action)||$action!='add')
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

//validation feedback
echo $this->validation->error_string;

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
        <td>Abbreviation</td>
        <td>"
        .form_input(array('name'=>'abbreviation',
                          'size'=>'5',
                          'value'=>$group->abbreviation))."
        </td>
        </tr>

        <tr><td colspan='2'>
        <hr><b>Rights profiles:</b><hr>
        </td></tr>
        
        <tr><td colspan='2'>
        The following rights profiles will by default be assigned to a user when it is added to this group.
        </td></tr>
        ";
        
        //list all profiles as checkboxes
        foreach ($this->rightsprofile_db->getAllRightsprofiles() as $rightsprofile) {
            $checked = FALSE;
            if (in_array($rightsprofile->rightsprofile_id,$group->rightsprofile_ids)) $checked=TRUE;
            echo "<tr><td>".$rightsprofile->name."</td><td>".form_checkbox('rightsprofile_'.$rightsprofile->rightsprofile_id, 'rightsprofile_'.$rightsprofile->rightsprofile_id, $checked)."</td></tr>";
        }

echo    "
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
