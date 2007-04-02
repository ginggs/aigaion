<?php
/**
views/topics/edit

Shows a form for editing topics.

Parameters:
    $topic=>the Topic object to be edited
    
If $topic is null, the edit for will be restyled as an 'add new topic' form

*/

$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('topics/commit');
$isAddForm = False;
if (!isset($topic)||($topic==null)) {
    $isAddForm = True;
    echo form_hidden('action','add');
    $topic = new Topic;
} else {
    echo form_hidden('action','edit');
    echo form_hidden('topic_id',$topic->topic_id);
}

if ($isAddForm) {
    echo "<p class='header2'>Add a topic</p>";
} else {
    echo "<p class='header2'>Change topic \"".$topic->name."\"</p>";
}
echo "
    <table>
        <tr><td><label for='name'>Name</label></td>
            <td>
     ";
echo form_input(array('name'=>'name','size'=>'30','value'=>$topic->name));
echo "
            </td>
        </tr>
     ";
echo "
        <tr><td><label for='parent'>Parent</label></td>
            <td>
     ";
     
echo $this->load->view('topics/optiontree',
                       array('topics'   => $this->topic_db->getByID(1),
                            'showroot'  => False,
                            'depth'     => -1,
                            'selected'  => $topic->parent_id
                            ),  
                       true)."\n";
echo "
            </td>
        </tr>
        <tr><td><label for='description'>Description</label></td>
            <td>
     ";
echo form_textarea(array('name'=>'description','cols'=>'70','rows'=>'7','value'=>$topic->description));
echo "
            </td>
        </tr>                
        <tr><td><label for='url'>URL</label></td>
            <td>
     ";
echo form_input(array('name'=>'url','size'=>'30','value'=>$topic->url));
echo "
            </td>
        </tr>                
        <tr><td>";
if ($isAddForm) {
    echo form_submit('submit','Add');
} else {
    echo form_submit('submit','Change');
}
echo "
        </td>
        </tr>
    </table>
     ";
echo form_close();
echo form_open('');
echo form_submit('cancel','Cancel');
echo form_close();
echo "</div>";

?>

