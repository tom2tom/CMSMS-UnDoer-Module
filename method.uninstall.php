<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/

if (!defined('CMS_VERSION')) exit;
if (!isset($gCms)) exit;
if (!$this->CheckPermission('Modify Modules')) exit;
if (!class_exists('CMSModule')) return false;

$dict = NewDataDictionary($db);

$sqlarray = $dict->DropTableSQL(CMS_DB_PREFIX.'module_undoer');
$dict->ExecuteSQLArray($sqlarray);

//$this->RemoveEventHandler('Core', 'ContentEditPost');
//$this->RemoveEventHandler('Core', 'AddTemplatePost');
//$this->RemoveEventHandler('Core', 'AddStylesheetPost');
//possibly (per preferences)
if (1) $this->RemoveEventHandler('Core', 'ContentEditPre');
if (1) $this->RemoveEventHandler('Core', 'EditTemplatePre');
if (1) $this->RemoveEventHandler('Core', 'EditStylesheetPre');

$this->RemovePermission('Manage Restores');
$this->RemovePermission('Delete Restores');

$this->RemovePreference();

audit('', $this->Lang('friendlyname'), $this->Lang('uninstalled'));
