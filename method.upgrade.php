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

$current_version = $oldversion;
switch($current_version) {
}
audit('', $this->Lang('friendlyname'), $this->Lang('upgraded', $this->GetVersion()));
