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
        $userlogin  = getUserLogin();
        $user       = $this->CI->user_db->getByID($userlogin->userID());
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
        if (   ($R->read_access_level=='group') 
            && (!in_array($R->group_id,$user->group_ids) ) 
            && (!$userlogin->hasRights('note_read_all'))) {
            return null;
        }
        //rights were OK; read data
        $note = new Note;
        foreach ($R as $key => $value)
        {
            $note->$key = $value;
        }
        //read the crossref_ids as they were cached in the database
        $Q = mysql_query("SELECT xref_id FROM notecrossrefid WHERE note_id = ".$note->note_id);
    	if (mysql_num_rows($Q) > 0) {
    		while ($R = mysql_fetch_array($Q))
    			$note->xref_ids[] = $R['xref_id'];
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
        $note->group_id           = $this->CI->input->post('group_id');
        if ($note->group_id=='') {
            //no group id: i guess the user has no group. Means that any 'group' restriuction on read-access-level will be changed to 'private'?
            //otherwise the note will disappear in the nonexisting group '0'
            $note->group_id='0';
            if ($note->read_access_level=='group') $note->read_access_level='private';
            if ($note->edit_access_level=='group') $note->edit_access_level='private';
        }

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
    
    /** Return an array of Note objects that crossref the given publication in their text. 
    Will return only accessible notes (i.e. wrt access_levels). This method can therefore
    not be used to e.g. update note texts for crossref changes due to a changed bibtex id. */
    function getXRefNotesForPublication($pub_id) {
        $result = array();
        $Q = $this->CI->db->getwhere('notecrossrefid', array('xref_id' => $pub_id));
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
        $userlogin    = getUserLogin();
        $user         = $this->CI->user_db->getByID($userlogin->userID());
        $publication  = $this->CI->publication_db->getByID($note->pub_id);
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
             ||
                (    ($publication->edit_access_level == 'group') 
                  && (!in_array($publication->group_id,$user->group_ids) ) 
                  && (!$userlogin->hasRights('publication_edit_all'))
                 )                
            ) 
        {
	        appendErrorMessage('Add note: insufficient rights.<br>');
	        return;
        }        
        //add new note
        $this->CI->db->insert("notes", array('text'              => $note->text,
                                             'pub_id'            => $note->pub_id,
                                             'read_access_level' => $note->read_access_level,
                                             'edit_access_level' => $note->edit_access_level,
                                             'group_id'          => $note->group_id,
                                             'user_id'           => $userlogin->userId()));
        $new_id = $this->CI->db->insert_id();
        $note->note_id = $new_id;
        
        //set crossref ids
        $xref_ids = getCrossrefIDsForText($note->text);
        foreach ($xref_ids as $xref_id) {
            $this->CI->db->query(
                $this->CI->db->insert_string("notecrossrefid", array('xref_id'=>$xref_id,
                                                                     'note_id'=>$note->note_id)
                                            )
                                 );
        }
                             
        return $new_id;
    }

    /** Commit the changes in the data of the given note. Returns TRUE or FALSE depending on 
    whether the operation was successful. */
    function update($note) {
        //check access rights (by looking at the original note in the database, as the POST
        //data might have been rigged!)
        $userlogin  = getUserLogin();
        $user       = $this->CI->user_db->getByID($userlogin->userID());
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
             ||
                (    ($note_testrights->edit_access_level == 'private') 
                  && (!in_array($note_testrights->group_id,$user->group_ids) ) 
                  && (!$userlogin->hasRights('note_edit_all'))
                 )                   ) 
        {
	        appendErrorMessage('Edit note: insufficient rights.<br>');
	        return;
        }
        
        //start update
        $updatefields =  array('text'=>$note->text);
        if (   ($note_testrights->user_id==$userlogin->userId())
            || $userlogin->hasRights('note_edit_all')) {                        
                $updatefields['read_access_level']=$note->read_access_level;
                $updatefields['edit_access_level']=$note->edit_access_level;
                $updatefields['group_id']=$note->group_id;
        }

        $this->CI->db->query(
            $this->CI->db->update_string("notes",
                                         $updatefields,
                                         "note_id=".$note->note_id)
                              );
        

        //remove old xref ids
        $this->CI->db->delete('notecrossrefid', array('note_id' => $note->note_id)); 
        //set crossref ids
        $xref_ids = getCrossrefIDsForText($note->text);
        foreach ($xref_ids as $xref_id) {
            $this->CI->db->query(
                $this->CI->db->insert_string("notecrossrefid", array('xref_id'=>$xref_id,
                                                                     'note_id'=>$note->note_id)
                                            )
                                 );
        }
                                                       
        return True;
    }

    /** change the text of all affected notes to reflect a change of the bibtex_id of the given publication.
    Note: this method does NOT make use of getByID($note_id), because one should also change the referring 
    text of all notes that are inaccessible through getByID($note_id) due to access level limitations. */
    function changeAllCrossrefs($pub_id, $new_bibtex_id) 
    {
		$bibtexidlinks = getBibtexIdLinks();
        $Q = $this->CI->db->getwhere('notecrossrefid',array('xref_id'=>$pub_id));
        foreach ($Q->result() as $R) {
            $noteQ = $this->CI->db->getwhere('notes',array('note_id'=>$R->note_id));
            if ($noteQ->num_rows()>0) {
              $R = $noteQ->row();
        		  $text = preg_replace($bibtexidlinks[$pub_id][1], $new_bibtex_id, $R->text);
                //update is done here, instead of using the update function, as some of the affected notes may not be accessible for this user
                $updatefields =  array('text'=>$text);
                $this->CI->db->query(
                    $this->CI->db->update_string("notes",
                                                 $updatefields,
                                                 "note_id=".$R->note_id)
                                      );
        		if (mysql_error()) {
        		    appendErrorMessage("Failed to update the bibtex-id in note ".$R->note_id.": <br>");
            	}
            }
        }
    }
  

}
?>