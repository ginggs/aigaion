<?php
/**
views/bookmarklist/controls

Shows the controls for using the bookmarklist

*/
$this->load->helper('form');
?>
<p class='header'>Bookmark list controls</p>

<?php     
    echo form_open('bookmarklist/addtotopic');
    $user = $this->user_db->getByID(getUserLogin()->userId());
    $config = array('onlyIfUserSubscribed'=>True,
                    'includeGroupSubscriptions'=>True,
                    'user'=>$user);
echo $this->load->view('topics/optiontree',
                       array('topics'   => $this->topic_db->getByID(1,$config),
                            'showroot'  => False,
                            'depth'     => -1,
                            'selected'  => -1,
                            'dropdownname' => 'topic_id',
                            'header'    => 'Add bookmarked to topic...'
                            ),  
                       true)."\n";
    echo form_submit(array('name'=>'addtotopic','title'=>'Add all bookmarked publications to the selected topic'),'Add all to topic');
    echo form_close();
?>
<br>
[export to BiBTeX]
<br>
[export to WhaTEveR]
<br>
[make new topic from bookmarked publications]
<br>
<br>