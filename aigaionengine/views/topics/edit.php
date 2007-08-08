<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
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

