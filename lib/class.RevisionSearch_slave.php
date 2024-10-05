<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/
namespace UnDoer;

//use AdminSearch\Slave as AdminSearch_slave;
//use CMSMS\Utils as cms_utils;
//use CMSMS\App as CmsApp;
//use UnDoer\Utils; //not really needed
use AdminSearch_slave;
use cms_utils;
use CmsApp;
use UnDoer; // module object in global space
use const CMS_DB_PREFIX;
use function check_permission;
use function get_userid;

final class RevisionSearch_slave extends AdminSearch_slave
{
    public function get_name()
    {
        $mod = cms_utils::get_module('UnDoer');
        return $mod->Lang('lbl_revision_search');
    }

    public function get_description()
    {
        $mod = cms_utils::get_module('UnDoer');
        return $mod->Lang('desc_revision_search');
    }

    public function check_permission(/*int */$userid = 0)
    {
        if ($userid == 0) {
            $userid = get_userid();
        }
        return check_permission($userid, 'Manage Restores');
    }

    public function get_matches()
    {
        $mod = cms_utils::get_module('UnDoer');
        if (!is_object($mod)) { return []; } // should never happen
//      $userid = get_userid();
        //TODO
        //if $this->search_descriptions()
        //if $this->search_casesensitive()
        //if $this->show_snippets()
        //if $this->include_inactive_items()
        //protected function generate_snippets($content){}
        $all = $this->include_inactive_items();
        $output = [];
        $sql = 'SELECT id,item_id,item_type,item_subtype,item_name,revision_number,archive_content FROM '.
            CMS_DB_PREFIX.'module_undoer ORDER BY item_name,revision_number';
        $db = CmsApp::get_instance()->GetDb();
        $dbr = $db->GetArray($sql);
        if ($dbr && is_array($dbr)) {
            $needle = $this->get_text();
            foreach ($dbr as $row) {
                $restore = Utils::retrieveSerializedObject($row['item_id'], $row['revision_number']); //unpack $row['archive_content']
                switch ($row['item_type']) {
                    case UnDoer::TYPE_CONTENT:
                        $props = [];//get relevant $restore properties
                        break;
                    case UnDoer::TYPE_STYLESHEET:
                        $props = [];
                        break;
                    case UnDoer::TYPE_TEMPLATE:
                        $props = [];
                        break;
                    default:
                        break 2;
                }
                foreach ($props as $name => $value) {
                    if ($this->check_match($value, $needle)) {
                        $res = $this->get_match_info($value, $mod);
                        $output[] = json_encode($res);
                    }
                }
            }
        }
        return $output;
    }

    private function check_match($propval, $needle)
    {
        //TODO where is fuzzy matching done?
        static $findfunc = null;
        if ($findfunc === null) {
            $findfunc = $this->search_casesensitive() ? 'strpos' : 'stripos'; // too bad if UTF8 char(s) in there with stripos!
        }
        return ($findfunc($propval, $needle) !== false);
    }

    private function get_match_info($propval, $id, $mod)
    {
        //TODO action url paths like &__c=1aa7a9f08c429ed1a94&m1_rev_id=38&m1_start=0&m1_type_filter=-1&m1_type_id=4&m1_sort_order=name&m1_item_id=10&m1_preview=1
        $resultSet = $this->get_resultset($propval, '',
            $mod->create_url('m1_', 'preview', '', ['gid'=>$id]));
        $from = $propval;
        $num = $this->get_number_of_occurrences($from);
        if ($num > 0) {
            $resultSet->count += $num;
            if ($this->show_snippets()) {
                $resultSet->locations[lang('name')] = $this->generate_snippets($from);
            }
        }
        return $resultSet;
    }
} // class
