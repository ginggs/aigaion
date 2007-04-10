<?php
/** This class holds the data structure of a topic. 

A Topic also serves as a (possibly implicit) configurable tree structure, through functions such
as getChildren() and getParent().

When creating a Topic through the topic_db library you can specify a configuration for the tree 
structure. Depending on this configuration, the tree will be constructed e.g. for all topics, for 
only those topics to which a specific user is subscribed, etc... More details can be found in the 
topic_db library which handles database access for topics.
*/

class Topic {
  
    #ID
    var $topic_id        = '';
    #content variables; to be changed by user when necessary
    var $parent_id          = -1;
    var $name               = '';
    var $description        = '';
    var $url                = '';
    #system variables, not to be changed by user
    var $children           = null; //array of Topic's. These are not necessarily all possible children, depending on the configuration provided at construction time.
    var $CI                 = null; //link to the CI base object

    //this configuration array may contain any number of settings that determine the behavior of this topic (tree)
    var $configuration      = array();
    //this flags collection may contain additional information related to the configuration, such as whether this
    //particular topic was assigned to a certain publication. Note: these flags should not be changed directly.
    var $flags              = array();
    
    //the following parameters will move or be renamed depending on the new configuration options for topic trees:
    //dont forget to searchreplace them....
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
            $this->children = $this->CI->topic_db->getChildren($this->topic_id, $this->configuration);
        }
        return $this->children;
    }

    /** Return a Topic. Note: every call to this function will return a NEW Topic object. */
    function getParent() {
        if ($this->parent_id == -1) {
            $this->parent_id = $this->CI->topic_db->getParent($this->topic_id);
        }
        if ($this->parent_id == null) return null;
        $p = $this->CI->topic_db->getByID($this->parent_id, $this->configuration);
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
    
    /** Add a new Topic with the given data. Returns TRUE or FALSE depending on whether the operation was
    successfull. After a successfull 'add', $this->topic_id contains the new topic_id. */
    function add() {
        $this->topic_id = $this->CI->topic_db->add($this);
        return ($this->topic_id > 0);
    }

    /** Commit the changes in the data of this topic. Returns TRUE or FALSE depending on whether the operation was
    operation was successfull. */
    function commit() {
        return $this->CI->topic_db->commit($this);
    }
    
    /** Collapse this topic for the current logged user */
    function collapse() {
        $this->CI->topic_db->collapse($this, getUserLogin()->userId());
    }

    /** Expand this topic for the current logged user */
    function expand() {
        $this->CI->topic_db->expand($this, getUserLogin()->userId());
    }
    
    
}
?>