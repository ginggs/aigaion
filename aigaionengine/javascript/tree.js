function collapse(topic_id,collapseCallback) {
    //collapse
    Element.hide('min_topic_'+topic_id);
    Element.show('plus_topic_'+topic_id);
    Element.hide('topic_children_'+topic_id);
    //call callback if not empty
    if (collapseCallback != "") {
        new Ajax.Updater('',collapseCallback,{evalScripts:true});
    }
}
function expand(topic_id,expandCallback) {
    //collapse
    Element.show('min_topic_'+topic_id);
    Element.hide('plus_topic_'+topic_id);
    Element.show('topic_children_'+topic_id);
    //call callback if not empty
    if (expandCallback != "") {
        new Ajax.Updater('',expandCallback,{evalScripts:true});
    }
}