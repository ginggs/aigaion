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
            $note->$key = $value;
        }
        return $note;
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
        $note->read_access_level  = $this->CI->input->post('read_access_level');
        $note->edit_access_level  = $this->CI->input->post('edit_access_level');
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
        //check access rights (!)
        $userlogin = getUserLogin();
        $publication = $this->CI->publication_db->getByID($note->pub_id);
        if (    ($publication == null) 
             ||
                (!$userlogin->hasRights('note_edit_self'))
             || 
                ($userlogin->isAnonymous() && ($publication->edit_access_level!='public'))
             ||
                (    ($publication->edit_access_level == 'private') 
                  && ($userlogin->userId() != $publication->user_id) 
                  && (!$userlogin->hasRights('publication_edit_all'))
                 )                
            ) 
        {
	        appendErrorMessage('Add note: insufficient rights.<br>');
	        return;
        }        
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
    function update($note) {
        //check access rights (by looking at the original note in the database, as the POST
        //data might have been rigged!)
        $userlogin = getUserLogin();
        $note_testrights = $this->CI->note_db->getByID($note->note_id);
        if (    ($note_testrights == null) 
             ||
                (!$userlogin->hasRights('note_edit_self'))
             || 
                ($userlogin->isAnonymous() && ($note_testrights->edit_access_level!='public'))
             ||
                (    ($note_testrights->edit_access_level == 'private') 
                  && ($userlogin->userId() != $note_testrights->user_id) 
                  && (!$userlogin->hasRights('note_edit_all'))
                 )                
            ) 
        {
	        appendErrorMessage('Edit note: insufficient rights.<br>');
	        return;
        }
        
        //start update
        $updatefields =  array('text'=>$note->text);
        if (   ($note_testrights->user_id==getUserLogin()->userId())
            || getUserLogin()->hasRights('note_edit_all')) {                        
                $updatefields['read_access_level']=$note->read_access_level;
                $updatefields['edit_access_level']=$note->edit_access_level;
        }

        $this->CI->db->query(
            $this->CI->db->update_string("notes",
                                         $updatefields,
                                         "note_id=".$note->note_id)
                              );
                                                       
        return True;
    }
    

}
?>