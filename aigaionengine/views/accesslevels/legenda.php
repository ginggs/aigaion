<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div style='border:1px solid black;'>
    <div style='border:1px solid black;'>
        <b>Legenda</b>
    </div>
    <?php
    echo "
    r:<img class='al_icon' src='".getIconurl('al_public.gif')."'/> read public<br/> 
    r:<img class='al_icon' src='".getIconurl('al_intern.gif')."'/> read intern<br/> 
    r:<img class='al_icon' src='".getIconurl('al_private.gif')."'/> read private<br/> 
    e:<img class='al_icon' src='".getIconurl('al_public.gif')."'/> edit public<br/> 
    e:<img class='al_icon' src='".getIconurl('al_intern.gif')."'/> edit intern<br/> 
    e:<img class='al_icon' src='".getIconurl('al_private.gif')."'/> edit private<br/> 
    - If nothing is shown, access level is 'intern'.<br/>
    ";
    ?>
</div>
<br/>