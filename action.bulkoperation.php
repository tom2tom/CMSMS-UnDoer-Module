<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/

if (empty($this) || !($this instanceof UnDoer)) exit;
/*
supplied $params[]
[start] "0"
[sort_order] "name"
[item_id] "-1"
[multiselect] array[e.g. 3]
 [0] "11"
 [1] "12"
 [2] "13"
IN SOME CASES [search_text] "whatever"
[action] "bulkoperation"
[bulkoperation] "search" etc
[submit] "Submit"
*/

$n = count($params['multiselect']);
$sels = array_map(function($a) { return (int)$a; }, $params['multiselect']);// simple proxy-validation

switch ($params['bulkoperation']) {
    case 'search':
        if (!$this->CheckPermission('Manage Restores')) return;
        if ($n > 0) {
            //TODO [redirect to ]display fuzzy matches for $params['search_text'] in $sels revisions
            //c.f. AdminSearch slave etc
        }
    break;
    case 'compare':
        if (!$this->CheckPermission('Manage Restores')) return;
        if ($n > 1) {
            $r1 = reset($sels);
            $r2 = next($sels);
            $data = $db->getArray('SELECT id,item_id,item_type,item_name,revision_number,archive_date,archive_content FROM '.CMS_DB_PREFIX."module_undoer WHERE id IN ($r1,$r2)");
            //fail if their types differ
            //TODO [redirect to ]display diff between $r1, $r2
        }
    break;
    case 'delete':
        if (!$this->CheckPermission('Delete Restores')) return;
        if ($n > 0) {
            $fillers = str_repeat('?,', $n - 1);
            $query = 'DELETE FROM '.CMS_DB_PREFIX."module_undoer WHERE id IN ($fillers?)";
            $dbr = $db->execute($query, $params['multiselect']);
            //$this->SetMessage(status message) OR $this->SetError(status message); per $dbr ?
            //$params['message'] = status message;
            $this->Redirect($id, 'defaultadmin', $returnid, $this->FilterParms($params));
        }
        break;
}

//$this->SetError(status message);
//$params['message'] = status message
$this->Redirect($id, 'defaultadmin', $returnid, $this->FilterParms($params));
