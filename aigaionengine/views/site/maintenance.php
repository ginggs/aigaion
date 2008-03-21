<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/site/maintenance

Shows a form for site maintenance.

Parameters:
    none

we assume that this view is not loaded if you don't have the appropriate database_manage rights

*/
?>
<p class='header'>Maintenance and checks</p>
There is a set of maintenance functions available. You can either perform the maintenance functions separately by selecting from the list, or you can <?php echo anchor('site/maintenance/all','perform all checks at once');?>.
<ul>
	<li><?php echo anchor('site/maintenance/attachments','Check attachments'); ?></li>
	<li><?php echo anchor('site/maintenance/topics','Check topics'); ?></li>
	<li><?php echo anchor('site/maintenance/notes','Check notes'); ?></li>
	<li><?php echo anchor('site/maintenance/authors','Check authors'); ?></li>
	<li><?php echo anchor('site/maintenance/passwords','Check passwords'); ?></li>
	<li><?php echo anchor('site/maintenance/cleannames','Check searchable names and titles'); ?></li>
	<li><?php echo anchor('site/maintenance/publicationmarks','Check publication marks'); ?></li>
	<li><?php echo anchor('site/maintenance/checkupdates','Check for updates'); ?></li>
</ul>
<p class='header'>Backup and restore</p>
Making regular backups of the database is recommended. Collecting a complete bibliography takes a lot of time and a single server crash fades all these efforts away. Storing the backupfiles on another server or medium is recommended.
<ul>
	<li><?php echo anchor('site/backup','Export database', array('class'=>'open_extern')); ?></li>
	<!--<li><a href='?page=maintenance&type=siteimport'>Restore database from backup</a></li>-->
	<!--<br/>-->
	<!--<li><a href='?page=maintenance&type=attachmentbackup'>Export attachments</a></li>-->
	<!--<li><a href='?page=maintenance&type=attachmentrestore'>Restore local attachments</a></li>-->
</ul>
