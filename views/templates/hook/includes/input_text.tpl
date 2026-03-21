{extends file=$modules_path|cat:"agcliente/views/templates/hook/includes/input.tpl"}

{block name="input"}
	{if $prefix|default || $suffix|default}
		<div class='input-group input'>
	{/if}

	{if $prefix|default}
		<span class="input-group-addon">{$prefix}</span>
	{/if}

	{if !$multilang|default}
		<input type="text" name="{$name}" value="{$value}" {if $required|default}required{/if} {if $disabled|default}disabled{/if} {if $placeholder|default}placeholder="{$placeholder}"{/if}>
	{else}
		<div class='row multilang' id="{$id}">
			<div class='col-lg-5'>
				{foreach $languages as $lang}
					{if is_array($value)}
						{assign var="input_value" value=$value[$lang.id_lang]}
					{else}
						{assign var="input_value" value=$value}
					{/if}

					<input type="text" name="{$name|cat:"["|cat:$lang.id_lang|cat:"]"}" value="{$input_value}" {if $disabled|default}disabled{/if} {if $placeholder|default}placeholder="{$placeholder}"{/if} data-lang="{$lang.id_lang}" {if $lang.id_lang != $default_lang} class="hidden"{/if}>
				{/foreach}
			</div>
			<div class='col-lg-2'>
				<div class="btn-group">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class='current-iso-code'>{$languages[0]['language_code']}</span> <span class="caret"></span></button>

					<ul class="dropdown-menu">
						{foreach $languages as $lang}
							<li><a href="#" data-id="{$lang.id_lang}" data-iso="{$lang.language_code}">{$lang.name}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
		</div>
	{/if}
	{if $suffix|default}
		<span class="input-group-addon">{$suffix}</span>
	{/if}

	{if $prefix|default || $suffix|default}
		</div>
	{/if}

	{if $multilang|default}
		<script type="text/javascript">
			$(function(){
				$('#{$id} .dropdown-menu li a').click(function(e){
					var that = $(this);

					$(this).closest('.btn-group').find('.dropdown-toggle span.current-iso-code').text($(this).attr('data-iso'));
					$(this).closest('.multilang').find('input').addClass('hidden').filter(function(){

						if ($(this).attr('data-lang') == $(that).attr('data-id')) {
							return true;
						}
					}).removeClass('hidden');

					e.preventDefault();
				});
			});
		</script>
	{/if}
{/block}