<?php
/** This class holds the data structure of a topic. This data structure has a 'lazy loading tree structure',
i.e. the children of this topic are by default NOT stored in the class, but when retrieved by a call to 'getChildren'
they will be stored in the topic object.

At construction time a publication_id can be provided. If this is the case, 
the topic is to be considered a 'publication classification tree'.
Every subtopic in the tree will be marked with a boolean 
stating whether the publication is subscribed to that topic.

Database access for topics is done through the Topic_db library */

class Topic {
  
    #ID
    var $topic_id        = '';
    #content variables; to be changed by user when necessary
    var $parent_id          = -1;
    var $name               = '';
    var $description        = '';
    var $url                = '';
    #system variables, not to be changed by user
    var $children           = null; //array of Topic's
    var $CI                 = null; //link to the CI base object
    
    var $isClassificationTree = False;
    var $publication_id            = '';
    var $publicationIsSubscribed   = False; //if this topic is a classification tree, this variable tells whether the publication is assigned to this topic
    
    function Topic()
    {
        $this->CI =&get_instance(); 
    }
    

    /** Return an array of Topic's. Note: not loaded until requested by this function. Every call to this
    function will return the same Topic objects (same pointers). */
    function getChildren() {
        if ($this->children == null) {
            $this->children = $this->CI->topic_db->getChildren($this->topic_id, $this->publication_id);
        }
        return $this->children;
    }

    /** Return a Topic. Note: every call to this function will return a NEW Topic object. */
    function getParent() {
        if ($this->parent_id == -1) {
            $this->parent_id = $this->CI->topic_db->getParent($this->topic_id);
        }
        if ($this->parent_id == null) return null;
        $p = $this->CI->topic_db->getByID($this->parent_id, $this->publication_id);
        return $p;
    }
  
    /** if this topic is a subscription tree, use this method to set the publication to being subscribed to this
    topic and commit it to the database. Afterwards, the topic tree has been updated and the database also. */  
    function subscribePublication() {
        if (!$this->isClassificationTree) return;
        $this->CI->topic_db->subscribePublication($this->publication_id, $this->topic_id);
        $this->publicationIsSubscribed = True;
        $parent = $this->getParent();
        if ($parent != null) {
            $parent->subscribePublication();
        }
    }    

    /** if this topic is a subscription tree, use this method to set the publciation to being unsubscribed from this
    topic and commit it to the database. Afterwards, the topic tree has been updated and the database also. */  
    function unsubscribePublication() {
        if (!$this->isClassificationTree) return;
        //don't accept unsubscription from topics with subscribed children? or unsubscribe from children as well?
        //  --> still to be checked...
        //foreach ($this->getChildren() as $child) {
        //    $child->unsubscribePublication();
        //}
        $this->CI->category_db->unsubscribePublication($this->publication_id, $this->topic_id);
        $this->publicationIsSubscribed = False;
    }    
}
?>