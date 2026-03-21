{extends file=$modules_path|cat:"agcliente/views/templates/hook/includes/input.tpl"}

{block name="input"}
	<span class="switch prestashop-switch fixed-width-lg">
		<input type="radio" value="1" name="{$name}" id="{$name}_on" {if $value == 1}checked="checked"{/if}>
		<label class="t" for="{$name}_on">{l s='Yes' mod='agcliente'}</label>
		<input type="radio" value="0" name="{$name}" id="{$name}_off" {if $value == 0}checked="checked"{/if}> <label class="t" for="{$name}_off">{l s='No' mod='agcliente'}</label>
		<a class="slide-button btn"></a>
	</span>
{/block}