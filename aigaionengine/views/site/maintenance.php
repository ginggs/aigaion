<?php
/**
views/site/maintenance

Shows a form for site maintenance.

Parameters:
    none

we assume that this view is not loaded if you don't have the appropriate database_manage rights

*/
?>
<p class='header'>Backup and restore</p>
Making regular backups of the database is recommended. Collecting a complete bibliography takes a lot of time and a single server crash fades all these efforts away. Storing the backupfiles on another server or medium is recommended.
<ul>
	<li><?php echo anchor('site/backup','Export database', array('target'=>'_blank')); ?></li>
	<!--<li><a href='?page=maintenance&type=siteimport'>Restore database from backup</a></li>-->
	<!--<br/>-->
	<!--<li><a href='?page=maintenance&type=attachmentbackup'>Export attachments</a></li>-->
	<!--<li><a href='?page=maintenance&type=attachmentrestore'>Restore local attachments</a></li>-->
</ul>
