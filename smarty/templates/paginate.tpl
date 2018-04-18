<div class="nav">
	<ul>
		<li>
				{if $prevpage != $pageno}
					<a href="{$prev_hl}">previous</a>
				{else}
          previous
				{/if}
		</li>

		{foreach from=$navArray item=$navLink}
			<li>{$navLink}</li>
		{/foreach}
		
		<li>
				{if $nextpage != $pageno}
					<a href="{$next_hl}">next</a>
				{else}
          next
				{/if}
		</li>
</ul>
</div>
{* vim: set ts=2 sw=2 :*}
