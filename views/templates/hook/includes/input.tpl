<div class="form-group {if $class|default}{$class}{/if}">
	<label class="control-label col-lg-3">{$label}</label>

	<div class="{if $col|default}col-lg-{$col}{else}col-lg-9{/if}">
		{block name="input"}{/block}		
	</div>

	{if $help|default}
		<div class="col-lg-9 col-lg-offset-3">
			<div class="help-block">{$help}</div>
		</div>
	{/if}
</div>