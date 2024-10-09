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
            $target = $params['search_text'];
            $parms = $this->FilterParms($params);
            $parms['search_text'] = $target;
            $parms['sels'] = $sels;
            $this->Redirect($id, 'search', $returnid, $parms);
        }
        break;
    case 'compare':
        if (!$this->CheckPermission('Manage Restores')) return;
        if ($n > 1) {
            $r1 = reset($sels);
            $r2 = next($sels);
            $data = $db->getCol('SELECT item_type FROM '.CMS_DB_PREFIX."module_undoer WHERE id IN ($r1,$r2)");
            if (!$data || count($data) != 2) {
                $this->SetError('TODO internal error');
                $this->Redirect($id, 'defaultadmin', $returnid, $this->FilterParms($params));
            } elseif ($data[0] != $data[1]) {
                $this->SetError('TODO not same types');
                $this->Redirect($id, 'defaultadmin', $returnid, $this->FilterParms($params));
            }
            $parms = $this->FilterParms($params);
            $parms['match1'] = $r1;
            $parms['match2'] = $r2;
            $this->Redirect($id, 'diff', $returnid, $parms);
        }
        //TODO consider: n == 1 does compare with current
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
