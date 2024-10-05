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

$dateIcon = '<img src="'.$this->GetModuleURLPath().'/images/sortable.png" class="systemicon" alt="'.
    $this->Lang('sort_able') . '" title="'. $this->Lang('tip_sort_asc') . '">';
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
                '" title="'.$this->Lang('tip_sort_asc').'">';
            $dateSortDir = 'ASC';
        } else {
            $dateIcon = '<img src="'.$this->GetModuleURLPath().
                '/images/sort_asc.png" class="systemicon" alt="'. $this->Lang('sort_asc') .
                '" title="'.$this->Lang('tip_sort_desc').'">';
            $dateSortDir = 'DESC';
        }
        break;
    case 'name':
        $tpl->assign('name_sort', ' class="active"');
        $query .= ' ORDER by LOWER(item_name) '. $params['sort_dir'].', item_type, archive_date'; //TODO caseless UTF8 sorting
        if ($params['sort_dir'] == 'DESC') {
            $nameIcon = '<img src="'. $this->GetModuleURLPath().
                '/images/sort_desc.png" class="systemicon" alt="'.$this->Lang('sort_desc') .
                '" title="'.$this->Lang('tip_sort_asc').'">';
            $nameSortDir = 'ASC';
        } else {
            $nameIcon = '<img src="'. $this->GetModuleURLPath().
                '/images/sort_asc.png" class="systemicon" alt="'.$this->Lang('sort_asc') .
                '" title="'.$this->Lang('tip_sort_desc').'">';
            $nameSortDir = 'DESC';
        }
        break;
    case 'revision':
        $tpl->assign('rev_sort', ' class="active"');
        $query .= ' ORDER BY revision_number '.$params['sort_dir'].', LOWER(item_name), item_type'; //TODO caseless UTF8 sorting
        if ($params['sort_dir'] == 'DESC') {
            $revIcon = '<img src="'.$this->GetModuleURLPath().
                '/images/sort_desc.png" class="systemicon" alt="'.
                $this->Lang('sort_desc').'" title="'.$this->Lang('tip_sort_asc').'">';
            $revSortDir = 'ASC';
        } else {
            $revIcon = '<img src="'.$this->GetModuleURLPath().
                '/images/sort_asc.png" class="systemicon" alt="'.
                $this->Lang('sort_desc').'" title="'.$this->Lang('tip_sort_desc').'">';
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
            $typeIcon = '<img src="'.$this->GetModuleURLPath().
                '/images/sort_asc.png" class="systemicon" alt="'.
                $this->Lang('sort_asc').'" title="'.$this->Lang('tip_sort_desc').'">';
            $typeSortDir = 'DESC';
        }
}

$pdel = $this->CheckPermission('Delete Restores');

