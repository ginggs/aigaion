<?php        
if (!isset($onlyIfSubscribed) || !$onlyIfSubscribed || $topic->publicationIsSubscribed) {
    echo $topic->name."\n";
}
?>