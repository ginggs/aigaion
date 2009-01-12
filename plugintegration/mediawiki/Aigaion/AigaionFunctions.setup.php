<?php

# Guard against direct invocation from the web, print a friendly help message
#
if ( !defined( 'MEDIAWIKI' ) ) {
    echo <<<EOT
<html><body>
<p>This is the Aigaion extension. To enable it, put the 
following in your LocalSettings.php:</p>
<pre>
  # Aigaion plugin settings
  /* the root URL of the aigaion installation, including the index.php */
  \$wgAigaion['root'] = "http://demo2.aigaion.nl/index.php";
  /* the lowest domain level that is shared between this wiki and the 
     Aigaion installation. Must be at least two levels deep... 
     Only necessary if you do the embedding, nbot if you only want to use the 
     aigaionlink function */ 
  \$wgAigaion['shareddomain'] = "aigaion.nl";
  /** If true, no data is retrievbed from the Aigaion server and no login is
   * performed, instead a simple link is shown */
  \$wgAigaion['link']['simplelink'] = False;
  /* login info: if dologin set to false, the embedding will not 
   * attempt to login, and therefore will only show public access 
   * information. If anonymous access is not enabled on the aigaion 
   * server, this will result in a loginscreen being shown
   */ 
  \$wgAigaion['link']['dologin'] = True;
  \$wgAigaion['link']['loginname'] = "demo";
  \$wgAigaion['link']['loginpass'] = "demo"; 
  # load the plugin
  require_once( "\$IP/extensions/Aigaion/AigaionFunctions.setup.php" );
</pre>
Furthermore, you must configure your Aigaion database properly for this parser plugin to work. 
</body></html>
EOT;
    exit( 1 );
}


# Define a setup function
$wgExtensionFunctions[] = 'aigaionParserFunction_Setup';
# Add a hook to initialise the magic word
$wgHooks['LanguageGetMagic'][]       = 'aigaionParserFunction_Magic';

$wgExtensionCredits['parserhook'][] = array(
	'name' => 'AigaionFunctions',
	'version' => '0.1',
	'url' => 'http://wiki.aigaion.nl',
	'author' => 'Dennis Reidsma',
	'description' => 'Enhance parser with functions for embedding Aigaion content',
	'descriptionmsg' => 'aigaion_desc',
);

$wgExtensionMessagesFiles['AigaionFunctions'] = dirname(__FILE__) . '/AigaionFunctions.i18n.php';

# init some necessary variables
$wgAigaion['scriptsloaded'] = false;
$wgAigaion['counter'] = 0;
//include the file that is going to do most of the work...
require_once(dirname(__FILE__) . "/AigaionFunctions.body.php");
    
function aigaionParserFunction_Setup() {
    global $wgParser;
    # Set a function hook associating the "example" magic word with our function
    $wgParser->setFunctionHook( 'aigaionlink', 'aigaionlinkParserFunction_Render' );
    $wgParser->setFunctionHook( 'aigaion', 'aigaionParserFunction_Render' );
}

/**
 * The 'magic word' for the new parser functions defined in this extension
 * is not i18n: the same magic words in any language */
function aigaionParserFunction_Magic( &$magicWords, $langCode ) {
    # Add the magic word
    # The first array element is case sensitivity, in this case it is not case sensitive
    # All remaining elements are synonyms for our parser function
    $magicWords['aigaion'] = array( 0, 'aigaion','aig');
    $magicWords['aigaionlink'] = array( 0, 'aigaionlink','aiglink');
    return true;
}

/**
 * the #aigaion function embeds Aigaion content in mediawiki pages:
 * publication summaries, publication lists of authors, etc. For _linking_
 * to Aigaion entries, see the #aigaionlink function. 
 * 
 * The different embeddings have different arguments: 
 * - "pub" | (pub_id or bibtex_id)  
 *        
 * - "topic" | (topic_id or path / of / topic names)
 * 
 * The #Aigaion function is based on the cross-subdomain Aigaion 
 * embedding example.
 */ 
function aigaionParserFunction_Render( &$parser, $param1 = '', $param2 = '', $param3 = '' ) {
    //depending on parameter 1, gather other parameters and call function that returns embedding content
    $output = ensureJavaScripts();
    $output .= getAigaionEmbedding($param1, $param2, $param3);
    $parser->disableCache();
    return $parser->insertStripItem( $output, $parser->mStripState );
}

/**
 * the #aigaionlink function shows a link to an Aigaion entry.
 * 
 * The different lkink types have different arguments: 
 * - "pub" | (pub_id or bibtex_id) | (simple)
 *        
 * - "topic" | (topic_id or path / of / topic names)
 * 
 * The #Aigaionlink function is based on the simple Aigaion embedding example.
 * 
 * If $wgAigaion['link']['simplelink'] is set and True, a simple link is shown 
 *  without invoking the simple embedding. Useful if wiki server does not allow 
 *  httpclient calls
 */ 
function aigaionlinkParserFunction_Render( &$parser, $param1 = '', $param2 = '', $param3 = '') {
    //depending on parameter 1, interpret other parameters and return link
    global $wgAigaion;
    $output = "";
    if (    (strtolower($param3) == 'simple') 
         || (    isset($wgAigaion['link']['simplelink']) 
              && ($wgAigaion['link']['simplelink'] == True)
            )
       ) 
    {
      $output = getSimpleAigaionLink($param1, $param2);
    }
    else 
    {
      $output = getAigaionLink($param1, $param2);
    }

    $parser->disableCache(); //'cause we don't know when the database content changes...
    return $parser->insertStripItem( $output, $parser->mStripState );
}

