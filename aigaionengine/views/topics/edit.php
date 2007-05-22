<?php
/**
views/topics/edit

Shows a form for editing topics.

Parameters:
    $topic=>the Topic object to be edited
    
If $topic is null, the edit for will be restyled as an 'add new topic' form
if $topic is not null, but $action == 'add', the edit form will be restyled as a
pre filled 'add new topic' form
*/

$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('topics/commit');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','topic');
$isAddForm = False;
$userlogin  = getUserLogin();
$user       = $this->user_db->getByID($userlogin->userID());

if (!isset($topic)||($topic==null)||(isset($action)&&$action=='add')) {
    $isAddForm = True;
    echo form_hidden('action','add');
    if (!isset($action)||$action!='add')
        $topic = new Topic;
    echo form_hidden('user_id',$userlogin->userId());
} else {
    echo form_hidden('action','edit');
    echo form_hidden('topic_id',$topic->topic_id);
    echo form_hidden('user_id',$topic->user_id);
}


if ($isAddForm) {
    echo "<p class='header2'>Add a topic</p>";
} else {
    echo "<p class='header2'>Change topic \"".$topic->name."\"</p>";
}
//validation feedback
echo $this->validation->error_string;
?>
    <table>
        <tr><td><label for='name'>Name</label></td>
            <td>
<?php echo form_input(array('name'=>'name','size'=>'30','value'=>$topic->name)); ?>
            </td>
        </tr>

        <tr><td><label for='parent_id'>Parent</label></td>
            <td>
<?php     

    $config = array('onlyIfUserSubscribed'=>True,
                    'includeGroupSubscriptions'=>True,
                    'user'=>$user);
echo $this->load->view('topics/optiontree',
                       array('topics'   => $this->topic_db->getByID(1,$config),
                            'showroot'  => True,
                            'depth'     => -1,
                            'selected'  => $topic->parent_id
                            ),  
                       true)."\n";
?>
            </td>
        </tr>
        <tr><td><label for='description'>Description</label></td>
            <td>
<?php
    echo form_textarea(array('name'=>'description','cols'=>'70','rows'=>'7','value'=>$topic->description));
?>
            </td>
        </tr>                
        <tr><td><label for='url'>URL</label></td>
            <td>
<?php
echo form_input(array('name'=>'url','size'=>'30','value'=>$topic->url));
?>
            </td>
        </tr>    
<?php
if ($topic->user_id==$userlogin->userId() || $userlogin->hasRights('topic_edit_all') || $isAddForm) {
?>            
        <tr><td><label for='read_access_level'>Read access level</label></td>
            <td>
<?php
$options = array('private'=>'private','intern'=>'intern','group'=>'group','public'=>'public');
echo form_dropdown('read_access_level',$options,$topic->read_access_level);
?>
            </td>
        </tr>                
        <tr><td><label for='edit_access_level'>Edit access level</label></td>
            <td>
<?php
echo form_dropdown('edit_access_level',$options,$topic->edit_access_level);
?>
            </td>
        </tr>                
        <tr><td><label for='group_id'>Group (only if 'group' selected as access level)</label></td>
            <td>
<?php
$options = array();
foreach ($user->group_ids as $group_id) {
  $group = $this->group_db->getByID($group_id);
    $options[$group_id] = $group->name;
}
echo form_dropdown('group_id',$options,$topic->group_id);
?>
            </td>
        </tr>                
<?php
}
?>
        <tr><td>
<?php
if ($isAddForm) {
    echo form_submit('submit','Add');
} else {
    echo form_submit('submit','Change');
}
?>
        </td>
        </tr>
    </table>
<?php
echo form_close();
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
?>
</div>

