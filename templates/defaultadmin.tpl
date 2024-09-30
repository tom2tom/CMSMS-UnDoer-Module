{$admin_nav}
<h3>{$mod->Lang('items')}</h3>
{if !empty($message)}<br><p>{$message}</p>{/if}
{if $page_no}<table class="pagetable" style="border-spacing:0"><tr><td>{$prev_page}</td><td>{$page_no}</td><td>{$next_page}</td></tr></table>{/if}
{if !$hide_filters}<table class="pagetable" style="border-spacing:0"><tr><td>{$startform}{$hidden}{$title_filter_type}{$input_filter_type}{*$submit*}{$endform}</td></tr></table>{/if}
{if $itemcount > 0}
<table class="pagetable" style="border-spacing:0">
	<thead>
		<tr>
			<th{$name_sort}>{$column_name}</th>
			<th{$type_sort}>{$column_type}</th>
			<th{$rev_sort}>{$column_revision}</th>
			<th{$date_sort}>{$column_date}</th>
			<th class="pageicon" colspan="2">{$column_action}</th>
		</tr>
	</thead>
	<tbody>
	{foreach $items as $entry}
		<tr class="{$entry->rowclass}" onmouseover="this.className='{$entry->rowclass}hover';" onmouseout="this.className='{$entry->rowclass}';">
			<td>{$entry->name}</td>
			<td>{$entry->type}</td>
			<td>{$entry->revision}</td>
			<td>{$entry->date}</td>
			<td>{$entry->viewlink}</td>
			<td>{if !empty($entry->restlink)}{$entry->restlink}{/if}</td>
		</tr>
	{/foreach}
	</tbody>
</table>
{if $page_no}<table class="pagetable" style="border-spacing:0"><tr><td>{$prev_page}</td><td>{$mod->Lang('page')} {$page_links}</td><td>{$next_page}</td></tr></table>{/if}{*TODO langify*}
{else}
<p class="advice">{$mod->Lang('no_revision')}</p>
{/if}
