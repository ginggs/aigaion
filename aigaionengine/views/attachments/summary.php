<!-- Single attachment displays -->
<?php
/**
views/attachments/summary

Shows a summary of an attachment: download link, name, delete link, main or noet, note, etc

Parameters:
    $attachment=>the Attachment object that is to be shown
*/
    $iconUrl = getIconUrl("attachment.gif");
    $extension=strtolower(substr(strrchr($attachment->location,"."),1));
    if (iconExists("attachment_".$extension.".gif")) {
        $iconUrl = getIconUrl("attachment_".$extension.".gif");
    }
    if ($attachment->isremote) {
        echo "<a href='".prep_url($attachment->location)."' target='_blank'><img title='Download ".htmlentities($attachment->name,ENT_QUOTES)."' class='icon' src='".$iconUrl."'/></a>\n";
    } else {
        echo anchor('attachments/view/'.$attachment->att_id,"<img class='icon' src='".$iconUrl."'/>" ,array('title'=>'Delete '.$attachment->name))."\n";
    }
    $name = $attachment->name;
    if (strlen($name)>31) {
        $name = substr($name,0,30)."...";
    }
    echo "&nbsp;".$name."&nbsp;";
    echo anchor('attachments/delete/'.$attachment->att_id,"<img title='Delete ".htmlentities($attachment->name,ENT_QUOTES)."' class='icon' src='".getIconUrl("delete.gif")."'/>")."\n";
    echo "\n";
?>
<!-- End of single attachment displays -->
