<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/attachments/edit

Shows the edit form for attachments.

Parameters:
    $attachment=>the attachment object to be edited.
    
we assume that this view is not loaded if you don't have the appropriate read and edit rights
*/
$this->load->helper('form');
$userlogin  = getUserLogin();
$user       = $this->user_db->getByID($userlogin->userID());
echo "<div class='editform'>";
echo form_open_multipart('attachments/commit','',array('action'=>'edit',
                                                       'att_id'=>$attachment->att_id,
                                                       'isremote'=>$attachment->isremote,
                                                       'ismain'=>$attachment->ismain,
                                                       'location'=>$attachment->location,
                                                       'pub_id'=>$attachment->pub_id,
                                                       'user_id'=>$attachment->user_id,
                                                       'mime'=>$attachment->mime));
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','attachment');
echo form_hidden('user_id',$attachment->user_id);
echo "<p class='header2'>Edit attachment info for \"".$attachment->name."\"</p>";
echo "
    <table>
        <tr><td><label for='name'>Set internal name</label></td>
            <td>
     ";
echo form_input(array('name'=>'name','size'=>'30','value'=>$attachment->name));
echo "
            </td>
        </tr>
        <tr><td><label for='note'>Note</label></td>
            <td>
     ";
echo form_input(array('name'=>'note','size'=>'30','value'=>$attachment->note));
echo "
            </td>
        </tr>
        <tr><td>";
echo form_submit('submit','Change');
echo "
        </td>
        </tr>
    </table>
     ";
echo form_close();
echo form_open('publications/show/'.$attachment->pub_id);
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";

?>