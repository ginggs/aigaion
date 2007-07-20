<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php 
$userlogin=getUserLogin();
$user_id = $userlogin->userId();
$this->load->helper('form');
?>
<div class='header1'>Edit access levels</div>
<p>When you modify access levels of individual objects, this may have consequences for the final 'effective' access level of other objects. For example, when you set a publication to private, the effective access level of all objects belonging to that publication will be set to private as well.</p>
<p>On the other hand, when you edit the read access level of for example an attachment, and the new level is higher than that of the publication it belongs to, the <b>actual</b> read level of the publication is updated as well!</p>
<p>Note that the effective access levels are shown on the left; the access levels defined per individual object are shown on the right. Editing of access levels is done through the right column.</p>
<p><b>Unsure how the access levels turned out?</b> The column on the left shows which objects are effectively accessible with what levels!</p>
<p>Example: publication is 'intern'; attachment is 'intern'. SET attachment to 'public' --&gt; publication will become 'public' as well.</p>
<p>Example: publication is 'intern'; attachment is 'intern'. SET publication to 'private' --&gt; attachment stays 'intern', but EFFECTIVE access level of attachment becomes 'private'. When you set the publication to 'intern' again, the effective access level of the attachment reverts to 'intern'.</p>
<p>Example: attachment read is 'public', attachment edit is 'intern'. Set attachment read to 'private' --&gt; attachment edit will also change to 'private'.</p>
<p>Example: a publication has edit level 'intern'. You are not the owner. You change the edit level to 'private'. --&gt; Subsequently, you can no longer edit that publication :o)</p>
<br/>
<?php 
echo anchor('publications/show/'.$publication->pub_id,'[back to publication]'); ?>
<br/><br/>
<div style='border:1px solid black;'>
    <div style='border:1px solid black;'>
        <b>Legenda</b>
    </div>
    <?php
    echo "
    r:<img class='al_icon' src='".getIconurl('al_public.gif')."'/> read public<br> 
    r:<img class='al_icon' src='".getIconurl('al_intern.gif')."'/> read intern<br> 
    r:<img class='al_icon' src='".getIconurl('al_private.gif')."'/> read private<br> 
    e:<img class='al_icon' src='".getIconurl('al_public.gif')."'/> edit public<br> 
    e:<img class='al_icon' src='".getIconurl('al_intern.gif')."'/> edit intern<br> 
    e:<img class='al_icon' src='".getIconurl('al_private.gif')."'/> edit private<br> 
    - If nothing is shown, access level is 'intern'.<br>
    ";
    ?>
</div>
<br/>
<div style='border:1px solid grey'>
<table>
    <tr >
        <td colspan='2'>
            <img src='<?php echo getIconUrl('info.gif'); ?>' title='Effective access levels (after combining all relevant access levels)'/>
            <br>Effective
        </td>
        <td>
            <img src='<?php echo getIconUrl('info.gif'); ?>' title='Object'/>
            <br>Object
        </td>
        <td>
            <img src='<?php echo getIconUrl('info.gif'); ?>' title='Owner of object (only owner can change objects with private edit levels...)'/>
            <br>Owner
        </td>
        <td colspan='2'>
            <img src='<?php echo getIconUrl('info.gif'); ?>' title='Per-object access levels'/>
            <br>Individual per-object access levels
        </td>
        
    </tr>

    <tr >
        <td>
        </td>
        <td>
        </td>
        <td style='padding-left:0.5em;'>
           <i><br>Publication:</i>
        </td>
    </tr>
    <tr <?php
        if ($type=='publication')echo 'style="background:#dfdfff;" ';
        ?>>
        <td>
            r:<img class='al_icon' src='<?php echo getIconurl('al_'.$publication->derived_read_access_level.'_grey.gif'); ?>'/>
        </td>
        <td>
            e:<img class='al_icon' src='<?php echo getIconurl('al_'.$publication->derived_edit_access_level.'_grey.gif'); ?>'/>
        </td>
        <td style='padding-left:0.5em;' class='header2'>
            <?php echo $publication->title; ?>
        </td>
        <td style='padding-left:0.5em;'>
            <?php 
            if ($publication->user_id==$user_id) {
                echo '<span class="owner_self">';
            } else {
                echo '<span class="owner_other">';
            }
            echo '['.getAbbrevForUser($publication->user_id).']</span>'; 
            ?>
        </td>
        <td>
            <?php
            echo $this->accesslevels_lib->getAccessLevelEditPanel($publication,'publication',$publication->pub_id);
            ?>
        </td>
    </tr>

    <tr>
        <td>
        </td>
        <td>
        </td>
        <td style='padding-left:2em;'>
           <i><br>Attachments:</i>
        </td>
    </tr>
<?php
foreach ($publication->getAttachments() as $attachment) {
?>
    <tr <?php
        if (($type=='attachment')&&($object_id==$attachment->att_id))echo 'style="background:#dfdfff;" ';
        ?>>
        <td>
            r:<img class='al_icon' src='<?php echo getIconurl('al_'.$attachment->derived_read_access_level.'_grey.gif'); ?>'/>
        </td>
        <td>
            e:<img class='al_icon' src='<?php echo getIconurl('al_'.$attachment->derived_edit_access_level.'_grey.gif'); ?>'/>
        </td>
        <td style='padding-left:2em;' class='header2'>
            <?php echo $attachment->name; ?>
        </td>
        <td style='padding-left:0.5em;'>
            <?php 
            if ($attachment->user_id==$user_id) {
                echo '<span class="owner_self">';
            } else {
                echo '<span class="owner_other">';
            }
            echo '['.getAbbrevForUser($attachment->user_id).']</span>'; 
            ?>
        </td>
        <td>
            <?php
            echo $this->accesslevels_lib->getAccessLevelEditPanel($attachment,'attachment',$attachment->att_id);
            ?>
        </td>
    </tr>
<?php  
}
?>

    <tr>
        <td>
        </td>
        <td>
        </td>
        <td style='padding-left:2em;'>
           <i><br>Notes:</i>
        </td>
    </tr>
<?php
foreach ($publication->getNotes() as $note) {
?>
    <tr <?php
        if (($type=='note')&&($object_id==$note->note_id))echo 'style="background:#dfdfff;" ';
        ?>>
        <td>
            r:<img class='al_icon' src='<?php echo getIconurl('al_'.$note->derived_read_access_level.'_grey.gif'); ?>'/>
        </td>
        <td>
            e:<img class='al_icon' src='<?php echo getIconurl('al_'.$note->derived_edit_access_level.'_grey.gif'); ?>'/>
        </td>
        <td style='padding-left:2em;' class='header2'>
            <?php echo $note->text; ?>
        </td>
        <td style='padding-left:0.5em;'>
            <?php 
            if ($note->user_id==$user_id) {
                echo '<span class="owner_self">';
            } else {
                echo '<span class="owner_other">';
            }
            echo '['.getAbbrevForUser($note->user_id).']</span>'; 
            ?>
        </td>
        <td>
            <?php
            echo $this->accesslevels_lib->getAccessLevelEditPanel($note,'note',$note->note_id);
            ?>
        </td>
    </tr>
<?php  
}
?>


</table>
</div>

