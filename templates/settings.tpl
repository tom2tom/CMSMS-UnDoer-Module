<p>{$admin_nav}</p>
<h3>{$mod->Lang('adminprefs')}</h3>
{if !empty($message)}<br><p>{$message}</p><br>{/if}
{$startform}
	<div class="pageoverflow">
		<label for="cbcnt" class="pagetext">{$mod->Lang('title_archive_content')}:</label><br>
		<input type="checkbox" id="cbcnt" value="1" name="{$actionid}archivecontent"{if !empty($docontent)} checked{if !$pdelc} disabled{/if}{/if}>
	</div>
	<div class="pageoverflow">
		<label for="cbcss" class="pagetext">{$mod->Lang('title_archive_sheets')}:</label><br>
		<input type="checkbox" id="cbcss" value="1" name="{$actionid}archivestyles"{if !empty($dostyles)} checked{if !$pdels} disabled{/if}{/if}>
	</div>
	<div class="pageoverflow">
		<label for="cbtpl" class="pagetext">{$mod->Lang('title_archive_tpls')}:</label><br>
		<input type="checkbox" id="cbtpl" value="1" name="{$actionid}archivetemlates"{if !empty($dotemplates)} checked{if !$pdelt} disabled{/if}{/if}>
	</div>
	<br>
	<div class="pageoverflow">
		<label for="scnt" class="pagetext">{$mod->Lang('title_purge_count')}</label>
		<p class="pageinput"><select id="scnt" name="{$actionid}purge_count">{html_options options=$countslist selected=$purge_count}</select></p>
	</div>
	<div class="pageoverflow">
		<label for="slife" class="pagetext">{$mod->Lang('title_purge_time')}</label>
		<p class="pageinput"><select id="slife" name="{$actionid}purge_time">{html_options options=$dayslist selected=$purge_time}</select></p>
	</div>
	<div class="pageoverflow">
		<p class="warning">{$mod->Lang('title_purge_warning')}</p>
		<p class="information">{$mod->Lang('purge_info2')}</p>
	</div>
	<br>
	<div class="pageoverflow">
		<label for="ifmt" class="pagetext">{$mod->Lang('title_date_format')}:</label>
		<p class="pageinput"><input type="text" id="ifmt" name="{$actionid}date_format" value="{$date_format}" size="12"></p>
	</div>
	<div class="pageoverflow">
		<p class="information">{$mod->Lang('date_format_help')}</p>
	</div>
	<br>
	<div class="pageoverflow">
		<p class="pageinput">
			<input type="submit" name="{$actionid}submit" value="{lang('submit')}">
			<input type="submit" name="{$actionid}cancel" value="{lang('cancel')}">
		</p>
	</div>
{$endform}
