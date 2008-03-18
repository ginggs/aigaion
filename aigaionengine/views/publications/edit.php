<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
  $publicationfields  = getPublicationFieldArray($publication->pub_type);
  $formAttributes     = array('ID' => 'publication_'.$publication->pub_id.'_edit');
  $userlogin          = getUserLogin();
  $user               = $this->user_db->getByID($userlogin->userID());
  
?>
<div class='publication'>
  <div class='header'><?php echo ucfirst($edit_type); ?> publication</div>
<?php
  $isAddForm = $edit_type=='new';
  //open the edit form
  echo form_open('publications/commit', $formAttributes)."\n";
  echo form_hidden('edit_type',   $edit_type)."\n";
  echo form_hidden('pub_id',      $publication->pub_id)."\n";
  echo form_hidden('user_id',     $publication->user_id)."\n";
  echo form_hidden('submit_type', 'submit')."\n";
  echo form_hidden('formname','publication')."\n";
  echo "<input type='hidden' name='pubform_authors' id='pubform_authors' value=''/>\n";//into this field, the selectedauthors box will be parsed upon commit
  echo "<input type='hidden' name='pubform_editors' id='pubform_editors' value=''/>\n";//into this field, the selectededitors box will be parsed upon commit
?>
  <table class='publication_edit_form' width='100%'>
    <tr>
      <td>Type of publication:</td>
      <td><?php echo form_dropdown('pub_type', getPublicationTypes(), $publication->pub_type, "onchange=\"this.form.submit_type.value='type_change'; submitPublicationForm('publication_".$publication->pub_id."_edit');\""); ?>
    </tr>
    <tr>
      <td>Title:</td>
      <td><?php echo form_input(array('name' => 'title', 
                                      'id'   => 'title', 
                                      'size' => '90',
                                      'class'=> 'required'), $publication->title); ?></td>
    </tr>
    <tr>
      <td>Citation:</td>
      <td><?php echo form_input(array('name' => 'bibtex_id', 'id' => 'bibtex_id', 'size' => '90'), $publication->bibtex_id); ?></td>
    </tr>
<?php 
    //collect show data for all publication fields 
    //the HIDDEN fields are shown at the end of the form; the NOT HIDDEN ones are shown here.
    $hiddenFields = "";
    $capitalfields = getCapitalFieldArray();
    foreach ($publicationfields as $key => $class):
      //fields that are hidden but non empty are shown nevertheless
      if (($class == 'hidden') && ($publication->$key != '')) {
        $class = 'nonstandard';
      }
      $fieldCol = "";
      if ($key=='namekey') {
        $fieldCol = 'Key <span title="This is the bibtex `key` field, used to define sorting keys">(?)</span>'; //stored in the databse as namekey, it is actually the bibtex field 'key'
      } else { 
        if (in_array($key,$capitalfields)) {
            $fieldCol = strtoupper($key); 
        } else  {
            $fieldCol = ucfirst($key); 
        }
      }
      if ($class=='nonstandard') {
        $fieldCol .= ' <span title="This field might not be used by BiBTeX for this publication type">(*)</span>';
      }
      $fieldCol .= ':';
      $valCol = "";
        if ($key == "month")
        {
          $month = $publication->month;
          if ($month == "" || $month < 0 || $month > 12 )
            $month = 0;
            
          $valCol .= form_dropdown('month', getMonthsArray(), $month);

        }
        else if ($key == "pages")
          $valCol .= "<span title='".$class." field'>".form_input(array('name' => 'firstpage', 
                                                                  'id' => 'firstpage', 
                                                                  'size' => '3', 
                                                                  'alt' => $class, 
                                                                  'autocomplete' => 'off', 
                                                                  'class' => $class), 
                                                            $publication->firstpage)
                                                ." - "
                                                .form_input(array('name' => 'lastpage', 
                                                                  'id' => 'lastpage', 
                                                                  'size' => '3', 
                                                                  'alt' => $class, 
                                                                  'autocomplete' => 'off', 
                                                                  'class' => $class), 
                                                            $publication->lastpage)."</span>\n";
        elseif (($key == "abstract") || ($key == "userfields" ))
          $valCol .= "<span title='".$class." field'>".form_textarea(array('name' => $key, 
                                                                     'id' => $key, 
                                                                     'cols' => '87', 
                                                                     'rows' => '3', 
                                                                     'alt' => $class, 
                                                                     'autocomplete' => 'off', 
                                                                     'class' => $class), 
                                                               $publication->$key)."</span>\n";
        else {
          $onelineval = $publication->$key;
          $valCol .= "<span title='".$class." field'>".form_input(array('name' => $key, 
                                                                     'id' => $key, 
                                                                     'size' => '90', 
                                                                     'alt' => $class, 
                                                                     'autocomplete' => 'off', 
                                                                     'class' => $class), 
                                                               $onelineval)."</span>\n";      
        }
    
    //at this point, $valcol and $fieldcol give the elements for the form. Now to decide:
    //show directly (non-hidden) or postpone to the dispreferred section?
        
      if ($class=='hidden') {
        $showdata = "<tr class='hidden'>";
      } else {
        $showdata = "<tr>";
      }   
      $showdata .= "
        <td valign='top'>
        ".$fieldCol."
        </td>
        <td valign='top'>
        ".$valCol."
        </td>
      </tr>";

      if ($class=='hidden') {
        $hiddenFields .= $showdata;
      } else {
        echo $showdata;
      }   
    endforeach;

    $keywords = $publication->keywords;
    if (is_array($keywords))
    {
      $keyword_string = "";
      foreach ($keywords as $keyword)
      {
        $keyword_string .= $keyword->keyword.", ";
      }
      $keywords = substr($keyword_string, 0, -2);
    }
    else
      $keywords = "";
      
      $key    = 'keywords';
      $class  = 'optional';
