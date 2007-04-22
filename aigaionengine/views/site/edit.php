<?php
/**
views/site/edit

Shows a form for editing the site configuration.

Parameters:
    $siteconfig     the site config object
*/
$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('site/configure/commit');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','siteconfig');

echo "<p class='header'>AIGAION SITE CONFIGURATION FORM</p>";
echo $this->validation->error_string;
?>
    <table width='100%'>
<!-- SITE ADMIN NAME -->
        <tr>
            <td colspan='2'><hr><p class='header2'>Site Admin:</p></td>
	    </tr>

	    <tr>
	        <td><label for='CFG_ADMIN'>Name of Aigaion administrator:</label></td>
	        <td align='left'><input type='text' cols='60' name='CFG_ADMIN' value='<?php echo $siteconfig->configSettings["CFG_ADMIN"]; ?>'></td>
	    </tr>

	    <tr>
	        <td><label for='CFG_ADMINMAIL'>Email of Aigaion administrator:</label></td>
	        <td align='left'><input type='text' cols='60' name='CFG_ADMINMAIL' value='<?php echo $siteconfig->configSettings["CFG_ADMINMAIL"]; ?>'></td>
	    </tr>

<!-- EXTERNAL LOGIN MODULES -->
        <tr>
            <td colspan='2'><hr><p class='header2'>Login modules:</p></td>
        </tr>

        <tr>
            <td>Use the following login module:</td>
            <td>
<?php              
            $options = array('Aigaion'=>'Aigaion login module',
                             'Httpauth'=>'.htpasswd file');
            $selected = $siteconfig->configSettings["EXTERNAL_LOGIN_MODULE"];
            if ($selected == '') {
                $selected = 'Aigaion';
            }
            echo form_dropdown('EXTERNAL_LOGIN_MODULE', $options,$selected);
?>
            </td>                
        </tr>
	    <tr>
	        <td align='left' colspan='2'>
	        <p><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Select the login module to be used. 
	        <br>- 'Aigaion' is the default built-in login system.
	        <br>- '.htpasswd' is a module that uses the .htaccess and .htpasswd login system to determine 
	        the name of the logged user.</p></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

        <tr>
	        <td><label>Create missing users:</label></td>
	        <td align='left'>
