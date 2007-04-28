<?php
/** This class regulates the database access for Notes. Several accessors are present that return a Note or 
array of Note's. */
class Note_db {
  
    var $CI = null;
  
    function Note_db()
    {
        $this->CI = &get_instance();
    }
    
    /** Return the Note object with the given id, or null if insufficient rights */
    function getByID($note_id)
    {
        $Q = $this->CI->db->getwhere('notes', array('note_id' => $note_id));
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  else {
            return null;
        }
    }
   
    /** Return the Note object stored in the given database row, or null if insufficient rights. */
    function getFromRow($R)
    {
        $userlogin = getUserLogin();
        //check rights; if fail: return null
        if (!$userlogin->hasRights('note_read')) {
            return null;
        }
        if ($userlogin->isAnonymous() && $R->read_access_level!='public') {
            return null;
        }
        if (   ($R->read_access_level=='private') 
            && ($userlogin->userId() != $R->user_id) 
            && (!$userlogin->hasRights('note_read_all'))) {
            return null;
        }
        //rights were OK; read data
        $note = new Note;
        foreach ($R as $key => $value)
        {
            $attachment->$key = $value;
        }
        return $attachment;
    }

    /** Construct a note from the POST data present in the note/edit or add view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        $note = new Note;
        //correct form?
        if ($this->CI->input->post('formname')!='note') {
            return null;
        }
        //get basic data
        $note->note_id            = $this->CI->input->post('note_id');
        $note->text               = $this->CI->input->post('text');
        $note->pub_id             = $this->CI->input->post('pub_id');
        $note->user_id            = $this->CI->input->post('user_id');
        //$note->read_access_level  = $this->CI->input->post('read_access_level');
        //$note->edit_access_level  = $this->CI->input->post('edit_access_level');
        return $note;
    }
        
    /** Return an array of Note object for the given publication. */
    function getNotesForPublication($pub_id) {
        $result = array();
        $Q = $this->CI->db->getwhere('notes', array('pub_id' => $pub_id));
        foreach ($Q->result() as $row) {
            $next  =$this->getByID($row->note_id);
            if ($next != null) {
                $result[] = $next;
            }
        }
        return $result;
    }


    /** Add a new note with the given data. Returns the new note_id, or -1 on failure. */
    function add($note) {
        //add new note
        $this->CI->db->query(
            $this->CI->db->insert_string("notes", array('text'=>$note->text,
                                                        'pub_id'=>$note->pub_id,
                                                        'read_access_level'=>$note->read_access_level,
                                                        'edit_access_level'=>$note->edit_access_level,
                                                        'user_id'=>getUserLogin()->userId()))
                             );
                                               
        $new_id = $this->CI->db->insert_id();
        return $new_id;
    }

    /** Commit the changes in the data of the given note. Returns TRUE or FALSE depending on 
    whether the operation was successful. */
    function commit($note) {
 
        $updatefields =  array('text'=>$note->text,
                               'read_access_level'=>$note->read_access_level,
                               'edit_access_level'=>$note->edit_access_level);

        $this->CI->db->query(
            $this->CI->db->update_string("notes",
                                         $updatefields,
                                         "note_id=".$note->note_id)
                              );
                                                       
        return True;
    }
    

}
?>