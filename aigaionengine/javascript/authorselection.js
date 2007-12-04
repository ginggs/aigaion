<script language="JavaScript">
<?php
	$count = 0;
	echo "var AUTHORIDS = new Array();\n";
	echo "var AUTHORS = new Array();\n";
    $CI = &get_instance();
    $CI->db->orderby('cleanname');
    $CI->db->select('author_id,cleanname');
	$Q = $CI->db->get("author");
	foreach ($Q->result() as $R)  {
		$name = addslashes ($R->cleanname);

		echo "AUTHORIDS [{$count}] = ".$R->author_id.";";
		echo "AUTHORS [".$R->author_id."] = '{$name}';\n";

		$count++;
	}
?>

function Init ()
{
	AuthorSearch ();
	$('authorinputtext').focus ();
}

function AuthorSearch ()
{
	searchtext = $('authorinputtext').value;
	$('authorinputselect').length = 0;
	for (a in AUTHORIDS)  {
		astring = new String (AUTHORS [AUTHORIDS [a]]);
		if (astring.toLowerCase().indexOf(searchtext.toLowerCase ()) != -1)  {
			$('authorinputselect').options [$('authorinputselect').length] = new Option (astring,a,false,false);
		}
	}
}

function AddAuthor()	{  AddWriter ($('selectedauthors'));  }
function AddEditor()	{  AddWriter ($('selectededitors'));  }
function AddWriter (obj)
{
	authorID = AUTHORIDS [$('authorinputselect').options [$('authorinputselect').selectedIndex].value];

	var isNew = true;
	for (var i = 0;  i < obj.length;  i++)  {
		if (obj.options [i].value == authorID)  {
			isNew = false;
			break;
		} else {
			obj.options [i].selected = false;
		}
	}
	if (isNew)  {
		newoption = new Option (AUTHORS [authorID], authorID, false, true);
		obj.options [obj.length] = newoption;
	}
}

function RemoveAuthor()	{  RemoveWriter ($('selectedauthors')); }
function RemoveEditor()	{  RemoveWriter ($('selectededitors')); }
function RemoveWriter (obj)
{
	i = obj.selectedIndex;
	if (i >= 0)  {
		obj.options [i] = null; // obj.length decreases by 1...
		if (i < (obj.length)) {
			// if there is an element on the old position, mark it
			obj.options [i].selected = true;
		} else if (obj.length > 0)  {
			// otherwise it was the lowest element; mark the el. above (if any is left)
			obj.options [i-1].selected = true;
		}
	}
}

function AuthorUp()	{  WriterUp ($('selectedauthors')); }
function EditorUp()	{  WriterUp ($('selectededitors')); }
function WriterUp (obj)
{
	oldAuthorID = obj.options [0].value;
	for (var i = 1;  i < obj.length;  i++)  {
		if (obj.options [i].selected)  {
			obj.options [i-1].text	= AUTHORS [obj.options [i].value];
			obj.options [i-1].value	= obj.options [i].value;
			obj.options [i-1].selected = true;
			obj.options [i].text		= AUTHORS [oldAuthorID];
			obj.options [i].value		= oldAuthorID;
			obj.options [i].selected	= false;
		}
		oldAuthorID = obj.options [i].value;
	}
}

function AuthorDown()	{  WriterDown ($('selectedauthors')); }
function EditorDown()	{  WriterDown ($('selectededitors')); }
function WriterDown (obj)
{
	oldAuthorID = obj.options [obj.length-1].value;
	for (var i = obj.length-2;  i >= 0;  i--)  {
		if (obj.options [i].selected)  {
			obj.options [i+1].text	= AUTHORS [obj.options [i].value];
			obj.options [i+1].value 	= obj.options [i].value;
			obj.options [i+1].selected = true;
			obj.options [i].text		= AUTHORS [oldAuthorID];
			obj.options [i].value		= oldAuthorID;
			obj.options [i].selected	= false;
		}
		oldAuthorID = obj.options [i].value;
	}
}

function ShowNewAuthor (ID, CleanName)
{
	AUTHORIDS.unshift(ID);
	AUTHORS[ID] = CleanName;
}

</script>
