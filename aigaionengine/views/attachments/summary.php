<!-- Single attachment displays -->
<?php
/**
views/attachments/summary

Shows a summary of an attachment: download link, name, delete link, main or noet, note, etc

Parameters:
    $attachment=>the Attachment object that is to be shown
*/
    if ($attachment->isremote) {
        echo "<a href='".prep_url($attachment->location)."' target='_blank'><img title='Download ".htmlentities($attachment->name,ENT_QUOTES)."' class='icon' src='".getIconUrl("attachment_html.gif")."'/></a>\n";
    } else {
        $iconUrl = getIconUrl("attachment.gif");
        $extension=strtolower(substr(strrchr($attachment->location,"."),1));
        if (iconExists("attachment_".$extension.".gif")) {
            $iconUrl = getIconUrl("attachment_".$extension.".gif");
        }
        echo anchor('attachments/view/'.$attachment->att_id,"<img class='icon' src='".$iconUrl."'/>" ,array('title'=>'Download '.$attachment->name))."\n";
    }
    $name = $attachment->name;
    if (strlen($name)>31) {
        $name = substr($name,0,30)."...";
    }
    echo $name;
    echo "&nbsp;&nbsp;".anchor('attachments/delete/'.$attachment->att_id,"[delete]",array('title'=>'Delete '.$attachment->name));
    echo "&nbsp;".anchor('attachments/edit/'.$attachment->att_id,"[edit]",array('title'=>'Edit information for '.$attachment->name));
    if ($attachment->note!='') {
        echo "<br>&nbsp;&nbsp;&nbsp;(".$attachment->note.")";
    }
?>
<!-- End of single attachment displays -->
