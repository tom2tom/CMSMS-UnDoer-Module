<h3>{$title}</h3>
{if !empty($message)}<br><p>{$message}</p><br>{/if}
{$formstart}
 <div class="pageoptions">
  <input type="submit" class="pagebutton" name="{$actionid}close" data-ui-icon="ui-icon-close" value="{lang('close')}">
 </div>
 <div id="rawcontent">{$content}</div>
{if $bottomnav} <div class="pageoptions">
  <input type="submit" class="pagebutton" name="{$actionid}close" data-ui-icon="ui-icon-close" value="{lang('close')}">
 </div>{/if}
</form>
