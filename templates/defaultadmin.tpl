{$admin_nav}
<h3>{$mod->Lang('items')}</h3>
{if !empty($message)}<br><p>{$message}</p>{/if}
{if $page_no}<br><table class="pagetable" style="border-spacing:0"><tr><td>{$prev_page}</td><td>{$page_no}</td><td>{$next_page}</td></tr></table>{/if}
{if !$hide_filters}<div id="filterelements">{$startfilterform}<span>{$title_filter_type}</span>{$input_filter_type}</form></div>{/if}
{if $itemcount > 0}
{$startbulkform}
<table id="revisions" class="pagetable">
	<thead>
		<tr>
			<th{$name_sort}>{$column_name}</th>
			<th{$type_sort}>{$column_type}</th>
			<th{$rev_sort}>{$column_revision}</th>
			<th{$date_sort}>{$column_date}</th>
			<th class="pageicon" colspan={if $pdel}"3"{else}"2"{/if}>{$column_action}</th>
			<th class="pageicon"><input type="checkbox" id="sel_all" style="vertical-align:middle" value="1" title="{$mod->Lang('tip_selectall')}"></th>
		</tr>
	</thead>
	<tbody>{$rowboxtip=$mod->Lang('tip_multiselect')}
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
	{/foreach}
	</tbody>
</table>
{if $page_no}<br><table class="pagetable" style="border-spacing:0"><tr><td>{$prev_page}</td><td>{$mod->Lang('page')} {$page_links}</td><td>{$next_page}</td></tr></table>{/if}{* TODO reconcile with pager above table *}
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
<div id="search_details" style="display:none" title="{$mod->Lang('title_search')}">
	{$startbulkform3}
		<label class="pagetext" for="target">{$searchtitle}:</label><br>
		<input type="search" id="target" name="{$actionid}target" tabindex="1" size="40" placeholder="{$mod->Lang('placeholder_search_text')}">
	</form>
</div>
{else}
<p class="advice">{$mod->Lang('no_revision')}</p>
{/if}
