<?php
/**
views/rightsprofiles/edit

Shows a form for editing or adding rightsprofiles.

Parameters:
    $rightsprofile=>the Rightsprofile object to be edited
    
If $rightsprofile is null, the edit form will be restyled as an 'add new rightsprofile' form
if $rightsprofile is not null, but $action == 'add', the edit form will be restyled as a
pre filled 'add new rightsprofile' form
*/
$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('rightsprofiles/commit');
$isAddForm = False;
if (!isset($rightsprofile)||($rightsprofile==null)||(isset($action)&&$action=='add')) {
    $isAddForm = True;
    echo form_hidden('action','add');
    if (!isset($action)||$action!='add')
        $rightsprofile = new Rightsprofile;
} else {
    echo form_hidden('action','edit');
    echo form_hidden('name',$rightsprofile->rightsprofile_id);
}

if ($isAddForm) {
    echo "<p class='header2'>Create a new rightsprofile</p>";
} else {
    echo "<p class='header2'>Edit rightsprofile \"".$rightsprofile->name."\"</p>";
}

echo "
    <table width='100%'>
        <tr>
        <td>Name</td>
        <td>"
        .form_input(array('name'=>'name',
                          'size'=>'10',
                          'value'=>$rightsprofile->name))."
        </td>
        </tr>

        <tr><td colspan='2'>
        <hr><b>User rights in this profile:</b><hr>
        </td></tr>
        ";
        
    //list all userrights as checkboxes
    foreach (getAvailableRights() as $right=>$description) {
        $checked = FALSE;
        if (in_array($right,$rightsprofile->rights)) $checked=TRUE;
        echo "<tr><td>".form_checkbox($right, $right, $checked).$right."</td><td>".$description."</td></tr>";
    }

echo "
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

