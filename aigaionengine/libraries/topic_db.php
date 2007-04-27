<?php
/** This class regulates the database access for Topic's. Several accessors are present that return a Topic or an
array of Topic's. 

For most methods a configuration for the tree structure can be provided. Depending on this configuration, 
the tree will be constructed e.g. for all topics, for only those topics to which a specific user is subscribed, 
etc... 
Furthermore, for some configurations each topic in the tree will be flagged with additional information.

Note: those topics for which you do not have sufficient rights will not be included in the topic tree, no matter 
what configuration you give... 

Possible configuration parameters:
    onlyIfUserSubscribed            -- if set to True, only those topics 
                                       will be included in the tree to which the user specified by 'user' is 
                                       subscribed
    user                            -- if set, the 'userIsSubscribed' will be set for all topics that this user 
                                       is subscribed to
    includeGroupSubscriptions       -- if set together with user, all topic subscriptions inherited from group
                                       memberships are taken into account as well
    flagCollapsed                   -- if set to True, the collapse status of the topic for the user specified by 
                                       'user' will be flagged by 'userIsCollapsed'
    
    onlyIfPublicationSubscribed     -- if set to True, only those topics 
                                       will be included in the tree to which the publication specified by 
                                       'publicationId' is subscribed
    publicationId                   -- if set, the 'publicationIsSubscribed' will be set for all topics that this 
                                       publication is subscribed to
                                       
Possible flags:
    userIsSubscribed
    
    userIsCollapsed
    
    publicationIsSubscribed
    
 
*/

class Topic_db {
    
    var $CI = null;
  
    function Topic_db()
    {
        $this->CI = &get_instance();
    }
   
