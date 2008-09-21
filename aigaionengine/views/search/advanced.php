<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$userlogin = getUserLogin();
echo "
    <p class=header>Advanced Search</p>
    <div class=editform>
    <p class=header2>Keyword search</p>";
echo form_open('search/advancedresults')."\n";
echo "<div>\n";
echo form_hidden('formname','keywordsearch');
echo form_input(array('name' => 'searchstring', 'size' => '25'));
echo form_submit('submit_search', 'search');
echo "</div>
    </div>";

    
echo form_close();

?>