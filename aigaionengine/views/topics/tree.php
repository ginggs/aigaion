<!-- topic browse displays -->
<?php        
/*

    'topics'       => $topics, //array of topics to be shown
    'showroot'      => False,    //if False, don't show the root(s) of the passed (sub)trees
    'depth'         => -1,       //max depth for which to render the trees

    The following var is passed around a lot, and not modified along the way, so it can be loaded using
    $this->load->vars(array( 
    
    'subviews' => array('topics/maintreerow'=>array('collapseCallback'=>$collapseCallback)) 
                             
        (subviews is array of 'viewname' => array(arguments,to,be,passed). $topic is always added to the arguments.
  
    Maybe optional: pass css classnames for node, leaf, subtree, etc. Just so we can make different trees even have different styling.
    Typically loaded with $this->load->vars(
  
                             */
?>
<?php
    if (!isset($depth))$depth = -1;
    if (!isset($showroot))$showroot = False;
    if (!isset($subviews))$this->load->vars(array('subviews' => array()));
    
    $todo = array();
    if (isset($topics)) {
        if (is_array($topics)) {
            $todo = $topics;
        } else {
            $todo = array($topics);
        }
    }
    
    $first = True;
    /* This is an experiment in left traversal of the tree that does not need nested views. (loading nested views seems to be extremely inefficient) */
    while (sizeof($todo)>0){
        //get next topic to be displayed
        $next = $todo[0];
        //remove from todo list
        unset($todo[0]);
        if ($next=="end") {
            //if next is an end marker:
            echo "<div>\n</ul>\n";
            $todo = array_values($todo); //reindex
        } else {
            //if next is a node: 
            $children = $next->getChildren();
            if (!$first || $showroot) {
                if (sizeof($children)==0) {
                    $li_class = 'topictree-leaf';
                } else {
                    $li_class = 'topictree-node';
                }
                echo "<li class='".$li_class."'>";
                foreach ($subviews as $subview => $args) {
                    $args['topic'] = $next;
                    echo $this->load->view($subview,
                                          $args,
                                          True);
                }
            }
            if (sizeof($children)>0) {
                echo "<ul class='topictree-list'>\n<div id='topic_children_".$next->topic_id."' class='topictree-children'>\n";
                //has children: open node and add all children + end marker in front of todo list; print this node
                $todo = array_merge($children,array('end'),$todo); //merge and reindex
            } else {
                $todo = array_values($todo); //reindex
            }
            $first = False;
        }
         //reindex
    }    
    
?>

<!-- End of topic browse displays -->
