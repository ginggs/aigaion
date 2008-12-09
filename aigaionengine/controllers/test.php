<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/** This controller contains a number of unit test like methods. Generally, 
 * you'll just access the test controller. Its main method will call a number of 
 * different tests, such as the test for the simple character conversions, the 
 * test for correct import of full bibtex entries, etc.
 * 
 * The controller has more or less been designed to only give output on FAILED 
 * tests.
 * 
 * You can extend the tests in many ways. Most of the test methods contain some 
 * indications of how to extend them with new cases. For example, with the bibtex
 * character conversions one can quite simply add some conversions that have gone
 * wrong in the past by adding them to the well structured list of 'correct conversions'.  
 */      
class Test extends Controller {

	function Test()
	{
		parent::Controller();	
	}
	
	/** A test controller. */
	function index()
	{
	   $this->testbibtex();
	}
	
	function testbibtex() 
	{
  	$this->load->library('unit_test');
	  $this->load->library('bibtex2utf8');

	  $content = $this->testbibtex_charconversion(true);
    $content .= $this->testbibtex_singleimport();
    //todo: tests for the conversions between internal and external format of months and unknown macros and stuff
    //todo: tests for bibtex export in many ways; tests for crossref support; and whichever class of test we find we need because of recurring bugs!
    
    $output = $content;
    
    //set output
    $this->output->set_output($output);
    	  
	  
  }
  
