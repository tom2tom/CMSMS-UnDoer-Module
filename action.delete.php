<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2024 CMS Made Simple Foundation Inc <foundation@cmsmadesimple.org>
Refer to license and other details at the top of file UnDoer.module.php
*/

if (empty($this) || !($this instanceof UnDoer)) exit;
if (!$this->CheckPermission('Delete Restores')) return;

$query = 'DELETE FROM '.CMS_DB_PREFIX.'module_undoer WHERE id=?';
$dbr = $db->execute($query, [(int)$params['rev_id']]);

//$params['message'] = TODO status message per $dbr ?
//$this->SetMessage(TODO);
$this->Redirect($id, 'defaultadmin', $returnid, $this->FilterParms($params));
