<?php
/**
views/attachments/add

Shows the upload form for new attachments.

Parameters:
    $publication=>the publication object for which the attachment is to be uploaded.
    
we assume that this view is not loaded if you don't have the appropriate read and edit rights

*/
$this->load->helper('form');

//the upload form is only for non-read-only servers
if (getConfigurationSetting('SERVER_NOT_WRITABLE')!= 'TRUE') {
    echo "<div class='editform'>";
    echo form_open_multipart('attachments/commit','',array('action'=>'add',
                                                           'pub_id'=>$publication->pub_id,
                                                           'isremote'=>False,
                                                           'ismain'=>False));
    //formname is used to check whether the POST data is coming from the right form.
    //not as security mechanism, but just to avoid painful bugs where data was submitted 
    //to the wrong commit and the database is corrupted
    echo form_hidden('formname','attachment');
    echo form_hidden('read_access_level','intern');
    echo form_hidden('edit_access_level','intern');
    echo form_hidden('user_id',getUserLogin()->userId());
    echo "<p class='header2'>Upload new attachment from this computer for \"".$publication->title."\"</p>";
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
}

echo "<div class='editform'>";
echo form_open_multipart('attachments/commit','',array('action'=>'add',
                                                       'pub_id'=>$publication->pub_id,
                                                       'isremote'=>True,
                                                       'ismain'=>False));
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','attachment');
echo form_hidden('user_id',getUserLogin()->userId());
echo form_hidden('read_access_level','intern');
echo form_hidden('edit_access_level','intern');
echo "<p class='header2'>Add new attachment (or web site) as a link, without uploading, for \"".$publication->title."\"</p>";
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

