<?php

function validAigaionId($id,$type)
{
  switch($type) {
    case "publication":
      return validAigaionPubId($id);
      break;
    case "topic":
      return validAigaionTopicId($id);
      break;
    case "author":
      return validAigaionAuthorId($id);
      break;
    default:
      return false; 
      break;
  }
}
/** Return true iff the given id can be a valid aigaion pub_id (numbers, or possibly correct bibtex_id's)
 */
function validAigaionPubId($id)
{
  //number?
  if (is_numeric($id)) return true;
  //bibtex id?
  if (preg_match("/[^\\w:\/\-]/i", $id) ==1) return false; //note that we allow the / as DBLP alqways includes it, but Agaion will think it s a segment separator!
  return true;
}
function validAigaionTopicId($id)
{
  //number?
  if (is_numeric($id)) return true;
  //also, if it consists of a series of topic names, i.e. top/sub1 name/sub2 name
  //what is not allowed?
  //&
  if (preg_match("/[&;]/i", $id) ==1) return false; 
  return true;
}
/** Return true iff the given id can be a valid aigaion author_id (numbers, or possibly correct name with not too many weird characters)
 */
function validAigaionAuthorId($id)
{
  if (is_numeric($id)) return true;
  if (preg_match("/[\/]/i", $id) ==1) return false; //no slashes; <>& are already filtered
  return true;
}

function getSimpleAigaionLink($param1 = '', $param2 = '')
{
    global $wgAigaion;
    if (!isset($wgAigaion['root']) || ($wgAigaion['root']==''))
    {
      wfLoadExtensionMessages( 'AigaionFunctions' );
      return '<strong class="error">' . wfMsg( 'aigaion_config_incomplete','root') . '</strong>';
    }
    $type = strtolower($param1);
    $id = htmlentities($param2);
    $link = $wgAigaion['root']."/"; 
    $linkname = "aigaion";   
    switch ($type) {
        case "pub":
          if (validAigaionPubId($id)) 
          {
            $link .= "publications/show/".$id;
            $linkname = "pub#".$id;
          }
          else 
          {
            wfLoadExtensionMessages( 'AigaionFunctions' );
            return '<strong class="error">' . wfMsg( 'aigaion_invalid_pub_id', $id ) . '</strong>';
          }
          break;
        case "author":
          if (validAigaionAuthorId($id)) 
          {
            $link .= "authors/show/".$id;
            $linkname = "author#".$id;
          }
          else 
          {
            wfLoadExtensionMessages( 'AigaionFunctions' );
            return '<strong class="error">' . wfMsg( 'aigaion_invalid_author_id', $id ) . '</strong>';
          }
          break;     
        case "topic":
          if (validAigaionTopicId($id)) 
          {
            $link .= "topics/single/".$id;
            $linkname = "topic#".$id;
          }
          else 
          {
            wfLoadExtensionMessages( 'AigaionFunctions' );
            return '<strong class="error">' . wfMsg( 'aigaion_invalid_topic_id', $id ) . '</strong>';
          }
          break;                  
        default: 
            wfLoadExtensionMessages( 'AigaionFunctions' );
            return '<strong class="error">' . wfMsg( 'aigaion_unk_linktype', $type ) . '</strong>';
          break;
    }
    return "<a target=_blank href='{$link}'>{$linkname}</a>";
}

