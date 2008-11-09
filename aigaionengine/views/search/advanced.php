<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$userlogin = getUserLogin();
echo "
  <div class=editform>    
    <p class=header>Advanced Search</p>
".form_open('search/advancedresults')
 .form_hidden('formname','keywordsearch')."\n

    <p class=header2>Keywords</p>
    Leave empty if you want to search all publications
    <div>\n";
echo form_input(array('name' => 'searchstring', 'size' => '50'));
echo "
    </div>
<p/>
    <p class=header2>Result types</p>
    Choose which types of results you want returned
    <div>\n"
.form_checkbox('return_authors','return_authors',TRUE)." Return authors<br/>\n"
.form_checkbox('return_publications','return_publications',TRUE)." Return publications<br/>\n"
.form_checkbox('return_topics','return_topics',TRUE)." Return topics<br/>\n"
.form_checkbox('return_keywords','return_keywords',TRUE)." Return keywords<br/>\n"
."
    </div>
<p/>
    <p class=header2>Publication search</p>
    Choose, if you are searching for publications (see above!), which fields are searched
    <div>\n"
.form_checkbox('search_publications_titles','search_publications_titles',TRUE)." Search publication titles<br/>\n"
.form_checkbox('search_publications_notes','search_publications_notes',TRUE)." Search publication notes<br/>\n"
.form_checkbox('search_publications_bibtex_id','search_publications_bibtex_id',TRUE)." Search publication bibtex id<br/>\n"
.form_checkbox('search_publications_abstracts','search_publications_abstracts',TRUE)." Search publication abstract<br/>\n"
."
    </div>
";

echo form_submit('submit_search',  $this->lang->line('main_search'));
echo form_close();

echo "
  </div>
";

?>