{extends file=$modules_path|cat:"agcliente/views/templates/hook/includes/input.tpl"}

{block name="input"}
	<textarea class='control-form' name="{$name}" {if $rows|default}rows="{$rows}"{/if} {if $required|default}required{/if} {if $disabled|default}disabled{/if} {if $placeholder|default}placeholder="{$placeholder}"{/if}>{$value|default}</textarea>
{/block}