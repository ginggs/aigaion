<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
Aigaion - Web based document management system
Copyright (C) 2003-2007 (in alphabetical order):
Wietse Balkema, Arthur van Bunningen, Dennis Reidsma, Sebastian Schleußner

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/*
This helper provides functions for selecting publicationtype dependent fields.
*/

function getFullFieldArray() {
    return array(
                  'title'          ,
                  'type'	       ,
                  'journal'        ,
                  'booktitle'      ,
                  'edition'        ,
                  'series'         ,
                  'volume'         ,
                  'number'         ,
                  'chapter'        ,
                  'year'           ,
                  'month'          ,
                  'firstpage'      ,
                  'lastpage'       ,
                  'pages'		   ,
                  'publisher'      ,
                  'location'       ,
                  'institution'    ,
                  'organization'   ,
                  'school'         ,
                  'address'        ,
                  'howpublished'   ,
                  'note'           ,
                  'keywords'       ,
                  'abstract'       ,
                  'issn'           ,
                  'isbn'           ,
                  'url'            ,
                  'doi'            ,
                  'crossref'       ,
                  'namekey'        ,
                  'userfields'     
    );
}
function getCapitalFieldArray() {
    return array(
                  'issn'           ,
                  'isbn'           ,
                  'url'            ,
                  'doi'            
    );
}
//move to somewhere else?
function getMonthsEng() {
    return array("","January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
}

function getMonthsArray() {
  return array( '0'  => '',
                '1'  => 'January',
                '2'  => 'February',
                '3'  => 'March',
                '4'  => 'April',
                '5'  => 'May',
                '6'  => 'June',
                '7'  => 'July',
                '8'  => 'August',
                '9'  => 'September',
                '10' => 'October',
                '11' => 'November',
                '12' => 'December');
}

function getPublicationFieldArray($type)
{
	$type = ucfirst(strtolower(trim($type)));
	switch ($type) {
		case "Article":
		return array( 
		          'type'	          => 'hidden',
                  'journal'         => 'required',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'optional',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'hidden',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'optional',
                  'isbn'            => 'hidden',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Book":
		return array( 
		              'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'optional',
                  'edition'         => 'optional',
                  'series'          => 'optional',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'required',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Booklet":
		return array( 
				          'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'optional',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'optional',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Inbook":
		return array( 'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'optional',
                  'series'          => 'optional',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'conditional',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'conditional',
                  'publisher'       => 'required',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Incollection":
		return array( 'type'	          => 'optional',
                  'journal'         => 'hidden',
                  'booktitle'       => 'required',
                  'edition'         => 'optional',
                  'series'          => 'optional',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'optional',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'optional',
                  'publisher'       => 'required',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'optional',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Inproceedings":
		return array( 'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'optional', //cannot be required, since it may have been stored in a crossref entry! (and then this field stays empty)
                  'edition'         => 'hidden',
                  'series'          => 'optional',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'optional',
                  'publisher'       => 'optional',
                  'location'        => 'optional',
                  'institution'     => 'hidden',
                  'organization'    => 'optional',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'optional',
                  'isbn'            => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Manual":
		return array( 'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'optional',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'optional',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'optional',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Mastersthesis":
		return array( 'type'	          => 'optional',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'required',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Misc":
  	return array( 'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'optional',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'hidden',
                  'howpublished'    => 'optional',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Phdthesis":
		return array( 'type'	          => 'optional',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'required',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Proceedings":
		return array( 'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'optional',
                  'edition'         => 'hidden',
                  'series'          => 'optional',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'optional',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'optional',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'optional',
                  'isbn'            => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Techreport":
		return array( 'type'	          => 'optional',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'optional',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'required',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'abstract'        => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Unpublished":
		return array( 'type'	    => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'optional',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'hidden',
                  'howpublished'    => 'hidden',
                  'note'            => 'required',
                  'abstract'        => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		default:
		return array();
		break;
	}
}

//note: the prefix may be an array instead of a string, in that case its (prefix,postfix)
function getPublicationSummaryFieldArray($type)
{
	$type = ucfirst(strtolower($type));
	switch ($type) {
		case "Article":
			return array( 
	                  'actualyear'    => array(' (',')'),
			              'journal'       => ', in: ',
	                  'volume'        => ', ', 
	                  'number'        => ':',
	                  'pages'         => array('(',')')
	                );
		break;
		case "Book":
			return array( 'publisher'     => ', ',
	                  'series'        => ', ',
	                  'volume'        => ', volume ', 
	                  'actualyear'    => ', '
                  );
		break;
		case "Booklet":
			return array( 'howpublished'  => ', ',
			              'actualyear'    => ', ',
			            );
		break;
		case "Inbook":
			return array( 'chapter'       => ', chapter ', 
			              'pages'         => ', pages ', 
			              'publisher'     => ', ',
			              'series'        => ', ',
			              'volume'        => ', volume ', 
			              'actualyear'    => ', '
			            );
		break;
		case "Incollection":
			return array( 'booktitle'     => ', in: ', 
			              'organization'  => ', ', 
	                  'pages'         => ', pages ', 
	                  'publisher'     => ', ',
	                  'actualyear'    => ', '
	                );
		break;
		case "Inproceedings":
			return array( 'booktitle'     => ', in: ', 
	                  'organization'  => ', ', 
 	                  'location'      => ', ',
	                  'pages'         => ', pages ', 
	                  'publisher'     => ', ',
	                  'actualyear'    => ', '
                  );
		break;
		case "Manual":
			return array( 'edition'       => ', ',
	                  'organization'  => ', ',
	                  'actualyear'    => ', '
	                );
		break;
		case "Mastersthesis":
			return array( 'school'        => ', ' ,
	                  'year'          => ', '
                  );
		break;
		case "Misc":
			return array( 'howpublished'  => ', ',
	                  'actualyear'    => ', '
                  );
		break;
		case "Phdthesis":
			return array( 'school'        => ', ',
	                  'actualyear'    => ', '
                  );
		break;
		case "Proceedings":
			return array( 'organization'  => ', ',
			              'publisher'     => ', ',
			              'actualyear'    => ', '
			            );
		break;
		case "Techreport":
			return array( 'institution'   => ', ',
	                  'number'        => ', number ', 
	                  'type'          => ', ',
	                  'actualyear'    => ', '
                  );
		break;
		case "Unpublished":
			return array( 'actualyear'    => ', '
			            );
		break;
		default:
	    return array();
		break;
	}
}

function getPublicationTypes()
{
  return array("Article"        => 'Article',
          		 "Book"           => 'Book',
          		 "Booklet"        => 'Booklet',
          		 "Inbook"         => 'Inbook',
          		 "Incollection"   => 'Incollection',
          		 "Inproceedings"  => 'Inproceedings',
          		 "Manual"         => 'Manual',
          		 "Mastersthesis"  => 'Mastersthesis',
          		 "Misc"           => 'Misc',
          		 "Phdthesis"      => 'Phdthesis',
          		 "Proceedings"    => 'Proceedings',
          		 "Techreport"     => 'Techreport',
          		 "Unpublished"    => 'Unpublished');
}


?>