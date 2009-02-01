<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
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
$userlogin  = getUserLogin();
$user       = $this->user_db->getByID($userlogin->userID());

if (!isset($note)||($note==null)||(isset($action)&&$action=='add')) {
    $isAddForm = True;
    echo form_hidden('action','add');
    if (!isset($note)||($note==null)) {
        $note = new Note;
        echo form_hidden('pub_id',$pub_id);
    } else {
        echo form_hidden('pub_id',$note->pub_id);
    }
    echo form_hidden('user_id',$userlogin->userId());
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
        <tr>
          <td colspan='2'><label for='text'>Text:</label><br/>
            <script type="text/javascript">
              tinyMCE.init({
                mode : "textareas",
                language : "<?php echo $userlogin->getPreference('language');?>",
                theme : "simple",
                plugins : "",
                editor_selector : "richeditor"
              });
            </script>
<?php 
  echo form_textarea(array('name' => 'text','id' => 'text','cols' => '70','rows' => '7','value' => $note->text,'class'=>"richeditor")); 
?>
            <br/><br/>
          </td>
        </tr>
<?php
  if (!$isAddForm)
  {
    $read_icon = $this->accesslevels_lib->getReadAccessLevelIcon($note);
    $edit_icon = $this->accesslevels_lib->getEditAccessLevelIcon($note);
    
    $readrights = $this->ajax->link_to_remote($read_icon,
                  array('url'     => site_url('/accesslevels/toggle/note/'.$note->note_id.'/read'),
                        'update'  => 'note_rights_'.$note->note_id
                       )
                  );
    $editrights = $this->ajax->link_to_remote($edit_icon,
                  array('url'     => site_url('/accesslevels/toggle/note/'.$note->note_id.'/edit'),
                        'update'  => 'note_rights_'.$note->note_id
                       )
                  );

?>
        <tr>
          <td>Access rights: <?php echo"<span id='note_rights_".$note->note_id."' title='note read / edit rights'>r:".$readrights."e:".$editrights."</span>";?><br/><br/></td>
        </tr>
        
<?php
  //$accesslevels = $this->accesslevels_lib->getAccessLevelSummary($note);
  //echo anchor('accesslevels/edit/note/'.$note->note_id,$accesslevels,array('title'=>'click to modify access levels'));
  //echo "<span id='note_rights_".$note->note_id."' title='note read / edit rights'>".$readrights.$editrights."</span>]";
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
echo form_open('publications/show/'.$note->pub_id);
echo form_submit('cancel','Cancel');
echo form_close();
?>
</div>

