<?php

$lang['accessdenied'] = 'Access denied. Please check your permissions.';
$lang['adminprefs'] = 'Settings'; //see also 'title_admin_prefs'

$lang['column_action'] = 'Operation';
$lang['column_date'] = 'Date';
$lang['column_name'] = 'Name';
$lang['column_revision'] = 'Revision';
$lang['column_type'] = 'Type';
$lang['content'] = 'Page Content';

$lang['date_format_help'] = 'Date formats are specified using PHP "date" format codes. For reference, please visit <a href="https://www.php.net/manual/en/datetime.format.php">the PHP website</a>. Remember to escape any characters you don\'t want interpreted as date format codes!<br />';

$lang['error'] = 'Error!';
$lang['error_invalid_info'] = 'Something went wrong. Trying to restore object of unknown type.';

$lang['friendlyname'] = 'Undo Changes';

//$lang['htmlblob'] = 'Global Content Block';

$lang['installed'] = 'Module version %s installed.';
$lang['items'] = 'Revisions';

//$lang['fulllist'] = 'Full List';
//$lang['listsnapshots'] = 'Snapshots';

$lang['moddescription'] = 'Archive and restore Content, Style Sheets and Templates.';

$lang['next'] = 'Next Page &gt;';
$lang['no_revision'] = 'No revision is recorded.';

$lang['page'] = 'Page';
$lang['postinstall'] = 'Be sure to tailor module settings, and grant "Restore" permission to users authorized to use this module!';
$lang['postuninstall'] = 'Module removed';
$lang['prefsupdated'] = 'Module preferences updated.';
$lang['prev'] = '&lt; Previous Page';
$lang['preview'] = 'Preview';
$lang['purge_info2'] = 'Your retention choices take effect the next time you edit a page. Archives are affected only when their corresponding page is next edited (so if you previously had selected "Keep archives forever" and then changed to "Keep most recent five revisions", your database will retain all historical copies of every page until you edit a page. After that, the database will only have five previous revisions.';

$lang['restore'] = 'Restore to current';
$lang['restored_content'] = 'Restored page %s to revision %s';
//$lang['restored_htmlblob'] = 'Restored Global Content Block %s to revision %s';
$lang['restored_stylesheet'] = 'Restored stylesheet %s to revision %s';
$lang['restored_template'] = 'Restored template %s to revision %s';

//$lang['simplelist'] = 'Summary List';
$lang['sort_asc'] = 'Sort Ascending';
$lang['sort_desc'] = 'Sort Descending';
$lang['stylesheet'] = 'Stylesheet';
$lang['surerestore'] = 'Are you sure you want to restore to revision %s?';

$lang['template'] = 'Template';
$lang['title_admin_panel'] = 'Undo Changes';
$lang['title_admin_prefs'] = 'Settings'; //see also 'adminprefs'
//$lang['title_admin_snaps'] = 'Snapshot Management';
$lang['title_archive_content'] = 'Keep Pages';
$lang['title_archive_sheets'] = 'Keep Stylesheets';
$lang['title_archive_tpls'] = 'Keep Templates';
$lang['title_date_format'] = 'Displayed Date/Times Format';
$lang['title_filter_content'] = 'Only Content';
//$lang['title_filter_htmlblob'] = 'Only Global Content Blocks';
$lang['title_filter_none'] = 'All Archive Types';
$lang['title_filter_stylesheet'] = 'Only Stylesheets';
$lang['title_filter_template'] = 'Only Templates';
$lang['title_filter_type'] = 'Show ';
$lang['title_mod_prefs'] = 'Module Preferences';
$lang['title_preview'] = 'Preview of revision %s';
$lang['title_purge_10_revisions'] = 'Most recent ten';
$lang['title_purge_14_days'] = 'Only from the last 14 days';
$lang['title_purge_180_days'] = 'Only from the last 180 days';
$lang['title_purge_1_days'] = 'Only from the last day';
$lang['title_purge_20_revisions'] = 'Most recent twenty';
$lang['title_purge_30_days'] = 'Only from the last 30 days';
$lang['title_purge_365_days'] = 'Only from the last 365 days';
$lang['title_purge_50_revisions'] = 'Most recent fifty';
$lang['title_purge_5_revisions'] = 'Most recent five';
$lang['title_purge_7_days'] = 'Only from the last 7 days';
$lang['title_purge_90_days'] = 'Only from the last 90 days';
$lang['title_purge_count'] = 'How many revisions of each item should be kept?';
$lang['title_purge_forever'] = 'Forever';
$lang['title_purge_time'] = 'How long should revisions be kept?';
$lang['title_purge_unlimited'] = 'All';
$lang['title_purge_warning'] = 'Warning! If you select both a specific revision count and a specific revision lifetime, the <strong>more restrictive</strong> of those two values will prevail when the system decides whether or not to keep a revision.';
//$lang['title_save_snapshot'] = 'Save a snapshot of your site';
//$lang['title_snapshot_name'] = 'Snapshot name';
//$lang['title_snapshot_not_yet'] = 'Whole-site snapshots are not yet implemented.';

