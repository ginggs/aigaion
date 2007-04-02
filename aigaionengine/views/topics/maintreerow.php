<?php        
    #make hide scripts to show and hide proper parts depending on some collapse state
    $hide1="";
    $hide2="Element.hide('plus_topic_".$topic->topic_id."');";
//    if ($topic->topic_id == 27) { //collapse or expand can be dependent on a collapse setting for this person.
//        $hide1="Element.hide('min_topic_".$topic->topic_id."');";
//        $hide2="";
//    }
    #
    if (sizeof($topic->getChildren())>0) {
        echo "<img id      = 'min_topic_".$topic->topic_id."' 
                   onclick = 'collapse(\"".$topic->topic_id."\",\"\");' 
                   src     = '".getIconUrl('tree_min.gif')."'/>\n";
        echo "<img id      = 'plus_topic_".$topic->topic_id."' 
                   onclick = 'expand(\"".$topic->topic_id."\",\"\");' 
                   src     = '".getIconUrl('tree_plus.gif')."'/>\n";
        echo "<script>".$hide1.$hide2."</script>"; 
    }
    echo anchor('topics/view/'.$topic->topic_id,$topic->name)."\n";

?>