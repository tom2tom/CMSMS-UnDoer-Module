<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/
if (empty($this) || !($this instanceof UnDoer)) exit;
if (!$this->VisibleToAdminUser()) exit;
//if (!$this->CheckPermission('TODO')) exit;
//if (!$this->CheckAccess()) exit;

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('defaultadmin.tpl'), null, null, $smarty);
$theme = cms_utils::get_theme_object();

$entryarray = [];
$total = 0;
$step = 25;
if (empty($params['start'])) {
    $params['start'] = 0;
}
if (!isset($params['item_id'])) {
    $params['item_id'] = -1;
}
if (!isset($params['preview'])) {
    $params['preview'] = false;
    $tpl->assign('preview', 0);
} else {
    $params['preview'] = true;
    $tpl->assign('preview', 1);
}
if (!isset($params['sort_order'])) {
    if ($params['item_id'] == -1) {
        $params['sort_order'] = 'name';
    } else {
        $params['sort_order'] = 'date';
    }
}

if (!isset($params['sort_dir'])) {
    $params['sort_dir'] = 'ASC';
}
$query = 'SELECT COUNT(*) AS total FROM '.CMS_DB_PREFIX.'module_undoer';
$restriction = [];
$whereClause = '';
if (isset($params['type_filter']) && $params['type_filter'] != -1) {
    if ($whereClause == '') {
        $whereClause = ' WHERE';
    }
    $query .= ' item_type=?';
    $restriction[] = $params['type_filter'];
} else {
    $params['type_filter'] = -1;
}
if ($params['item_id'] != -1) {
    if ($whereClause == '') {
        $whereClause = ' WHERE';
    }
    $query .= ' item_id=?';
    $restriction[] = $params['item_id'];
}
$query .= $whereClause;
$dbresult = $db->GetOne($query, $restriction);
if ($dbresult !== false) {
    $total = $dbresult;
} else {
    $total = 0;
}

$query = 'SELECT id, item_name, item_id, item_type, revision_number, archive_date FROM '.
    CMS_DB_PREFIX.'module_undoer';
$restriction = [];
$whereclause = false;
if ($params['type_filter'] != -1) {
    if (!$whereclause) {
        $query .= ' WHERE';
        $whereclause = true;
    } else {
        $query .= ' AND';
    }
    $query .= ' item_type=?';
    $restriction[] = $params['type_filter'];
}
if ($params['item_id'] != -1) {
    if (!$whereclause) {
        $query .= ' WHERE';
        $whereclause = true;
    } else {
        $query .= ' AND';
    }
    // showing a specific item history
    $query .= ' item_id=?';
    $restriction[] = $params['item_id'];
    $tpl->assign('hide_filters', 1);
} else {
    $tpl->assign('hide_filters', 0);
}

$dateIcon = '<img src="'.$this->GetModuleURLPath().'/images/sort_asc.png" class="systemicon" alt="'.
    $this->Lang('sort_asc') . '" title="'. $this->Lang('sort_asc') . '">';
$nameIcon = $dateIcon;
$revIcon = $dateIcon;
$typeIcon = $dateIcon;
$dateSortDir = 'ASC';
$typeSortDir = 'ASC';
$nameSortDir = 'ASC';
$revSortDir = 'ASC';
$tpl->assign('name_sort', '');
$tpl->assign('type_sort', '');
$tpl->assign('date_sort', '');
$tpl->assign('rev_sort', '');

