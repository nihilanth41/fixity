{extends file="layout.tpl"}
{block name=title}Fixity for Darklib{/block}
{block name=heading}Fixity for Darklib{/block}
{block name=subheading}{$dbdir}{/block}
{block name=subheading2}Files by UUID{/block}
{block name=body}
<ul class="breadcrumb">
  <li><a href="index.php">Home</a></li>
	<li><a href="dates.php?dbdir={$dbdir}&dpn={$dpn}">{$dbdir}</a></li>
	<li><a href="audit.php?dbdir={$dbdir}&dpn={$dpn}&uuid={$uuid}">{$uuid}</a></li>
</ul>
<div id="content">
	<p>Displaying results: {$result_lower}-{$result_upper} of {$result_total}</p>
	{if count($ares) gt 0}
	<h2> Add </h2>
	<table>
		<thead>
			<tr>
				<th>File</th>
				<th>Size</th>
				<th>Time Modified</th>
				<th>SHA256 Digest</th>
			</tr>
		</thead>
		{foreach $ares as $r}
		{strip}
		<tr>
			<td>{$r.relpath}</td>
			<td>{$r.size}</td>
			<td>{$r.mtime}</td>
			<td>{$r.sha256}</td>
		</tr>
		{/strip}
		{/foreach}
	</table>
	{/if}
	
	{if count($dres) gt 0}
	<h2> Delete </h2>
	<table>
		<thead>
			<tr>
				<th>File</th>
				<th>Size</th>
				<th>Time Modified</th>
				<th>SHA256 Digest</th>
			</tr>
		</thead>
		{foreach $dres as $r}
		{strip}
		<tr>
			<td>{$r.relpath}</td>
			<td>{$r.size}</td>
			<td>{$r.mtime}</td>
			<td>{$r.sha256}</td>
		</tr>
		{/strip}
		{/foreach}
	</table>
	{/if}

	{if count($mres) gt 0}
	<h2> Modify </h2>
	<table>
		<thead>
			<tr>
				<th>File</th>
				<th>Size</th>
				<th>Time Modified</th>
				<th>SHA256 Digest</th>
			</tr>
		</thead>
		{foreach $mres as $r}
		{strip}
		<tr>
			<td>{$r.relpath}</td>
			<td>{$r.size}</td>
			<td>{$r.mtime}</td>
			<td>{$r.sha256}</td>
		</tr>
		{/strip}
		{/foreach}
	</table>
	{/if}
</div>

{include file='paginate.tpl'}
{/block}

{* vim: set ts=2 sw=2 : *}

