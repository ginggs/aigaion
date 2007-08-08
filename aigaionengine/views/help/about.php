<div id="help-holder">
  <p class='header1'>About</p>
  <p>
<?php
        $Q = $this->db->get('aigaiongeneral');
        if ($Q->num_rows()>0) {
          $R = $Q->row();
            $version = $R->version;
            $release = $R->releaseversion;
        } else {
            $version = '0.0';
            $release = "0.0";
        }
        echo "Aigaion Database Version: ".$version."<br/>";
        echo "Aigaion Release: ".$release."<br/>";
        echo "Administrator of this installation: <a href='mailto:".getConfigurationSetting('CFG_ADMINMAIL')."'>".getConfigurationSetting('CFG_ADMIN')."</a><br/>";
        echo "URL of this installation: ".AIGAION_ROOT_URL."<br/>";
        echo "PHP version: ".phpversion()."<br/>";
?>
  </p>
</div>