$rs = $db->SelectLimit($query, $step, $params['start'], $restriction);
if ($rs) {
    $rowclass = 'row1';
    $dateFmt = $this->GetPreference('date_format', 'l, j F Y H:i');
    // escaped 'templates' with placeholders for replacement in js
    $prompt = addcslashes($this->Lang('surerestore', '~!~', '|^|'), "'\n\r");
    $prompt2 = addcslashes($this->Lang('suredelete', '|^|', '~!~'), "'\n\r");

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
            ['rev_id' => $row['id'], 'revision_number' => $row['revision_number'], //any param[id] gets ignored TODO
            'start' => $params['start'], 'type_filter' => $params['type_filter'],
            'type_id' => $row['item_type'], 'sort_order' => $params['sort_order'],
            'item_id' => $row['item_id']],
            '', false, false, 'class="reverter" data-name="'.addcslashes($row['item_name'], '"').'" data-rev="'.$row['revision_number'].'"'
        );

        $onerow->viewlink = $this->CreateLink($id, 'preview', $returnid,
            $theme->DisplayImage(
                'icons/system/view.gif',
                $this->Lang('preview'),
                '',
                '',
                'systemicon'
            ),
            ['rev_id' => $row['id'], //'revision_number' => $row['revision_number'], //any param[id] gets ignored TODO
             'start' => $params['start'], 'type_filter' => $params['type_filter'],
             'type_id' => $row['item_type'], 'sort_order' => $params['sort_order'],
             'item_id' => $row['item_id']
            ]
        );

        if ($pdel) {
            $onerow->dellink = $this->CreateLink($id, 'delete', $returnid,
                $theme->DisplayImage(
                    'icons/system/delete.gif',
                    lang('delete'),
                    '',
                    '',
                    'systemicon'
                ),
                ['rev_id' => $row['id'],
                'start' => $params['start'], 'type_filter' => $params['type_filter'], //pass-thru's
                'type_id' => $row['item_type'], 'sort_order' => $params['sort_order']
                ],
                '', false, false, 'class="deleter" data-name="'.addcslashes($row['item_name'], '"').'" data-rev="'.$row['revision_number'].'"'
            );
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
    $tpl->assign('page_no', $this->Lang('pageof', $curpg, ceil($total / $step))); //i.e. 'Page '.$curpg.' of '.ceil($total / $step);
    $tpl->assign('page_links', $linkstr);
} else {
    $tpl->assign('page_no', '');
}
//TODO title attribute for each name element, matching the respective icon title l.e. current.., '', false, false, $addttext='title="TODO"'
$tpl->assign('column_name', $this->CreateLink($id, 'defaultadmin', $returnid, $nameIcon.$this->Lang('column_name'), ['sort_order' => 'name', 'start' => $params['start'], 'type_filter' => $params['type_filter'], 'item_id' => $params['item_id'], 'sort_dir' => $nameSortDir]));
$tpl->assign('column_type', $this->CreateLink($id, 'defaultadmin', $returnid, $typeIcon.$this->Lang('column_type'), ['sort_order' => 'type', 'start' => $params['start'], 'type_filter' => $params['type_filter'], 'item_id' => $params['item_id'], 'sort_dir' => $typeSortDir]));
$tpl->assign('column_revision', $this->CreateLink($id, 'defaultadmin', $returnid, $revIcon.$this->Lang('column_revision'), ['sort_order' => 'revision', 'start' => $params['start'], 'type_filter' => $params['type_filter'], 'item_id' => $params['item_id'], 'sort_dir' => $revSortDir]));
$tpl->assign('column_date', $this->CreateLink($id, 'defaultadmin', $returnid, $dateIcon.$this->Lang('column_date'), ['sort_order' => 'date', 'start' => $params['start'], 'type_filter' => $params['type_filter'], 'item_id' => $params['item_id'], 'sort_dir' => $dateSortDir]));
$tpl->assign('column_action', $this->Lang('column_action'));

$tpl->assign('title_filter_type', $this->Lang('title_filter_type'));
$filters = [
    $this->Lang('title_filter_none') => -1,
    $this->Lang('title_filter_content') => UnDoer::TYPE_CONTENT,
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
        'onchange="this.form.submit();"'
    )
);

// escape strings for js
$s1 = addcslashes($this->Lang('suredelete_multi'), "'\n\r");
$s2 = addcslashes(lang('ok'), "'\n\r");
$s3 = addcslashes(lang('cancel'), "'\n\r");
$s4 = addcslashes($this->Lang('begin'), "'\n\r");
$s5 = addcslashes($this->Lang('error_compare'), "'\n\r");

