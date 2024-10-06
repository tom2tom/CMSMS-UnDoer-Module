<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/

use UnDoer\Utils;

if (empty($this) || !($this instanceof UnDoer)) exit;
if (!$this->VisibleToAdminUser()) exit;

$query = 'SELECT item_name,item_subtype,revision_number,archive_content FROM '.CMS_DB_PREFIX.'module_undoer WHERE id=?';
$row = $db->GetRow($query, [(int)$params['rev_id']]);
if (!$row) {
    //TODO handle error
    $X = $CRASH;
}

switch ($params['type_id']) {
    case UnDoer::TYPE_CONTENT:
        $restore = Utils::retrieveSerializedObject($params['item_id'], $row['revision_number']);
        if (!$restore) {
            $X = $CRASH;
            Utils::DisplayErrorPage($this->Lang('error_X'));
            return;
        }
        $tpl = $smarty->CreateTemplate($this->GetTemplateResource('preview.tpl'), null, null, $smarty);
        $tpl->assign('formstart', $this->CreateFormStart($id, 'defaultadmin', $returnid, 'post', '', false, '', ['start' => $params['start'], 'sort_order' => $params['sort_order']]));
        $tpl->assign('title', $this->Lang('title_preview2', $row['revision_number'], $this->Lang('content'), $row['item_name']));
        if (0) { $tpl->assign('message', 'TODO'); }

        $_SESSION['__cms_preview__'] = serialize($restore);
        $_SESSION['__cms_preview_type__'] = $row['item_subtype'];
        $tpl->assign('preview_url', $config['root_url'] .'/index.php?'.$config['query_var'].'='.__CMS_PREVIEW_PAGE__);

        $tpl->assign('bottomnav', true); //e.g. func($restore)
        $tpl->display();
        break;
    case UnDoer::TYPE_STYLESHEET:
    case UnDoer::TYPE_TEMPLATE:
        $restore = unserialize($row['archive_content'], ['allowed_classes' => true]); //TODO type-specific class e.g. CmsLayoutTemplate
        if (!$restore) {
            //TODO handle error
            $X = $CRASH;
            Utils::DisplayErrorPage($this->Lang('error_X'));
            return;
        }
        $tpl = $smarty->CreateTemplate($this->GetTemplateResource('dump.tpl'), null, null, $smarty);
        $tpl->assign('formstart', $this->CreateFormStart($id, 'defaultadmin', $returnid, 'post', '', false, '', ['start' => $params['start'], 'sort_order' => $params['sort_order']]));
        $type = ($params['type_id'] == UnDoer::TYPE_STYLESHEET) ? $this->Lang('stylesheet') : $this->Lang('template');
        $tpl->assign('title', $this->Lang('title_preview2', $row['revision_number'], $type, $row['item_name']));
        if (0) { $tpl->assign('message', 'TODO'); }

        //TODO other sanitization for risky content ?
        $clean = str_replace(['&', '{', '}', '<', "\t", "\n"], ['&amp;', '&#123;', '&#125;', '&lt;', '&ensp;', '<br>'], $restore->get_content());
        $tpl->assign('content', $clean);

        $tpl->assign('bottomnav', true); //e.g. func(count of rows in $clean)
        $tpl->display();
        break;
    default:
       //TODO handle error
       $X = $CRASH;
       Utils::DisplayErrorPage($this->Lang('error_X'));
       break;
}
