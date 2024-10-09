<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/
/*
this operates similarly to the defaultadmin action, except
explicit items to be processed
no sorting or filtering or paging but related params
'start', 'sort_order', 'sort_dir' will be passed-thru for use after closing the search page
no (further) searching via the bulk-operation selector
*/
use UnDoer\Utils;

if (empty($this) || !($this instanceof UnDoer)) exit;
if (!$this->VisibleToAdminUser()) exit;
//if (!$this->CheckPermission('TODO')) exit;
//if (!$this->CheckAccess()) exit;

//echo("Not yet implemented");
//return;

if (empty($params['sels'])) {
    $this->SetError($lang['missingparams']);
    $this->Redirect($id, 'defaultadmin', $returnid, $this->FilterParms($params));
} else {
    $sels = array_map(function($a) { return (int)$a; }, $params['sels']);// simple proxy-validation
}
$pdel = $this->CheckPermission('Delete Restores');
$target = $params['search_text']; // TODO sanitized
$entryarray = [];

$query = 'SELECT id,item_id,item_type,item_name,revision_number,archive_date,archive_content FROM '.
    CMS_DB_PREFIX.'module_undoer WHERE id IN('.implode(',', $sels).') ORDER BY item_name,revision_number';
$data = $db->GetArray($query);
if ($data) {
    $theme = cms_utils::get_theme_object();
    $needle = Utils::get_regex_pattern($target, false);
    $cleaner = function($from) {
        return str_replace(['{', '}'], ['&#123;', '&#125;'], htmlentities($from, ENT_NOQUOTES|ENT_SUBSTITUTE|ENT_HTML5, null, false));
    };
    $desc = true;
    $rowclass = 'row1';
    $dateFmt = $this->GetPreference('date_format', 'l, j F Y H:i');
    // escaped 'templates' with placeholders for replacement in js
    $prompt = addcslashes($this->Lang('surerestore', '~!~', '|^|'), "'\n\r");
    $prompt2 = addcslashes($this->Lang('suredelete', '|^|', '~!~'), "'\n\r");
    // the following replicates the process in RevisionSearch_slave class
    foreach ($data as $row) {
        switch ($row['item_type']) {
            case UnDoer::TYPE_CONTENT:
                $restore = Utils::retrieveSerializedObject($row['item_id'], $row['revision_number']); // unpack $row['archive_content'] with type-class pre-processing
                if (!$restore) {
                    break 2;
                    //TODO handle error $this->SetError(); etc
                }
                 //TODO other content-like extras e.g. Sidebar ?
                $props = [
                 'name' => $restore->Name(),
                 'menu' => $restore->MenuText(),
                 'alias' => $restore->Alias(),
                 'title' => '',
                 'content' => $restore->GetPropertyValue('content_en')
                ];
                if ($desc) {
                    $props['title'] = $restore->TitleAttribute();
                } else {
                    unset($props['title']);
                }
                break;
            case UnDoer::TYPE_STYLESHEET:
                $restore = unserialize($row['archive_content']); //['allowed_classes' => true OR whatever]
                if (!$restore) {
                    break 2;
                    //TODO handle error $this->SetError(); etc
                }
                $props = [
                 'name' => $restore->get_name(),
                 'description' => '',
                 'content' => $restore->get_content()
                ];
                if ($desc) {
                    $props['description'] = $restore->get_description();
                } else {
                    unset($props['description']);
                }
                break;
            case UnDoer::TYPE_TEMPLATE:
                $restore = unserialize($row['archive_content']); //['allowed_classes' => true OR whatever]
                if (!$restore) {
                    break 2;
                    //TODO handle error $this->SetError(); etc
                }
                $props = [
                 'name' => $restore->get_name(),
                 'description' => '',
                 'content' => $restore->get_content()
                ];
                if ($desc) {
                    $props['description'] = $restore->get_description();
                } else {
                    unset($props['description']);
                }
                break;
            default:
                break 2; //TODO handle error $this->SetError(); etc
        }
        //interrogate property values
        $output = [];
        foreach ($props as $name => $value) {
            $matches = [];
            $num = preg_match_all($needle, $value, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            if ($num > 0) {
                $label = lang($name);
                $tmp = [$label]; //TODO styling classes
                $len = strlen($value);
                foreach ($matches as $bdl) {
                    $found = $bdl[0];
                    //$found[0] = text to be highligted
                    //$found[1] = start of text to be highligted, use for surrounding context recovery
                    $start = max(0, $found[1] - 40); // too bad if inside multi-byte !
                    $pre = substr($value, $start, $found[1] - $start);
                    $end = min($len, $found[1] + 40); // ditto
                    $l2 = strlen($found[0]);
                    $post = substr($value, $found[1] + $l2,  $end - $found[1] - $l2);
                    $tmp[] = $cleaner($pre) . '<span class="search_match">' . $cleaner($found[0]) . '</span>' . $cleaner($post);
                }
                $output[] = implode('<br>', $tmp);
            }
        }
        if (!$output) {
            continue; // no match to report
        }

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
            'start' => $params['start'], // TODO want this to be provided in $params 'type_filter' => $params['type_filter'],
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
             'start' => $params['start'], // TODO want this to be provided in $params 'type_filter' => $params['type_filter'],
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
                'start' => $params['start'], // TODO want this to be provided in $params 'type_filter' => $params['type_filter'], //pass-thru's
                'type_id' => $row['item_type'], 'sort_order' => $params['sort_order']
                ],
                '', false, false, 'class="deleter" data-name="'.addcslashes($row['item_name'], '"').'" data-rev="'.$row['revision_number'].'"'
            );
        }

        $onerow->snippets = ($output) ? implode('<br>', $output) : '';

        $entryarray[] = $onerow;

        ($rowclass == 'row1' ? $rowclass = 'row2' : $rowclass = 'row1');
    }
} else {
    $this->SetError(lang('informationmissing')); //TODO better advice
    $this->Redirect($id, 'defaultadmin', $returnid, $this->FilterParms($params));
}