$js = <<<EOS
<script>
  $(function() {
    var lastClicked = null; // TODO OR -1
    $('#bulksel, #bulksubmit').prop('disabled',true);
    $('#sel_all').on('click',function() {
      var v = $(this).is(':checked');
      $('#bulksel, #bulksubmit').prop('disabled',!v);
    }).cmsms_checkall();
    $('.multiselect').on('click',function(e) {
      var v = this.checked;
      $('#bulksel, #bulksubmit').prop('disabled',!v);
      if (e.shiftKey) {
        if (lastClicked) { //TODO OR index > -1
          var cbs = $('.multiselect').toArray();
          var end = cbs.indexOf(lastClicked);
          if (end !== -1) {
            var start = cbs.indexOf(this);
            var checks = cbs.slice(Math.min(start, end), Math.max(start, end) + 1);
            $(checks).prop('checked',v).trigger('change');
          }
        }
      }
      lastClicked = this; //TODO OR array index of this
    });
    $('#bulk_form').on('submit',function(e) {
      var v = $('#bulksel').val();
      switch (v) {
        case 'delete':
          e.preventDefault();
          cms_confirm('$s1').done(function() {
           $('#bulk_form').off('submit').trigger('submit');
          });
          return false;
/* cms_confirm() doesn't support warning-class for styling the prompt
          $('#delete_confirm').dialog({
           modal: true,
           width: 'auto',
           buttons: [
            {
             tabIndex: 1,
             text: '$s3',
             icon: 'ui-icon-close',
             click: function() {
              $(this).dialog('close');
             }
            },
            {
             tabIndex: 2,
             text: '$s2',
             icon: 'ui-icon-check',
             click: function() {
              $(this).dialog('close');
              $('#bulk_form').off('submit').trigger('submit');
             }
            }
           ]
          });
          return false;
*/
        case 'search':
          e.preventDefault();
          $('#search_details').dialog({
           modal: true,
           width: 'auto',
           buttons: [
            {
             tabIndex: 3,
             text: '$s3',
             icon: 'ui-icon-close',
             click: function() {
               $(this).dialog('close');
             }
            },
            {
             tabIndex: 2,
             text: '$s4',
             icon: 'ui-icon-search',
             click: function() {
              var v = $('#target').val().trim();
              if (v.length > 2) {
               $(this).dialog('close');
               $('#bulk_form').find('input[name="{$id}search_text"]').val(v);
               $('#bulk_form').off('submit').trigger('submit');
              } else {
               var here = 1; //TODO indicate more text needed
              }
             }
            }
           ]
          });
          return false;
        case 'compare':
          var v = 0;
          $('.multiselect').each(function() {
            if (this.checked) {
              if (++v >= 2) {
                return;
              }
            }
          });
          e.preventDefault();
          cms_alert('$s5');
          return false;
        break;
      }
    });
    $('.reverter, .deleter').on('click',function(e) {
      e.preventDefault();
      var elm = $(e.currentTarget);
      var p;
      if (elm.hasClass('reverter')) {
        p = '$prompt';
      } else {
        p = '$prompt2';
      }
      p = p.replace('~!~', elm.attr('data-name')).replace('|^|', elm.attr('data-rev'));
      var _href = elm.attr('href');
      cms_confirm(p).done(function() {
        window.location.href = _href;
      });
      return false;
    });
  });
</script>

EOS;
$cmsmsv3 = cmsversion_compare(CMS_VERSION, '2.900') >= 0;
if ($cmsmsv3) {
    add_bottom_content($js); // CMSMS 3 inject js at bottom
}

//TODO filter-form elements needed if ! hide_filters
//$tpl->assign('endform', $this->CreateFormEnd());
//auto submit on selector change $tpl->assign('filtersubmit', $this->CreateInputSubmit($id, 'submit', lang('filter'), 'data-ui-icon="ui-icon-gear"'));
//TODO why was 'item_id' a parameter submitted with this form ? 'item_id' => $params['item_id'] ?
$html = $this->CreateFormStart($id, 'bulkoperation', $returnid, 'post', '', false, '', ['start' => $params['start'], 'sort_order' => $params['sort_order'], 'sort_dir' => $params['sort_dir'], 'search_text' => '']);
$html2 = preg_replace('/id="\w+?"/', 'id="bulk_form"', $html);
$tpl->assign('startbulkform', $html2);
//TODO custom hidden inputs for form id="multi_delete" if different from above
$html = $this->CreateFormStart($id, 'bulkoperation', $returnid, 'post', '', false, '', ['start' => $params['start'], 'sort_order' => $params['sort_order']]);
$html2 = preg_replace('/id="\w+?"/', 'id="multi_delete"', $html);
$tpl->assign('startbulkform2', $html2);
//TODO custom hidden inputs for form id="multi_search" if different from above
$html2 = preg_replace('/id="\w+?"/', 'id="multi_search"', $html);
$tpl->assign('startbulkform3', $html2);

$message = (!empty($params['message'])) ? $params['message'] : '';
$tpl->assign('message', $message);
$tpl->assign('deleteprompt', $this->Lang('suredelete_multi'));
$tpl->assign('searchtitle', $this->Lang('search_help'));

$tpl->assign('items', $entryarray);
$n = count($entryarray);
$tpl->assign('itemcount', $n);
$hide = ($n == 0 && !$restriction);
$tpl->assign('hide_filters', $hide);
if (!$hide) {
    //TODO why was 'item_id' a parameter submitted with this form ? 'item_id' => $params['item_id']
    $tpl->assign('startfilterform',
        $this->CreateFormStart($id, 'defaultadmin', $returnid, 'post', '', false, '', ['start' => $params['start'], 'sort_order' => $params['sort_order']])
    );
}

$tpl->assign('pdel', $pdel);
$bulkopts = [
    'search' => $this->Lang('title_search'),
    'compare' => $this->Lang('title_compare')
];
if ($pdel) {
    $bulkopts['delete'] = lang('delete');
}
$tpl->assign('bulkslist', $bulkopts);

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
if (!$cmsmsv3) {
    echo $js;
}
