<!-- topic browse displays -->
<?php        
/*

    'topics'       => $topics, //array of topics to be shown
    'showroot'      => False,    //if False, don't show the root(s) of the passed (sub)trees
    'depth'         => -1,       //max depth for which to render the trees

    The following var is passed around a lot, and not modified along the way, so it must be loaded using
    $this->load->vars(array( 
    
    'subviews' => array('topics/collapselink'=>array(), 
                                            //or maybe: 'topics/collapselink'=>array('collapseCallback'=>$collapseCallback) 
                             'topics/browsesingletopiclink',
                             'topics/editlink') 
                             
        (subviews is array of 'viewname' => array(arguments,to,be,passed). $topic is always added to the arguments.
  
        
    Maybe optional: pass css classnames for node, leaf, subtree, etc. Just so we can make different trees even have different styling.
    Typically loaded with $this->load->vars(
  
                             */
?>                         
 
<ul class='topictree-list'>
    <?php
    if (!isset($depth))$depth = -1;
    if (!isset($showroot))$showroot = False;
    if (!isset($subviews))$this->load->vars(array('subviews' => array()));
    
    foreach ($topics as $topic) {
        /** show a <li> element formatted according to the subviews that were passed.
            Inside the <li> another <ul> with children-topics may be included, depending 
            on $depth, and existence of topics. */
        
        $children=$topic->getChildren(); 
        
        if (sizeof($children)==0) {
            $li_class = 'topictree-leaf';
        } else {
            $li_class = 'topictree-node';
        }
        //show this topic element as a list item with all the subviews that were specified
        if ($showroot) {
            echo "<li class='".$li_class."'>";
            foreach ($subviews as $subview => $args) {
                $args['topic'] = $topic;
                echo $this->load->view($subview,
                                      $args,
                                      True);
            }
        }
        //recurse to children: call the topics/tree view for the children
        if ($depth != 0) {
            $newdepth = -1;
            if ($depth != -1) $newdepth = $depth-1;
            echo "<div id='topic_children_".$topic->topic_id."' class='topictree-children'>\n";
            echo $this->load->view('topics/tree',
                                   array('topics'  => $children, 
                                         'showroot' => True, 
                                         'depth'    => $newdepth
                                        ),
                                   true);        
            echo "</div>\n"; 
            //here we would hide this element if we had decided that this node is collapsed 
            //(calling Element.hide() directly from a piece of javascript)
            if ($topic->topic_id == 27) {
                echo "<script>Element.hide('topic_children_".$topic->topic_id."')</script>";
            }
        }
        //close the list item for this class element 
        if ($showroot) {
            echo "</li>\n";
        }
    }
    ?>
</ul>
<!-- End of topic browse displays -->
