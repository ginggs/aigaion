<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
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
  echo "<input type='hidden' name='pubform_authors' id='pubform_authors' value=''/>\n";//into this field, the selectedauthors box will be parsed upon commit
  echo "<input type='hidden' name='pubform_editors' id='pubform_editors' value=''/>\n";//into this field, the selectededitors box will be parsed upon commit
?>
  <table class='publication_edit_form' width='100%'>
    <tr>
      <td>Type of publication:</td>
      <td><?php echo form_dropdown('pub_type', getPublicationTypes(), $publication->pub_type, 'onchange="this.form.submit_type.value=\'type_change\'; this.form.submit();"'); ?>
    </tr>
    <tr>
      <td>Title:</td>
      <td><?php echo form_input(array('name' => 'title', 'id' => 'title', 'size' => '90'), $publication->title); ?></td>
    </tr>
    <tr>
      <td>Citation:</td>
      <td><?php echo form_input(array('name' => 'bibtex_id', 'id' => 'bibtex_id', 'size' => '90'), $publication->bibtex_id); ?></td>
    </tr>
<?php 
    //show all publication fields that are not hidden
    //at the end of this table, we show all hidden fields as hidden form elements
    foreach ($publicationfields as $key => $class):
      
      if ($class != 'hidden'): 
        
?>
    <tr>
      <td valign='top'><?php echo ucfirst($key); ?>:</td>
      <td valign='top'><?php 
        if ($key == "pages")
          echo "<span title='".$class." field'>".form_input(array('name' => 'firstpage', 
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
                                                            $publication->lastpage)."</span></td>\n";
        elseif ($key == "abstract")
          echo "<span title='".$class." field'>".form_textarea(array('name' => $key, 
                                                                     'id' => $key, 
                                                                     'cols' => '90', 
                                                                     'rows' => '20', 
                                                                     'alt' => $class, 
                                                                     'autocomplete' => 'off', 
                                                                     'class' => $class), 
                                                               $publication->$key)."</span></td>\n";
        else
          echo "<span title='".$class." field'>".form_input(array('name' => $key, 
                                                                     'id' => $key, 
                                                                     'size' => '90', 
                                                                     'alt' => $class, 
                                                                     'autocomplete' => 'off', 
                                                                     'class' => $class), 
                                                               $publication->$key)."</span></td>\n";
?>
    </tr>
<?php
      endif; //class != hidden
    endforeach;

    $keywords = $publication->keywords;
    if (is_array($keywords))
      $keywords = implode($keywords, ', ');
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
      <?php echo $this->ajax->auto_complete_field('keywords', $options = array('url' => base_url().'index.php/keywords/li_keywords/', 'update' => 'keyword_autocomplete', 'tokens'=> ',', 'frequency' => '0.01'))."\n";?>
      </td>
    </tr>
<?php
	include_once(APPPATH."/javascript/authorselection.js");
	include_once(APPPATH."/javascript/publications.js");
	/*a short note: the following long piece of code creates the author and editor boxes which can be 
	filled, emptied and reordered. When the main form is committed, these boxes should be processed into 
	two form fields using the 'getAUthors' and 'getEditors' javascript functions*/
?>
    <tr>
        <td colspan='2'>
    	<table width='100%'>
			<!--tr><td colspan='2' align='center'>
				<span class='islink' onclick=\"javascript:window.open('indexlight.php?page=author&kind=new','author_window',
					'resizable, scrollbars, width=800, height=480, dependent, left=0, top=0');\">[Add new author]</span>
			</td></tr-->
			<tr>
				<td  width='55%' valign='top'>
					<table width='100%'>
						<tr><td width='80%' align='left'>Authors</td>
							<td width='20%'></td></tr>
						<tr><td align='right'>
							<select name='selectedauthors' id='selectedauthors' style='width:100%;' size='10'>
<?php
                                if (is_array($publication->authors))
                                {
                                  foreach ($publication->authors as $author)
                                  {
                            		echo "<option value=".$author->author_id.">".$author->cleanname."</option>\n";
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
							<select name='selectededitors' id='selectededitors' style='width: 100%;' size='10'>
<?php
                                if (is_array($publication->editors))
                                {
                                  foreach ($publication->editors as $editor)
                                  {
                            		echo "<option value=".$editor->author_id.">".$editor->cleanname."</option>\n";
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
						<tr><td><input type='text' onkeyup='AuthorSearch();' name='authorinputtext' id='authorinputtext' size='50'></td></tr>
						<tr><td><select style='width:100%;' size='23' name='authorinputselect' id='authorinputselect'></select></td></tr>
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
  foreach ($publicationfields as $key => $class):
    if ($class == 'hidden'): 
      echo form_hidden($key, $publication->$key)."\n";
    endif;
  endforeach;
      

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