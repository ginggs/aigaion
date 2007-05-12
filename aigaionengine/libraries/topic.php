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
    var $parent_id          = 1;
    var $name               = '';
    var $description        = '';
    var $url                = '';
    var $read_access_level  = 'intern';
    var $edit_access_level  = 'intern';
    #system variables, not to be changed by user
    var $user_id            = -1; //owner who created it
    var $group_id            = -1; //group to which access is restricted
    var $children           = null; //array of Topic's. These are not necessarily all possible children, depending on the configuration provided at construction time.
    var $CI                 = null; //link to the CI base object

    //this configuration array may contain any number of settings that determine the behavior of this topic (tree)
    //NOTE: upon construction, this array is set. After that it will not be changed anymore. This is relevant, because
    //children may share a pointer to this object.
    var $configuration      = array();
    //this flags collection may contain additional information related to the configuration, such as whether this
    //particular topic was assigned to a certain publication. Note: these flags should not be changed directly.
    var $flags              = array();
        
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
  
    
    /** if this topic is a user subscription tree, use this method to set the user to being subscribed to this
    topic and commit it to the database. Afterwards, the topic tree has been updated and the database also. 
    This method subscribes the ancestors and children as well.
    Pre: $this->configuration['user'] must be set. */  
    function subscribeUser() {
        $this->subscribeUserDownRecursive();
        $this->subscribeUserUpRecursive();
    }    
    function subscribeUserDownRecursive() {
        $this->CI->topic_db->subscribeUser($this->configuration['user'], $this->topic_id);
        $this->flags['userIsSubscribed'] = True;
        foreach ($this->getChildren() as $child) {
            $child->subscribeUserDownRecursive();
        }
    }    
    function subscribeUserUpRecursive() {
        $this->CI->topic_db->subscribeUser($this->configuration['user'], $this->topic_id);
        $this->flags['userIsSubscribed'] = True;
        $parent = $this->getParent();
        if ($parent != null) {
            $parent->subscribeUserUpRecursive();
        }
    }    

    /** if this topic is a user subscription tree, use this method to set the user to being unsubscribed to this
    topic and commit it to the database. Afterwards, the topic tree has been updated and the database also. 
    This method unsubscribes the children as well.
    Pre: $this->configuration['user'] must be set. */  
    function unsubscribeUser() {
        $this->CI->topic_db->unsubscribeUser($this->configuration['user'], $this->topic_id);
        $this->flags['userIsSubscribed'] = False;
        foreach ($this->getChildren() as $child) {
            $child->unsubscribeUser();
        }
    }    

    /** if this topic is a publication subscription tree, use this method to set the publication to being subscribed to this
    topic and commit it to the database. Afterwards, the topic tree has been updated and the database also. 
    This method subscribes the ancestors and children as well.
    Pre: $this->configuration['publicationId'] must be set. */  
    function subscribePublication() {
        $this->subscribePublicationDownRecursive();
        $this->subscribePublicationUpRecursive();
    }    
    function subscribePublicationDownRecursive() {
        $this->CI->topic_db->subscribePublication($this->configuration['publicationId'], $this->topic_id);
        $this->flags['publicationIsSubscribed'] = True;
        foreach ($this->getChildren() as $child) {
            $child->subscribePublicationDownRecursive();
        }
    }    
    function subscribePublicationUpRecursive() {
        $this->CI->topic_db->subscribePublication($this->configuration['publicationId'], $this->topic_id);
        $this->flags['publicationIsSubscribed'] = True;
        $parent = $this->getParent();
        if ($parent != null) {
            $parent->subscribePublicationUpRecursive();
        }
    }    

    /** if this topic is a Publication subscription tree, use this method to set the Publication to being unsubscribed to this
    topic and commit it to the database. Afterwards, the topic tree has been updated and the database also. 
    This method unsubscribes the children as well.
    Pre: $this->configuration['publicationId'] must be set. */  
    function unsubscribePublication() {
        $this->CI->topic_db->unsubscribePublication($this->configuration['publicationId'], $this->topic_id);
        $this->flags['publicationIsSubscribed'] = False;
        foreach ($this->getChildren() as $child) {
            $child->unsubscribePublication();
        }
    }        
    
    /** Add a new Topic with the given data. Returns TRUE or FALSE depending on whether the operation was
    successfull. After a successfull 'add', $this->topic_id contains the new topic_id. */
    function add() {
        $this->topic_id = $this->CI->topic_db->add($this);
        return ($this->topic_id > 0);
    }

    /** Commit the changes in the data of this topic. Returns TRUE or FALSE depending on whether the operation was
    operation was successfull. */
    function update() {
        return $this->CI->topic_db->update($this);
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