<?php        
if (!isset($notIfSubscribed) || !$notIfSubscribed || !$topic->publicationIsSubscribed) {
    if (!isset($noWrap) || !$noWrap) {
        echo "<span id='subscription_".$topic->publication_id."_".$topic->topic_id."'>";
    }
    echo "<span class='lightlink' onclick=\"";
    echo $this->ajax->remote_function(
                        array('url' => site_url("publications/subscribe/".$topic->publication_id."/".$topic->topic_id),
                              'update' => "subscription_".$topic->publication_id."_".$topic->topic_id
                              )
                       );
    echo "\"><span class='unsubscribedtopic'>".$topic->name."</span></span>\n";
    if (!isset($noWrap) || !$noWrap) {
        echo "</span>";
    }
}
?>