<h3>{$mod->Lang('items')}</h3>
{if !empty($message)}<br><p>{$message}</p>{/if}
{if $itemcount > 2}
{$startadminform}
	<div class="pageoptions">
		<input type="submit" class="pagebutton" name="{$actionid}close" data-ui-icon="ui-icon-close" value="{lang('close')}">
	</div>
</form>
<br>
{/if}
{if $itemcount > 0}
{$startbulkform}
<table id="revisions" class="pagetable">
	<thead>
		<tr>
			<th>{$column_name}</th>
			<th>{$column_type}</th>
			<th>{$column_revision}</th>
			<th>{$column_date}</th>
			<th class="pageicon" colspan={if $pdel}"3"{else}"2"{/if}>{$column_action}</th>
			<th class="pageicon"><input type="checkbox" id="sel_all" style="vertical-align:middle" value="1" title="{$mod->Lang('tip_selectall')}"></th>
		</tr>
	</thead>
	<tbody id="searchresults">{$rowboxtip=$mod->Lang('tip_multiselect')}
	{foreach $items as $entry}
		<tr class="{$entry->rowclass}" onmouseover="this.className='{$entry->rowclass}hover';" onmouseout="this.className='{$entry->rowclass}';">
			<td>{$entry->name}</td>
			<td>{$entry->type}</td>
			<td>{$entry->revision}</td>
			<td>{$entry->date}</td>
			<td>{$entry->viewlink}</td>
{if $pdel}			<td>{$entry->dellink}</td>{/if}
			<td>{if !empty($entry->restlink)}{$entry->restlink}{/if}</td>
			<td><input type="checkbox" class="multiselect" name="{$actionid}multiselect[]" value="{$entry->id}" title="{$rowboxtip}"></td>
		</tr>
		<tr class="{$entry->rowclass}">
			<td class="snippets" colspan={if $pdel}"8"{else}"7"{/if}>{$entry->snippets}</td>
		</tr>
	{/foreach}
	</tbody>
</table>
<div id="bulkelements">
	<label for="bulksel">{lang('selecteditems')}</label>
	<select id="bulksel" class="cms_dropdown" name="{$actionid}bulkoperation">
	 {html_options options=$bulkslist}	</select>
	<input type="submit" id="bulksubmit" name="{$actionid}submit" data-ui-icon="ui-icon-gear" value="{lang('submit')}">
</div>
</form>
<div id="delete_confirm" style="display:none" title="{lang('confirm')}">
	{$startbulkform2}
		<p class="warning">{$deleteprompt}</p><br>
	</form>
</div>
{else}
<p class="advice">{$mod->Lang('no_match', $target)}</p>
{/if}
{$startadminform}
	<div class="pageoptions">
		<input type="submit" class="pagebutton" name="{$actionid}close" data-ui-icon="ui-icon-close" value="{lang('close')}">
	</div>
</form>
