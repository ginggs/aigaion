<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for special character conversion (bibtex<->utf8)
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
|	Usage:
|       //load this helper:
|       $this->load->helper('bibtexutf8'); 

	utf8ToBibCharsFromArray(&$array)
		converts utf8 chars to bibtex special chars from an array

	utf8ToBibCharsFromString(&$array)
		converts utf8 chars to bibtex special chars from a string

	bibCharsToUtf8FromArray(&$array)
		converts bibtex special chars to utf8 chars from an array

	bibCharsToUtf8FromString(&$string)
		converts bibtex to utf8 chars special chars from a string

	getUtf8CharsArray()
		gets an array with utf8 chars that can be replaced by bibtex

	getUtf8CharsReplaceArray()
		gets an array with bibtex replace chars for utf8 chars.
	
	getBibtexCharsArray()
		gets an array with bibtex exporessions that can be replaced by utf8

	getBibtexCharsReplaceArray()
		gets an array with utf replace chars for bibtex chars.

*/

function utf8ToBibCharsFromArray($array)
{
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$array[$key] = utf8ToBibCharsFromString($array[$key]);
	}
	return $array;
}

function utf8ToBibCharsFromString($string)
{
	$specialUtf8Chars = getUtf8CharsArray();
	$replaceChars		= getUtf8CharsReplaceArray();

	$string = preg_replace($specialUtf8Chars, $replaceChars, $string);
	return $string;
}

//		converts bibtex special chars to utf8 chars from an array
function bibCharsToUtf8FromArray($array) {
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$array[$key] = bibCharsToUtf8FromString($array[$key]);
	}
	return $array;
}

//		converts bibtex to utf8 chars special chars from a string
function bibCharsToUtf8FromString($string) {
	$specialBibtexChars = getBibtexCharsArray();
	$replaceChars		= getBibtexCharsReplaceArray();

	$string = preg_replace($specialBibtexChars, $replaceChars, $string);
	return $string;
}

function getUtf8CharsArray()
{
	return array(
			"/À/",
			"/Á/",
			"/Â/",
			"/Æ/",
			"/È/",
			"/É/",
			"/Ê/",
			"/Ì/",
			"/Í/",
			"/Î/",
			"/Ò/",
			"/Ó/",
			"/Ô/",
			"/Ù/",
			"/Ú/",
			"/Û/",
			"/à/",
			"/á/",
			"/â/",
			"/æ/",
			"/è/",
			"/é/",
			"/ê/",
			"/ì/",
			"/í/",
			"/î/",
			"/ò/",
			"/ó/",
			"/ô/",
			"/ù/",
			"/ú/",
			"/û/",
			"/ä/",
			"/Ä/",
			"/ë/",
			"/Ë/",
			"/ï/",
			"/ï/",
			"/ü/",
			"/Ü/",
			"/ö/",
			"/Ö/",
			"/ç/",
			"/Ç/",
			"/Œ/",
			"/ÿ/",
			"/Ÿ/",
			"/ß/",
			"/å/",
			"/Å/",
			"/ý/",
			"/Ý/",
			"/þ/",
			"/Þ/",
			"/ø/",
			"/Ø/",
			"/ñ/",
			"/Ñ/",
			"/ã/",
			"/Ã/",
			"/õ/",
			"/Õ/",
			"/&/" //added because latex thinks & indicates a table, and it occurs often enough in a title
	);
}

function getUtf8CharsReplaceArray()
{
	return array(
			"{\\`A}",
			"{\\'A}",
			"{\\^A}",
			"{\\AE}",
			"{\\`E}",
			"{\\'E}",
			"{\\^E}",
			"{\\`I}",
			"{\\'I}",
			"{\\^I}",
			"{\\`O}",
			"{\\'O}",
			"{\\^O}",
			"{\\`U}",
			"{\\'U}",
			"{\\^U}",
			"{\\`a}",
			"{\\'a}",
			"{\\^a}",
			"{\\ae}",
			"{\\`e}",
			"{\\'e}",
			"{\\^e}",
			"{\\`i}",
			"{\\'i}",
			"{\\^i}",
			"{\\`o}",
			"{\\'o}",
			"{\\^o}",
			"{\\`u}",
			"{\\'u}",
			"{\\^u}",
			"{\\\"a}",
			"{\\\"A}",
			"{\\\"e}",
			"{\\\"E}",
			"{\\\"i}",
			"{\\\"I}",
			"{\\\"u}",
			"{\\\"U}",
			"{\\\"o}",
			"{\\\"O}",
			"{\\c{c}}",
			"{\\C{c}}",
			"{\\OE}",
			"{\\\"y}",
			"{\\\"Y}",
			"{\\ss}",
			"{\\aa}",
			"{\\AA}",
			"{\\'y}",
			"{\\'Y}",
			"{\\l}",
			"{\\L}",
			"{\\o}",
			"{\\O}",
			"{\\~n}",
			"{\\~N}",
			"{\\~a}",
			"{\\~A}",
			"{\\~o}",
			"{\\~O}",
			"{\&}"
	);
}


