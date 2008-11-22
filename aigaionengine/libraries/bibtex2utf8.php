<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
//Code adapted from org.variorum.services.bibtex.UTF8Converter.java
//see https://variorum.htmlweb.com/trac/browser/webappDemo/trunk/src/org/variorum/services/bibtex/UTF8Converter.java

/*
| -------------------------------------------------------------------
|  Library for special character conversion (bibtex<->utf8)
| -------------------------------------------------------------------
|
|   Provides several functions for special character conversion (bibtex<->utf8)
|
|   Though based upon the old specialcharfunctions of aigaion 1.x,
|   this helper ONLY considers itself with converting certain UTF-8 encoded
|   characters to BiBTeX equivalents and vice versa. No more helper functions 
|   are available for conversion to html entities and such - as we use utf-8
|   this is no longer needed in most cases; and for the quote replacement, other 
|   possibilities exist.
|
|   This helper is of course woefully incomplete - we can never capture ALL bibtex codes 
|   and their utf8 equivalents. We use a number of codes hardcoded in this file.
|   Do you find missing codes there? Just suggest the additions to the Aigaion developers.
|
|   We expect that this helper is only loaded on import and export of bibtex.
|
| Note:
| A string containing math code will not be converted from bibtex to utf8 -- the
| risks of making a mistake are currently too large, we need some more time for
| extensive coding for that :)
|
|    Usage:
|       //load this library:
|       $this->load->library('bibtex2utf8');
|       

    $this->bibtex2utf8->utf8ToBibCharsFromArray(&$array)
        converts utf8 chars to bibtex special chars from an array

    $this->bibtex2utf8->utf8ToBibCharsFromString(&$array)
        converts utf8 chars to bibtex special chars from a string

    $this->bibtex2utf8->bibCharsToUtf8FromArray(&$array)
        converts bibtex special chars to utf8 chars from an array

    $this->bibtex2utf8->bibCharsToUtf8FromString(&$string)
        converts bibtex to utf8 chars special chars from a string

TODO:
 make a test file to test as many conversions up and down as possible, including weird and slightly erroneous brace usage (such as that of DBLP)
 add polish charset
 add some often used symbols such as the copyright, trademark, etc?
 handle empty suffix (e.g. \l{}ambda )

If you want to add extra character conversions:
  check which group it belongs to (one of the four below)
  add its entry
  add the reverse conversion in the reverse lists
  don't forget to take care of escapes needed for PHP as well as those needed for regexps!
*/

class Bibtex2utf8 {

    var $accentedLetters = array();
    var $combinedLetters = array();
    var $stringsAndCommands = array();
    var $specialChars = array();
    var $specialCharsBack = array();
    
    function Bibtex2utf8()
    {
      $this->init();
      //$this->test();
    }

    function test() 
    {
      appendMessage("Testing the character conversions......<br/><br>");
      appendMessage("Convert to utf8: ".$this->bibCharsToUtf8FromString("\\'eBLIEB{\\'e}\\'{e}{\\'{e}}")."<br/>");
      appendMessage("Convert to utf8: ".$this->bibCharsToUtf8FromString("\\'eBLIEB{\\\"e}\\`{e}{\\^{e}}\\~e\\=e")."<br/>");
      appendMessage("Convert to utf8: ".$this->bibCharsToUtf8FromString("\\'EBLIEB{\\'E}\\'{E}{\\'{E}}")."<br/>");
      appendMessage("Convert to utf8: ".$this->bibCharsToUtf8FromString("\\l\\L\\lambda{\\l}ambda")."<br/>");
      appendMessage("Convert to utf8: ".$this->bibCharsToUtf8FromString("\\c cBLIEB\\c{c}{\\c c}{\\c{c}}")."<br/>");
      appendMessage("Convert to utf8: ".$this->bibCharsToUtf8FromString("\\c CBLIEB\\c{C}{\\c C}{\\c{C}}")."<br/>");
      appendMessage("Convert to utf8: ".$this->bibCharsToUtf8FromString("\\#\\\\\\?\\$\\{\\}\\%\\_\\v s\\v S")."<br/>");
      appendMessage("Convert to bibtex: ".$this->utf8ToBibCharsFromString("éëèêẽē")."<br/>");
      appendMessage("Convert to bibtex: ".$this->utf8ToBibCharsFromString("ł\\lambda")."<br/>");
      appendMessage("Convert to bibtex: ".$this->utf8ToBibCharsFromString("çÇšŠ")."<br/>");
      appendMessage("Convert to bibtex: ".$this->utf8ToBibCharsFromString("#?\\\${}%_")."<br/>");
      appendMessage("Testing the character conversions finished.<br/>");
      
    }
    
    function utf8ToBibCharsFromArray($array)
    {
        $keys = array_keys($array);
        foreach ($keys as $key)
        {
            $array[$key] = $this->utf8ToBibCharsFromString($array[$key]);
        }
        return $array;
    }
    
