<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/
use UnDoer\Utils;

if (empty($this) || !($this instanceof UnDoer)) exit;
if (!$this->VisibleToAdminUser()) exit;
//if (!$this->CheckAccess()) exit;

//supplied $params rev_id=N start=0 type_filter=-1 type_id=1 sort_order=date item_id=34 revision_number=1
$restore = Utils::retrieveSerializedObject($params['item_id'], $params['revision_number']);
// get the current object, and update select attributes. That way, we don't screw up hierarchy, etc.
if (!$restore) {
	$msg = lang('error_internal'); //TODO better advice
	$this->SetError($msg);
	//$params['message'] = $msg;
	$this->Redirect($id, 'defaultadmin', $returnid, $this->FilterParms($params));
}

switch ($params['type_id']) {
	case UnDoer::TYPE_CONTENT:
		if (!($this->CheckPermission('Modify Any Page') || $this->CheckPermission('Manage All Content'))) exit;
		$ops = $gCms->GetContentOperations();
		$current = $ops->LoadContentFromId($params['item_id'], true); // gets cached content, if any TODO forced ?
		//TODO simply migrate all relevant aspects of $restore via clone() etc sans datetimes ?
		$current->SetName($restore->Name());
		$current->SetTemplateId($restore->TemplateId());
		$current->SetMenuText($restore->MenuText());
		//TODO other specific properties ?
		$tmp = $restore->Properties();
		foreach ($tmp as $key => $val) {
			$current->SetPropertyValue($key, $val);
		}
		$current->Save();
		$msg = $this->Lang('restored_content', [$current->Name(), $params['revision_number']]);
		break;
	case UnDoer::TYPE_STYLESHEET:
		if (!$this->CheckPermission('Manage Stylesheets')) exit;
		$current = CmsLayoutStylesheet::load($params['item_id']); // gets cached content, if any TODO forced original ?
		//TODO simply migrate all relevant aspects of $restore via clone() etc sans datetimes ?
		$current->set_content($restore->get_content());
/*
		TODO other specific properties ? some / all of
		content_filename
		description
		designs
		media_types
		media_query
		name
*/
		$current->Save();
		$msg = $this->Lang('restored_stylesheet', [$current->get_name(), $params['revision_number']]);
		break;
	case UnDoer::TYPE_TEMPLATE:
		if (!$this->CheckPermission('Modify Templates')) exit;
		$current = CmsLayoutTemplate::load($params['item_id']); // gets cached content, if any TODO forced ?
		//TODO simply migrate all relevant aspects of $restore via clone() etc sans datetimes ?
		$current->set_content($restore->get_content());
/*
		TODO other specific properties ? some / all of
		additional_editors
		category
		category_id
		description
		designs
		listable
		name
		owner_id
		type_dflt
		type_id
*/
		$current->Save();
		$msg = $this->Lang('restored_template', [$current->get_name(), $params['revision_number']]);
		break;
}

if ($current === false) {
	Utils::DisplayErrorPage($this->Lang('error_invalid_info'));
	return;
}
audit('', $this->Lang('friendlyname'), $msg);

$this->SetMessage($msg);
//$params['message'] = $msg;
$this->Redirect($id, 'defaultadmin', $returnid, $this->FilterParms($params));
