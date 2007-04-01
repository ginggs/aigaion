function collapse(topic_id,collapseCallback) {
    //collapse
    Element.hide('min_topic_'+topic_id);
    Element.show('plus_topic_'+topic_id);
    Element.hide('topic_children_'+topic_id);
    //call callback if not empty
}
function expand(topic_id,collapseCallback) {
    //collapse
    Element.show('min_topic_'+topic_id);
    Element.hide('plus_topic_'+topic_id);
    Element.show('topic_children_'+topic_id);
    //call callback if not empty
}