    function utf8ToBibCharsFromString($string)
    {
        //DR: if string contains math, don't convert at all, as it only leads to problems... 
        if (preg_match("/(^\\$|[^\\\\]\\$)/i", $string) ==1) return $string;
        if (preg_match("/\\\\ensuremath(\\s)*\\{/i", $string) ==1) return $string;
        if (preg_match("/\\\\\\(/i", $string) ==1) return $string;
        if (preg_match("/\\\\begin(\\s)*\\{math\\}/i", $string) ==1) return $string;
        
        foreach ($this->combinedLetters as $cl) 
        {
          $char1 = $cl[0];
          $char2 = $cl[1];
          $utf8char = $cl[2];
          $string = preg_replace("/".$utf8char."/", "{\\".$char1." ".$char2."}", $string);
        }
        foreach ($this->accentedLetters as $al) 
        {
          $accent = $al[0];
          $char = $al[1];
          $utf8char = $al[2];
          $string = preg_replace("/".$utf8char."/", "{\\".$accent."{".$char."}}", $string);
          $accent = utf8_strtoupper($al[0]);
          $char = utf8_strtoupper($al[1]);
          $utf8char = utf8_strtoupper($al[2]);
          $string = preg_replace("/".$utf8char."/", "{\\".$accent."{".$char."}}", $string);
        }
        //restore {\I}
        $string = preg_replace("/\\{\\\\I\\}/", "{I}", $string);
        foreach ($this->stringsAndCommands as $sac) 
        {
          $command = $sac[0];
          $utf8char = $sac[1];
          $string = preg_replace("/".$utf8char."/", "{\\".$command."}", $string);
        }
        foreach ($this->specialCharsBack as $sc) 
        {
          $command = $sc[0];
          $utf8char = $sc[1];
          $string = preg_replace("/".$utf8char."/", $command, $string);
        }
        return $string;
    }
    
    //        converts bibtex special chars to utf8 chars from an array
    function bibCharsToUtf8FromArray($array) {
        $keys = array_keys($array);
        foreach ($keys as $key)
        {
            $array[$key] = $this->bibCharsToUtf8FromString($array[$key]);
        }
        return $array;
    }
    
    //        converts bibtex to utf8 chars special chars from a string
    function bibCharsToUtf8FromString($string) {
        //DR: if string contains math, don't convert at all, as it only leads to problems... 
        if (preg_match("/(^\\$|[^\\\\]\\$)/i", $string) ==1) return $string;
        if (preg_match("/\\\\ensuremath(\\s)*\\{/i", $string) ==1) return $string;
        if (preg_match("/\\\\\\(/i", $string) ==1) return $string;
        if (preg_match("/\\\\begin(\\s)*\\{math\\}/i", $string) ==1) return $string;
        
        foreach ($this->accentedLetters as $al) 
        {
          $accent = $al[0];
          $char = $al[1];
          $utf8char = $al[2];
          $regexp = "/(\\\\".$accent."(".$char."|\\{".$char."\\})|\\{\\\\".$accent."(".$char."|\\{".$char."\\})\\})/";
          $string = preg_replace($regexp, $utf8char, $string);
          $accent = utf8_strtoupper($al[0]);
          $char = utf8_strtoupper($al[1]);
          $utf8char = utf8_strtoupper($al[2]);
          $regexp = "/(\\\\".$accent."(".$char."|\\{".$char."\\})|\\{\\\\".$accent."(".$char."|\\{".$char."\\})\\})/";
          $string = preg_replace($regexp, $utf8char, $string);
        }
        foreach ($this->combinedLetters as $cl) 
        {
          $char1 = $cl[0];
          $char2 = $cl[1];
          $utf8char = $cl[2];
          $regexp = "/(\\\\".$char1."(\\s".$char2."|\\{".$char2."\\})|\\{\\\\".$char1."(\\s".$char2."|\\{".$char2."\\})\\})/";
          $string = preg_replace($regexp, $utf8char, $string);
        }
        foreach ($this->stringsAndCommands as $sac) 
        {
          $command = $sac[0];
          $utf8char = $sac[1];
          $regexp = "/\\{\\\\".$command."(\\{\\})?\\}/";
          $string = preg_replace($regexp, $utf8char, $string);
          $regexp = "/\\\\".$command."(\\{\\})/";
          $string = preg_replace($regexp, $utf8char, $string);
          $regexp = "/\\\\".$command."(\\W)/"; //remove that whitespace!
          $string = preg_replace($regexp, $utf8char, $string);
        }
        foreach ($this->specialChars as $sc) 
        {
          $command = $sc[0];
          $utf8char = $sc[1];
          $regexp = "/(\\\\".$command."|\\{\\\\".$command."\\})/";
          $string = preg_replace($regexp, $utf8char, $string);
        }
        return $string;
    }