if ($entryarray) {

    // escape strings for js
    $s1 = addcslashes($this->Lang('suredelete_multi'), "'\n\r");
    $s2 = addcslashes($this->Lang('error_compare'), "'\n\r");

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
          cms_alert('$s2');
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
} //$entryarray

$bulkopts = [
    'compare' => $this->Lang('title_compare')
];
if ($pdel) {
    $bulkopts['delete'] = lang('delete');
}
$message = (!empty($params['message'])) ? $params['message'] : '';

$tpl = $smarty->CreateTemplate($this->GetTemplateResource('search.tpl'), null, null, $smarty);

$html = $this->CreateFormStart($id, 'defaultadmin', $returnid, 'post', '', false, '', ['start' => $params['start'], 'sort_order' => $params['sort_order'], 'sort_dir' =>
$params['sort_dir']]);
$html2 = preg_replace('/id="\w+?"/', 'id="cancel_form"', $html);
$tpl->assign('startadminform', $html2);
$html = $this->CreateFormStart($id, 'bulkoperation', $returnid, 'post', '', false, '', ['start' => $params['start'], 'sort_order' => $params['sort_order'], 'sort_dir' => $params['sort_dir']]);
$html2 = preg_replace('/id="\w+?"/', 'id="bulk_form"', $html);
$tpl->assign('startbulkform', $html2);
$html2 = preg_replace('/id="\w+?"/', 'id="multi_delete"', $html);
$tpl->assign('startbulkform2', $html2)
 ->assign('bulkslist', $bulkopts)
 ->assign('column_action', $this->Lang('column_action'))
 ->assign('column_date', $this->Lang('column_date'))
 ->assign('column_name', $this->Lang('column_name'))
 ->assign('column_revision', $this->Lang('column_revision'))
 ->assign('column_type', $this->Lang('column_type'))
 ->assign('deleteprompt', $this->Lang('suredelete_multi'))
 ->assign('itemcount', count($entryarray))
 ->assign('items', $entryarray)
 ->assign('message', $message)
 ->assign('pdel', $pdel)
 ->assign('target', $target);

$tpl->display();
if ($entryarray && empty($cmsmsv3)) {
    echo $js;
}
