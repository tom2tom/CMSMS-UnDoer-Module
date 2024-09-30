<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/

if (empty($this) || !($this instanceof UnDoer)) exit;
if (!$this->VisibleToAdminUser()) exit;
if (!$this->CheckPermission('Modify Site Preferences')) exit;

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('settings.tpl'), null, null, $smarty);

$tpl->assign('startform', $this->CreateFormStart($id, 'save_settings', $returnid));
$tpl->assign('endform', $this->CreateFormEnd());

$tpl->assign('docontent', $this->GetPreference('ArchiveContent', true));
$tpl->assign('dostyles', $this->GetPreference('ArchiveTemplates', true));
$tpl->assign('dotemplates', $this->GetPreference('ArchiveStylesheets', true));
// flags whether archive-types may be unset TODO ok to unset if nothing would be deleted after doing so
$tpl->assign('pdelc', $this->CheckPermission('Modify Any Page') || $this->CheckPermission('Manage All Content'));
$tpl->assign('pdels', $this->CheckPermission('Manage Stylesheets'));
$tpl->assign('pdelt', $this->CheckPermission('Modify Templates'));

$tpl->assign('date_format', $this->GetPreference('date_format', 'l, j F Y H:i'));

$tpl->assign('countslist', [
	-1 => $this->Lang('title_purge_unlimited'),
	 5 => $this->Lang('title_purge_5_revisions'),
	10 => $this->Lang('title_purge_10_revisions'),
	20 => $this->Lang('title_purge_20_revisions'),
	50 => $this->Lang('title_purge_50_revisions')
]);
$tpl->assign('purge_count', $this->GetPreference('purge_count', -1));
$tpl->assign('dayslist', [
	-1 => $this->Lang('title_purge_forever'),
	 1 => $this->Lang('title_purge_1_days'),
	 7 => $this->Lang('title_purge_7_days'),
	14 => $this->Lang('title_purge_14_days'), 
	30 => $this->Lang('title_purge_30_days'), 
	90 => $this->Lang('title_purge_90_days'), 
	180 => $this->Lang('title_purge_180_days'),
	365 => $this->Lang('title_purge_365_days')
]);
$tpl->assign('purge_time', $this->GetPreference('purge_time', -1));

$nav = $this->CreateLink($id, 'defaultadmin', $returnid,
	'<img src="'.$this->GetModuleURLPath().'/images/items.png" class="navicon" alt="'.
	$this->Lang('items').'" title="'.$this->Lang('items').'">',
	[]
) .
$this->CreateLink($id, 'defaultadmin', $returnid, $this->Lang('items'), [], '', false, false, 'class="pageoptions"');
$smarty->assign('admin_nav', $nav);

$message = (!empty($params['message'])) ? $params['message'] : '';
$tpl->assign('message', $message);

$tpl->display();
