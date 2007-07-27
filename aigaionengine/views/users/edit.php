<?php
/**
views/users/edit

Shows a form for editing or adding users.

Parameters:
    $user=>the User object to be edited
    
If $user is null, the edit for will be restyled as an 'add new user' form
if $user is not null, but $action == 'add', the edit form will be restyled as a
pre filled 'add new user' form

we assume that this view is not loaded if you don't have the appropriate read and edit rights

the rights-checkboxes and group assignment however are still visible only contingent on the appropriate rights
*/
$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('users/commit');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','user');
$isAddForm = False;
$userlogin  = getUserLogin();

if (!isset($user)||($user==null)||(isset($action)&&$action=='add')) {
    $isAddForm = True;
    echo form_hidden('action','add');
    if (!isset($action)||$action!='add')
        $user = new User;
} else {
    echo form_hidden('action','edit');
    //hidden fields which should be remembered for commit, but which are not modifyable:
    echo form_hidden('user_id',$user->user_id);
    echo form_hidden('lastreviewedtopic',$user->lastreviewedtopic);
}

if ($isAddForm) {
    echo "<p class='header2'>Create a new user</p>";
} else {
    echo "<p class='header2'>Edit User Preferences</p>";
}

//validation feedback
echo $this->validation->error_string;

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
        <td>First name</td>
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
                          'value'=>$user->login,
                          'AUTOCOMPLETE'=>'off'))."
        </td>
        </tr>
        <tr>
        <td>Password (leave blank for no change)</td>
        <td>"
        .form_password(array('name'=>'password',
                          'size'=>'10',
                          'value'=>'',
                          'AUTOCOMPLETE'=>'off'))."
        </td>
        </tr>
        <tr>
        <td>Re-type new password</td>
        <td>"
        .form_password(array('name'=>'password_check',
                          'size'=>'10',
                          'value'=>'',
                          'AUTOCOMPLETE'=>'off'))."
        </td>
        </tr>";

if ($userlogin->hasRights('user_edit_all')) {
    echo "        
        <tr>
        <td>Anonymous account (check if this account is an anonymous (guest) account)</td>
        <td>"
        .form_checkbox('isAnonymous','isAnonymous',$user->isAnonymous)."
        </td>
        </tr>";
}


if ($userlogin->hasRights('user_edit_all')) {
    echo "   
        <tr><td colspan='2'>
        <hr><b>Groups:</b><hr>
        The groups to which this user belongs. When you add this user to a group that it was previously not a member of,
        all rights associated with that group will be appended to the user rights of this user.
        </td></tr>
        ";
        
        //list all groups as checkboxes
        foreach ($this->group_db->getAllGroups() as $group) {
            $checked = FALSE;
            if (in_array($group->group_id,$user->group_ids)) $checked=TRUE;
            echo "<tr><td>".$group->name."</td><td>".form_checkbox('group_'.$group->group_id, 'group_'.$group->group_id, $checked)."</td></tr>";
        }
}

        
if ($userlogin->hasRights('user_assign_rights')) {
    $rightsprofiles = $this->rightsprofile_db->getAllRightsprofiles();
        echo "   
    
            <tr><td colspan='2'>
            <hr><b>User rights:</b><hr>
            </td></tr>
            
            <tr>
            <td>Check all rights:</td>
            <td>
            ";
    echo $this->ajax->button_to_function('Check all', "selectAllRights();");
    
    echo "
            </td>
            </tr>

            <tr>
            <td>Uncheck all rights:</td>
            <td>
            ";
    echo $this->ajax->button_to_function('Uncheck all', "deselectAllRights();");
    
    echo "
            </td>
            </tr>

            <tr>
            <td>Check all rights from:</td>
            <td>
            ";
    $options = array(''=>'');
    foreach ($rightsprofiles as $profile) {
        $options[$profile->name] = $profile->name;
    }
    echo form_dropdown('checkrightsprofile', $options,null,"onchange='selectProfile();' id='checkrightsprofile'");
    
    echo "
            </td>
            </tr>
            
            <tr>
            <td>Uncheck all rights from:</td>
            <td>
            ";
            
    echo form_dropdown('uncheckrightsprofile', $options,null,"onchange='deselectProfile();'  id='uncheckrightsprofile'");
    
    echo "
            </td>
            </tr>
            
            <tr>
            <td>Restore old state:</td>
            <td>
            ";
    echo $this->ajax->button_to_function('Restore', "restoreRights();");
    
    echo "
            </td>
            </tr>
            
            <tr><td colspan=2>
            <hr>
            </td></tr>
            ";
            
            //list all userrights as checkboxes
            foreach (getAvailableRights() as $right=>$description) {
                $checked = FALSE;
                if (in_array($right,$user->assignedrights)) {
                    $checked=TRUE;
                }
                $classes = 'rightbox';
                foreach ($rightsprofiles as $profile) {
                    if (in_array($right,$profile->rights)) {
                        $classes .= ' '.$profile->name;
                    }
                }
                if ($checked) {
                    $classes .= ' rightbox_on';
                } else {
                    $classes .= ' rightbox_off';
                }
                $data=array('name'=>$right,'id'=>$right,'value'=>$right,'checked'=>$checked,'class'=>$classes);
                echo "<tr><td>".form_checkbox($data).$right."</td><td>".$description."</td></tr>";
            }
}

$theme_array = array();
$availableThemes = getThemes();
foreach ($availableThemes as $theme)
{
  $theme_array[$theme] = $theme;
}
echo "
        
        <tr><td colspan='2'>
        <hr><b>Display preferences:</b><hr/>
        </td></tr>
        
        <tr>
        <td>Theme</td>
        <td>
        ".form_dropdown('theme',
                        $theme_array,
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

