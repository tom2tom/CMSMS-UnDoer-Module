<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/
namespace UnDoer;

/*use CMSMS\Utils as cms_utils;
use CMSMS\Stylesheet as CmsLayoutStylesheet;
use CMSMS\Template as CmsLayoutTemplate;
*/
use cms_utils;
//use CmsLayoutStylesheet;
//use CmsLayoutTemplate;
use UnDoer; //module class in global space
use const CMS_DB_PREFIX;
use function cmsms;
use function lang;

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
        $flat = serialize($object);
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
        if ($pc != -1) {
            // purge old archives by count (see also action.save_settings where no $objectID, $objectType)
            $query = 'SELECT id FROM (
SELECT id,item_id,revision_number,
  ROW_NUMBER() OVER (PARTITION BY item_id ORDER BY revision_number DESC) AS row_num
FROM '.CMS_DB_PREFIX.'module_undoer
WHERE item_id=? AND item_type=?
) ranked
WHERE row_num > ?';
            $purge_ids = $db->getCol($query, [$objectId, $objectType, $pc]);
            if ($purge_ids) {
                //TODO consider merging both results then one deletion
                $query = 'DELETE FROM '.CMS_DB_PREFIX.
                    'module_undoer WHERE id IN('.implode(',', $purge_ids).')';
                $dbr = $db->execute($query);
            }
        }
        if ($pt != -1) {
            // purge old archives by time
            $cutoff = mktime(0, 0, 0, date('m'), date('d') - $pt, date('Y'));
            $query = 'SELECT id FROM '.CMS_DB_PREFIX.
                'module_undoer WHERE item_id=? AND item_type=? AND archive_date<'.
                $db->DBTimeStamp($cutoff);
            $purge_ids = $db->getCol($query, [$objectID, $objectType]);
            if ($purge_ids) {
                $query = 'DELETE FROM '.CMS_DB_PREFIX.
                    'module_undoer WHERE id IN('.implode(',', $purge_ids).')';
                $dbr = $db->execute($query);
            }
        }
    }

    public static function retrieveSerializedObject($item_id, $revision)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $query = 'SELECT item_type,item_subtype,archive_content FROM '.CMS_DB_PREFIX.
            'module_undoer WHERE item_id=? AND revision_number=?';
        $row = $db->GetRow($query, [$item_id, $revision]);
        if ($row) {
            if ($row['item_type'] == UnDoer::TYPE_CONTENT) {
                $ops = $gCms->GetContentOperations();
                $ops->LoadContentType($row['item_subtype']);
//            } elseif ($row['item_type'] == UnDoer::TYPE_STYLESHEET) {
                //CmsLayoutStylesheet::load($id);//TODO process $row
//            } elseif ($row['item_type'] == UnDoer::TYPE_TEMPLATE) {
                //CmsLayoutTemlate::load($id);//TODO process $row
            }
            $restore = unserialize($row['archive_content'], ['allowed_classes' => true]); //TODO type-specific class e.g. ContentManager\contenttypes\Content
            return $restore;
        }
        return null;
    }

    /**
     * Get a regex derived from the supplied $needle, and which is
     * suitable for fuzzy matching
     * Adapted from https://codereview.stackexchange.com/questions/23899/faster-javascript-fuzzy-string-matching-function
     *
     * @param string $needle the raw search string
     * @param bool $casesensitive optional flag whether to setup for case-sensitive matching. Default true
     * @return string regular expression
     */
    public static function get_regex_pattern($needle, $casesensitive = true)//: string
    {
        $reserved = '/\\^-]'; // intra-class reserves
        $reserved2 = '/\\.,+-*?^$[](){}'; // extra-class reserves
        //UTF-8 has single bytes (0-127), leading bytes (192-254) and continuation bytes (128-191)
        if (preg_match('/[\xc0-\xfe][\x80-\xbf]+/', $needle)) {
            $chars = preg_split('//u', $needle, null, PREG_SPLIT_NO_EMPTY);
            $tail = '/u';
        } else {
            $chars = str_split($needle);
            $tail = '/';
        }
        if (!$casesensitive) {
            $tail .= 'i';
        }
        $c = $chars[0];
        if (strpos($reserved2, $c) !== false) {
            $c = "\\$c";
        }
        $t = "/$c";
        unset($chars[0]);
        $patn = array_reduce($chars, function($m, $c) use ($reserved, $reserved2) {
            $a = strpos($reserved, $c) !== false;
            $b = strpos($reserved2, $c) !== false;
            if ($a && $b) {
                return $m . "[^\\$c]{0,3}\\$c";
            } elseif ($a) {
                return $m . "[^\\$c]{0,3}$c";
            } elseif ($b) {
                return $m . "[^$c]{0,3}\\$c";
            } else {
                return $m . "[^$c]{0,3}$c";
            }
        }, $t);
        $patn .= $tail;
        return $patn;
    }

    public static function DisplayErrorPage($message = '')
    {
        $mod = cms_utils::get_module('UnDoer');
        if (!$message) {
            $message = $mod->Lang('error_unspecified');
        }
        $smarty = cmsms()->GetSmarty();
        $tpl = $smarty->CreateTemplate($mod->GetTemplateResource('error.tpl'), null, null, $smarty);
        $tpl->assign('title_error', lang('error'));
        $tpl->assign('message', $message);
        $tpl->display();
    }
}
