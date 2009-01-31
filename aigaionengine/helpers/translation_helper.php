<?php
/**
 * Helper to translate the names of publication fields, which are columns in the database, to other languages
 * for display in edit forms and overview pages.
 *
 * Originally controbuted by Manuel Strehl
 */
function translateField ($fieldname, $ucfirst=false) {

  /**
   * all fields to be translated come in here:
   */
  $fields = array (
    // publications:
    "year"         => __("year"),
    "title"        => __("title"),
    "survey"       => __("survey"),
    "mark"         => __("mark"),
    "series"       => __("series"),
    "volume"       => __("volume"),
    "publisher"    => __("publisher"),
    "location"     => __("location"),
    "issn"         => __("issn"),
    "isbn"         => __("isbn"),
    "firstpage"    => __("firstpage"),
    "lastpage"     => __("lastpage"),
    "journal"      => __("journal"),
    "booktitle"    => __("booktitle"),
    "number"       => __("number"),
    "institution"  => __("institution"),
    "address"      => __("address"),
    "chapter"      => __("chapter"),
    "edition"      => __("edition"),
    "howpublished" => __("howpublished"),
    "month"        => __("month"),
    "organization" => __("organization"),
    "school"       => __("school"),
    "note"         => __("note"),
    "abstract"     => __("abstract"),
    "url"          => __("url"),
    "doi"          => __("doi"),
    "pages"        => __("pages"),
    
    // authors:
    "surname"   => __("surname"),
    "von"       => __("von"),
    "firstname" => __("firstname"),
    "email"     => __("email"),
    "url"       => __("url"),
    "institute" => __("institute")
  );
  
  
  if (array_key_exists ($fieldname, $fields)) {
    return $ucfirst? ucfirst ($fields[$fieldname]) : $fields[$fieldname];
  } else {
    return $ucfirst? ucfirst ($fieldname) : $fieldname;
  }
}

?>