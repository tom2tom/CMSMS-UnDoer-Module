<h3>{$title}</h3>
{if !empty($message)}<br><p>{$message}</p><br>{/if}
{$formstart}
 <div class="pageoptions">
  <input type="submit" class="pagebutton" name="{$actionid}close" data-ui-icon="ui-icon-close" value="{lang('close')}">
 </div>
 <p class="pagewarning">{$mod->Lang('preview_notice')}</p>
 <iframe id="previewframe" class="preview" src="{$preview_url}"></iframe>
{if $bottomnav} <div class="pageoptions">
  <input type="submit" class="pagebutton" name="{$actionid}close" data-ui-icon="ui-icon-close" value="{lang('close')}">
 </div>{/if}
</form>