  function testbibtex_charconversion($debug = false) 
  {
    $result = "";
    $result .= "<h1>Bibtex character conversions</h1>";
    //bibtex characters - expected conversion vs actual conversion
    $bibtextests = array(
      array ('thoseThatCurrentlyGoWrong', /* IF  you find conversions that go 
      wrong, please add them here at this point. This will remind us to fix 
      them. Also, it's kind of a 'prevent this error recurring after it was 
      solved check', so DO NOT REMOVE THOSE CONVERSION CHECKS FROM HERE WHEN YOU 
      FIXED THEM! Rather, start a new array in which people can again add their 
      found conversion errors :) */
             "Polish: \\c{a} \\c{e} \\'{c} \\l{} \\'{n} \\'{s} \\.{z} \\'{z} \\c{A} \\c{E} \\'{C} \\L{} \\'{N} \\'{S} \\.{Z} \\'{Z} French: {\\oe} {\\OE} Other: {\\TH} {\\th} {\\v{s}}", //input on this line...
             "Some missing -- not converted on import and not on export. Polish: (some missing chars) ć ł (more missing chars) Ć Ł (yet more missing chars) French: œ Œ Other: Þ þ š" //expected output on conversion from bibtex to utf8 characters on this line...
      ),
      array ('test-Latin-1-misc',
             "\\pounds \\S \\textcopyright \\textordfeminine \\- \\textregistered \\P \\textperiodcentered \\textordmasculine !` \\c{} ?`",
             "(All missing -- not converted on import and not on export)"
      ),
      array ('test-ASCII-chars',
            "! \\# \\$ \\% \\& ' ( ) * + , - . / 0-9 : ; = ? @ A-Z [ ] \\_ ` a-z \\{ \\}",
            "(These still need some work)"
      ),
      array ('test-Latin-1-lower-braces1', //this one is supposedly the most complete of thge brace and case variations-- the other variations of braces may miss one or two characters...
             "{\\`a} {\\'a} {\\^a} {\\~a} {\\=a} {\\\"a} {\\aa} {\\ae} {\\c c} {\\`e} {\\'e} {\\^e} {\\~e} {\\=e} {\\\"e} {\\i} {\\`\\i} {\\'\\i} {\\^\\i} {\\~\\i} {\\=\\i} {\\\"\\i} {\\`i} {\\'i} {\\^i} {\\~i} {\\=i} {\\\"i} {\\~n} {\\`o} {\\'o} {\\^o} {\\~o} {\\=o} {\\\"o} {\\o} {\\`u} {\\'u} {\\^u} {\\~u} {\\=u} {\\\"u} {\\'y} {\\\"y} {\\ss}",
             "à á â ã ā ä å æ ç è é ê ẽ ē ë ı ì í î ĩ ī ï ì í î ĩ ī ï ñ ò ó ô õ ō ö ø ù ú û ũ ū ü ý ÿ ß"
      ),
      array ('test-Latin-1-lower-braces2', 
             "{\\`{a}} {\\'{a}} {\\^{a}} {\\~{a}} {\\\"{a}} {\\c{c}} {\\`{e}} {\\'{e}} {\\^{e}} {\\\"{e}} {\\`{\\i}} {\\'{\\i}} {\\^{\\i}} {\\\"{\\i}} {\\`{i}} {\\'{i}} {\\^{i}} {\\\"{i}} {\\~{n}} {\\`{o}} {\\'{o}} {\\^{o}} {\\~{o}} {\\\"{o}} {\\`{u}} {\\'{u}} {\\^{u}} {\\\"{u}} {\\'{y}} {\\\"{y}}",
             "à á â ã ä ç è é ê ë ì í î ï ì í î ï ñ ò ó ô õ ö ù ú û ü ý ÿ"
      ),
      array ('test-Latin-1-lower-braces3', 
             "\\`a \\'a \\^a \\~a \\\"a \\aa \\ae \\c c \\`e \\'e \\^e \\\"e \\i \\`\\i \\'\\i \\^\\i \\\"\\i \\`i \\'i \\^i \\\"i \\~n \\`o \\'o \\^o \\~o \\\"o \\o \\`u \\'u \\^u \\\"u \\'y \\\"y \\ss",
             "à á â ã ä åæç è é ê ë ıì í î ï ì í î ï ñ ò ó ô õ ö øù ú û ü ý ÿ ß" //note how spaces afer unbraced \aa and \ae get removed (see also next test)
      ),
      array ('test-Latin-1-lower-spacesAfterUnbracedSymbols', 
             "~\\aa~\\ae~\\ss~\\o~\\i~",
             "~å~æ~ß~ø~ı~" 
      ),
      array ('test-Latin-1-lower-braces4', 
             "\\`{a} \\'{a} \\^{a} \\~{a} \\\"{a} \\c{c} \\`{e} \\'{e} \\^{e} \\~{e} \\\"{e} \\`{\\i} \\'{\\i} \\^{\\i} \\~{\\i} \\\"{\\i} \\`{i} \\'{i} \\^{i} \\~{i} \\\"{i} \\~{n} \\`{o} \\'{o} \\^{o} \\~{o} \\\"{o} \\`{u} \\'{u} \\^{u} \\\"{u} \\'{y} \\\"{y}",
             "à á â ã ä ç è é ê ẽ ë ì í î ĩ ï ì í î ĩ ï ñ ò ó ô õ ö ù ú û ü ý ÿ"
      ),
      array ('test-Latin-1-upper-braces1', 
             "{\\`A} {\\'A} {\\^A} {\\~A} {\\\"A} {\\AA} {\\AE} {\\c C} {\\`E} {\\'E} {\\^E} {\\\"E} {\\`\\I} {\\'\\I} {\\^\\I} {\\\"\\I} {\\`I} {\\'I} {\\^I} {\\\"I} {\\~N} {\\`O} {\\'O} {\\^O} {\\~O} {\\\"O} {\\O} {\\`U} {\\'U} {\\^U} {\\\"U} {\\'Y} {\\\"Y} {\\SS}",
             "À Á Â Ã Ä Å Æ Ç È É Ê Ë Ì Í Î Ï Ì Í Î Ï Ñ Ò Ó Ô Õ Ö Ø Ù Ú Û Ü Ý Ÿ {\\SS}"
      ),
      array ('test-Latin-1-upper-braces2', 
             "{\\`{A}} {\\'{A}} {\\^{A}} {\\~{A}} {\\\"{A}} {\\c{C}} {\\`{E}} {\\'{E}} {\\^{E}} {\\\"{E}} {\\`{\\I}} {\\'{\\I}} {\\^{\\I}} {\\\"{\\I}} {\\`{I}} {\\'{I}} {\\^{I}} {\\\"{I}} {\\~{N}} {\\`{O}} {\\'{O}} {\\^{O}} {\\~{O}} {\\\"{O}} {\\`{U}} {\\'{U}} {\\^{U}} {\\\"{U}} {\\'{Y}} {\\\"{Y}}",
             "À Á Â Ã Ä Ç È É Ê Ë Ì Í Î Ï Ì Í Î Ï Ñ Ò Ó Ô Õ Ö Ù Ú Û Ü Ý Ÿ"
      ),
      array ('test-Latin-1-upper-braces3', 
             "\\`A \\'A \\^A \\~A \\\"A \\AA \\AE \\c C \\`E \\'E \\^E \\\"E \\`\\I \\'\\I \\^\\I \\\"\\I \\`I \\'I \\^I \\\"I \\~N \\`O \\'O \\^O \\~O \\\"O \\O \\`U \\'U \\^U \\\"U \\'Y \\\"Y \\SS",
             "À Á Â Ã Ä ÅÆÇ È É Ê Ë Ì Í Î Ï Ì Í Î Ï Ñ Ò Ó Ô Õ Ö ØÙ Ú Û Ü Ý Ÿ \\SS" //NOTE HOW SPACES AFER UNBRACED \AA AND \AE GET REMOVED (SEE ALSO NEXT TEST)
      ),
      array ('test-Latin-1-upper-spacesAfterUnbracedSymbols', 
             "~\\AA~\\AE~\\SS~\\O~",
             "~Å~Æ~\\SS~Ø~" 
      ),
      array ('test-Latin-1-upper-braces4', 
             "\\`{A} \\'{A} \\^{A} \\~{A} \\\"{A} \\c{C} \\`{E} \\'{E} \\^{E} \\\"{E} \\`{\\I} \\'{\\I} \\^{\\I} \\\"{\\I} \\`{I} \\'{I} \\^{I} \\\"{I} \\~{N} \\`{O} \\'{O} \\^{O} \\~{O} \\\"{O} \\`{U} \\'{U} \\^{U} \\\"{U} \\'{Y} \\\"{Y}",
             "À Á Â Ã Ä Ç È É Ê Ë Ì Í Î Ï Ì Í Î Ï Ñ Ò Ó Ô Õ Ö Ù Ú Û Ü Ý Ÿ"
      )

            
    );
    foreach ($bibtextests as $test) {
      $debugout ="&nbsp;&nbsp;".$test[1]."<br>should be<br>&nbsp;&nbsp;".$test[2]."<br>but rather becomes<br>&nbsp;&nbsp;".$this->bibtex2utf8->bibCharsToUtf8FromString($test[1])."<br>";
      if ($this->bibtex2utf8->bibCharsToUtf8FromString($test[1])!=$test[2]) 
      {
          $result .= "Test: ".$test[0]."<br>";
          $result .= " FAILED: <br>".$debugout."<br>";
      } 
      else 
      {
        if ($debug) 
        {
          $result .= "Test: ".$test[0]."<br>";
          $result .= " PASSED<br>";
        } 
          
      }
      $result .= "<br>";
    }
    return $result;
  }
  
