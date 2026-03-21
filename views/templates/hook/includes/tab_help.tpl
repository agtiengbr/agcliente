<form class='form-horizontal'>
	<div class='panel'>
		<div class='panel-heading'><i class='icon-mail'></i> Suporte</div>

		{if $wiki_url}
			<div class='form-group'>
				<div class='col-lg-3 text-right'>Documentação:</div>
				<div class='col-lg-9'><a href='{$wiki_url}' target="_blank">{$wiki_url}</a></div>
			</div>
		{/if}
	</div>
</form>