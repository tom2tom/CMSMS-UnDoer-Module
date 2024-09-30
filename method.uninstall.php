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
//$sqlarray = $dict->DropTableSQL(CMS_DB_PREFIX.'module_restorer_item');
//$dict->ExecuteSQLArray($sqlarray);
//$sqlarray = $dict->DropTableSQL(CMS_DB_PREFIX.'module_undoer');
$sqlarray = $dict->DropTableSQL(CMS_DB_PREFIX.'module_undoer');
$dict->ExecuteSQLArray($sqlarray);

//$db->DropSequence(CMS_DB_PREFIX.'module_restorer_seq');

//these should be audit-type-preferences-specific
$this->RemoveEventHandler('Core', 'ContentEditPost');
$this->RemoveEventHandler('Core', 'ContentEditPre');
$this->RemoveEventHandler('Core', 'AddTemplatePost');
$this->RemoveEventHandler('Core', 'EditTemplatePre');
$this->RemoveEventHandler('Core', 'EditStylesheetPre');
$this->RemoveEventHandler('Core', 'AddStylesheetPost');

$this->RemovePermission('Manage Restores');

$this->RemovePreference();

audit('', $this->Lang('friendlyname'), $this->Lang('uninstalled'));
