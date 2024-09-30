<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/

if (empty($this) || !($this instanceof UnDoer)) exit;
if (!$this->VisibleToAdminUser()) exit;
if (!$this->CheckAccess()) exit;

//$params start=0 type_filter=-1 type_id=1 sort_order=date item_id=34 revision_number=1

$restore = UnDoer\Utils::retrieveSerializedObject($params['item_id'], $params['revision_number']);
// get the current object, and update select attributes. That way, we don't screw up hierarchy, etc.
if (!$restore) {
	$this->SetError($this->Lang('error'));
	$this->Redirect($id, 'defaultadmin', $returnid);//TODO to relevant action per old DisplayArchives() send relevant $params[]
}

if ($params['type_id'] == UnDoer::TYPE_CONTENT) {
if (!($this->CheckPermission('Modify Any Page') || $this->CheckPermission('Manage All Content'))) exit;
	$cm = $gCms->GetContentOperations();
	$current = $cm->LoadContentFromId($params['item_id'], true);
	$current->SetName($restore->Name());
	$current->SetTemplateId($restore->TemplateId());
	$current->SetMenuText($restore->MenuText());
	$tmp = $restore->Properties();
	foreach ($tmp as $key => $val) { //old: foreach ($tmp->mPropertyValues as $key=>$val)
		$current->SetPropertyValue($key, $val);
	}
	$current->Save();
	$msg = $this->Lang('restored_content', [$current->Name(), $params['revision_number']]);
} elseif ($params['type_id'] == UnDoer::TYPE_STYLESHEET) {
	if (!$this->CheckPermission('Manage Stylesheets')) exit;
//	$cssOps = $gCms->GetStylesheetOperations(); class N/A
//	$current = $cssOps->LoadStylesheetByID($params['item_id'], true);
	$current = CmsLayoutStylesheet::load($params['item_id']);
	$current->name = $restore->name;
	$current->value = $restore->value;
	$current->media_type = $restore->media_type;
	$current->Save();
	UnDoer\Utils::ArchiveObject($current, UnDoer::TYPE_STYLESHEET);
	$msg = $this->Lang('restored_stylesheet', [$current->name, $params['revision_number']]);
/*} elseif ($params['type_id'] == UnDoer::TYPE_HTMLBLOB) {
	$gcbops = $gCms->GetGlobalContentOperations();
	$current = $gcbops->LoadHtmlBlobByID($params['item_id']);
	$current->name = $restore->name;
	$current->content = $restore->content;
	$current->Save();
	UnDoer\Utils::ArchiveObject($current, UnDoer::TYPE_HTMLBLOB);
	$msg = $this->Lang('restored_htmlblob', [$current->name, $params['revision_number']]);
*/
} elseif ($params['type_id'] == UnDoer::TYPE_TEMPLATE) {
	if (!$this->CheckPermission('Modify Templates')) exit;
//	$templateOps = $gCms->GetTemplateOperations(); class N/A
//	$current = $templateOps->LoadTemplateByID($params['item_id']);
	$current = CmsLayoutTemplate::load($params['item_id']);
	$current->content = $restore->content;
	$current->name = $restore->name;
	$current->stylesheet = $restore->stylesheet;
	$current->encoding = $restore->encoding;
	$current->Save();
	UnDoer\Utils::ArchiveObject($current, UnDoer::TYPE_TEMPLATE);
	$msg = $this->Lang('restored_template', [$current->name, $params['revision_number']]);
}

if ($current === false) {
	return UnDoer\Utils::DisplayErrorPage($this->Lang('error_invalid_info'));
}
audit('', $this->Lang('friendlyname'), $msg);

$this->SetMessage($msg);
$this->Redirect($id, 'defaultadmin', $returnid);//TODO to relevant action per old DisplayArchives() send relevant $params[]
