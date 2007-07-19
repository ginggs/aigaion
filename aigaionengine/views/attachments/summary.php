<!-- Single attachment displays -->
<?php
/**
views/attachments/summary

Shows a summary of an attachment: download link, name, delete link, main or not, note, etc

Parameters:
    $attachment=>the Attachment object that is to be shown

access rights: we presume that this view is not loaded when the user doesn't have the read rights.
as for the edit rights: they determine which edit links are shown.
*/
$userlogin  = getUserLogin();
$user       = $this->user_db->getByID($userlogin->userID());
        
    if ($attachment->isremote) {
        echo "<a href='".prep_url($attachment->location)."' target='_blank'><img title='Download ".htmlentities($attachment->name,ENT_QUOTES)."' class='icon' src='".getIconUrl("attachment_html.gif")."'/></a>\n";
    } else {
        $iconUrl = getIconUrl("attachment.gif");
        $extension=strtolower(substr(strrchr($attachment->location,"."),1));
        if (iconExists("attachment_".$extension.".gif")) {
            $iconUrl = getIconUrl("attachment_".$extension.".gif");
        }
        echo anchor('attachments/single/'.$attachment->att_id,"<img class='icon' src='".$iconUrl."'/>" ,array('title'=>'Download '.$attachment->name))."\n";
    }
    $name = $attachment->name;
    if (strlen($name)>31) {
        $name = substr($name,0,30)."...";
    }
    echo $name;
    $accesslevels = $this->accesslevels_lib->getAccessLevelSummary($attachment);
    echo anchor('accesslevels/edit/attachment/'.$attachment->att_id,$accesslevels,array('title'=>'click to modify access levels'));
        
    //the block of edit actions: dependent on user rights
    $userlogin = getUserLogin();
    if (    ($userlogin->hasRights('attachment_edit'))
         && 
            $this->accesslevels_lib->canEditObject($attachment)         
        ) 
    {
        echo "&nbsp;&nbsp;".anchor('attachments/delete/'.$attachment->att_id,"[delete]",array('title'=>'Delete '.$attachment->name));
        echo "&nbsp;".anchor('attachments/edit/'.$attachment->att_id,"[edit]",array('title'=>'Edit information for '.$attachment->name));
        if ($attachment->ismain) {
            echo "&nbsp;".anchor('attachments/unsetmain/'.$attachment->att_id,"[unset main]",array('title'=>'Unset as main attachment'));
        } else {
            echo "&nbsp;".anchor('attachments/setmain/'.$attachment->att_id,"[set main]",array('title'=>'Set as main attachment'));
        }
    }
    
    //always show note
    if ($attachment->note!='') {
        echo "<br>&nbsp;&nbsp;&nbsp;(".$attachment->note.")";
    }
?>
<!-- End of single attachment displays -->