  function testbibtex_singleimport($debug = false)
  {
    $result = "";
    $result .= "<h1>Bibtex single entry imports</h1>Note: these tests do not take the review and database result into account, only the result of the bibtex parsing...<br>";
    //bibtex single entry imports: bibtex code, and a list of expected attributes of the resulting publication 
    $bibtexsingleentrytests = array(
      array ('example', /* IF  you find imports that go 
      wrong, please add them here at this point. This will remind us to fix 
      them. Also, it's kind of a 'prevent this error recurring after it was 
      solved check', so DO NOT REMOVE THOSE IMPORT RESULT CHECKS FROM HERE WHEN YOU 
      FIXED THEM! Rather, start a new array in which people can again add their 
      found errors :) */
             //input:
             "@article{some-bibtex-id,title={The title},month=apr#{~1st},author={Fst von Last and Last2, Fst2}}",
             //an array of pairs for all fields except author and editor: what are the expected values of the fields? 
             array(
               "pub_type"=>"article",
               "bibtex_id"=>"some-bibtex-id",
               "title"=>"The title",
               "month"=>"\"apr\"~1st"
             ),
             //the expected author result:
             array(
                 array(
                   "firstname" => "Fst",
                   "von"=>"von",
                   "surname"=>"Last",
                   "jr"=>"" 
                 ),
                 array(
                   "firstname" => "Fst2",
                   "von"=>"",
                   "surname"=>"Last2",
                   "jr"=>""
                 )
             ),
             //the expected editor result:
             array(
             
             )
      )
      //next test case:
    );
    foreach ($bibtexsingleentrytests as $test) {
      $debugout ="&nbsp;&nbsp;<pre>".$test[1]."</pre><br>";
      //parse the $test[1]
      //inspect the resulting publication object
      //report result (if debug, or if test failed)
      $result .= $debugout;
    }
    return $result;
        
  }

}
?>