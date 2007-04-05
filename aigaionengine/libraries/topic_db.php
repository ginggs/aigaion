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
    whether the operation was successfull. */
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
}
?>