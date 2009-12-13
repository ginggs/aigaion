<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class provides an generic interface to the customfields entries for
publications, authors and topics. Furthermore, the interface functions for the
site settings page is provided.

*/
class Customfields_db {

  function Customfields_db()
  {
  }

  function getForAuthor($authorID) {
    return $this->getForTypeByID('author', $authorID);
  }

  function getForPublication($publicationID) {
    return $this->getForTypeByID('publication', $publicationID);
  }

  function getForTopic($topicID) {
    return $this->getForTypeByID('topic', $topicID);
  }

  function getForTypeByID($type, $object_id) {
    $CI = &get_instance();
    $Q = $CI->db->query("SELECT ".AIGAION_DB_PREFIX."customfieldsinfo.name, customfields.value
                          FROM ".AIGAION_DB_PREFIX."customfieldsinfo 
                            LEFT JOIN ".AIGAION_DB_PREFIX."customfields
                        		  ON (".AIGAION_DB_PREFIX."customfieldsinfo.type_id = ".AIGAION_DB_PREFIX."customfields.type_id)
                          WHERE ".AIGAION_DB_PREFIX."customfieldsinfo.type = ".$CI->db->escape($type)."
                            AND ".AIGAION_DB_PREFIX."customfields.object_id = ".$CI->db->escape($object_id)."
                       ORDER BY ".AIGAION_DB_PREFIX."customfieldsinfo.order ASC");
    
    $result = array();
    if ($Q->num_rows() > 0)
    {
      foreach ($Q->result() as $R) {
        $result[] = $this->getFromRow($R);
      }
    }
    return $result;
  }
  
  function getFromRow($R) {
    return array ('fieldname' => $R->name, 'value' => $R->value);
  }
  
  function getAllFieldsInfo() {
    $CI = &get_instance();
    $Q = $CI->db->query("SELECT ".AIGAION_DB_PREFIX."customfieldsinfo.*
                          FROM ".AIGAION_DB_PREFIX."customfieldsinfo
                          ORDER BY ".AIGAION_DB_PREFIX."customfieldsinfo.type ASC,
                            ".AIGAION_DB_PREFIX."customfieldsinfo.order ASC");
    $result = array();
    if ($Q->num_rows() > 0)
    {
      foreach ($Q->result() as $R) {
        $result[] = array('type_id' => $R->type_id, 'type' => $R->type, 'name' => $R->name, 'order' => $R->order, 'keep' => "TRUE");
      }
    }
    return $result;
  }
  
  function getSettingsFromPost() {
    $CI = &get_instance();
    //fetch customfields count:
    $count = trim($CI->input->post('customfield_count'));
    
    //we fetch $count+1 entries
    $customFields = array();
    $count++;
    for ($i = 0; $i < $count; $i++)
    {
      $customField = array();
      $customField['type_id'] = trim($CI->input->post('CUSTOM_FIELD_ID_'.$i));
      $customField['type']    = trim($CI->input->post('CUSTOM_FIELD_TYPE_'.$i));
      $customField['name']    = trim($CI->input->post('CUSTOM_FIELD_NAME_'.$i));
      $customField['order']   = trim($CI->input->post('CUSTOM_FIELD_ORDER_'.$i));
      $customField['keep']    = trim($CI->input->post('CUSTOM_FIELD_KEEP_'.$i));
      
      $customFields[] = $customField;
    }
    
    return $customFields;
  }
  
  function updateFromPost($customFieldsPostInfo) {
    $CI = &get_instance();
    foreach ($customFieldsPostInfo as $customField) 
    {
      //check if we need to do an update or deletion
      if ($customField['type_id'] != '') //there is an old custom field
      {
        if ($customField['keep']) //the field is to be updated
        {
          $CI->db->where('type_id', $customField['type_id']);
          $CI->db->update('customfieldsinfo', array('type'=>trim($customField['type']), 
                                                    'name'=>trim($customField['name']),
                                                    'order'=>trim($customField['order'])));
        }
        else //the field is to be deleted
        {
          $CI->db->delete('customfieldsinfo', array('type_id' => trim($customField['type_id'])));
          
          //delete all instances of this field type
          $CI->db->delete('customfields', array('type_id' => trim($customField['type_id'])));
        }
      }
      else if ($customField['keep']) //add new customfield
      {
        $CI->db->insert('customfieldsinfo', array('type'=>trim($customField['type']), 
                                                   'name'=>trim($customField['name']),
                                                   'order'=>trim($customField['order'])));
      }
    }
    return $this->getAllFieldsInfo();
  }
}
?>