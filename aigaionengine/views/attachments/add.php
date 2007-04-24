<?php
/**
views/attachments/add

Shows the upload form for new attachments.

Parameters:
    $publication=>the publication object for which the attachment is to be uploaded.
*/
$this->load->helper('form');
echo "<div class='editform'>";
echo form_open_multipart('attachments/commit','',array('action'=>'add',
                                                       'pub_id'=>$publication->data->pub_id,
                                                       'isremote'=>False,
                                                       'ismain'=>False));
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','attachment');
echo "<p class='header2'>Upload new attachment from this computer for \"".$publication->data->title."\"</p>";
echo "
    <table>
        <tr><td><label for='upload'>Select a file...</label></td>
            <td>
     ";
echo form_upload(array('name'=>'upload','size'=>'30'));
echo "
            </td>
        </tr>
        <tr><td><label for='name'>Set new name (blank: keep original name)...</label></td>
            <td>
     ";
echo form_input(array('name'=>'name','size'=>'30'));
echo "
            </td>
        </tr>
        <tr><td><label for='note'>Note</label></td>
            <td>
     ";
echo form_input(array('name'=>'note','size'=>'30'));
echo "
            </td>
        </tr>
        <tr><td>";
echo form_submit('submit','Upload attachment');
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

echo "<div class='editform'>";
echo form_open_multipart('attachments/commit','',array('action'=>'add',
                                                       'pub_id'=>$publication->data->pub_id,
                                                       'isremote'=>True,
                                                       'ismain'=>False));
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','attachment');
echo "<p class='header2'>Add new attachment (or web site) as a link, without uploading, for \"".$publication->data->title."\"</p>";
echo "
    <table>
        <tr><td><label for='location'>Location of file or web address</label></td>
            <td>
     ";
echo form_input(array('name'=>'location','size'=>'30'));
echo "
            </td>
        </tr>
        <tr><td><label for='name'>Set internal name (blank: keep original name)...</label></td>
            <td>
     ";
echo form_input(array('name'=>'name','size'=>'30'));
echo "
            </td>
        </tr>
        <tr><td><label for='note'>Note</label></td>
            <td>
     ";
echo form_input(array('name'=>'note','size'=>'30'));
echo "
            </td>
        </tr>
        <tr><td>";
echo form_submit('submit','Add file link');
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

