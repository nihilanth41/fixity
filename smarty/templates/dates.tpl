{extends file="layout.tpl"}
{block name=title}Fixity for Darklib{/block}
{block name=heading}Fixity for Darklib{/block}
{block name=subheading}{$dbdir}{/block}
{block name=subheading2}{/block}
{block name=body}
<ul class="breadcrumb">
  <li><a href="index.php">Home</a></li>
	<li><a href="dates.php?dbdir={$dbdir}">{$dbdir}</a></li>
</ul>

<div id="content">
	<p align=center>{$result_lower}-{$result_upper} of {$result_total}</p>
  {include file='paginate.tpl'}
	<p>Click on a date to see associated files.</p>
	<table>
				<thead>
					<tr>
						<th>Run start time</th>
						<th>File Count*</th>
						<th>Run Finished?</th>
						<th>UUID</th>
						<th>CSV Export</th>
					</tr>
				</thead>
				<tbody>
						{foreach $res as $r}
							{strip}
								{if $r.finished eq "No"}
									<tr class="nf">
								{elseif $r.count != 0}
									<tr class="nz">
								{else}
									<tr class="zr">
								{/if}
									<td>
										{if $r.count != 0}
												<a href="audit.php?dbdir={$dbdir}&dpn={$pageno}&uuid={$r.uuid}&apn=1">
													<div style="height:100%;width:100%">	
														{$r.utime}
													</div>	
												</a>
										</td>
										{else}
											{$r.utime}
										{/if}
									</td>
									<td>{$r.count}</td>
									<td>{$r.finished}</td>
									<td>{$r.uuid}</td>
									{if $r.count != 0}
										<td><a href="{$r.csv}">Download CSV</a></td>
									{else}
										<td></td>
									{/if}
								</tr>
							{/strip}
						{/foreach}
					</tbody>
		</table>
	<p>* Sum of all files that were found added, deleted, or modified since the previous run.</p>
</div>


{include file='paginate.tpl'}
{/block}

{* vim: set ts=2 sw=2 : *}

