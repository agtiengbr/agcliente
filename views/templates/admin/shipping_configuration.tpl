<div class="form-group">

    <label class="control-label col-lg-3">Exibir simulação no carrinho de compras</label>

    <div class='col-lg-3'>

        <span class="switch prestashop-switch fixed-width-lg">

            <input type="radio" value="1" name="AGTI_SIMULATION_CART" id="AGTI_SIMULATION_CART_on" {if $simulation_cart == 1}checked="checked"{/if}>

            <label class="t" for="AGTI_SIMULATION_CART_on">Sim</label>

            <input type="radio" value="0" name="AGTI_SIMULATION_CART" id="AGTI_SIMULATION_CART_off" {if $simulation_cart == 0}checked="checked"{/if}> <label class="t" for="AGTI_SIMULATION_CART_off">Não</label>

            <a class="slide-button btn"></a>

        </span>

    </div>

</div>



{if $ps16}

   <div class="form-group">
        <label class="control-label col-lg-3">Hook para simulação na página do produto:</label>

        <div class="col-lg-9">
            <div class='radio'><label><input type="radio" name="AGTI_SIMULATION_PRODUCT" value="2" {if $simulation_product ==2}checked{/if}>Product Buttons (abaixo do botão de comprar)</label></div>

            <div class='radio'><label><input type="radio" name="AGTI_SIMULATION_PRODUCT" value="1" {if $simulation_product ==1}checked{/if}>Product Left (abaixo da descrição)</label></div>

            <div class='radio'><label><input type="radio" name="AGTI_SIMULATION_PRODUCT" value="0" {if $simulation_product ==0}checked{/if}>Simulação Desativada</label></div>
        </div>
    </div>
{else}

    <div class="form-group">

        <label class="control-label col-lg-3">Exibir simulação na página do produto</label>

        <div class='col-lg-3'>

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" value="1" name="AGTI_SIMULATION_PRODUCT" id="AGTI_SIMULATION_PRODUCT_on" {if $simulation_product == 1}checked="checked"{/if}>

                <label class="t" for="AGTI_SIMULATION_PRODUCT_on">Sim</label>

                <input type="radio" value="0" name="AGTI_SIMULATION_PRODUCT" id="AGTI_SIMULATION_PRODUCT_off" {if $simulation_product == 0}checked="checked"{/if}> <label class="t" for="AGTI_SIMULATION_PRODUCT_off">Não</label>

                <a class="slide-button btn"></a>

            </span>

        </div>

    </div>

{/if}



<div class="form-group">

        <label class="control-label col-lg-3">Exibir logos das transportadoras na simulação de frete</label>

        <div class='col-lg-3'>

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" value="1" name="AGTI_SIMULATION_DISPLAY_IMAGES" id="AGTI_SIMULATION_DISPLAY_IMAGES_on" {if $display_images == 1}checked="checked"{/if}>

                <label class="t" for="AGTI_SIMULATION_DISPLAY_IMAGES_on">Sim</label>

                <input type="radio" value="0" name="AGTI_SIMULATION_DISPLAY_IMAGES" id="AGTI_SIMULATION_DISPLAY_IMAGES_off" {if $display_images == 0}checked="checked"{/if}> <label class="t" for="AGTI_SIMULATION_DISPLAY_IMAGES_off">Não</label>

                <a class="slide-button btn"></a>

            </span>

        </div>

        <div class="col-lg-9 col-lg-offset-3">

            <div class="help-block">Não recomendado na versão 1.6 do PrestaShop. Pode necessitar de personalizações de CSS!</div>

        </div>

    </div>



<ps-input-text-lang name="AGTI_SIMULATION_FREE_SHIPPING_TEXT" label="Texto para 'Frete Grátis'" col-lg="5" active-lang="{$languages[0]['id_lang']}">

	{foreach from=$languages item=lang}

		<div data-is="ps-input-text-lang-value" iso-lang="{$lang['language_code']}" id-lang="{$lang['id_lang']}" lang-name="{$lang['name']}" value="{$free_shipping_texts[$lang['id_lang']]}"></div>

	{/foreach}

</ps-input-text-lang>



<div class='panel-footer'>

    <button type="submit" value="1" name="agti_shipping_submit" class="btn btn-default pull-right">

      <i class="process-icon-save"></i> Salvar

    </button>

    <a class='btn btn-default' href="#" onclick="window.history.back();">

      <i class="process-icon-cancel"></i> Cancelar

    </a>

</div>