switch ($params['sort_order']) {
    case 'date':
        $tpl->assign('date_sort', ' class="active"');
        $query .= ' ORDER BY archive_date '.$params['sort_dir'].', item_type, item_name';
        if ($params['sort_dir'] == 'DESC') {
            $dateIcon = '<img src="'.$this->GetModuleURLPath().
                '/images/sort_desc.png" class="systemicon" alt="'. $this->Lang('sort_desc') .
                '" title="'.$this->Lang('sort_desc').'">';
            $dateSortDir = 'ASC';
        } else {
            $dateSortDir = 'DESC';
        }
        break;
    case 'name':
        $tpl->assign('name_sort', ' class="active"');
        $query .= ' ORDER by LOWER(item_name) '. $params['sort_dir'].', item_type, archive_date'; //TODO caseless UTF8 sorting
        if ($params['sort_dir'] == 'DESC') {
            $nameIcon = '<img src="'. $this->GetModuleURLPath().
                '/images/sort_desc.png" class="systemicon" alt="'.$this->Lang('sort_desc') .
                '" title="'.$this->Lang('sort_desc').'">';
            $nameSortDir = 'ASC';
        } else {
            $nameSortDir = 'DESC';
        }
        break;
    case 'revision':
        $tpl->assign('rev_sort', ' class="active"');
        $query .= ' ORDER BY revision_number '.$params['sort_dir'].', LOWER(item_name), item_type'; //TODO caseless UTF8 sorting
        if ($params['sort_dir'] == 'DESC') {
            $revIcon = '<img src="'.$this->GetModuleURLPath().
                '/images/sort_desc.png" class="systemicon" alt="'.
                $this->Lang('sort_desc').'" title="'.$this->Lang('sort_desc').'">';
            $revSortDir = 'ASC';
        } else {
            $revSortDir = 'DESC';
        }
        break;
    case 'type':
        $tpl->assign('type_sort', ' class="active"');
        $query .= ' ORDER BY item_type '.$params['sort_dir'].', LOWER(item_name), archive_date'; //TODO caseless UTF8 sorting
        if ($params['sort_dir'] == 'DESC') {
            $typeIcon = '<img src="'.$this->GetModuleURLPath().
                '/images/sort_desc.png" class="systemicon" alt="'.
                $this->Lang('sort_desc').'" title="'.$this->Lang('sort_desc').'">';
            $typeSortDir = 'ASC';
        } else {
            $typeSortDir = 'DESC';
        }
}

$rs = $db->SelectLimit($query, $step, $params['start'], $restriction);
if ($rs) {
    $rowclass = 'row1';
    $dateFmt = $this->GetPreference('date_format', 'l, j F Y H:i');

    while ($row = $rs->FetchRow()) {
        $onerow = new stdClass();

        $onerow->id = $row['id'];
        $onerow->item_id = $row['item_id'];
        $onerow->name = $row['item_name'];

        $onerow->type = $this->TYPE_NAMES[$row['item_type']];
        $onerow->rowclass = $rowclass;
        $onerow->revision = $row['revision_number'];
        $onerow->date = date($dateFmt, $db->UnixTimeStamp($row['archive_date']));

        $onerow->restlink = $this->CreateLink($id, 'restore', $returnid,
            '<img src="'.
            $this->GetModuleURLPath().'/images/restore.png" class="systemicon" alt="'.
            $this->Lang('restore') . '" title="'. $this->Lang('restore') . '">',
            ['id' => $row['id'], 'start' => $params['start'],
            'type_filter' => $params['type_filter'], 'type_id' => $row['item_type'], 'sort_order' => $params['sort_order'],
            'item_id' => $row['item_id'], 'revision_number' => $row['revision_number']],
            $this->Lang('surerestore', $row['revision_number'])
        );
        if ($row['item_type'] == UnDoer::TYPE_CONTENT) {
            $onerow->viewlink = $this->CreateLink($id, 'preview', $returnid,
                $theme->DisplayImage(
                    'icons/system/view.gif',
                    $this->Lang('preview'),
                    '',
                    '',
                    'systemicon'
                ),
                ['id' => $row['id'], 'start' => $params['start'], 'type_filter' => $params['type_filter'],
                'type_id' => $row['item_type'], 'sort_order' => $params['sort_order'], 'item_id' => $row['item_id'],
                'preview' => true, 'revision_number' => $row['revision_number']]
            );
        } else {
            $onerow->viewlink = '';
        }
        $entryarray[] = $onerow;

        ($rowclass == 'row1' ? $rowclass = 'row2' : $rowclass = 'row1');
    }
    $rs->close();
}

if ($params['start'] >= $step) {
    $tpl->assign('prev_page', $this->CreateLink($id, 'defaultadmin', $returnid, $this->Lang('prev'), ['start' => ($params['start'] - $step), 'type_filter' => $params['type_filter'], 'sort_order' => $params['sort_order'], 'item_id' => $params['item_id']]));
} else {
    $tpl->assign('prev_page', '');
}
if ($params['start'] + $step < $total) {
    $tpl->assign('next_page', $this->CreateLink($id, 'defaultadmin', $returnid, $this->Lang('next'), ['start' => ($params['start'] + $step), 'type_filter' => $params['type_filter'], 'sort_order' => $params['sort_order'], 'item_id' => $params['item_id']]));
} else {
    $tpl->assign('next_page', '');
}

