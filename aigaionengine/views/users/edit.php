<?php
/**
views/users/edit

Shows a form for editing or adding users.

Parameters:
    $user=>the User object to be edited
    
If $user is null, the edit for will be restyled as an 'add new user' form

*/
$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('users/commit');
$isAddForm = False;
if (!isset($user)||($user==null)) {
    $isAddForm = True;
    echo form_hidden('action','add');
    $user = new User;
} else {
    echo form_hidden('action','edit');
    echo form_hidden('user_id',$user->user_id);
}

if ($isAddForm) {
    echo "<p class='header2'>Create a new user</p>";
} else {
    echo "<p class='header2'>Edit User Preferences</p>";
}

echo "
    <table width='100%'>
        
        <tr><td colspan='2'>
        <hr><b>Person details:</b><hr>
        </td></tr>

        <tr>
        <td>Initials</td>
        <td>"
        .form_input(array('name'=>'initials',
                          'size'=>'5',
                          'value'=>$user->initials))."
        </td>
        </tr>
        <tr>
        <td>Name</td>
        <td>"
        .form_input(array('name'=>'firstname',
                          'size'=>'10',
                          'value'=>$user->firstname))."
        </td>
        </tr>
        <tr>
        <td>Middle name</td>
        <td>"
        .form_input(array('name'=>'betweenname',
                          'size'=>'5',
                          'value'=>$user->betweenname))."
        </td>
        </tr>
        <tr>
        <td>Surname</td>
        <td>"
        .form_input(array('name'=>'surname',
                          'size'=>'15',
                          'value'=>$user->surname))."
        </td>
        </tr>
        <tr>
        <td>Abbreviation (about three characters)</td>
        <td>"
        .form_input(array('name'=>'abbreviation',
                          'size'=>'5',
                          'value'=>$user->abbreviation))."
        </td>
        </tr>
        <tr>
        <td>E-Mail Address</td>
        <td>"
        .form_input(array('name'=>'email',
                          'size'=>'20',
                          'value'=>$user->email))."
        </td>
        </tr>
        
        <tr><td colspan='2'>
        <hr><b>Account settings:</b><hr>
        </td></tr>
        
        <tr>
        <td>Login </td>
        <td>"
        .form_input(array('name'=>'login',
                          'size'=>'10',
                          'value'=>$user->login))."
        </td>
        </tr>
        <tr>
        <td>Password (leave blank for no change)</td>
        <td>"
        .form_input(array('name'=>'password',
                          'size'=>'10',
                          'value'=>''))."
        </td>
        </tr>
        <tr>
        <td>Re-type new password</td>
        <td>"
        .form_input(array('name'=>'password_check',
                          'size'=>'10',
                          'value'=>''))."
        </td>
        </tr>";
        
            #userrights change
//            if ($_SESSION["USER"]->hasRights("user_assign_rights")) {
                echo "
                    <tr><td colspan='2'>
                    <hr><b>User rights:</b><hr>
                    </td></tr>
                    
                    <tr><td colspan='2'>
                    <b>Note: an interface for assigning 'all rights from profile X in one go' will be added here.</b><hr>
                    </td></tr>";
        
                //list all userrights as checkboxes
                foreach (getAvailableRights() as $right=>$description) {
                    $checked = FALSE;
                    if (in_array($right,$user->assignedrights)) $checked=TRUE;
                    echo "<tr><td>".form_checkbox($right, $right, $checked).$right."</td><td>".$description."</td></tr>";
                }
//            }

echo "

        <tr><td colspan='2'><hr>
        <b>Note: an interface for assigning users to groups will be added here.</b>
        </td></tr>        
        
        <tr><td colspan='2'><hr>
        <b>Note: an interface for modifying the individual topic subscription will be added here.</b>
        </td></tr>        
        
        <tr><td colspan='2'>
        <hr><b>Display preferences:</b><hr/>
        </td></tr>
        
        <tr>
        <td>Theme</td>
        <td>
        ".form_dropdown('theme',
                        getThemes(),
                        $user->preferences["theme"])."
        </td>
        </tr>
        <tr>
        <td>Publication summary style</td>
        <td>
        ".form_dropdown('summarystyle',
                        array('author'=>'author first','title'=>'title first'),
                        $user->preferences["summarystyle"])."
        </td>
        </tr>
        <tr>
        <td>Author display style</td>
        <td>
        ".form_dropdown('authordisplaystyle',
                        array('fvl'=>'First [von] Last','vlf'=>'[von] Last, First','vl'=>'[von] Last'),
                        $user->preferences["authordisplaystyle"])."
        </td>
        </tr>
        <tr>
        <td>Number of publications per page</td>
        <td>
        ".form_dropdown('liststyle',
                        array('0'=>"All", "10"=>"10", '15'=>"15", '20'=>"20", '25'=>"25", '50'=>"50", '100'=>"100"),
                        $user->preferences["liststyle"])."
        </td>
        </tr>
        <tr>
        <td>Open attachments in new browser window</td>
        <td>
        ".form_checkbox('newwindowforatt','newwindowforatt',$user->preferences['newwindowforatt']=="TRUE")."
        </td>
        </tr>


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

