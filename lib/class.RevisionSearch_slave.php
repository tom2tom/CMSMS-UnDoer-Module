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
        $output = [];
        $sql = 'SELECT id,item_id,item_type,item_subtype,item_name,revision_number,archive_content FROM '.
            CMS_DB_PREFIX.'module_undoer ORDER BY item_name,revision_number';
        $db = CmsApp::get_instance()->GetDb();
        $dbr = $db->GetArray($sql);
        if ($dbr && is_array($dbr)) {
            $needle = $this->get_text(); //TODO if want fuzzy match
            $desc = $this->search_descriptions();
            foreach ($dbr as $row) {
                switch ($row['item_type']) {
                    case UnDoer::TYPE_CONTENT:
                        $restore = Utils::retrieveSerializedObject($row['item_id'], $row['revision_number']); // unpack $row['archive_content'] with type-class pre-processing
                        if (!$restore) { break 2; /*TODO handle error*/}
//irrelevant here       $all = $this->include_inactive_items();
                         //TODO other content-like extras e.g. Sidebar ?
                        $props = [
                         'name' => $restore->Name(),
                         'menu' => $restore->MenuText(),
                         'alias' => $restore->Alias(),
                         'title' => '',
                         'content' => $restore->GetPropertyValue('content_en')
                        ];
                        if ($desc) { $props['title'] = $restore->TitleAttribute(); } else { unset($props['title']); }
                        break;
                    case UnDoer::TYPE_STYLESHEET:
                        $restore = unserialize($row['archive_content']); //['allowed_classes' => true OR whatever]
                        if (!$restore) { break 2; /*TODO handle error*/}
                        $props = [
                         'name' => $restore->get_name(),
                         'description' => '',
                         'content' => $restore->get_content()
                        ];
                        if ($desc) { $props['description'] = $restore->get_description(); } else { unset($props['description']); }
                        break;
                    case UnDoer::TYPE_TEMPLATE:
                        $restore = unserialize($row['archive_content']); //['allowed_classes' => true OR whatever]
                        if (!$restore) { break 2; /*TODO handle error*/}
                        $props = [
                         'name' => $restore->get_name(),
                         'description' => '',
                         'content' => $restore->get_content()
                        ];
                        if ($desc) { $props['description'] = $restore->get_description(); } else { unset($props['description']); }
                        break;
                    default:
                        break 2;
                }
                foreach ($props as $name => $value) {
                    if ($this->check_match($value, $needle)) {
                        $label = lang($name);
                        $res = $this->get_match_info($value, $row, $label, $mod);
                        $output[] = json_encode($res);
                    }
                }
            }
        }
        return $output;
    }

    private function check_match($propval, $needle)
    {
        //TODO confirm fuzzy matching handled in ancestor class (for CMSMS3)
        static $findfunc = null;
        if ($findfunc === null) {
            $findfunc = $this->search_casesensitive() ? 'strpos' : 'stripos'; // too bad if UTF8 char(s) in there with stripos!
        }
        return ($findfunc($propval, $needle) !== false);
    }

    private function get_match_info($propval, $row, $label, $mod)
    {
        $resultSet = $this->get_resultset($row['item_name'].' @ '.$row['revision_number'], '',
            $mod->create_url('m1_', 'preview', '', [
            'rev_id' => $row['id'],
            'type_id' => $row['item_type'],
            'item_id' => $row['item_id'],
            'start' => 0, //dummy value
            'sort_order' => '',
            'type_filter' => -1
        ]));
        $num = $this->get_number_of_occurrences($propval);
        if ($num > 0) {
            $resultSet->count += $num;
            if ($this->show_snippets()) {
                $resultSet->locations[$label] = $this->generate_snippets($propval);
            }
        }
        return $resultSet;
    }
} // class