function getBibtexCharsArray()
{
	return array(
	        "/{([aeiouyAEIOUYnN])}}/",//first, replace all single chars between {}; hopefully this catches the {\"{e}} to {\"e}, which is easier to convert
			"/{\\\\`A}/",
			"/{\\\\'A}/",
			"/{\\\\\^A}/",
			"/{\\\\AE}/",
			"/{\\\\`E}/",
			"/{\\\\'E}/",
			"/{\\\\\^E}/",
			"/{\\\\`I}/",
			"/{\\\\'I}/",
			"/{\\\\\^I}/",
			"/{\\\\`O}/",
			"/{\\\\'O}/",
			"/{\\\\\^O}/",
			"/{\\\\`U}/",
			"/{\\\\'U}/",
			"/{\\\\\^U}/",
			"/{\\\\`a}/",
			"/{\\\\'a}/",
			"/{\\\\\^a}/",
			"/{\\\\ae}/",
			"/{\\\\`e}/",
			"/{\\\\'e}/",
			"/{\\\\\^e}/",
			"/{\\\\`i}/",
			"/{\\\\'i}/",
			"/{\\\\\^i}/",
			"/{\\\\`o}/",
			"/{\\\\'o}/",
			"/{\\\\\^o}/",
			"/{\\\\`u}/",
			"/{\\\\'u}/",
			"/{\\\\\^u}/",
			"/{\\\\\"a}/",
			"/{\\\\\"A}/",
			"/{\\\\\"e}/",
			"/{\\\\\"E}/",
			"/{\\\\\"i}/",
			"/{\\\\\"I}/",
			"/{\\\\\"u}/",
			"/{\\\\\"U}/",
			"/{\\\\\"o}/",
			"/{\\\\\"O}/",
			"/{\\\\c{c}}/",
			"/{\\\\C{c}}/",
			"/{\\\\OE}/",
			"/{\\\\\"y}/",
			"/{\\\\\"Y}/",
			"/{\\\\ss}/",
			"/{\\\\aa}/",
			"/{\\\\AA}/",
			"/{\\\\'y}/",
			"/{\\\\'Y}/",
			"/{\\\\l}/",
			"/{\\\\L}/",
			"/{\\\\o}/",
			"/{\\\\O}/",
			"/{\\\\~n}/",
			"/{\\\\~N}/",
			"/{\\\\~a}/",
			"/{\\\\~A}/",
			"/{\\\\~o}/",
			"/{\\\\~O}/",
			"/{\\\\&}/"
	);
}


function getBibtexCharsReplaceArray()
{
	return array(
	        "$1}",
			"À",
			"Á",
			"Â",
			"Æ",
			"È",
			"É",
			"Ê",
			"Ì",
			"Í",
			"Î",
			"Ò",
			"Ó",
			"Ô",
			"Ù",
			"Ú",
			"Û",
			"à",
			"á",
			"â",
			"æ",
			"è",
			"é",
			"ê",
			"ì",
			"í",
			"î",
			"ò",
			"ó",
			"ô",
			"ù",
			"ú",
			"û",
			"ä",
			"Ä",
			"ë",
			"Ë",
			"ï",
			"ï",
			"ü",
			"Ü",
			"ö",
			"Ö",
			"ç",
			"Ç",
			"Œ",
			"ÿ",
			"Ÿ",
			"ß",
			"å",
			"Å",
			"ý",
			"Ý",
			"þ",
			"Þ",
			"ø",
			"Ø",
			"ñ",
			"Ñ",
			"ã",
			"Ã",
			"õ",
			"Õ",
			"&"
	);
}
?>