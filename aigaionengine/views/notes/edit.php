<?php
/**
views/notes/edit

Shows a form for editing notes.

Parameters:
    $note=>the Note object to be edited
    
If $note is null, the edit for will be restyled as an 'add new note' form
if $note is not null, but $action == 'add', the edit form will be restyled as a
pre filled 'add new note' form
*/

$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('notes/commit');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','note');
$isAddForm = False;
if (!isset($note)||($note==null)||(isset($action)&&$action=='add')) {
    $isAddForm = True;
    echo form_hidden('action','add');
    if (!isset($note)||($note==null)) {
        $note = new Note;
        echo form_hidden('pub_id',$pub_id);
    } else {
        echo form_hidden('pub_id',$note->pub_id);
    }
    echo form_hidden('user_id',getUserLogin()->userId());
} else {
    echo form_hidden('action','edit');
    echo form_hidden('note_id',$note->note_id);
    echo form_hidden('user_id',$note->user_id);
    echo form_hidden('pub_id',$note->pub_id);
}

if ($isAddForm) {
    echo "<p class='header2'>Add a note</p>";
} else {
    echo "<p class='header2'>Change note</p>";
}
//validation feedback
echo $this->validation->error_string;
?>
    <table>
        <tr><td><label for='text'>Text</label></td>
            <td>
<?php echo form_textarea(array('name'=>'text','cols'=>'70','rows'=>'7','value'=>$note->text)); ?>
            </td>
        </tr>
<?php
if ($note->user_id==getUserLogin()->userId() || getUserLogin()->hasRights('note_edit_all')) {
?>            
        <tr><td><label for='read_access_level'>Read access level</label></td>
            <td>
<?php
$options = array('private'=>'private','intern'=>'intern','public'=>'public');
echo form_dropdown('read_access_level',$options,$note->read_access_level);
?>
            </td>
        </tr>                
        <tr><td><label for='edit_access_level'>Edit access level</label></td>
            <td>
<?php
echo form_dropdown('edit_access_level',$options,$note->edit_access_level);
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