    function init()
    {
      $CL = &get_instance();
      $CL->load->helper('utf8');
    
      /*
      \'e (for any type of accent) with any type of braces
        backslash accent letter
        needed info: 
          which accent, 
          which letter (smallcap, uppercap version will be made automatically)
          which output
        note whether the accent needs to be escaped for php, or for regexp!
      */
      
      $this->accentedLetters = array(
        array("`",'a',"à"),
        array("'",'a',"á"),
        array("\\^",'a',"â"),
        array("~",'a',"ã"),
        array("=",'a',"ā"),
        array("\"",'a',"ä"),        
        
        array("`",'e',"è"),
        array("'",'e',"é"),
        array("\\^",'e',"ê"),
        array("~",'e',"ẽ"),
        array("=",'e',"ē"),
        array("\"",'e',"ë"),
        
        array("`","\\\\i","ì"),
        array("'","\\\\i","í"),
        array("\\^","\\\\i","î"),
        array("~","\\\\i","ĩ"),
        array("=","\\\\i","ī"),
        array("\"","\\\\i","ï"), 

        array("`",'i',"ì"),
        array("'",'i',"í"),
        array("\\^",'i',"î"),
        array("~",'i',"ĩ"),
        array("=",'i',"ī"),
        array("\"",'i',"ï"),         
                
        array("`",'o',"ò"),
        array("'",'o',"ó"),
        array("\\^",'o',"ô"),
        array("~",'o',"õ"),
        array("=",'o',"ō"),
        array("\"",'o',"ö"), 
        
        array("`",'u',"ù"),
        array("'",'u',"ú"),
        array("\\^",'u',"û"),
        array("~",'u',"ũ"),
        array("=",'u',"ū"),
        array("\"",'u',"ü"), 
        
        array("'",'y',"ý"),
        array("\"",'y',"ÿ"), 
        
        array("~",'n','ñ'),
        
        /*
        {"\\^w", "ŵ"},
        
        */  

        /*
        add more of those!
        */
        array("'","c","ć")
       
      ); //did you put the comma's right? the last entry without comma!
      
      /*
        \v s For any combination of two single letters: a space after the first, 
        or braces around the second. outside braces optional. the expressions 
        cannot just be converted to uppercase: take directly from list below
        
        backslash letter letter (add any small and large cap explicitly)
        needed info: first and second letter
      */
      
      $this->combinedLetters = array ( 
            array("c","c","ç"),
            array("c","C","Ç"),
            
            array("d","o","ọ"),
            array("d","o","Ọ"),
            
            array("v","o","ŏ"),
            array("v","O","Ŏ"),
            array("v","c","č"),
            array("v","C","Č"),
            array("v","s","š"),
            array("v","S","Š")
      ); //did you put the comma's right? the last entry without comma!
      
      /*
      \ae single expressions. space afterwards, or braces around total expression.
        backslash letters (add any small and large cap explicitly)
        needed info: the string
      */
      
      $this->stringsAndCommands = array(
            array("oe","œ"),
            array("OE", "Œ"),
 	          array("ae", "æ"),
 	          array("AE", "Æ"),
 	          array("aa", "å"),
 	          array("AA", "Å"), 
 	          array("ss", "ß"), 
 	          array("o", "ø"),
            array("O", "Ø"),
            array("i", "ı"),

 	                //{"\\\\gal" ,"α"}, ??? never new that encoding? was in the file from variothingy... 

 	          array("l", "ł"),   
 	          array("L", "Ł")   
 	          
 	    ); //did you put the comma's right? the last entry without comma!
      
      
      /*
      \& single special characters. braces optional (inner and outer)
        backslash char
        needed info: the special character
      */

      $this->specialChars = array(
            array("#","#"),
            //array("\\?", "?"), //not neccesary, according to PDM
            array("\\&", "&"),
 	          array("\\$", "$"),
 	          //array("\\{", "{"),//these two play havoc with all other expressions :( but the old A|igaion converters didn't have it either
 	          //array("\\}", "}"), //these two play havoc with all other expressions :( but the old A|igaion converters didn't have it either
 	          array("%", "%"), 
 	          array("_", "_")
            //array("SS", "SS") //one waY ONLY, dont convert back! TUrned off for now. THey almost never occur, and because we cannot symmetrycally export all SS as \SS, better to leave them unconverted  
            
            
        //{"\\\\~?", "¡"},
 	      //{"\\\\?? ", "¿"},   
 	          
 	    ); //did you put the comma's right? the last entry without comma!

/* for utf82bibtyex conversion! */

      $this->specialCharsBack = array(
            array("\\#","#"),
            array("\\&","&"),
            //array("\\?", "\\?"), //not neccesary, according to PDM
 	          array("\\\\$", "\\$"), //why do we need the extra slashes here to e4xport $ as \$ ?
 	          //array("\\{", "\\{"), //these two play havoc with all other expressions :( but the old A|igaion converters didn't have it either
 	          //array("\\}", "\\}"),  //these two play havoc with all other expressions :( but the old A|igaion converters didn't have it either
 	          array("\\%", "%"), 
 	          array("\\_", "_")
            
            
        //{"\\\\~?", "¡"},
 	      //{"\\\\?? ", "¿"},   
 	          
 	    ); //did you put the comma's right? the last entry without comma!
    }
}
?>