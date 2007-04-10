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
/* subscriptionCallback is an url that must be suffixed with '/(un)subscribe/topic_id/user_id' */
function toggleSubscription(user_id,topic_id,subscriptionCallback) {
    //toggle class and call async (un)subscription controller
    if($('subscription_'+topic_id).className=='subscribedtopic') {
        //was subscribed 
        $('subscription_'+topic_id).className = 'unsubscribedtopic';
        new Ajax.Updater('',subscriptionCallback+'/unsubscribe/'+topic_id+'/'+user_id,{evalScripts:true});
    } else {
        //was unsubscribed 
        $('subscription_'+topic_id).className = 'subscribedtopic';
        new Ajax.Updater('',subscriptionCallback+'/subscribe/'+topic_id+'/'+user_id,{evalScripts:true});
    }
}