$lang['uninstalled'] = 'Module Uninstalled.';
$lang['upgraded'] = 'Module upgraded to version %s.';

$lang['welcome_text'] = 'Please select an operation.';

//<li>Snapshots (i.e. archives of the exact state of the entire website at some instant in time) are not yet implemented.</li>
//<p>Any admin who has "Manage Restore" permissions can view the revision histories. There are two interfaces, simple and advanced. Simple lists each extant object in the archive, which you can sort by name or type. The advanced interface shows all revisions of all objects in the archive; you can sort this list by name, type, date, or revision number.</p>
//<p>Typically, you\'ll find the object in the archive via the simple interface. Using the pulldown menu, you can filter to show only one type of archive object (Content, Template, Global Content Blocks, or Stylesheet). Clicking on the item\'s name will take you to that object\'s history page. You can then preview the object (in the case of Content) by clicking on the preview icon.</p>
$lang['help'] = '<h3>What Does This Do?</h3>
<p>This module keeps around a copy of every Content Page, Stylesheet or Template that is edited or deleted. It also provides an interface so that authorized administrators can restore anything in the archive to become the current version.</p>
<p>This means that after editing some aspects of the site, those changes can be reversed later.</p>
<p>Archived changes can be automatically purged, either by revision count (i.e. keep only a set number of revisions of each page, stylesheet, and template) or by date (i.e. keep only revisions from the last n days). You do not have to purge archives, though, and can keep accumulating them until your database overflows.</p>
<h3>What Does This NOT Do?</h3>
<p>This is not a true revision control system. Due to the data structures of CMS Made Simple, implementing a true RCS proved too much of a challenge for my puny intellect. This was the next best solution.</P>
<ul>
<li>Not all metadata is archived. Properties of content pages such as hierarchy position, owner, active, show in menu, etc are not archived. Similarly, stylesheet and template associations are not archived.</li>
<li>The system does not store deltas, but entire copies of each archive. This is wasteful of database space, but much easier to implement.</li>
</ul>
<h3>How Do I Use It</h3>
<h4>General</h4>
<p>After installation, the system will automatically start saving changes into the archives. You don\'t have to do anything special.</p>
<h4>Expiration of Archives</h4>
<p>Any admin user who has "Manage Restore" and "Manage Site Preferences" permissions may go into the archive admin, and set Archive expiration parameters. in the "Archive Settings" page. This should be self-explanatory. Keep in mind that the <strong>shorter</strong> of the expiration parameters you set will be used -- if you set "Keep Revisions Forever" in the time-based expiration, but only "keep the most recent 5 revisions" from the revision count expiration, you will only keep the five most recent.</p>
<h4>Restoring from Archives</h4>
<p>Any admin user who has "Manage Restore" permission can view the revisions history. The listed archives can be sorted by name, type, date, or revision number.</p>
<p>Using the pulldown menu, you can filter to show only one type of archive object (Content, Template, or Stylesheet). Clicking on the item\'s name will take you to that object\'s history page. You can then preview the object (in the case of Content) by clicking on the preview icon.</p>
<P>Restore an item by clicking on the "restore" icon on the far right. That\'s all there is to it!</p>
<h3>Possible Improvements</h3>
<ul>
<li>Preview of Templates?</li>
<li>Snapshots (i.e. archives of the exact state of the entire website at some instant in time)?</li>
<li>Better/more efficient storage? Store deltas?</li>
</ul>
<h3>Features That Might Be Misinterpreted as Bugs</h3>
<ul>
<li>Restoring an item creates an additional revision. So if say there are versions 1 - 4, and you restore to version 1, you\'ll get an additional copy of revision 1 called "revision 5" thrown in to your archive.</li>
<li>If the name of an item is changed, the item will show up under both names in the Archive view. Clicking on either name will show you all revisions of that page, without regard to the name.</li>
</ul>
<h3>Support</h3>
<p>For the latest version of this module, or to file a Bug Report, please visit the <a href="http://dev.cmsmadesimple.org/projects/archiver">CMSMS Forge project</a>.</p>
<p>Discussion of this module may also be found in the <a href="http://forum.cmsmadesimple.org">CMSMS Forums</a> and/or on the <a href="https://www.cmsmadesimple.org/support/documentation/chat" target="_blank">CMSMS Slack channel</a>.</p>
<p>As per the GPL, this software is provided as-is. Please read the text of that license for the full disclaimer.</p>
<h3>Copyright and License</h3>
<p>Copyright &copy; 2024 CMS Made Simple Foundation Inc &lt;foundation@cmsmadesimple.org&gt;. All rights reserved.</p>
<p>This module has been released under the <a href="http://www.gnu.org/licenses/licenses.html#GPL">GNU General Public License</a>. This module may not be distributed or used otherwise than in accordance with that license.';