function getAigaionLink($param1 = '', $param2 = '')
{
  //construct page to embed
    global $wgAigaion;
    if (!isset($wgAigaion['root']) || ($wgAigaion['root']==''))
    {
      wfLoadExtensionMessages( 'AigaionFunctions' );
      return '<strong class="error">' . wfMsg( 'aigaion_config_incomplete','root') . '</strong>';
    }
    $type = strtolower($param1);
    $id = htmlentities($param2);
    $pageToEmbed = "readapi/link/"; 
    //the following code can be a lot shorter:
    switch ($type) {
        case "pub":
          if (validAigaionPubId($id)) 
          {
            $pageToEmbed .= "publication/".$id;
          }
          else 
          {
            wfLoadExtensionMessages( 'AigaionFunctions' );
            return '<strong class="error">' . wfMsg( 'aigaion_invalid_pub_id', $id ) . '</strong>';
          }
          break;
        case "author":
          if (validAigaionAuthorId($id)) 
          {
            $pageToEmbed .= "author/".$id;
          }
          else 
          {
            wfLoadExtensionMessages( 'AigaionFunctions' );
            return '<strong class="error">' . wfMsg( 'aigaion_invalid_author_id', $id ) . '</strong>';
          }
          break;
        case "topic":
          if (validAigaionTopicId($id)) 
          {
            $pageToEmbed .= "topic/".$id;
          }
          else 
          {
            wfLoadExtensionMessages( 'AigaionFunctions' );
            return '<strong class="error">' . wfMsg( 'aigaion_invalid_topic_id', $id ) . '</strong>';
          }
          break;
        default: 
            wfLoadExtensionMessages( 'AigaionFunctions' );
            return '<strong class="error">' . wfMsg( 'aigaion_unk_linktype', $type ) . '</strong>';
          break;
    }
    require_once (dirname(__FILE__) . "/aigaion_simpleembed.php");
    return getSimpleEmbedding($pageToEmbed );
}


/**
 * Adds the necessary javascripts to the output, but only once!
 */
function ensureJavaScripts() 
{
  global $wgAigaion;
  $result = "";
  if (!$wgAigaion['scriptsloaded'])
  {
    global $wgScriptPath;
    $wgAigaion['scriptsloaded'] = True;
    $result .= '<script language="javascript" src="'.$wgScriptPath.'/extensions/Aigaion/javascript/prototype.js"></script>';
    $result .= '<script language="javascript" src="'.$wgScriptPath.'/extensions/Aigaion/javascript/builder.js"></script>';
    $result .= '<script language="javascript" src="'.$wgScriptPath.'/extensions/Aigaion/javascript/externallinks.js"></script>';
    $result .= '<script language="javascript" src="'.$wgScriptPath.'/extensions/Aigaion/javascript/yahoo/YAHOO.js"></script>';
    $result .= '<script language="javascript" src="'.$wgScriptPath.'/extensions/Aigaion/javascript/yahoo/connection.js"></script>';
    $result .= '<script language="javascript">document.domain="'.$wgAigaion['shareddomain'].'";function doEmbedding(content,target) {Element.replace(target,content);}</script>';  
  }
  return $result;
} 

function getAigaionEmbedding($param1 = '', $param2 = '', $param3 = '')
{
    global $wgAigaion;
    $output = "";
    if (!isset($wgAigaion['root']) || ($wgAigaion['root']==''))
    {
      wfLoadExtensionMessages( 'AigaionFunctions' );
      return '<strong class="error">' . wfMsg( 'aigaion_config_incomplete','root') . '</strong>';
    }
    if (!isset($wgAigaion['shareddomain']) || ($wgAigaion['shareddomain']==''))
    {
      wfLoadExtensionMessages( 'AigaionFunctions' );
      return '<strong class="error">' . wfMsg( 'aigaion_config_incomplete','shareddomain') . '</strong>';
    }
    // determine name of embedding container (must be unique for every embedding!)
    $containername = 'embeddingcontainer_'.$wgAigaion['counter'];
    $wgAigaion['counter'] = $wgAigaion['counter'] + 1;
    // parse parameters to construct right embedding command
    $type = strtolower($param1);
    $id = htmlentities($param2);
    if ($type=='pub')$type='publication';
    if (!in_array($type,array('publication','author','topic')))
    {
            wfLoadExtensionMessages( 'AigaionFunctions' );
            return '<strong class="error">' . wfMsg( 'aigaion_unk_embedtype', $type ) . '</strong>';
    }    
    if (!validAigaionId($id,$type)) 
    {
            wfLoadExtensionMessages( 'AigaionFunctions' );
            return '<strong class="error">' . wfMsg( 'aigaion_invalid_id', $id , $type) . '</strong>';
    }
    $pageToEmbed = $wgAigaion['root']."/readapi/embed/".$containername."/".$type."/".$id;
    
    // embed the content
    $output .= "<div id='".$containername."' name='".$containername."'><iframe id='ifr' name='ifr' src='".$pageToEmbed."'></iframe></div>";
    return $output;
}