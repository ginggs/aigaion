<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$userlogin = getUserLogin();
if (!isset($query)||($query==null)) $query='';
if (!isset($options)||($options==null)) 
            $options = array('authors',
                             'topics',
                             'keywords',
                             'publications',
                             'publications_titles',
                             'publications_notes',
                             'publications_bibtex_id',
                             'publications_abstracts');
                             
echo "
  <div class=editform>    
    <p class=header>Advanced Search</p>
".form_open('search/advancedresults')
 .form_hidden('formname','advancedsearch')."\n

    <p class=header2>Search terms</p>
    <div>\n";
echo form_input(array('name' => 'searchstring', 'size' => '50','value'=>$query));

echo "
    </div>
<p/>
    <p class=header2>Result types</p>
    Choose which types of results you want returned
    <div>\n"
.form_checkbox('return_authors','return_authors',in_array('authors',$options))." Return authors<br/>\n"
.form_checkbox('return_publications','return_publications',in_array('publications',$options))." Return publications<br/>\n"
.form_checkbox('return_topics','return_topics',in_array('topics',$options))." Return topics<br/>\n"
.form_checkbox('return_keywords','return_keywords',in_array('keywords',$options))." Return keywords<br/>\n"
."
    </div>
<p/>
    <p class=header2>Publication search</p>
    Choose, if you are searching for publications (see above!), which fields are searched
    <div>\n"
.form_checkbox('search_publications_titles','search_publications_titles',in_array('publications_titles',$options))." Search publication titles<br/>\n"
.form_checkbox('search_publications_notes','search_publications_notes',in_array('publications_notes',$options))." Search publication notes<br/>\n"
.form_checkbox('search_publications_bibtex_id','search_publications_bibtex_id',in_array('publications_bibtex_id',$options))." Search publication bibtex id<br/>\n"
.form_checkbox('search_publications_abstracts','search_publications_abstracts',in_array('publications_abstracts',$options))." Search publication abstract<br/>\n"
."
    </div>
<p/>
";

echo form_submit('submit_search',  __('Search'));
echo form_close();

echo "
  </div>
";

echo "
  <div class=editform>    
    <p class=header>Advanced Search: Publications on topic restriction</p>
".form_open('search/advancedresults')
 .form_hidden('formname','advancedsearch')."\n

    <p class=header2>Search terms</p>
    Leave empty if you want to search all publications
    <div>\n";
echo form_input(array('name' => 'searchstring', 'size' => '50','value'=>$query));

echo "
    </div>
"
.form_hidden('return_publications','return_publications')."\n"
."
<p/>
    Search publications with these terms in the following fields:
    <div>\n"
.form_checkbox('search_publications_titles','search_publications_titles',in_array('publications_titles',$options))." Search publication titles<br/>\n"
.form_checkbox('search_publications_notes','search_publications_notes',in_array('publications_notes',$options))." Search publication notes<br/>\n"
.form_checkbox('search_publications_bibtex_id','search_publications_bibtex_id',in_array('publications_bibtex_id',$options))." Search publication bibtex id<br/>\n"
.form_checkbox('search_publications_abstracts','search_publications_abstracts',in_array('publications_abstracts',$options))." Search publication abstract<br/>\n"
."
    </div>
<p/>";
//the encoding of the topic conditions with encodeURIcomponent is a messy business. We need it because there may be all sorts of stuff in the option tree that we cannot just show in javascript here without breaking the boundaries of the relevant javascript string ;-)
$config = array('onlyIfUserSubscribed'=>True,
                'includeGroupSubscriptions'=>True,
                'user'=>$userlogin->user());
$this->load->helper('encode');
echo "
    <p class=header2>Choose the topic restrictions that apply. </p>
    Return all publications that satisfy <br/>
    <input type=radio name=\"anyAll\" value=\"All\" CHECKED/>All<br/>
    <input type=radio name=\"anyAll\" value=\"Any\"/>Any
    <br/>of the following conditions:<br/><br/>
    <div>
    <script language='javascript'>
    var n = 0;
    function more() {
        n++;
        var newCondition = '<b>Condition '+n+'</b>:<br/><input type=radio name=\"doOrNot'+n+'\" value=\"True\" CHECKED/>Do<br/><input type=radio name=\"doOrNot'+n+'\" value=\"False\"/>Do not&nbsp;&nbsp;&nbsp;appear in '+decodeURIComponent('".encodeURIComponent($this->load->view('topics/optiontree',
                                             array('topics'   => $this->topic_db->getByID(1,$config),
                                                  'showroot'  => False,
                                                  'header'    => 'Select topic to include or exclude...',
                                                  'dropdownname' => 'dropdownname',
                                                  'depth'     => -1,
                                                  'selected'  => 1
                                                  ),  
                                             true))."');
        newCondition = newCondition.replace('dropdownname','topicSelection'+n);
        Element.replace('moreconditions',newCondition+'<br/><div id=\"moreconditions\" name=\"moreconditions\"><input type=\"hidden\" name=\"numberoftopicconditions\" value=\"'+n+'\"/>".$this->ajax->button_to_function('More...', "more();" )."</div>');
    }
    </script>
    \n"
    
."<div id='moreconditions' name='moreconditions'><input type=\"hidden\" name=\"numberoftopicconditions\" value=\"0\"/>".$this->ajax->button_to_function('More...', "more();" )."</div>"
."
    <script language='javascript'>more();</script></div><br/>
";

echo form_submit('submit_search',  __('Search'));
echo form_close();

echo "
  </div>
";

?>