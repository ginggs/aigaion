
<div class='author_list'>
  <div class='header'><?php echo $header ?></div>
<?php 
echo "    ".form_open('authors/searchlist')."\n";
echo "    ".form_input(array('name' => 'author_search', 'id' => 'author_search', 'size' => '50', 'autocomplete' => 'off'));
echo "    ".form_submit('submit', 'show')."\n";
echo "    <script type='text/javascript'>".$this->ajax->observe_field('author_search', $options = array('url' => 'authors/searchlist/\' + value +\'', 'update' => 'autocomplete_results', 'function' => '', 'frequency' => '0.2', 'on' => 'focus'))."</script>\n";
echo "    ".form_close()."\n";
?>
  <div id='autocomplete_results'>
<?php 
$this->load->view('authors/list_items', $authorlist);
?>
  </div>
</div>
<?php

//echo "<span id='indicator1' style='display: none'>Working</span>";
//echo "<div name='autocomplete_results' id='autocomplete_results'>";
//echo $this->ajax->auto_complete_field('author_search', $options = array('url' => base_url().'index.php/authors/searchlist/', 'update' => 'autocomplete_results',  'frequency' => '0.01', 'indicator' => 'indicator1'))."\n";

?>