?>      
    <tr>
      <td valign='top'>Keywords:</td>
      <td valign='top'><?php echo "<span title='".$class." field'>".form_input(array('name' => $key, 'id' => $key, 'size' => '90', 'alt' => $class, 'autocomplete' => 'off', 'class' => $class), $keywords);?></span>
      <div name='keyword_autocomplete' id='keyword_autocomplete' class='autocomplete'>
      </div>
      <?php echo $this->ajax->auto_complete_field('keywords', $options = array('url' => base_url().'index.php/keywords/li_keywords/', 'update' => 'keyword_autocomplete', 'tokens' => array(",", ";"), 'frequency' => '0.01'))."\n";?>
      </td>
    </tr>
<?php
    //show dispreferred fields at the end
    echo $hiddenFields;
    echo "<tr class='hidden'><td colspan=2>"; //otherwise we sometimes see the javascript code displayed in the browser window :/
	include_once(APPPATH."/javascript/authorselection.js");
	include_once(APPPATH."/javascript/publications.js");
	echo "</td></tr>";
	/*a short note: the following long piece of code creates the author and editor boxes which can be 
	filled, emptied and reordered. When the main form is committed, these boxes should be processed into 
	two form fields using the 'getAUthors' and 'getEditors' javascript functions*/
?>
    <tr>
        <td colspan='2'>
    	<table width='100%'>
			<tr>
				<td  width='55%' valign='top'>
					<table width='100%'>
						<tr><td width='80%' align='left'>Authors</td>
							<td width='20%'></td></tr>
						<tr><td align='right'>
							<select name='selectedauthors' id='selectedauthors' style='width:100%;' size='5'>
<?php
                                if (is_array($publication->authors))
                                {
                                  foreach ($publication->authors as $author)
                                  {
                            		echo "<option value=".$author->author_id.">".$author->getName('vlf')."</option>\n";
                            	  }
                            	}
?>
							</select>
						</td>
						<td align='center'>
<?php
                            echo '['.$this->ajax->link_to_function('&lt;&lt;&nbsp;add','AddAuthor();').']<br/>';
                            echo '['.$this->ajax->link_to_function('rem&nbsp;&gt;&gt;','RemoveAuthor();').']<br/>';
?>
                        </td>
                        </tr>
						<tr><td align='right'>
<?php
                            echo '['.$this->ajax->link_to_function('up','AuthorUp();').']';
                            echo '['.$this->ajax->link_to_function('down','AuthorDown();').']';
?>
						</td><td></td></tr>
						<tr><td align='left'>Editors</td><td></td></tr>
						<tr>
						<td align='right'>
							<select name='selectededitors' id='selectededitors' style='width: 100%;' size='5'>
<?php
                                if (is_array($publication->editors))
                                {
                                  foreach ($publication->editors as $editor)
                                  {
                            		echo "<option value=".$editor->author_id.">".$editor->getName('vlf')."</option>\n";
                            	  }
                            	}
?>
							</select></td>
						<td align='center'>
<?php
                            echo '['.$this->ajax->link_to_function('&lt;&lt;&nbsp;add','AddEditor();').']<br/>';
                            echo '['.$this->ajax->link_to_function('rem&nbsp;&gt;&gt;','RemoveEditor();').']<br/>';
?>
                        </td>
					    </tr>
						<tr><td align='right'>
<?php
                            echo '['.$this->ajax->link_to_function('up','EditorUp();').']';
                            echo '['.$this->ajax->link_to_function('down','EditorDown();').']';
?>
						</td><td></td></tr>
					</table>
				</td>
				<td width='45%' valign='top'>
					<table width='100%'>
						<tr><td align='center'><div id='addnewauthorbutton'>[<a href="#" onclick="AddNewAuthor(); return false;">Create as new name</a>]</div></td></tr>
						<tr><td>Search: <input title='Type in name to quick search. Note: use unaccented letters!' type='text' onkeyup='AuthorSearch();' name='authorinputtext' id='authorinputtext' size='31'></td></tr>
						<tr><td><select style='width:22em;' size='12' name='authorinputselect' id='authorinputselect'></select></td></tr>
						<tr><td align='right'></td></tr>
					</table>
				</td>
				
			</tr>
		</table>
	    <script language='JavaScript'>Init();</script>
	    </td>
    </tr>

  </table>
<?php
     

if ($edit_type=='edit') {
  echo $this->ajax->button_to_function('Change',"submitPublicationForm('publication_".$publication->pub_id."_edit');")."\n";
} else {
  echo $this->ajax->button_to_function('Add',"submitPublicationForm('publication_".$publication->pub_id."_edit');")."\n";
}
  echo form_close()."\n";

if ($edit_type=='edit') {
  echo form_open('publications/show/'.$publication->pub_id);
} else {
  echo form_open('');
}
  echo form_submit('Cancel', 'Cancel');
  echo form_close()."\n";
?>
</div>