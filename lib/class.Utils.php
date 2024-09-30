<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/
namespace UnDoer;

use cms_utils;
//use CmsLayoutStylesheet;
use CmsLayoutTemplate;
use UnDoer; //module class in global space
use const CMS_DB_PREFIX;
use const TMP_CACHE_LOCATION;
use function cmsms;

class Utils
{
    public static function ArchiveObject($object, $objectType, $onlyIfNew = false)
    {
        switch ($objectType) {
            case UnDoer::TYPE_CONTENT:
                $objectId = $object->Id();
                break;
            case UnDoer::TYPE_STYLESHEET:
            case UnDoer::TYPE_TEMPLATE:
               $objectId = $object->get_id();
                break;
            default:
                $objectId = -1;
        }
        if ($objectId == -1) {
            return;
        }
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $changed = false;
        $revision_number = 1;
        switch ($objectType) {
            case UnDoer::TYPE_CONTENT:
                $objectName = $object->Name();
                break;
            case UnDoer::TYPE_STYLESHEET:
            case UnDoer::TYPE_TEMPLATE:
                $objectName = $object->get_name();
        }
        $flat = serialize($object); // ancestor modules/versions stored a base64 encoded md5 hashed format
        $hash = sha1($flat);
        $query = 'SELECT item_hash, revision_number FROM '.CMS_DB_PREFIX.
            'module_undoer WHERE item_id=? AND item_type=? ORDER BY archive_date DESC';
        $rs = $db->SelectLimit($query, 1, 0, [$objectId, $objectType]);
        if ($rs->RecordCount() == 0) {
            // no record exists yet, so we'll store one
            $changed = true;
        } elseif (!$onlyIfNew) {
            $result = $rs->FetchRow();
            if ($result['item_hash'] != $hash) {
                $changed = true;
                $revision_number = $result['revision_number'] + 1;
            }
        }
        $rs->Close();
        if ($changed) {
            $subtype = '';
            if ($objectType == UnDoer::TYPE_CONTENT) {
                $subtype = $object->Type();
            }
            $query = 'INSERT INTO '.CMS_DB_PREFIX.'module_undoer
(item_id,item_hash,item_type,item_subtype,item_name,revision_number,archive_date,archive_content)
VALUES (?,?,?,?,?,?,?,?)';
            $now = trim($db->DBTimeStamp(time()), ' \'"');
            $dbr = $db->Execute($query, [$objectId, $hash, $objectType, $subtype, $objectName, $revision_number, $now, $flat]);
        }
        self::PurgeArchive($objectId, $objectType);
    }

    public static function PurgeArchive($objectID, $objectType)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $mod = cms_utils::get_module('UnDoer');
        $pc = $mod->GetPreference('purge_count', -1);
        $pt = $mod->GetPreference('purge_time', -1);
        $purge_ids = [];
        if ($pc != -1) {
            // purge old archives by count
            $query = 'SELECT MAX(revision_number) AS toprev FROM '.CMS_DB_PREFIX.
                'module_undoer WHERE item_id=? AND item_type=?';
            $dbr = $db->GetOne($query, [$objectID, $objectType]);
            if ($dbr !== false) {
                $revs_to_purge = $dbresult - $pc + 1;
                $purgequery = 'SELECT id FROM '.CMS_DB_PREFIX.
                    'module_undoer WHERE revision_number < ? AND item_id=? AND item_type=?';
                $subresult = $db->Execute($purgequery, [$revs_to_purge, $objectID, $objectType]);
                while ($subresult !== false && $row = $subresult->FetchRow()) {
                    // weird push, because we're using Connection's bulk execute TODO
                    $purge_ids[] = [$row['id']]; //TODO
                }
            }
        }
        if ($pt != -1) {
            // purge old archives by time
            $cutoff = mktime(0, 0, 0, date('m'), date('d') - $pt, date('Y'));
            $query = 'SELECT id FROM '.CMS_DB_PREFIX.
                'module_undoer WHERE item_id=? AND item_type=? AND archive_date<'.
                $db->DBTimeStamp($cutoff);

            $rs = $db->Execute($query, [$objectID, $objectType]);
            if ($rs) {
                while ($row = $rs->FetchRow()) {
                    $purge_ids[] = [$row['id']]; //TODO
                }
                $rs->Close();
            }
        }
        if ($purge_ids) {
            $delquery = 'DELETE FROM '.CMS_DB_PREFIX.'module_undoer WHERE id=?';
            $dbr = $db->Execute($delquery, $purge_ids);
        }
    }

    public static function retrieveSerializedObject($item_id, $revision)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $query = 'SELECT item_type, item_subtype, archive_content FROM '.CMS_DB_PREFIX.
            'module_undoer WHERE item_id=? AND revision_number=?';
        $row = $db->GetRow($query, [$item_id, $revision]);
        if ($row) {
            if ($row['item_type'] == UnDoer::TYPE_CONTENT) {
                $contentops = $gCms->GetContentOperations();
                $contentops->LoadContentType($row['item_subtype']);
//            } elseif ($row['item_type'] == UnDoer::TYPE_STYLESHEET) {
                //CmsLayoutStylesheet::load($id);//TODO process $row
//            } elseif ($row['item_type'] == UnDoer::TYPE_TEMPLATE) {
                //CmsLayoutTemlate::load($id);//TODO process $row
            }
            $restore = unserialize($row['archive_content']); //TODO type-specific $options
            return $restore;
        }
        return null;
    }

    public static function createtmpfname($contentobj)
    {
        $gCms = cmsms();
        $config = $gCms->GetConfig();
//      $templateops = $gCms->GetTemplateOperations();class N/A
//      $stylesheetops = $gCms->GetStyleSheetOperations();class N/A
        $data = [];
        $data['content_id'] = $contentobj->Id();
        $data['content_type'] = $contentobj->Type();
        $data['title'] = $contentobj->Name();
        $data['menutext'] = $contentobj->MenuText();
        $data['content'] = $contentobj->Show();
        $data['template_id'] = $contentobj->TemplateId();
        $data['hierarchy'] = $contentobj->Hierarchy();

//      $templateobj = $templateops->LoadTemplateById($contentobj->TemplateId());
        $templateobj = CmsLayoutTemplate::load($contentobj->TemplateId());
        $data['template'] = $templateobj->get_content();

        //TODO CMSMS 2.2 forces use of Designs and from there, stylesheet(s) ?
        //$stylesheetobj = get_stylesheet($contentobj->TemplateId());
//      $stylesheetobj = $stylesheetops->LoadStylesheetByID($contentobj->TemplateId());
//      $stylesheetobj = CmsLayoutStylesheet::load(TODO);
//      $data['encoding'] = $stylesheetobj['encoding']; N/A
        $data['serialized_content'] = serialize($contentobj); //$data is serialised, so WHY twice?

        if (is_writable($config['previews_path'])) {
            $tmpfname = tempnam($config['previews_path'], 'cmspreview');
        } else {
            $tmpfname = tempnam(TMP_CACHE_LOCATION, 'cmspreview');
        }
        $handle = fopen($tmpfname, 'wb');
        fwrite($handle, serialize($data));
        fclose($handle);

        return $tmpfname;
    }

    public static function DisplayErrorPage($message = '')
    {
        $mod = cms_utils::get_module('UnDoer');
        $smarty = cmsms()->GetSmarty();
        $tpl = $smarty->CreateTemplate($mod->GetTemplateResource('error.tpl'), null, null, $smarty);
        $tpl->assign('title_error', $mod->Lang('error'));
        if ($message) {
            $tpl->assign('message', $message);
        }
        // Display the populated template
        $tpl->display();
    }
}
