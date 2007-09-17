<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for whether the current version of Aigaion is up-to-date
| -------------------------------------------------------------------
|
|   Provides information whether the version of Aigaion is up-to-date by checking
|   version information on http://aigaion.nl
|   Used by the login module: once every 2 days, when a dbadmin logs in, a check is done whether 
|   the current version is up-to-date. A very short time-out is used, to make it least intrusive.
|   If an update is available, a warning is returned and the up-to-date-check is not performed for 
|   48 hours for this dbadmin user. If the new version is a security update, a red warning ('error message')
|   is returned.
|
|	Usage:
|       $this->load->helper('checkupdates'); //load this helper
|       checkUpdate(); //is Aigaion up to date? if not, an message is generated using appendMessage or appendErrorMessage
|       
*/
    /** Returns a piece of HTML giving the result of the check for updates, or empty string if no update available. */
    function checkUpdates() {
        $CI = &get_instance();
        $CI->load->helper('readremote');
        //return '<div class="message">no info on updates available</div>';
        #try with short timeout, to get version info from aigaion.nl
        $remoterelease = getRemoteFile ('http://gonnagles.adsl.utwente.nl:81/aigaion2/index.php/version');
        if ($remoterelease == '') {
            return "<div class='message'>Couldn't connect to demo.aigaion.nl to check for updates</div>";
        }
        if ($remoterelease == '') {
            return "<div class='message'>Couldn't obtain release version info from demo.aigaion.nl</div>";
        }
        #compare info to current version
        $CI->db->orderby('version','desc');
        $CI->db->limit(1);
        $Q = $CI->db->get('changehistory');
        foreach ($Q->result() as $R) {
            $thisrelease = $R->version;
        }
        #if same: report 'up to date'
        if ($remoterelease==$thisrelease) {
            return '';
        }
        $result = '<p>There is a new version available: <b>'.$remoterelease.'</b> (Current version: '.$thisrelease.')<br/>';
        #if deviation: get detailed info for change history from aigaion.nl
        $remotechangehistory = getRemoteFile("http://gonnagles.adsl.utwente.nl:81/aigaion2/index.php/version/details/".$thisrelease);
        #parse detailed info
        $class='message';
        if ($remotechangehistory=='') {
            $result .= "Couldn't obtain detailed update info from demo.aigaion.nl<br/>";
        } else { 
            #note: we use quite ugly parsing here - assuming that version/details outputs exactly what we expect and assuming that description contains NO XML
            $p = xml_parser_create();
            xml_parse_into_struct($p, $remotechangehistory, $vals, $index);
            xml_parser_free($p);
            $i = 1;
            $history = array();
            $alltypes = '';
            while (($i+3) < count($vals)) { //the last one is the close for the changehistory
                $release = $vals[$i+1]['value'];
                $type    = $vals[$i+2]['value'];
                $alltypes.=','.$type;
                $description = $vals[$i+3]['value'];
                $history[] = array($release,$type,$description);
                $i+=5;
            }
            if (strpos($alltypes,'security')>0) { //if this is a security update
                $class='errormessage';
                //also extend message with extra warning
                $result .= 'Note: the updated version contains security fixes. You are strongly recommended to get the latest version of Aigaion.<br/>';
            }
            $result .= '<span class=header2>Detailed info for available updates: </span><br/>';
            //print out the new versions into $result
            $result .= "
              <table class=tablewithborder>
                <tr>
                    <td class='tablewithborder'></td>
                    <td class='tablewithborder' colspan=4>Types of update</td>
                    <td class='tablewithborder'></td>
                </tr>
                <tr>
                    <td class='tablewithborder'>Version</td>
                    <td class='tablewithborder'>bugfix</td>
                    <td class='tablewithborder'>features</td>
                    <td class='tablewithborder'>layout</td>
                    <td class='tablewithborder'>security</td>
                    <td class='tablewithborder'>Description</td>
                </tr>
                ";
            foreach ($history as $version) {
                $result .= '<tr>';
                $result .= '<td class="tablewithborder">'.$version[0].'</td>';
                $result .= '<td class="tablewithborder">';
                if (!(strpos($version[1],'bugfix')===false)) {
                    $result .= '<img class="icon" title="Some bugs were fixed this release" src="'.getIconUrl('check.gif').'"/>';
                }
                $result .= '</td><td class="tablewithborder">';
                if (!(strpos($version[1],'features')===false)) {
                    $result .= '<img class="icon" title="Some features were added this release" src="'.getIconUrl('check.gif').'"/>';
                }
                $result .= '</td><td class="tablewithborder">';
                if (!(strpos($version[1],'layout')===false)) {
                    $result .= '<img class="icon" title="Some layout elements were changed this release" src="'.getIconUrl('check.gif').'"/>';
                }
                $result .= '</td><td class="tablewithborder">';
                if (!(strpos($version[1],'security')===false)) {
                    $result .= '<img class="icon" title="This release contains security fixes!" src="'.getIconUrl('check.gif').'"/>';
                }
                $result .= '</td>';
                $result .= '<td class="tablewithborder">'.$version[2].'</td>';
                $result .= '</tr>';            
            }
            $result .= '</table>';
        }
        #give message depending on type of update (normal, minor, security, etc; max type that was missed since current version of installation); 
        $result .= "<p>You can download the new version <a href='http://www.aigaion.nl'>here</a>.";
        #update status of 'last check for this user'
        //return message or errormessage
        return '<div class="'.$class.'">'.$result.'</div>';
    }

?>