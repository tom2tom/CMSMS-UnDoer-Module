<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/
//NOTE if now working with a different version of CMSMS, classnames in
//recorded archives (serialize'd etc) might need to be manually altered 

if (!defined('CMS_VERSION')) exit;
if (!isset($gCms)) exit;
if (!$this->CheckPermission('Modify Modules')) exit;
if (!class_exists('CMSModule')) return false;

$current_version = $oldversion;
switch ($current_version) {
	case '0.6':
	$this->RemoveEventHandler('Core', 'ContentEditPost');
	$this->RemoveEventHandler('Core', 'AddTemplatePost');
	$this->RemoveEventHandler('Core', 'AddStylesheetPost');
	//possibly (per module preferences)
	if (0) $this->RemoveEventHandler('Core', 'ContentEditPre');
	if (0) $this->RemoveEventHandler('Core', 'EditTemplatePre');
	if (0) $this->RemoveEventHandler('Core', 'EditStylesheetPre');
	// no break here
}
audit('', $this->Lang('friendlyname'), $this->Lang('upgraded', $this->GetVersion()));
