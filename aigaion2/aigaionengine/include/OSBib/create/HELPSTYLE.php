<?php
/********************************
OSBib:
A collection of PHP classes to create and manage bibliographic formatting for OS bibliography software 
using the OSBib standard.

Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net 
so that your improvements can be added to the release package.

Adapted from WIKINDX: http://wikindx.sourceforge.net

Mark Grimshaw 2006
http://bibliophile.sourceforge.net
********************************/
/**
*	Help file
*
*	@author Mark Grimshaw
*
*	$Header: /cvsroot/aigaion/webinterface/includes/OSBib/create/HELPSTYLE.php,v 1.3 2006/12/01 14:27:49 reidsma Exp $
*/
class HELPSTYLE
{
// Constructor
	function HELPSTYLE()
	{
		include_once(OSBIB__MISC);
////////////////////////////////////////////////////////////////////////////////////
// TRANSLATORS start here.  Translate ONLY the second string in each define().
define("TEXT4", "You can create a new style based on an existing one by copying the existing style. To remove a style from the list available to your users, simply remove that style's directory from " . OSBIB_STYLE_DIR . ".");
// 'Short Name' and 'Long Name' should match the name given in MESSAGES.php style array.
define("TEXT5", "Each style has a set of options that define the heading style of titles, how to display numbers and dates etc. and then a separate style definition for each resource type that OSBib handles. The 'Short Name' is used by OSBib as both the folder and file name and for this reason should not only be a unique name within " . OSBIB_STYLE_DIR . ", but should also have no spaces or any other characters that may cause confusion with your operating system (i.e. alphanumeric characters only). The 'Long Name' is the description of the style that is displayed to OSBib users.");
// 'generic style' should be whatever you set for $style['generic'] in MESSAGES.php.
define("TEXT6", "The three 'generic style' definitions are required and are used to display any resource type for which there does not yet exist a style definition. This allows you to build up your style definitions bit by bit.  Furthermore, some bibliographic styles provide no formatting guidelines for particular types of resource in which case the generic styles will provide some formatting for those resources according to the general guidelines for that bibliographic style. Each resource for which there is no style definition will fall back to the chosen generic style. The generic styles try their best but if formatting is strange for a particular resource type then you should explicitly define a style definition for that type. ");
// Don't translate HTML tags!
define("TEXT7", "Each style definition has a range of available fields listed to the right of each input box. These fields are <strong>case-sensitive</strong> and need not all be used. However, with some of the more esoteric styles, the more database fields that have been populated for each resource in the OSBib-compatible bibliographic database, the more likely it is that the formatting will be correct.");
define("TEXT9", "The formatting of the names, edition and page numbers and the capitalization of the title depends on the global settings provided for your bibliographic style.");
define("TEXT8", "If the value entered for the edition of a resource contains non-numeric characters, then, despite having set the global setting for the edition format to ordinal (3rd. etc.), no conversion will take place.");
// 'Editor switch' should be whatever you set for $style['editorSwitchHead'] in MESSAGES.php.
// 'Yes' should be whatever you set for $style['yes'] in MESSAGES.php.
define("TEXT10", "The 'Editor switch' requires special attention. Some bibliographic styles require that for books and book chapters, where there exists an editor but no author, that the position occupied by the author is taken by the editor. If you select 'Yes' here, you should then supply a replacement editor field. Please note that if the switch occurs, the editor(s) formatting will be inherited from the global settings you supplied for the author. See the examples below.");
define("TEXT11", "Tip: In most cases, you will find it easiest to attach punctuation and spacing at the end of the preceding field rather than at the start of the following field. This is especially the case with finite punctuation such as full stops.");
// Don't translate 'session'
define("TEXT12", "Please note, to edit a style, you should do it from the same browser window as you use to view a bibliographic list.  This is because, in order to save processing the style definition file each time you list a bibliography, WIKINDX will check to see if the style definition file has been edited and therefore needs reprocessing. This information is stored in a PHP session variable; each browser window has its own separate set of session variables with no cross-interrogation available. If you edit the style definition file from another browser window then you are unlikely to see changes when you refresh your bibliographic list.");

define("SYNTAX_HEADING", "SYNTAX");
define("SYNTAX1", "The style definition syntax uses a number of rules and special characters:");
define("SYNTAX2", "The character '|' separates fields from one another.");
define("SYNTAX3", "If a field does not exist or is blank in the database, none of the definition for that field is printed.");
define("SYNTAX4", "Field names are case-sensitive");
// follows on from above in the same sentence...
define("SYNTAX5", "and need not all be used.");
define("SYNTAX6", "Within a field, you can add any punctuation characters or phrases you like before and after the field name.");
define("SYNTAX7", "Any word that you wish to be printed and that is the same (even a partial word) as a field name should be enclosed in backticks '`'.");
// Do not translate |^p.^pp.^pages|, 'pages', 'pp.' and 'p.'
define("SYNTAX8", "For creator lists (editors, revisers, directors etc.) and pages, alternative singular and plural text can be specified with '^' (e.g. |^p.^pp.^pages| would print the field 'pages' preceded by 'pp.' if there were multiple pages or 'p.' if not).");
define("SYNTAX9", "BBCode [u]..[/u], [i]..[/i] and [b]..[/b] can be used to specify underline, italics and bold.");
// Do not translate HTML tags!
define("SYNTAX10", "The character '%' enclosing any text or punctuation <em>before</em> the field name states that that text or those characters will only be printed if the <em>preceeding</em> field exists or is not blank in the database. The character '%' enclosing any text or punctuation <em>after</em> the field name states that that text or those characters will only be printed if the <em>following</em> field exists or is not blank in the database. It is optional to have a second pair in which case the construct should be read 'if target field exists, then print this, else, if target field does not exist, print that'.  For example, '%: %' will print ': ' if the target field exists else nothing if it doesn't while '%: %. %' will print ': ' if the target field exists else '. ' if it does not.");
// Do not translate HTML tags!
/* The following is an alternative version for SYNTAX11:
"You may enclose groups of fields in characters, such as paired parentheses, stipulating that these characters are only to be printed if at least one of the enclosed fields exists.  To do this, place the characters into their own fields (delimited by '|' but without a fieldname) surrounding the target fields.  If such special fields exist in isolation (i.e.  are not paired to enclose other fields), unintended results may occur.
*/
define("SYNTAX11", "Characters in fields that do not include a field name should be paired with another set and together enclose a group of fields. If these special fields are not paired unintended results may occur. These are intended to be used for enclosing groups of fields in brackets where <em>at least</em> one of the enclosed fields exists or is not blank in the database.");
// Don't translate <code>|%,\"%\". %|xxxxx|xxxxx|%: %; %|</code> or other HTML tags
define("SYNTAX12", "The above two rules can combine to aid in defining particularly complex bibliographic styles (see examples below). The pair <br /><code>|%,\"%\". %|xxxxx|xxxxx|%: %; %|</code><br /> states that if at least one of the intervening fields exists, then the comma and colon will be printed; if an intervening field does not exist, then the full stop will be printed <em>only</em> if the <em>preceeding</em> field exists (else nothing will be printed) and the semicolon will be printed <em>only</em> if the <em>following</em> field exists (else nothing will be printed).");
define("SYNTAX13", "If the final set of characters in the style definition is '|.' for example, the '.' is taken as the ultimate punctuation printed at the very end.");
// NB - the definition must be in single quotes because of the '$' in the example string (which should not be translated).
define("SYNTAX14", 'Fields can be printed or not dependent upon the existence of preceding or subsequent fields. For example, <code><br />creator. |$shortTitle. $title. $|publicationYear.</code><br /> would print the shortTitle field if the creator were populated otherwise it prints the title field.  <code><br />creator. |title. |#ISBN. ##|edition.</code><br /> prints the ISBN field if edition exists otherwise it prints nothing.');
define("EXAMPLE_HEADING", "EXAMPLES");
// Do not translate HTML tags!
define("EXAMPLE2", "<em>might produce:</em>");
define("EXAMPLE4", "<em>and, if there were no publisher location or edition entered for that resource and only one page number given, it would produce:</em>");
define("EXAMPLE9", "<em>and, if there were no publisher location or publication year entered for that resource, it would produce:</em>");
// don't translate 'editor ^ed.^eds.^ '
define("EXAMPLE13", "<em>and, if there were no author entered for that resource and the replacement editor field were 'editor ^ed.^eds.^ ', it would produce:</em>");
define("EXAMPLE15", "Consider the following (IEEE-type) generic style definition and what it does with a resource type lacking certain fields:");
// don't translate HTML tags
define("EXAMPLE18", "<em>and, when applied to a resource type with editor and edition fields:</em>");
define("EXAMPLE20", "Clearly there is a problem here, notably at the end of the resource title. The solution is to use rule no. 10 above:");
define("EXAMPLE23", "<em>and:</em>");
define("EXAMPLE25", "Bibliographic styles requiring this complexity are few and far between.");
// TRANSLATORS end here
////////////////////////////////////////////////////////////////////////////////////
// Do not translate these:
define("EXAMPLE1", "author. |publicationYear. |title. |In [i]book[/i], |edited by editor (^ed^eds^). |publisherLocation%:% |publisherName. |edition ed%,%.% |(Originally published originalPublicationYear) |^p.^pp.^pages|.");

define("EXAMPLE3", "de Maus, Mickey. 2004. An amusing diversion. In <em>A History of Cartoons</em>, Donald D. A. F. F. Y. Duck, and Bugs Bunny (eds). London: Animatron Publishing. 10th ed, (Originally published 2000) pp.20-9.");

define("EXAMPLE5", "de Maus, Mickey. 2004. An amusing diversion. In <em>A History of Cartoons</em>, Donald D. A. F. F. Y. Duck, and Bugs Bunny (eds). Animatron Publishing. (Originally published 2000) p.20.");

define("EXAMPLE7", "author. |[i]title[/i]. |(|publisherLocation%: %|publisherName%, %|publicationYear.|) |ISBN|.");
define("EXAMPLE8", "de Maus, Mickey. <em>A big book</em> (London: Animatron Publishing, 1999.) 1234-09876.");
define("EXAMPLE10", "de Maus, Mickey. <em>A big book</em>. (Animatron Publishing.) 1234-09876.");

define("EXAMPLE11", "author. |publicationYear. |[i]title[/i]. |Edited by editor. |edition ed. |publisherLocation%:%.% |publisherName. |Original `edition`, originalPublicationYear|.");
define("EXAMPLE12", "Duck, Donald D. A. F. F. Y. 2004. <em>How to Make it Big in Cartoons</em>. Edited by M. de Maus and Goofy. 3rd ed. Selebi Phikwe: Botswana Books. Original edition, 2003.");
define("EXAMPLE14", "de Maus, Mickey and Goofy eds. 2004. <em>How to Make it Big in Cartoons</em>. 3rd ed. Selebi Phikwe: Botswana Books. Original edition, 2003.");

define("EXAMPLE16", "creator, |\"title,\"| in [i]collection[/i], |editor, ^Ed.^Eds.^, |edition ed|. publisherLocation: |publisherName, |publicationYear, |pp. pages|.");
define("EXAMPLE17", "ed Software, \"Mousin' Around,\". Gaborone: Computer Games 'r' Us, 1876.");
define("EXAMPLE19", "Donald D. A. F. F. Y. de Duck, \"How to Make it Big in Cartoons,\"Mickey de Maus and Goofy, Eds., 3rd ed. Selebi Phikwe: Botswana Books, 2003.");
define("EXAMPLE21", "creator, |\"title|%,\" %.\" %|in [i]collection[/i]|%, %editor, ^Ed.^Eds.^|%, %edition ed|%. %|publisherLocation: |publisherName, |publicationYear, |pp. pages|.");
define("EXAMPLE22", "ed Software, \"Mousin' Around.\" Gaborone: Computer Games 'r' Us, 1876.");
define("EXAMPLE24", "Donald D. A. F. F. Y. de Duck, \"How to Make it Big in Cartoons,\" Mickey de Maus and Goofy, Eds., 3rd ed. Selebi Phikwe: Botswana Books, 2003.");

		include_once(OSBIB__MESSAGES);
		$this->messages = new MESSAGES();
		include_once(OSBIB__UTF8);
		$this->utf8 = new UTF8();
	}
// Help page
	function display()
	{
		$this->pString = MISC::h($this->messages->text("heading", "helpStyles"), FALSE, 3);
		$this->pString .= MISC::aName("top");
		$this->pString .= MISC::p(TEXT4);
		$this->pString .= MISC::p(TEXT12);
		$this->pString .= MISC::p(MISC::hr());
		$this->pString .= MISC::p(TEXT5);
		$this->pString .= MISC::p(TEXT10);
		$this->pString .= MISC::p(TEXT6);
		$this->pString .= MISC::p(TEXT7);
		$this->pString .= MISC::p(MISC::hr());
		$this->pString .= MISC::h(SYNTAX_HEADING);
		$this->pString .= MISC::p(SYNTAX1);
		$this->pString .= MISC::ol(
			MISC::li(SYNTAX2) . 
			MISC::li(SYNTAX3) . 
			MISC::li(MISC::b(SYNTAX4) . ' ' . SYNTAX5) . 
			MISC::li(SYNTAX6) . 
			MISC::li(SYNTAX7) . 
			MISC::li(SYNTAX8) . 
			MISC::li(SYNTAX9) . 
			MISC::li(SYNTAX10) . 
			MISC::li(SYNTAX11) . 
			MISC::li(SYNTAX12) . 
			MISC::li(SYNTAX13) . 
			MISC::li(SYNTAX14)
			);
		$this->pString .= MISC::p(TEXT11);
		$this->pString .= MISC::p(MISC::hr());
		$this->pString .= MISC::h(EXAMPLE_HEADING);
		$this->pString .= MISC::p("<code>" . EXAMPLE1 . "</code>" . MISC::BR() . 
			EXAMPLE2 . "</code>" . MISC::BR() . "<code>" . EXAMPLE3 . "</code>" );
		$this->pString .= MISC::p(EXAMPLE4 . MISC::BR() . "<code>" . EXAMPLE5 . "</code>");
		$this->pString .= MISC::hr();
		$this->pString .= MISC::p("<code>" . EXAMPLE7 . "</code>" . MISC::BR() . 
			EXAMPLE2 . "</code>" . MISC::BR() . "<code>" . EXAMPLE8 . "</code>" );
		$this->pString .= MISC::p(EXAMPLE9 . MISC::BR() . "<code>" . EXAMPLE10 . "</code>");
		$this->pString .= MISC::hr();
		$this->pString .= MISC::p("<code>" . EXAMPLE11 . "</code>" . MISC::BR() . 
			EXAMPLE2 . "</code>" . MISC::BR() . "<code>" . EXAMPLE12 . "</code>" );
		$this->pString .= MISC::p(EXAMPLE13 . MISC::BR() . "<code>" . EXAMPLE14 . "</code>");
		$this->pString .= MISC::hr();
		$this->pString .= MISC::p(EXAMPLE15 . MISC::BR() . "<code>" . EXAMPLE16 . "</code>" . MISC::BR() . 
			EXAMPLE2 . MISC::BR() . "<code>" . EXAMPLE17 . "</code>" . MISC::br() . 
			EXAMPLE18 . MISC::br() . "<code>" . EXAMPLE19 . "</code>");
		$this->pString .= MISC::p(EXAMPLE20 . MISC::BR() . "<code>" . EXAMPLE21 . "</code>" . MISC::BR() . 
			EXAMPLE2 . MISC::BR() . "<code>" . EXAMPLE22 . "</code>" . MISC::br() . 
			EXAMPLE23 . MISC::br() . "<code>" . EXAMPLE24 . "</code>");
		$this->pString .= MISC::p(EXAMPLE25);
		$this->pString .= MISC::hr();
		$this->pString .= MISC::p(TEXT8);
		$this->pString .= MISC::p(TEXT9);
		$this->pString .= MISC::p(MISC::a("link", 
			$this->utf8->decodeUtf8($this->messages->text("misc", "top")), "#top"), "small", "right");
		return $this->pString;
	}
}
