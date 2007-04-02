<?php
/** This class regulates the database access for Topic's. Several accessors are present that return a Topic or an
array of Topic's. 

For most methods a pub_id can be provided. If this is done, topics in will be marked with a boolean 
stating whether that particular publication is subscribed to the topic. */

class Topic_db {
    
    var $CI = null;
  
    function Topic_db()
    {
        $this->CI = &get_instance();
    }
   
    /** Returns the Topic with the given ID */
    function getByID($topic_id, $pub_id = '')
    {
        $Q = $this->CI->db->getwhere('topics', array('topic_id' => $topic_id));
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row(), $pub_id);
        } else {
            return null;
        }
    }

    /** Returns the Topic stored in the given table row */
    function getFromRow($R, $pub_id = '')
    {
        $topic = new Topic;
        foreach ($R as $key => $value)
        {
            $topic->$key = $value;
        }
        
        //subscription tree? then store relevant info
        if ($pub_id != '') {
            $topic->isClassificationTree = True;
            $topic->pub_id = $pub_id;
            $Q = $this->CI->db->getwhere('publicationtopiclink', array('topic_id' => $topic->category_id, 'pub_id' => $pub_id));
            if ($Q->num_rows() > 0) {
                $topic->publicationIsSubscribed = True;
            }
        }
        //always get parent
        $topic->parent_id = $this->getParent($topic->topic_id);
        return $topic;
    }

    /** Return an array of Topic's retrieved from the database that are the children of the given topic. */
    function getChildren($topic_id, $pub_id = '') {
        $children = array();
        //get children from database; add to array
        $query = $this->CI->db->getwhere('topictopiclink',array('target_topic_id'=>$topic_id));
        foreach ($query->result() as $row) {
            $c = $this->getByID($row->source_topic_id,$pub_id);
            $children[] = $c;
        }
        return $children;
    }

    /** Returns the topic_id of the parent of the given Topic through database access. */
    function getParent($topic_id) {
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
        $this->CI->db->delete('publicationtopiclink', array('pub_id' => $pub_id, 'topic_id' => $topic_id)); 
        $this->CI->db->insert('publicationtopiclink', array('pub_id' => $pub_id, 'topic_id' => $topic_id)); 
    }
    
    /** Unsubscribe given publication from given topic in database. no recursion. */
    function unsubscribePublication($pub_id,$topic_id) {
        $this->CI->db->delete('publicationtopiclink', array('pub_id' => $pub_id, 'topic_id' => $topic_id)); 
    }
    
}
?>