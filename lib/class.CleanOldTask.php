<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/
// periodic cleanout of time-expired and/or excessive-count archive items
namespace UnDoer;

/*use CMSMS\Utils as cms_utils;
use CMSMS\App as CmsApp;
use CMSMS\TODO as CmsRegularTask;
*/
use cms_utils;
use CmsApp;
use CmsRegularTask;
use const CMS_DB_PREFIX;

class CleanOldTask implements CmsRegularTask
{
    public function get_name()
    {
        return basename(get_class($this));
    }

    public function get_description()
    {
        return $this->get_name();
    }

    public function test($time = '')
    {
        if (!$time) {
            $time = time();
        }
        $mod = cms_utils::get_module('UnDoer');
        $opt = (int)$mod->GetPreference('purge_time', -1);
        $opt2 = (int)$mod->GetPreference('purge_count', -1);
        if ($opt == -1 && $opt2 == -1) {
            return false;
        }
        $lastrun = (int) $mod->GetPreference('task1_lastrun');
        if ($lastrun >= ($time - 86400)) { // hardcoded to 24 hrs
            return false;
        }
        return true;
    }

    public function on_success($time = '')
    {
        if (!$time) {
            $time = time();
        }
        $mod = cms_utils::get_module('UnDoer');
        $mod->SetPreference('task1_lastrun', $time);
    }

    public function on_failure($time = '')
    {
    }

    public function execute($time = '')
    {
        $res = true;
        $mod = cms_utils::get_module('UnDoer');
        $lifedays = (int)$mod->GetPreference('purge_time', -1);
        if ($lifedays > -1) {
            $cutoff = time() - $lifedays * 86400;
            $db = CmsApp::get_instance()->GetDb();
            // purge old
            $query = 'SELECT id FROM '.CMS_DB_PREFIX.
                'module_undoer WHERE archive_date<'.$db->DBTimeStamp($cutoff);
            $purge_ids = $db->getCol($query);
            if ($purge_ids) {
                $filler = str_repeat('?,', count($purge_ids) - 1);
                $delquery = 'DELETE FROM '.CMS_DB_PREFIX.'module_undoer WHERE id IN('.$filler.'?)';
                $dbr = $db->execute($delquery, $purge_ids);
                $res = $dbr != false;
            }
        }
        $keepcount = (int)$mod->GetPreference('purge_count', -1);
        if ($keepcount > -1) {
            if (!isset($db)) { $db = CmsApp::get_instance()->GetDb(); }
            // purge excess
            $query = 'SELECT id FROM (
SELECT id,item_id,revision_number,
  ROW_NUMBER() OVER (PARTITION BY item_id ORDER BY revision_number DESC) AS row_num
FROM '.CMS_DB_PREFIX.'module_undoer
) ranked
WHERE row_num > ?';
            $purge_ids = $db->getCol($query, [$keepcount]);
            if ($purge_ids) {
                $filler = str_repeat('?,', count($purge_ids) - 1);
                $delquery = 'DELETE FROM '.CMS_DB_PREFIX.'module_undoer WHERE id IN('.$filler.'?)';
                $dbr = $db->execute($delquery, $purge_ids);
                $res = $dbr != false && $res;
            }
        }
        return $res;
    }
}
