{extends file=$modules_path|cat:"agcliente/views/templates/hook/includes/input.tpl"}

{block name="input"}
	<input type="file" name="{$name}" class="filestyle" data-buttontext="{$label}" id="filestyle-0" tabindex="-1" style="position: absolute; clip: rect(0px, 0px, 0px, 0px);">
	<div class="bootstrap-filestyle input-group"><input type="text" class="form-control " placeholder="" disabled=""> <span class="group-span-filestyle input-group-btn" tabindex="0"><label for="filestyle-0" class="btn btn-default "><span class="icon-span-filestyle fa fa-folder-open"></span> <span class="buttonText">Escolha um arquivo</span></label></span>
	</div>
	<script>
		$('[name={$name}]').change(function(){
			var filename = $(this).val().split('/').pop().split('\\').pop();
			$(this).parent().find('input[type=text]').val(filename);
		});
	</script>
{/block}