<?php	            
    echo form_checkbox('CREATE_MISSING_USERS','CREATE_MISSING_USERS',$siteconfig->configSettings["CREATE_MISSING_USERS"]== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Check this box to force the system to create users that are logged in in the external login module,
	        but do not have an Aigaion user account yet. Note that this setting only has an effect when a login
	        module different from 'Aigaion' has been selected.
	        </td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>
<!-- ANONYMOUS ACCESS SETTINGS -->
        <tr>
            <td colspan='2'><hr><p class='header2'>Anonymous access:</p></td>
        </tr>

        <tr>
	        <td><label>Enable anonymous access:</label></td>
	        <td align='left'>
<?php	            
    echo form_checkbox('ENABLE_ANON_ACCESS','ENABLE_ANON_ACCESS',$siteconfig->configSettings["ENABLE_ANON_ACCESS"]== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Check this box to enable anonymous (guest) access.</td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

        <tr>
            <td>Default anonymous user account</td>
            <td>
<?php              
            $options = array(''=>'');
            foreach ($this->user_db->getAllAnonUsers() as $anonUser) {
                $options[$anonUser->user_id] = $anonUser->login;
            }
            echo form_dropdown('ANONYMOUS_USER', $options,$siteconfig->configSettings["ANONYMOUS_USER"]);
?>
            </td>                
        </tr>
	    <tr>
	        <td align='left' colspan='2'>
	        <p><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Select the user account that will be used by default for logging in anonymous users. 
	        <p>Note: Be careful in assigning user rights to anonymous accounts!</td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

<!-- ATTACHMENT SETTINGS -->
        <tr>
            <td colspan='2'><hr><p class='header2'>Attachment settings:</p></td>
        </tr>
	    <tr>    
	        <td><label>Allowed extensions for attachments:</label></td>
	        <td align='left'><input type='text' cols='100' name='ALLOWED_ATTACHMENT_EXTENSIONS'	
<?php
             echo "value='".implode(",",$siteconfig->configSettings["ALLOWED_ATTACHMENT_EXTENSIONS"])."'>";
?>
	        </td>
        </tr>
        <tr>
            <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        The list of allowed extensions for attachments. Attachments that do not have an extension from this 
	        list can not be uploaded.</td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>
	    <tr>
	        <td><label>Allow all remote attachments:</label></td>
	        <td align='left'>
<?php
            echo form_checkbox('ALLOW_ALL_EXTERNAL_ATTACHMENTS','ALLOW_ALL_EXTERNAL_ATTACHMENTS',$siteconfig->configSettings["ALLOW_ALL_EXTERNAL_ATTACHMENTS"]== "TRUE");
?>
	        </td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Check this box if you want to allow all external attachment names, instead of just those ending in 
	        one of the 'allowed extensions' specified above. This may be useful because external attachments are 
	        often to sites such as portal.acm or doi, with link names ending in meaningless numbers instead of a 
	        proper file name. This only affects *remote* attachments.</td>
	    </tr>
	
	    <tr>
	        <td><label>The server is read only:</label></td>
	        <td align='left'>
<?php 
            echo form_checkbox('SERVER_NOT_WRITABLE','SERVER_NOT_WRITABLE',$siteconfig->configSettings["SERVER_NOT_WRITABLE"]== "TRUE");
?>
	        </td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Check this box if the server is read-only, i.e. if you cannot write files such as attachments to 
	        the server.</td>
	    </tr>

<!-- DISPLAY SETTINGS -->
	    <tr>
	        <td colspan='2'><hr><p class='header2'>Some display settings:</p></td>
	    </tr>

        <tr>
	        <td><label for='WINDOW_TITLE'>Title of the site:</label></td>
	        <td align='left'><input type='text' cols='60' name='WINDOW_TITLE' 
<?php
	        echo "value='".$siteconfig->configSettings["WINDOW_TITLE"]."'>";
?>
	        </td>

        <tr>
	        <td><label>Display publications on single-topic page:</label></td>
	        <td align='left'>
<?php
            echo form_checkbox('ALWAYS_INCLUDE_PAPERS_FOR_TOPIC','ALWAYS_INCLUDE_PAPERS_FOR_TOPIC',$siteconfig->configSettings["ALWAYS_INCLUDE_PAPERS_FOR_TOPIC"]== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Checking this box means that the full list of publications for a topic is included below the topic 
	        description, to speed up browsing for papers. Turning this on might however slow down the loading 
	        of the topic pages.</td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>
	
	    <tr>
	        <td><label>Merge crossreffed publications in single publication view:</label></td>
	        <td align='left'>
<?php
            echo form_checkbox('PUBLICATION_XREF_MERGE','PUBLICATION_XREF_MERGE',$siteconfig->configSettings["PUBLICATION_XREF_MERGE"]== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Check to merge cross-referenced publications on a single publication page view.</td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

<!-- INPUT/OUTPUT SETTINGS -->
	    <tr>
	        <td colspan='2'><hr><p class='header2'>In- and output settings:</p></td>
	    </tr>
	    <tr>
	        <td><label>Convert latinchars</label></td>
	        <td align='left'>
<?php
            echo form_checkbox('CONVERT_LATINCHARS_IN','CONVERT_LATINCHARS_IN',$siteconfig->configSettings["CONVERT_LATINCHARS_IN"]== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Turn this on if you want to convert input latin characters to bibtex conform characters.
	        Latin characters are &uuml;, &ntilde;, &ccedil; etc...</td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>
	
	    <tr>
	        <td valign='top'><label for='BIBTEX_STRINGS_IN'>BibTeX strings:</label></td>
	        <td><textarea name='BIBTEX_STRINGS_IN' wrap='virtual' cols='50' rows='10'><?php echo $siteconfig->configSettings["BIBTEX_STRINGS_IN"]; ?></textarea></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        BibTeX allows definition of strings. Strings that are defined here are converted when importing BibTeX.
	        The correct syntax for strings is: @string {AIG = \"Aigaion bibliography System\"}<br/><br/></td>
	    </tr>

        <tr><td>
<?php
    echo form_submit('submit','Change');
?>
        </td>
        </tr>
    </table>
<?php
echo form_close();
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
?>
</div>