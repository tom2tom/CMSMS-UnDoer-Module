<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/
// periodic cleanout of time-expired archive items
namespace UnDoer;

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
        $lifedays = (int)$mod->GetPreference('purge_time', -1);
        if ($lifedays == -1) {
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
        $mod = cms_utils::get_module('UnDoer');
        $lifedays = (int)$mod->GetPreference('purge_time', -1);
        if ($lifedays < 0) {
            return false;
        }
        $cutoff = time() - $lifedays * 86400;
        $purge_ids = [];
        $db = CmsApp::get_instance()->GetDb();
        // purge old
        $query = 'SELECT id FROM '.CMS_DB_PREFIX.
            'module_undoer WHERE archive_date<'.$db->DBTimeStamp($cutoff);
        $rs = $db->Execute($query);
        if ($rs) {
            while ($row = $rs->FetchRow()) {
                $purge_ids[] = $row['id'];
            }
            $rs->Close();
        }
        if ($purge_ids) {
            $filler = str_repeat('?,', count($purge_ids) - 1);
            $delquery = 'DELETE FROM '.CMS_DB_PREFIX.'module_undoer WHERE id IN('.$filler.'?)';
            $dbr = $db->Execute($delquery, $purge_ids);
            return $dbr != false;
        }
        return true;
    }
}
