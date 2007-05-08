<?php
/**
views/attachments/edit

Shows the edit form for attachments.

Parameters:
    $attachment=>the attachment object to be edited.
*/
$this->load->helper('form');
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
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";

?>
