<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for special character conversion
| -------------------------------------------------------------------
|
|   Provides several functions for special character conversion
|
|	Usage:
|       //load this helper:
|       $this->load->helper('theme'); 
|       //get available themes by name:
	findSpecialCharsInArray(&$array)
		returns true when special chars are found.

	findSpecialCharsInString(&$string)
		returns true when special chars are found.

	addSlashesToArray(&$array)
		addslashes to each element in the array.

	addHtmlEntitiesToArray(&$array)
		adds html entities to each element in the array.

	prettyPrintBibCharsFromArray(&$array)
		strips the special chars in an array and replaces by html special char

	prettyPrintBibCharsFromString(&$string)
		strips the special chars in a string and replaces by html special char

	stripBibCharsFromArray(&$array)
		strips the bibtex special chars from an array

	stripBibCharsFromString(&$string)
		strips the bibtex special chars from a string

	latinToBibCharsFromArray(&$array)
		converts latin chars to bibtex special chars form an array

	latinToBibCharsFromString(&$array)
		converts latin chars to bibtex special chars form a string

	quotesToHTMLFromArray(&$array)
		converts single and double quotes to their html equivalents

	quotesToHTMLFromString(&$string)
		converts single and double quotes to their html equivalents

	stripSlashesFromArray(&$array)
		stripslashes on each element in the array.

	stripHtmlEntitiesFromArray(&$array)
		strips html entities from each element in the array.

	function stripQuotesFromString($string)
		strips the " and ' character from a string and returns it

	getSpecialCharsArray()
		gets an array with regexps for finding special chars.

	getSpecialCharsReplaceArray()
		gets an array with regexps for replacing special chars.

	getHTMLSpecialCharsArray()
		gets an array with regexps for finding html special chars (quotes)

	getHTMLSpecialCharsReplaceArray()
		gets an array with the html codes for quotes.

	getLatinCharsArray()
		gets an array with latin chars that can be replaced by bibtex

	getLatinCharsReplaceArray()
		gets an array with bibtex replace chars for latin chars.

*/

function findSpecialCharsInArray(&$array)
{
	$bFound = false;
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$bFound = findSpecialCharsInString($array[$key]);
		if ($bFound)
		{
			return true;
		}
	}
	return false;
}

function findSpecialCharsInString(&$string)
{
	$specialChars = getSpecialCharsArray();
	foreach ($specialChars as $char)
	{
		if (preg_match($char, $string))
		{
			return true;
		}
	}
	return false;
}

function addSlashesToArray($array)
{
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$array[$key] = trim(addslashes($array[$key]));
	}
	return $array;
}

function addHtmlEntitiesToArray($array)
{
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$array[$key] = htmlentities($array[$key], ENT_QUOTES);
	}
	return $array;
}

function prettyPrintBibCharsFromArray($array)
{
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$array[$key] = prettyPrintBibCharsFromString($array[$key]);
	}
	return $array;
}

function prettyPrintBibCharsFromString($string)
{
	$specialBibChars = getSpecialCharsArray();
	$replaceChars		= getSpecialCharsReplaceArray();
	//$replaceChars = "$1";

	$string = preg_replace($specialBibChars, $replaceChars, $string);
	return $string;
}

function stripBibCharsFromArray($array)
{
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$array[$key] = stripBibCharsFromString($array[$key]);
	}
	return $array;
}

function stripBibCharsFromString($string)
{
	$specialBibChars = getSpecialCharsArray();
	$replaceChars = "$1";

	$string = preg_replace($specialBibChars, $replaceChars, $string);
	return $string;
}

function latinToBibCharsFromArray($array)
{
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$array[$key] = latinToBibCharsFromString($array[$key]);
	}
	return $array;
}

function latinToBibCharsFromString($string)
{
	$specialLatinChars = getLatinCharsArray();
	$replaceChars		= getLatinCharsReplaceArray();

	$string = preg_replace($specialLatinChars, $replaceChars, $string);
	return $string;
}

function quotesToHTMLFromArray($array)
{
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$array[$key] = quotesToHTMLFromString($array[$key]);
	}
	return $array;
}

function quotesToHTMLFromString($string)
{
	$HTMLSpecialCharsArray = getHTMLSpecialCharsArray();
	$replaceChars = getHTMLSpecialCharsReplaceArray();

	$string = preg_replace($HTMLSpecialCharsArray, $replaceChars, $string);
	return $string;
}

function stripSlashesFromArray($array)
{
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$array[$key] = stripslashes($array[$key]);
	}
	return $array;
}

function stripHtmlEntitiesFromArray($array)
{
	$keys = array_keys($array);
	foreach ($keys as $key)
	{
		$array[$key] = html_entity_decode($array[$key], ENT_QUOTES);
	}
	return $array;
}

function stripQuotesFromString($string)
{
	$stripchars = array("'", "\"", "`", "-");
	return str_replace($stripchars, "", $string);
}

function getSpecialCharsArray()
{
	return array(
			"/[{}]/",
			"/\\\`([aeiou])/i",
			"/\\\'([aeiou])/i",
			"/\\\\\^([aeiou])/i",
			"/\\\~([aon])/i",
			'/\\\"([aeiouy])/i',
			"/\\\(a)\s?(a)/i",
			"/\\\(c)\s?(c)/i",
			"/\\\(ae|oe)/i",
			'/\\\(s)\s?(s)/i',
			"/\\\(o)/",
			"/\\\.(I)/"
	);
}

function getSpecialCharsReplaceArray()
{
	return array(
			'',
			"&$1grave;",
			"&$1acute;",
			"&$1circ;",
			"&$1tilde;",
			"&$1uml;",
			"&$2ring;",
			"&$2cedil;",
			"&$1lig;",
			"&$2zlig;",
			"&$1slash;",
			"$1"
	);
}

function getHTMLSpecialCharsArray()
{
	return array(
			'/"/',
			"/'/"
	);
}

function getHTMLSpecialCharsReplaceArray()
{
	return array(
			"&quot;",
			"&#039;"
	);
}

function getLatinCharsArray()
{
	return array(
			"/À/",
			"/Á/",
			"/Â/",
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
			"/Õ/"
	);
}

function getLatinCharsReplaceArray()
{
	return array(
			"{\\`A}",
			"{\\'A}",
			"{\\^A}",
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
			"\\c{c}",
			"\\C{c}",
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
			"\\~{n}",
			"\\~{N}",
			"\\~{a}",
			"\\~{A}",
			"\\~{o}",
			"\\~{O}"
	);
}
?>