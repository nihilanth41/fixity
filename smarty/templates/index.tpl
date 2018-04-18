{extends file="layout.tpl"}
{block name=title}Fixity for Darklib{/block}
{block name=heading}Fixity for Darklib{/block}
{block name=subheading}Directory Listing{/block}
{block name=subheading2}{/block}
{block name=body}
<div class="center">
	<ul class="main">
		{foreach from=$dirs item=$i}
			<li>{$i}</li>
		{/foreach}
	</ul>
</div>
{/block}

{* vim: set ts=2 sw=2 :*}

