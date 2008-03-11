<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Site extends Controller {

	function Site()
	{
		parent::Controller();	
	}
	
	/** Pass control to the site/configure/ controller */
	function index()
	{
		$this->configure();
	}

    /** 
    site/configure
        
    Fails when unsufficient user rights
    
    Paramaters:
        3rd segment: if 3rd segment is 'commit', site config data is expected in the POST
    
    Returns a full html page with a site configuration form. */
	function configure()
	{
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('database_manage'))
            ) 
        {
	        appendErrorMessage('Configure database: insufficient rights.<br/>');
	        redirect('');
        }
        
	    $commit = $this->uri->segment(3,'');
	    
	    $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');
	    if ($commit=='commit') {
	        $siteconfig = $this->siteconfig_db->getFromPost();
	        if ($siteconfig!= null) {
    	        //do validation
                //----no validation rules are implemented yet. When validation rules are defined, see e.g. users/commit for
                //examples of validation code
            	//if ($this->validation->run() == TRUE) {
    	            //if validation successfull, set settings
    	            $siteconfig->update();
    	            $siteconfig = $this->siteconfig_db->getSiteConfig();
    	        //}
    	    }
	    } else {
	        $siteconfig = $this->siteconfig_db->getSiteConfig();
	    }
	    
        //get output: always return to configuration page
        $headerdata = array();
        $headerdata['title'] = 'Site configuration';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        $output .= $this->load->view('site/edit',
                                      array('siteconfig'=>$siteconfig),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}

	/** 
	site/maintenance
	
	Entry point for maintenance functions.
	
	Fails with error message when one of:
	    insufficient user rights
	    non-existing maintenance function given

	Paramaters:
	    3rd segment: name of the maintenance function to be executed (can be 'all')
	    
	Returns:
	    A full HTML page presenting 
	        the maintenance options
	        plus, if a maintenance function is given, the result of the chosen maintenance option 
	*/
	function maintenance()
	{
	    $this->load->helper('maintenance');
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('database_manage'))
            ) 
        {
	        appendErrorMessage('Maintain database: insufficient rights.<br/>');
	        redirect('');
        }

	    $maintenance = $this->uri->segment(3,'');

        $checkresult = "<table class='message' width='100%'>";
        
	    switch ($maintenance) {
	        case 'all':
	        case 'attachments':
	            $checkresult .= checkAttachments();
	            if ($maintenance != 'all') 
	                break;
	        case 'topics':
	            $checkresult .= checkTopics();
	            if ($maintenance != 'all') 
	                break;
	        case 'notes':
	            $checkresult .= checkNotes();
	            if ($maintenance != 'all') 
	                break;
	        case 'authors':
	            $checkresult .= checkAuthors();
	            if ($maintenance != 'all') 
	                break;
	        case 'passwords':
	            $checkresult .= checkPasswords();
	            if ($maintenance != 'all') 
	                break;
	        case 'cleannames':
	            $checkresult .= checkCleanNames();
	            if ($maintenance != 'all') 
	                break;
	        case 'publicationmarks':
	            $checkresult .= checkPublicationMarks();
	            if ($maintenance != 'all') 
	                break;
	        case 'checkupdates':
	            $this->load->helper('checkupdates');
                $checkresult .= "<tr><td colspan=2><p class='header1'>Aigaion updates</p></td></tr>\n";
	            $checkresult .= "<tr><td>Checking for updates...</td>";
//	            $updateinfo = '';
	            $updateinfo = checkUpdates();
	            if ($updateinfo == '') {
    		        $checkresult .= '<td><b>OK</b></td></tr>';
        			$checkresult .= '<tr><td colspan=2><div class="message">This installation of Aigaion is up-to-date</div></td></tr>';
	            } else {
        			$checkresult .= '<td><span class="errortext">ALERT</span></td>';
        			$checkresult .= '</tr>';
        			$checkresult .= '<tr><td colspan=2>'.$updateinfo.'</td></tr>';
    	        }
	            //if ($maintenance != 'all') 
	            break;
	        case '':
	            break;
	        default:
    	        appendMessage('Maintenance function "'.$maintenance.'" not implemented.<br>');
	            break;
	    }
	    
	    $checkresult .= "</table>";
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Site maintenance';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);
        
        $output .= $checkresult;
        
        $output .= $this->load->view('site/maintenance',
                                      array(),
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
    }
    
	/** 
	site/backup
	
	Entry point for backup
	
	Fails with error message when one of:
	    insufficient user rights

	Paramaters:
	    3rd segment: win|unix|mac  determines linebreaks
	    
	Returns:
	    A sql file
	*/
	function backup()
	{
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('database_manage'))
            ) 
        {
	        appendErrorMessage('Backup database: insufficient rights.<br/>');
	        redirect('');
        }

	    $type = $this->uri->segment(3,'win');
	    if (!in_array($type,array('win','unix','mac'))) {
	        $type = 'win';
	    }
		if ($type == "win")
			$linebreak = "\r\n";
		else if ($type == "unix")
			$linebreak = "\n";
		else if ($type == "mac")
			$linebreak = "\r";

        // Load the DB utility class
        $this->load->dbutil();
        
        //tables to backup: only those with right prefix, if prefix set (!)
        $tables=array();
        if (AIGAION_DB_PREFIX!='') {
            foreach ($this->db->list_tables() as $table) {
                $p = strpos($table,AIGAION_DB_PREFIX);
                if (!($p===FALSE)) {
                    if ($p==0) {
                        $tables[]=$table;
                    }
                }
            }
        }
        // Backup your entire database and assign it to a variable
        //note: we could make a site setting for whether a gz, zip or txt is returned. But gz is OK, I guess.
        $backup =$this->dbutil->backup(array('tables'=>$tables,'newline'=>$linebreak,'format'=>'txt'));
        
        // Load the download helper and send the file to your desktop
        $this->load->helper('download');
        force_download(AIGAION_DB_NAME."_backup_".date("Y_m_d").'.sql', $backup);
        
    }

	/** 
	site/restore
	
	Entry point for restoring from a backup file
	
	Fails with error message when one of:
	    insufficient user rights

	Paramaters:
	    uploaded sql file from earlier backup
	    
	Returns:
	    To the front page, with a message
	*/
	function restore()
	{
	}
}
?>