    /** Returns the Topic with the given ID. Note: because $configuration is passed by reference, 
    you must have a proper variable. You cannot call this method as 'getByID($some_id, array(some=>config))' !!!*/
    function getByID($topic_id, &$configuration = array())
    {
        $Q = $this->CI->db->getwhere('topics', array('topic_id' => $topic_id));
        //configuration stuff will be handled by the getFromRow method...
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row(), $configuration);
        } else {
            return null;
        }
    }

    /** Returns the Topic stored in the given table row */
    function getFromRow($R, &$configuration = array())
    {
        $topic = new Topic;
        foreach ($R as $key => $value)
        {
            $topic->$key = $value;
        }
        $topic->configuration = $configuration;
        //process configuration settings
        /*  onlyIfUserSubscribed            -- if set to True, only those topics 
                                               will be included in the tree to which the user specified by 'user' is 
                                               subscribed 
            user                            -- if set, the 'userIsSubscribed' flag will be set for all topics that this user 
                                               is subscribed to
            includeGroupSubscriptions       -- if set together with user, all topic subscriptions inherited from group
                                               memberships are taken into account as well
            flagCollapsed                   -- if set to True, the collapse status of the topic for the user specified by 
                                               'user' will be flagged by 'userIsCollapsed'
            Flags:                                   
                userIsSubscribed
                userIsCollapsed
        */
        if (array_key_exists('user',$configuration)) {
            $userSubscribedQ = $this->CI->db->getwhere('usertopiclink', array('topic_id' => $topic->topic_id,  
                                                                              'user_id'=>$configuration['user']->user_id));
            $groupIrrelevant = True;
            $groupSubscribed = False;
            if (array_key_exists('includeGroupSubscriptions',$configuration)) {
                $groupIrrelevant = False;
                if (count($configuration['user']->group_ids)>0) {
                    $groupSubscribedQ = $this->CI->db->query('SELECT * FROM usertopiclink WHERE topic_id='.$topic->topic_id.' AND user_id IN ('.implode(',',$configuration['user']->group_ids).');');
                    $groupSubscribed = $groupSubscribedQ->num_rows()>0;
                } else {
                    $groupSubscribed = FALSE;
                }
                    
            }
            if (array_key_exists('onlyIfUserSubscribed',$configuration)) {
                if ($userSubscribedQ->num_rows() == 0) { //not subscribed: check group subscriptions
                    if ($groupIrrelevant || !$groupSubscribed) {
                        return null;
                    }
                }
            }
            if (($userSubscribedQ->num_rows() > 0) || $groupSubscribed) {
                $topic->flags['userIsSubscribed'] = True;
                if (array_key_exists('flagCollapsed',$configuration)) {
                    if ($userSubscribedQ->num_rows() > 0) {
                        $topic->flags['userIsCollapsed'] = $userSubscribedQ->row()->collapsed=='1';
                    } else {
                        $topic->flags['userIsCollapsed'] = True;
                    }
                }
            } else {
                $topic->flags['userIsSubscribed'] = False;
            }
                
        }
        /*  onlyIfPublicationSubscribed     -- if set to True, only those topics 
                                               will be included in the tree to which the publication specified by 
                                               'publicationId' is subscribed
            publicationId                   -- if set, the 'publicationIsSubscribed' will be set for all topics that this 
                                               publication is subscribed to
            Flags:                                   
                publicationIsSubscribed
                                               */
        if (array_key_exists('publicationId',$configuration)) {
            $pubSubscribedQ = $this->CI->db->getwhere('topicpublicationlink', 
                                                       array('topic_id' => $topic->topic_id,  
                                                             'pub_id'=>$configuration['publicationId']));
            $topic->flags['publicationIsSubscribed'] = False;
            if (array_key_exists('onlyIfPublicationSubscribed',$configuration)) {
                if ($pubSubscribedQ->num_rows() == 0) { //not subscribed: return null!
                    return null;
                }
                $topic->flags['isPublicationSubscriptionTree'] = True;
            }
            if ($pubSubscribedQ->num_rows() > 0) {
                $topic->flags['publicationIsSubscribed'] = True;
            } 
        }
            
        //always get parent
        $topic->parent_id = $this->getParentId($topic->topic_id);
        return $topic;
    }


    /** Construct a topic from the POST data present in the topics/edit view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        $topic = new Topic;
        //correct form?
        if ($this->CI->input->post('formname')!='topic') {
            return null;
        }
        //get basic data
        $topic->topic_id           = $this->CI->input->post('topic_id');
        $topic->name               = $this->CI->input->post('name');
        $topic->description        = $this->CI->input->post('description');
        $topic->url                = $this->CI->input->post('url');
        $topic->parent_id          = $this->CI->input->post('parent_id');
        return $topic;
    }
    
    /** Return an array of Topic's retrieved from the database that are the children of the given topic. */
    function getChildren($topic_id, &$configuration=array()) {
        $children = array();
        //get children from database; add to array
        $query = $this->CI->db->getwhere('topictopiclink',array('target_topic_id'=>$topic_id));
        foreach ($query->result() as $row) {
            $c = $this->getByID($row->source_topic_id,$configuration);
            if ($c != null) {
                $children[] = $c;
            }
        }
        return $children;
    }

    /** Returns the topic_id of the parent of the given Topic through database access. */
    function getParentId($topic_id) {
        $query = $this->CI->db->getwhere('topictopiclink',array('source_topic_id'=>$topic_id));
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->target_topic_id;
        } else {
            return null;
        }
    }
    
    /** Subscribe given publication to given topic in database. no recursion. */
    function subscribePublication($pub_id,$topic_id) {
        $this->CI->db->delete('topicpublicationlink', array('pub_id' => $pub_id, 'topic_id' => $topic_id)); 
        $this->CI->db->insert('topicpublicationlink', array('pub_id' => $pub_id, 'topic_id' => $topic_id)); 
    }
    
    /** Unsubscribe given publication from given topic in database. no recursion. */
    function unsubscribePublication($pub_id,$topic_id) {
        $this->CI->db->delete('topicpublicationlink', array('pub_id' => $pub_id, 'topic_id' => $topic_id)); 
    }

    
    /** Subscribe given user to given topic in database. no recursion. */
    function subscribeUser($user,$topic_id) {
        $this->CI->db->delete('usertopiclink', array('user_id' => $user->user_id, 'topic_id' => $topic_id)); 
        $this->CI->db->insert('usertopiclink', array('user_id' => $user->user_id, 'topic_id' => $topic_id)); 
    }
    
    /** Unsubscribe given user from given topic in database. no recursion. */
    function unsubscribeUser($user,$topic_id) {
        $this->CI->db->delete('usertopiclink', array('user_id' => $user->user_id, 'topic_id' => $topic_id)); 
    }


    /** Add a new topic with the given data. Returns the new topic_id, or -1 on failure. */
    function add($topic) {
        //add new topic
        $this->CI->db->query(
            $this->CI->db->insert_string("topics", array('name'=>$topic->name,'description'=>$topic->description,'url'=>$topic->url))
                             );
                                               
        //add parent
        $new_id = $this->CI->db->insert_id();
        if ($topic->parent_id < 0)$topic->parent_id=1;
        $this->CI->db->query($this->CI->db->insert_string("topictopiclink",array('source_topic_id'=>$new_id,'target_topic_id'=>$topic->parent_id)));
        return $new_id;
    }

    /** Commit the changes in the data of the given topic. Returns TRUE or FALSE depending on 
    whether the operation was successful. */
    function commit($topic) {
 
        $updatefields =  array('name'=>$topic->name,'description'=>$topic->description,'url'=>$topic->url);

        $this->CI->db->query(
            $this->CI->db->update_string("topics",
                                         $updatefields,
                                         "topic_id=".$topic->topic_id)
                              );
                                               
        //remove and set parent link
        $this->CI->db->query("DELETE FROM topictopiclink WHERE source_topic_id=".$topic->topic_id);
        if ($topic->parent_id < 0)$topic->parent_id=1;
        $this->CI->db->query($this->CI->db->insert_string("topictopiclink",array('source_topic_id'=>$topic->topic_id,'target_topic_id'=>$topic->parent_id)));
        
        return True;
    }
    
    /** Collapse given topic for the given user, if that user is susbcribed to it */
    function collapse($topic, $user_id) {
        $this->CI->db->query("UPDATE usertopiclink SET collapsed='1' WHERE topic_id=".$topic->topic_id." AND user_id=".$user_id);
    }

    /** Expand given topic for the given user, if that user is susbcribed to it */
    function expand($topic, $user_id) {
        $this->CI->db->query("UPDATE usertopiclink SET collapsed='0' WHERE topic_id=".$topic->topic_id." AND user_id=".$user_id);
    }
    
}
?>