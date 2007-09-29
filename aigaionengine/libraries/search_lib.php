<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/** This class provides calls for performing searchs both complex and simple

*/
class Search_lib {
  
    function Search_lib()
    {
    }

    /** A simple search on all types of data using a single string of query.
    Returns an array map ('type'=>$resultArray) like this:
    ('author'=>$arrayOfAuthors,
     'topic'=>$arrayOfTopics,
     'publication'=>array(pub=>'title,keyword,note,abstract') //types dependent on how publication was found
     ) */
    function simpleSearch($query) {
        
    }

?>