<?php
/*
This class will insert/embed into the html output the contents of an Aigaion page.
For parameters and use example, see the file ../simple_embedding_example.php

*/

function getSimpleEmbedding($pageToEmbed )
{
$pageToEmbed = str_replace(' ','%20',$pageToEmbed);
  require_once(dirname(__FILE__) . "/httpclient/http.php");
  global $wgAigaion;
  $result = "";
	//set_time_limit(0);
	$http=new http_class;

	/* Connection timeout */
	$http->timeout=0;

	/* Data transfer timeout */
	$http->data_timeout=0;

	/* Output debugging information about the progress of the connection */
	$http->debug=0;

	/* Format dubug output to display with HTML pages */
	$http->html_debug=1;


	/*
	 *  Need to emulate a certain browser user agent?
	 *  Set the user agent this way:
	 */
	//$http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";

	/*
	 *  If you want to the class to follow the URL of redirect responses
	 *  set this variable to 1.
	 */
	$http->follow_redirect=1;

	/*
	 *  How many consecutive redirected requests the class should follow.
	 */
	$http->redirection_limit=5;

	/*
	 *  If your DNS always resolves non-existing domains to a default IP
	 *  address to force the redirection to a given page, specify the
	 *  default IP address in this variable to make the class handle it
	 *  as when domain resolution fails.
	 */
	$http->exclude_address="";

	/*
	 *  If you want to establish SSL connections and you do not want the
	 *  class to use the CURL library, set this variable to 0 .
	 */
	$http->prefer_curl=0;

	/*
	 *  If basic authentication is required, specify the user name and
	 *  password in these variables.
	 */
  if ($wgAigaion['link']['dologin'])
  {
    $url= $wgAigaion['root']."/login/dologin/".$pageToEmbed;
  } 
  else
  {
   $url= $wgAigaion['root']."/".$pageToEmbed;
  }
	

	/*
	 *  Generate a list of arguments for opening a connection and make an
	 *  HTTP request from a given URL.
	 */
	$error=$http->GetRequestArguments($url,$arguments);

  if ($wgAigaion['link']['dologin'])
  {
    if (!isset($wgAigaion['link']['loginname']) || ($wgAigaion['link']['loginname']==''))
    {
      wfLoadExtensionMessages( 'AigaionFunctions' );
      return '<strong class="error">' . wfMsg( 'aigaion_config_incomplete','loginname') . '</strong>';
    }
    if (!isset($wgAigaion['link']['loginpass']) || ($wgAigaion['link']['loginpass']==''))
    {
      wfLoadExtensionMessages( 'AigaionFunctions' );
      return '<strong class="error">' . wfMsg( 'aigaion_config_incomplete','loginpass') . '</strong>';
    }
  	$arguments["RequestMethod"]="POST";
  	$arguments["PostValues"]=array(
  		"loginName"=>$wgAigaion['link']['loginname'],
  		"loginPass"=>$wgAigaion['link']['loginpass'],
  		"remember"=>"remember"
  	);
	}
//	$arguments["PostFiles"]=array(
//		"userfile"=>array(
//			"Data"=>"This is just a plain text attachment file named attachment.txt .",
//			"Name"=>"attachment.txt",
//			"Content-Type"=>"automatic/name",
//		),
//		"anotherfile"=>array(
//			"FileName"=>"test_http_post.php",
//			"Content-Type"=>"automatic/name",
//		)
//	);

	/* Set additional request headers */
	$arguments["Headers"]["Pragma"]="nocache";

	$error=$http->Open($arguments);

	if($error=="")
	{
		$error=$http->SendRequest($arguments);

		if($error=="")
		{
			for(Reset($http->request_headers),$header=0;$header<count($http->request_headers);Next($http->request_headers),$header++)
			{
				$header_name=Key($http->request_headers);
				if(GetType($http->request_headers[$header_name])=="array")
				{
					for($header_value=0;$header_value<count($http->request_headers[$header_name]);$header_value++)
						;//echo $header_name.": ".$http->request_headers[$header_name][$header_value],"\r\n";
				}
				else
					;//echo $header_name.": ".$http->request_headers[$header_name],"\r\n";
			}
			//echo "</PRE>\n";
			flush();

			$headers=array();
			$error=$http->ReadReplyHeaders($headers);
			if($error=="")
			{
				//echo "<H2><LI>Response status code:</LI</H2>\n<P>".$http->response_status;
				switch($http->response_status)
				{
					case "301":
					case "302":
					case "303":
					case "307":
						//echo " (redirect to <TT>".$headers["location"]."</TT>)<BR>\nSet the <TT>follow_redirect</TT> variable to handle redirect responses automatically.";
						break;
				}
				//echo "</P>\n";
				//echo "<H2><LI>Response headers:</LI</H2>\n<PRE>\n";
				for(Reset($headers),$header=0;$header<count($headers);Next($headers),$header++)
				{
					$header_name=Key($headers);
					if(GetType($headers[$header_name])=="array")
					{
						for($header_value=0;$header_value<count($headers[$header_name]);$header_value++)
							;//echo $header_name.": ".$headers[$header_name][$header_value],"\r\n";
					}
					else
						;//echo $header_name.": ".$headers[$header_name],"\r\n";
				}
				//echo "</PRE>\n";
				//flush();

				//echo "<H2><LI>Response body:</LI</H2>\n<PRE>\n";
				for(;;)
				{
					$error=$http->ReadReplyBody($body,1000);
					if($error!=""
					|| strlen($body)==0)
						break;
					$result = $body;
				}
				//echo "</PRE>\n";
				flush();
			}
		}
		$http->Close();
	}
	if(strlen($error))
		$result .=  "<CENTER><H2>Error: ".$error."</H2><CENTER>\n";
	return $result;
}