if (ceil($total / $step) > 1) {
    $curpg = floor($params['start'] / $step) + 1;
    $linkstr = '';
    $first = 1;
    for ($i = 0; $i < $total; $i += $step) {
        $thisPg = floor($i / $step) + 1;
        if ($first) {
            $first = 0;
        } else {
            $linkstr .= ' : ';
        }
        if ($i == $params['start']) {
            $linkstr .= '['.$thisPg.']';
        } else {
            $linkstr .= $this->CreateLink($id, 'defaultadmin', $returnid, $thisPg, ['start' => $i, 'type_filter' => $params['type_filter'], 'sort_order' => $params['sort_order'], 'item_id' => $params['item_id']]);
        }
    }
    $tpl->assign('page_no', 'Page '.$curpg.' of '.ceil($total / $step)); //TODO langify
    $tpl->assign('page_links', $linkstr);
} else {
    $tpl->assign('page_no', '');
}
$tpl->assign('column_name', $this->CreateLink($id, 'defaultadmin', $returnid, $nameIcon.$this->Lang('column_name'), ['sort_order' => 'name', 'start' => $params['start'], 'type_filter' => $params['type_filter'], 'item_id' => $params['item_id'], 'sort_dir' => $nameSortDir]));
$tpl->assign('column_type', $this->CreateLink($id, 'defaultadmin', $returnid, $typeIcon.$this->Lang('column_type'), ['sort_order' => 'type', 'start' => $params['start'], 'type_filter' => $params['type_filter'], 'item_id' => $params['item_id'], 'sort_dir' => $typeSortDir]));
$tpl->assign('column_revision', $this->CreateLink($id, 'defaultadmin', $returnid, $revIcon.$this->Lang('column_revision'), ['sort_order' => 'revision', 'start' => $params['start'], 'type_filter' => $params['type_filter'], 'item_id' => $params['item_id'], 'sort_dir' => $revSortDir]));
$tpl->assign('column_date', $this->CreateLink($id, 'defaultadmin', $returnid, $dateIcon.$this->Lang('column_date'), ['sort_order' => 'date', 'start' => $params['start'], 'type_filter' => $params['type_filter'], 'item_id' => $params['item_id'], 'sort_dir' => $dateSortDir]));
$tpl->assign('column_action', $this->Lang('column_action'));

$tpl->assign('title_filter_type', $this->Lang('title_filter_type'));
$filters = [
    $this->Lang('title_filter_none') => -1,
    $this->Lang('title_filter_content') => UnDoer::TYPE_CONTENT,
//  $this->Lang('title_filter_htmlblob') => UnDoer::TYPE_HTMLBLOB,
    $this->Lang('title_filter_stylesheet') => UnDoer::TYPE_STYLESHEET,
    $this->Lang('title_filter_template') => UnDoer::TYPE_TEMPLATE
];
$tpl->assign('input_filter_type',
    $this->CreateInputDropdown(
        $id,
        'type_filter',
        $filters,
        -1,
        $params['type_filter'],
        "onchange='this.form.submit()';"
    )
);
$tpl->assign('startform',
    $this->CreateFormStart($id, 'defaultadmin', $returnid, 'post', '', false, '', ['start' => $params['start'], 'sort_order' => $params['sort_order'], 'item_id' => $params['item_id']])
);
$tpl->assign('endform', $this->CreateFormEnd());
$tpl->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('filter'), 'data-ui-icon="ui-icon-gear"'));

$tpl->assign('items', $entryarray);
$tpl->assign('itemcount', count($entryarray));

$message = (!empty($params['message'])) ? $params['message'] : '';
$tpl->assign('message', $message);
$nav = $this->CreateLink($id, 'settings', $returnid, $theme->DisplayImage(
    'icons/topfiles/siteprefs.gif',
    $this->Lang('adminprefs'),
    '',
    '',
    'navicon'
), []) .
$this->CreateLink($id, 'settings', $returnid, $this->Lang('adminprefs'), [], '', false, false, 'class="pageoptions"');

$smarty->assign('admin_nav', $nav);

$tpl->display();
