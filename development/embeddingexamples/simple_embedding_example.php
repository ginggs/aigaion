<?php header("Content-Type: text/html; charset=UTF-8"); ?>
<?php
/*
This example shows one simple way to embed Aigaion output in another php page.
Simply set the parameters as below and include the aigaion_embed file.

Note that the $pageToEmbed may contain links. Often, such links may lead to pages
in Aigaion. If the user follows such a link, he or she will end up on the Aigaion
page itself. If you have set $dologin here, note that the user, after following the link, 
will *not* be logged in. If anymous access is disallowed, the user will then be presented
with a login screen. If anonymous access is allowed, the user may get a message 
that the requested resource does not exist, *because the resource does not exist 
for the anonymous user*

In the example in this file, try this effect out:
- set dologin to true, 
- view the output of this page in the browser
- follow the link to the second paper by Klapuri
- and observe the message "View publication: non-existing publication id passed"

So. Take this into account when designing embedding pages following this simple 
example.

Basic ideas behind this type of embedding contributed by {\O}yvind Hauge
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Example of simple embedding in Aigaion</title>
</head>
<body>
<h1>Example of embedding a page from the Aigaion demo installation in another php web page</h1>
This example specifies a page from the Aigaion 2 demo installation and embeds it in this larger example page, using the demo login to get access to the database server side. The example can be modified so that it does not use a login account, but displays publications using the anonymous guest login instead.
<p>
Note the Aigaion output below, enclosed in a div with a fat border. Also note that all links in the embedded output lead directly to the Aigaion page itself. This is because the output of the author/embed controller, the contents of which are retrieved in this example, has been designed in that way.
<p>

<p><p>
Finally, note that in these embedded views, the headers are lost. This means that embedded views should link to their javascrips not in the header, but in the body.
<p><p>
Some remarks that need to be processed into the documentation:
<ul>
<li>Login to Aigaion is done server side. This means that you can embed Aigaion content to which the visitor of your web page would not have had access rights if he were to login to Aigaion using his own account.
<li>This additionally means that if the embedded page contains links (e.g. to publications, or pdf downloads, or bibtex downloads, ...), and the visitor to your web site follows the link, he may find that the link is actually not accessible to him. This would result is error messages such as 'non existing attachment id' or 'access denied'.
<li>The above problem would, for obvious reasons, not occur if you are embedding public content only (i.e. specifying <i>$doLogin=false</i> in the script)
<li>In contrast to the other embedding examples in this directory, this example works from any combination of embedding page domain / aigaion domain. That is, you can have this embedding script reside at www.domain1.com and embed Aigaion output from aigaion.domain2.com without problems.
</ul>
<h2>Does it work on this server?</h2>
If you do not see publications of A. Klapuri at the ISMIR 2006 in the box below, your server might not support the httpclient used in this example. You could try to find out whether, for example, the curl libraries are supported on your system -- that might help.
<div style="border:5px black solid;width:50em;">
<?php
//====== BEGIN EMBEDDING CODE ======  
//the page to embed, as an aigaion anchor, without slash at end
$pageToEmbed = "authors/embed/1054"; 
//the aigaion root, without index.php suffix, with slash at end
$aigaionRoot = "http://demo2.aigaion.nl/";  
//login info: if dologin set to false, the embedding will not 
//attempt to login, and therefore will only show public access 
//information. If anonymous access is not enabled on the aigaion 
//server, this will result in a loginscreen being shown
$dologin = true; //note that in this example, the demo user 
                  //has access to one more publication than 
                  //the anonymous user  
$loginName = "demo";
$loginPass = "demo"; 
require ("aigaion_simpleembedding/aigaion_embed.php");
//====== END EMBEDDING CODE ====== 
?>
</div>
</body>
</html>