<?php header("Content-Type: text/html; charset=UTF-8"); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Client side embedding example</title>

  </head>
  <body>
<h1>Example of embedding a page from an Aigaion installation in another php web page</h1>
This example specifies a page from an Aigaion installation and embeds it in this larger example page.
<p>
In contrast to the simple embedding example, which retrieves the Aigaion page server-side (and includes it in the output server-side), this example retrieves the Aigaion data using an AJAX call from the client browser.
<p> 
To make this example work, you have to configure it properly for your own Aigaion installation. (i.e. call up an output from your own Aigaion installation by moifying the YAHOO.util.Connect.asyncRequest call)
<p>
Note how the Aigaion output is enclosed in a div with a fat border. Also note that all links in the embedded output lead directly to the Aigaion page itself. This is because the output of the author/embed controller, the contents of which are retrieved in this example, has been designed in that way.
<p>
<p>
Some remarks that need to be processed into the documentation:
<ul>
<li>The Aigaion page is retrieved by a call from the client computer. This means that this Aigaion call actually uses the session that the user may already have established on the Aigaion server.
<li>This means that if the user who accesses this page is currently not logged in to Aigaion, he will get a login screen or only the public content, whereas, if he was already logged in to Aigaion, he will get in this page the content that he can access from his login.
<li>Because this embedding option uses simple Ajax calls, which have to adhere to the "same domain" restriction, this type of embedding only works if the surrounding page (this example page) resides in exactly the same domain.  
That is, output from https://aigaion.domain1.com can only embedded in pages that reside on https://aigaion.domain1.com 
<li>Several anti virus programs, such as norton security, don't like Ajax calls. They may block this script from functioning. If they do, you can circumvent the problem by using an https connection rather than an http connection -- because then Norton doesn't reconize the Ajax call because it is encrypted.
</ul>
<p>
<h2>Use case</h2>
<p>
Research group X maintains all their own publications in an Aigaion database.
Each researcher in the group wants to embed his own list of publications on his homepage. By default, visitors should not be able to download the pdfs of the publications, due to copyright issues. Because a random visitor will only see anonymous guest output, this is not a problem: the guest user account simply doesn't have attachment-read-rights. However, a visitor who is at the same time logged in into Aigaion may have the right to read attachments, and in that case *should* get the pdf download links.  

<p><p>
<div style="border:5px black solid;width:50em;">

<?php  

//insert docs & copyright etc

// ------------------------------------------------------------------------

/**
 0) 
 1) The embedding page should reside in the same subdomain as the Aigaion database
 2) Several anti virus programs, such as norton security, don't like Ajax calls. They may block this script from functioning. If they do, you can circumvent the problem by using an https connection rather than an http connection 
 3) Note that in embedded views, the headers are lost. This means that embedded views should link to their javascrips in the body rather than in the header.
 */	
?>
    <script type="text/javascript" src="aigaion_clientsideembeddingwithinsubdomain/prototype.js"></script>
    <script type="text/javascript" src="aigaion_clientsideembeddingwithinsubdomain/scriptaculous.js"></script>
    <script type="text/javascript" src="aigaion_clientsideembeddingwithinsubdomain/builder.js"></script>
    <script type="text/javascript" src="aigaion_clientsideembeddingwithinsubdomain/externallinks.js"></script>
    <script type="text/javascript" src="aigaion_clientsideembeddingwithinsubdomain/yahoo/YAHOO.js"></script>
    <script type="text/javascript" src="aigaion_clientsideembeddingwithinsubdomain/yahoo/connection.js"></script>
<div id='a' name='a'>
This content should be replaced by embedded Aigaion content
<script language="javascript">
    var callback =
    {
	    success: function(o) {
	      Element.replace('a',o.responseText);
        return;
      },    	  
      failure: function(o) {
        Element.replace('a',o.statusText);
        return;
      }
    }
    //change this to retrieve a page from your own Aigaion database!
    YAHOO.util.Connect.asyncRequest('AJAX','http://demo2.aigaion.nl/index.php/authors/embed/1054',callback);
</script>
</div>
</body>
</html>