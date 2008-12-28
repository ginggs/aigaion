<?php header("Content-Type: text/html; charset=UTF-8"); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Client side embedding example -- cross subdomain</title>

  </head>
  <body>



<h1>Example of embedding a page from an Aigaion installation in another php web page</h1>
This example specifies a page from an Aigaion installation and embeds it in this larger example page.
<p>
In contrast to the simple embedding example, which retrieves the Aigaion page server-side (and includes it in the output server-side), this example retrieves the Aigaion data using an AJAX call from the client browser. Also, to a certain extent, this solution works across subdomains.
<p> 
To make this example work, you have to configure it properly for your own Aigaion installation. This takes a bit of work... And still needs to be documented.
<p>
<p>
Some remarks that need to be processed into the documentation:
<ul>
<li>The embedding page may reside in another subdomain, but should share the same parent domain as the Aigaion database: if the database is on aigaion.somedomain.nl, this page may be at somedomain.nl or at sub.somedomain.nl
<li>The Aigaion page is retrieved by a call from the client computer. This means that this Aigaion call actually uses the session that the user may already have established on the Aigaion server.
<li>This means that if the user who accesses this page is currently not logged in to Aigaion, he will get a login screen or only the public content, whereas, if he was already logged in to Aigaion, he will get in this page the content that he can access from his login.
<li>NOTE: after embedding the aigaion data, you can, on many systems, only do AJAX calls anymore to the shared parent domain somedomain.nl, and no longer to the subdomain on which this embedding page may reside... 
<li>Several anti virus programs, such as norton security, don't like Ajax calls. They may block this script from functioning. If they do, you can circumvent the problem by using an https connection rather than an http connection -- because then Norton doesn't reconize the Ajax call because it is encrypted.
<li>Note that in embedded views, the headers are lost. This means that embedded views should link to their javascrips in the body rather than in the header.
</ul>
<p>
<h2>Use case</h2>
<p>
Research group X maintains all their own publications in an Aigaion database on aigaion.xresearch.org<br>
Also, they run their Trac installation on trac.xresearch.org<br>
They want to embed content from the Aigaion installation in their Trac wiki (publication summaries, etc)<br>
They adapt this example solution to be able to load embedding divs with info from Aigaion into their Trac wiki.<br>
 
<!-- [EXPAND: HOW ABOUT THE SHARED LOGIN BETWEEN AIGAION AND TRAC, WHICH DOES NOT YET EXIST, BUT SHOULD?] -->


  <!-- all those prototype stuff needed for replacing content of divs -->
<script type="text/javascript" src="aigaion_clientsideembedding/prototype.js"></script>
    <script type="text/javascript" src="aigaion_clientsideembedding/scriptaculous.js"></script>
    <script type="text/javascript" src="aigaion_clientsideembedding/builder.js"></script>
    <script type="text/javascript" src="aigaion_clientsideembedding/externallinks.js"></script>
    <script type="text/javascript" src="aigaion_clientsideembedding/yahoo/YAHOO.js"></script>
    <script type="text/javascript" src="aigaion_clientsideembedding/yahoo/connection.js"></script>  
    
    <!-- this script will be called from the Aigaion embedding controller to explode the controller output out of the iframe into the higher level div -->
<script language="javascript">
  //important drawback, should be noted in docs: after this domain setting call, you can on many systems no longer call AJAX scripts to the subdomain where this script itself resides :)
  //i.e., not suitable for embedding Aigaion content from aigaion.domain.nl in another AJAX-app that resides in otherapp.domain.nl (but you *can* still do AJAX calls to the higher leven domain.nl, and you *can* use this for non-AJAX apps in otherapp.domain.nl)
   //this needs to be changed to your own domain:
  document.domain="aigaion.nl";
  function doEmbedding(text) { //DR: change name of function
    //alert('important drawback, should be noted in docs: after this domain setting call, you can on many systems no longer call AJAX scripts to the subdomain where this script itself resides :)');
    
    Element.replace('embeddingcontainer',text);
  }
</script>
<?php  

//insert docs & copyright etc

// ------------------------------------------------------------------------

/**
 0) 

 */	
?>
    
<div id='embeddingcontainer' name='embeddingcontainer'>
  This embedding container will end up containing the specific data that you tried to 
  retrieve from the Aigaion database. The iframe below is there to make cross-subdomain access possible first.
  This text, and the border below should disappear, if the script works allright! (Because the stuff inside the border will explode out of its enclosing iframe)
  <div style="border:5px black solid;width:50em;">
    <!-- adapt this URL to point to the aigaion installation in your own domain. Dont forget to adapt the embedded content, too! (example found in aigaionengine/controllers/embeddingtest.php) -->
    <iframe id="ifr" name="ifr" width=100% height=100% src="http://demo2.aigaion.nl/index.php/embeddingtest/embed/1054"/>
  </div>
</div>
</body>
</html>