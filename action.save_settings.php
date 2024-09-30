<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/

if (empty($this) || !($this instanceof UnDoer)) exit;
if (!$this->VisibleToAdminUser()) exit;
if (!$this->CheckPermission('Modify Site Preferences')) exit;

if (!isset($params['submit'])) {
    $this->Redirect($id, 'settings', $returnid); //user cancelled
}

$p1 = $this->CheckPermission('Modify Any Page') || $this->CheckPermission('Manage All Content');
$p2 = $this->CheckPermission('Modify Templates');
$p3 = $this->CheckPermission('Manage Stylesheets');

$a1 = $this->GetPreference('ArchiveContent');
$a2 = $this->GetPreference('ArchiveTemplates');
$a3 = $this->GetPreference('ArchiveStylesheets');
// if any of these change to false, update db content accordingly if permitted
$b1 = !empty($params['archivecontent']);
$b2 = !empty($params['archivestyles']);
$b3 = !empty($params['archivetemlates']);

if ($a1 != $b1) {
	$this->SetPreference('ArchiveContent', $b1);
	if ($a1 && !$b1 && $p1) {
		$db->Execute('DELETE FROM '.CMS_DB_PREFIX.'module_undoer WHERE item_type='.UnDoer::TYPE_CONTENT);
	}
}
if ($a2 != $b2) {
	$this->SetPreference('ArchiveTemplates', $b2);
	if ($a2 && !$b2 && $p2) {
		$db->Execute('DELETE FROM '.CMS_DB_PREFIX.'module_undoer WHERE item_type='.UnDoer::TYPE_STYLESHEET);
	}
}
if ($a3 != $b3) {
	$this->SetPreference('ArchiveStylesheets', $b3);
	if ($a3 && !$b3 && $p3) {
		$db->Execute('DELETE FROM '.CMS_DB_PREFIX.'module_undoer WHERE item_type='.UnDoer::TYPE_TEMPLATE);
	}
}

$this->SetPreference('date_format', isset($params['date_format']) ? $params['date_format'] : 'l, j F Y H:i'); //TODO sanitized

$c1 = $this->GetPreference('purge_count');
$c2 = $this->GetPreference('purge_time');
// if any of these reduce, update db content accordingly if permitted
$d1 = isset($params['purge_count']) ? (int) $params['purge_count'] : -1;
$d2 = isset($params['purge_time']) ? (int) $params['purge_time'] : -1; //> 0 = days interval

if ($c1 != $d1) {
	$this->SetPreference('purge_count', $d1);
	if ($d1 > 0 && $d1 < $c1) {
//TODO relevant type-specific permission(s) $p1 $p2 and/or $p3
		$dbr = $db->GetCol('SELECT id FROM '.CMS_DB_PREFIX.'module_undoer
GROUP BY item_id HAVING COUNT(item_id)<'.$d1);
//		if ($dbr) {
//			$db->Execute('DELETE FROM '.CMS_DB_PREFIX.'module_undoer WHERE id IN('.implode(',',$dbr).')');
//		}
//TODO keep only latest $d1 for each item_id
//		if ($dbr) {
//			$db->Execute('DELETE ALL EXCEPT LATEST $d1 FROM EVERY GROUP IN '.CMS_DB_PREFIX.'module_undoer');
//		}
	}
}
if ($c2 != $d2) {
	$this->SetPreference('purge_time', $d2);
	if ($d2 > 0 && $d2 < $c2) {
		$cutoff = $db->DBTimeStamp(strtotime("today -{$d2} days"));
		$query = 'DELETE FROM '.CMS_DB_PREFIX.'module_undoer WHERE archive_date < '.$cutoff;
		if (!($p1 && $p2 && $p3)) {
			$where = [];
			if ($p1) { $where[] = 'item_type='.UnDoer::TYPE_CONTENT; }
			if ($p2) { $where[] = 'item_type='.UnDoer::TYPE_STYLESHEET; }
			if ($p3) { $where[] = 'item_type='.UnDoer::TYPE_TEMPLATE; }
			$query .= ' AND ('. implode(' OR ', $where).')';
		}
		$db->Execute($query);
	}
}

$this->SetMessage($this->Lang('prefsupdated'));
$this->Redirect($id, 'settings', $returnid);
