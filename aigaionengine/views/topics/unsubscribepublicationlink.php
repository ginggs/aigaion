<?php
if (!isset($onlyIfSubscribed) || !$onlyIfSubscribed || $topic->publicationIsSubscribed) {
    if (!isset($noWrap) || !$noWrap) {
        echo "<span id='subscription_".$topic->publication_id."_".$topic->topic_id."'>";
    }
    echo "<span class='lightlink' onclick=\"";
    echo $this->ajax->remote_function(
                        array('url' => site_url("publciations/unsubscribe/".$topic->topic_id."/".$topic->topic_id),
                              'update' => "subscription_".$topic->publication_id."_".$topic->topic_id
                              )
                       );
    echo "\"><span class='subscribedtopic'>".$topic->name."</span></span>\n";
    if (!isset($noWrap) || !$noWrap) {
        echo "</span>";
    }
}
?>