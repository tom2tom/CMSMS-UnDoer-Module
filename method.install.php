<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/

//use CMSMS\AppParams as cms_siteprefs;

if (!defined('CMS_VERSION')) exit;
if (!isset($gCms)) exit;
if (!$this->CheckPermission('Modify Modules')) exit;
if (!class_exists('CMSModule')) return false;

$taboptarray = ['mysqli' => 'ENGINE=MyISAM', 'mysql' => 'ENGINE=MyISAM'];
$dict = NewDataDictionary($db);

// table schema description
/*
$flds = '
id I KEY,
serialized_item X
';

//TODO why was this a separate table
$sqlarray = $dict->CreateTableSQL(CMS_DB_PREFIX.'module_restorer_item', $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);
//TODO sequence for this table instead?
*/

//TODO item_name often sorted, want it caseless if not ci whole-table
//item_subtype used for content-object type
//TODO long-blob for serialized items if can be > 64kB?
$flds = '
id I AUTO KEY,
item_id I,
item_type I2,
item_subtype C(32),
item_name C(100),
item_hash C(40),
revision_number I2,
archive_date DT,
archive_content B
';

//$sqlarray = $dict->CreateTableSQL(CMS_DB_PREFIX.'module_undoer', $flds, $taboptarray);
$sqlarray = $dict->CreateTableSQL(CMS_DB_PREFIX.'module_undoer', $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);
//$db->CreateSequence(CMS_DB_PREFIX.'module_restorer_seq');

$this->SetPreference('ArchiveContent', true);
$this->SetPreference('ArchiveTemplates', true);
$this->SetPreference('ArchiveStylesheets', true);
//NOTE this pref value might be strftime() compatible, but this module expects date()
$fmt = cms_siteprefs::get('defaultdateformat', 'l, j F Y H:i');
$this->SetPreference('date_format', $fmt);
$this->SetPreference('purge_count', -1); // unlimited
$this->SetPreference('purge_time', -1); // never

$this->CreatePermission('Manage Restores', 'Manage Restores');
$this->CreatePermission('Delete Restores', 'Delete Restores'); // manual deletion

//all 3 types of monitored revision are initially enabled
//NOTE these events issue AFTER the item is edited, but before saving
$this->AddEventHandler('Core', 'ContentEditPre', false);
$this->AddEventHandler('Core', 'EditTemplatePre', false);
$this->AddEventHandler('Core', 'EditStylesheetPre', false);
//$this->AddEventHandler('Core', 'ContentEditPost', false);
//$this->AddEventHandler('Core', 'AddTemplatePost', false);
//$this->AddEventHandler('Core', 'AddStylesheetPost', false);

audit('', $this->Lang('friendlyname'), $this->Lang('installed', $this->GetVersion()));
