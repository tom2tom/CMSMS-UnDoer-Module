<?php
/*
This file is part of CMS Made Simple module: UnDoer
Copyright (C) 2005-2024 Samuel Goldstein <unknown email address>
Refer to license and other details at the top of file UnDoer.module.php
*/

if (empty($this) || !($this instanceof UnDoer)) exit;
if (!$this->VisibleToAdminUser()) exit;
//TODO
echo 'Previews not yet implemented';
return;

//TODO preview processing elsewhere
/*
{if $preview}
<p class="pageheader">{$title_preview}</p>
<iframe name="previewframe" class="preview" src="{$preview_file}"></iframe>
{/if}
*/
if ($params['preview'] && $params['type_id'] == UnDoer::TYPE_CONTENT) {
    $restore = UnDoer\Utils::retrieveSerializedObject($params['item_id'], $params['revision_number']);
    if (!$restore) {
        //TODO handle error
        $here = 1;
    }
    $tmpfname = UnDoer\Utils::createtmpfname($restore);
    $_SESSION['cms_preview'] = str_replace('\\', '/', $tmpfname);
    $tpl->assign('preview_file',
        $config['root_url'] .'/index.php?'.$config['query_var'].'=__CMS_PREVIEW_PAGE__'
    );

    $tpl->assign('title_preview', $this->Lang('title_preview', $params['revision_number']));
}
