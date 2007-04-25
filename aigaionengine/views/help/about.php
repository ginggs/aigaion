<div id="help-holder">
<p class="header">
    About
</p>
<?php
        $Q = $this->db->query("SELECT * FROM aigaiongeneral");
        if ($Q->num_rows()>0) {
            $version = $Q->row()->version;
            $release = $Q->row()->releaseversion;
        } else {
            $version = '0.0';
            $release = "0.0";
        }
        echo "Aigaion Database Version: ".$version."<br/>Aigaion Release: ".$release;
?>
</div>
