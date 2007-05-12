<?php        
/**
views/attachments/download

This view forces the download of an attachment.

This view is supposed to be called WITHOUT a surrounding HTML page. When this view is loaded, it must be the only content 
of the page.

Parameters:
    $attachment=>the Attachment object that is to be downloaded.

we assume that this view is not loaded if you don't have the appropriate read rights
*/

if ($attachment->isremote) {
} else {
    if (!file_exists(AIGAION_ATTACHMENT_DIR."/".$attachment->location)) {
        appendErrorMessage("Attachment file could not be found: ".AIGAION_ATTACHMENT_DIR."/".$attachment->location."<br>");
        redirect('');
    } else {
        $this->load->helper('download');
        $this->output->set_header("Content-type: ".$attachment->mime);
        $this->output->set_header('Content-Disposition: attachment; filename="'.$attachment->name.'"');
        $this->output->set_header("Cache-Control: cache, must-revalidate");
        //$this->output->set_header("Content-Length: ".strlen($data)); ?
        $this->output->set_header("Pragma: public");
        //force_download($name, $data);
        $data = file_get_contents(AIGAION_ATTACHMENT_DIR."/".$attachment->location); // Read the file's contents
        //$name = $attachment->name;
        echo $data;
    }
}
?>