{if version_compare($ps_version, '1.7', '<')}
    <div class="{$class} agti_shipping_simulation agti_shipping_simulation-ps16 p-0" data-hook="productButtons">

        <div class="agti_simulation_loading" style="display: none;"></div>

        <div class="p-1">

            <div>
                <label>Simule o custo do frete abaixo</label>
            </div>

            <input class="form-control" type="text" id="{$class}_agti_shipping_simulation_postcode" placeholder="Digite o CEP"
                {if $postcode} value="{$postcode}" {/if} data-mask="00000-000" />
            <button class="btn btn-primary"><i class="icon-truck"></i> CALCULAR</button>

            <div class=''><a href='https://buscacepinter.correios.com.br/app/endereco/index.php' target='_blank'>Não sei o meu CEP</a></div>

            <div class="agti_shipping_simulation_options">
                {if $display_address && $postcode}
                    <hr>
                    <div class='address mt-1'>
                        {if $address != null && $address->city}
                            <p>Envio para {if $address->street}{$address->street}, {/if}{$address->city} -
                                {$address->state}</p>
                        {else}
                            <p class='alert alert-danger'>Não localizamos esse endereço em nossa base. Por favor se certifique de que ele
                                está correto.</p>
                        {/if}
                        <p id="invalid_cep" class='alert alert-danger hidden'>Por favor, informe um CEP válido.</p>
                </div>
                <div id="invalid_cep" class="hidden mt-1">
                    <p class='alert alert-danger'>Por favor, informe um CEP válido.</p>
                </div>
                {/if}

                <div class="table-responsive">
                    <table class="simulation">
                        <tbody>
                            {if is_array($prices) && count($prices)}
                                {foreach from=$prices item=price}
                                    <tr data-carrier-id='{$price['carrier']->id}' onclick='selectCarrier(this)'>
                                        <td class='radio'>
                                            <input type='radio' name='carrier' value='{$price['carrier']->id}' {if $this->context->cart->carrierIsSelected($price['carrier']->id)}checked="checked"{/if}/>
                                        </td>
                                        {if $agti_display_images|default}
                                            <td class='image'><img src='{$price['carrier']->img}' alt={$price['carrier']->name} />
                                        {/if}
                                        <td class='name'>{$price['carrier']->name}</td>
                                        <td class='price'>{$price['price']}</td>
                                        <td class='delay'>{$price['delay']}</td>
                                    </tr>
                                {/foreach}
                            {else if $postcode && $simulate}
                                <tr><td class='alert-danger'>{l s='Unfortunately, there are no carriers available for your delivery address.' mod='agcliente'}</td></tr>
                            {/if}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
{else}
    <div class="card {$class} agti_shipping_simulation agti_shipping_simulation-ps17 p-0" data-hook="productButtons"
        data-id-product-attribute="{$id_product_attribute|default}">

        <div class="agti_simulation_loading" style="display: none;"></div>

        {if Context::getContext()->customer->id && $is_cart_simulation && count(Context::getContext()->customer->getAddresses(Context::getContext()->language->id))}
            {assign var="displayRadio" value=true}
        {else}
            {assign var="displayRadio" value=false}
        {/if}

        <div class="p-1">
            <input type="text" id="{$class}_agti_shipping_simulation_postcode" placeholder="Digite o CEP" {if $postcode}
                value="{$postcode}" {/if} data-mask="00000-000" />
            <button class="btn btn-primary">{if $use_material_icons}<i class="material-icons">local_shippings</i>{/if} SIMULAR
                FRETE</button>

            <div class=''><a href='https://buscacepinter.correios.com.br/app/endereco/index.php' target='_blank'>Não sei o meu CEP</a></div>

            <div class="agti_shipping_simulation_options" {if !$displayRadio}style="pointer-events: none;"{/if}>
                {if $display_address && $postcode && $postcode !== 'undefined'}
                    <hr>
                    <div class='address mt-1'>
                        {if $address != null && $address->city}
                            <p>Envio para {if $address->street}{$address->street}, {/if}{$address->city} -
                                {$address->state}</p>
                        {else}
                            <p class='alert alert-danger'>Não localizamos esse endereço em nossa base. Por favor se certifique de que ele
                                está correto.</p>
                        {/if}
                    </div>
                    <div id="invalid_cep" class="hidden mt-1">
                    <p class='alert alert-danger'>Por favor, informe um CEP válido.</p>
                </div>
            {/if}

                <div class="table-responsive">
                    <table class="simulation">
                        {if is_array($prices) && count($prices)}
                            <thead>
                                <tr>
                                    {if $displayRadio}
                                        <td></td>
                                    {/if}
                                    <td>Modalidade de Entrega</td>
                                    <td>Valor</td>
                                    <td>Preparação + Entrega</td>
                                </tr>
                                
                            </thead>
                        {/if}
                        <tbody>
                            {if is_array($prices) && count($prices)}
                                {foreach from=$prices item=price}
                                    <tr {if $agcliente_carrier == $price['carrier']->id} class="selected-carrier" {/if} data-carrier-id='{$price['carrier']->id}' {if $displayRadio}onclick='selectCarrier(this)'{/if}>
                                        {if $displayRadio}
                                            <td class='radio'>
                                                <span class="custom-radio">
                                                    <input type='radio' name='carrier' value='{$price['carrier']->id}' {if $price['carrier']->id == $agcliente_carrier}checked{/if}>
                                                    <span></span>
                                                </span>
                                            </td>
                                        {/if}
                                        {if $agti_display_images|default}
                                            <td class='image'><img src='{$price['carrier']->img}' alt={$price['carrier']->name} />
                                        {/if}
                                        <td class='name'>{$price['carrier']->name}</td>
                                        <td class='price'>{$price['price']}</td>
                                        <td class='delay'>{$price['delay']}</td>
                                    </tr>
                                {/foreach}
                            {else if $postcode && $simulate}
                                <tr><td class='alert-danger'>{l s='Unfortunately, there are no carriers available for your delivery address.' mod='agcliente'}</td></tr>
                            {/if}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
	</div>
{/if}

<div id="agti-shipping-address-registration-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header pb-1 d-flex justify-content-end">
                <h5 class="modal-title">Novo endereço</h5>
            </div>
            <div class="modal-body">
                <div id="agti-shipping-address-loading" style="display: none;"><span colspan="4"></span></div>

                <div class="help-block m-0">
                    <ul>
                        <li id="agti-shipping-address-error-message-box" class="alert alert-danger mb-4" ></li>
                        <li id="agti-shipping-address-success-message-box" class="alert alert-success mb-4"></li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-md-12 col-lg-12">

                        <div id="agti-shipping-address-registration-container" class="container">

                            <section class="mt-2">
                                
                                <form id="agti-shipping-address-registration-form">
                                    <section class="form-fields">
                                        <div class="form-group row hidden">
                                            <label class="col-md-3 form-control-label" for="field-alias">
                                                Apelido
                                            </label>
                                            <div class="col-md-7">
                                                <input id="field-alias" class="form-control w-100" name="alias" type="text" value="Meu endereço" maxlength="32">
                                            </div>
                                        </div>
                                        <div class="form-group row ">
                                            <label class="col-md-3 form-control-label required" for="field-firstname">
                                                Nome
                                            </label>
                                            <div class="col-md-7">
                                                <input id="field-firstname" class="form-control w-100" name="firstname" type="text" value="teste" maxlength="255" required="">
                                            </div>
                                        </div>
                                        <div class="form-group row ">
                                            <label class="col-md-3 form-control-label required" for="field-lastname">
                                                Sobrenome
                                            </label>
                                            <div class="col-md-7">
                                                <input id="field-lastname" class="form-control w-100" name="lastname" type="text" value="teste" maxlength="255" required="">
                                            </div>
                                        </div>
                                        <div class="form-group row hidden">
                                            <label class="col-md-3 form-control-label required" for="field-id_country">
                                                País
                                            </label>
                                            <div class="col-md-7">
                                                <select id="field-id_country" class="form-control w-100 form-control-select js-country" name="id_country" required="">
                                                    <option value="" disabled="" selected="">Please choose</option>
                                                    <option value="58" selected="">Brazil</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row ">
                                            <label class="col-md-3 form-control-label required" for="field-postcode">
                                                CEP
                                            </label>
                                            <div class="col-md-7">
                                                <input id="field-postcode" class="form-control w-100" disabled name="postcode" type="text" maxlength="12" required="">
                                            </div>
                                        </div>
                                        <div class="form-group row ">
                                            <label class="col-md-3 form-control-label required" for="field-address1">
                                                Endereço
                                            </label>
                                            <div class="col-md-7">
                                                <input id="field-address1" class="form-control w-100" name="address1" type="text" value="" maxlength="128" required="" readonly="">
                                            </div>
                                        </div>
                                        <div class="form-group row hidden">
                                            <label class="col-md-3 form-control-label" for="field-number">Número</label>
                                            <div class="col-md-7">
                                                <input id="field-number" class="form-control w-100" name="number" type="text" value="" required="">
                                            </div>
                                        </div>
                                        <div class="form-group row ">
                                            <label class="col-md-3 form-control-label" for="field-address2">
                                                Bairro
                                            </label>
                                            <div class="col-md-7">
                                                <input id="field-address2" class="form-control w-100" name="address2" type="text" value="" maxlength="128" required="" readonly="">
                                            </div>
                                        </div>
                                        <div class="form-group row ">
                                            <label class="col-md-3 form-control-label required" for="field-city">
                                                Cidade
                                            </label>
                                            <div class="col-md-7">
                                                <input id="field-city" class="form-control w-100" name="city" type="text" value="" maxlength="64" required="" readonly="">
                                            </div>
                                        </div>
                                        <div class="form-group row ">
                                            <label class="col-md-3 form-control-label required" for="field-id_state">
                                                Estado
                                            </label>
                                            <div class="col-md-7">
                                                <select id="field-id_state" class="form-control w-100 form-control-select" name="id_state" required="">
                                                    <option value="" disabled="" selected="">Please choose</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row hidden">
                                            <label class="col-md-3 form-control-label" for="field-phone">
                                                Telefone
                                            </label>
                                            <div class="col-md-7">
                                                <input id="field-phone" class="form-control w-100" name="phone" type="tel" value="" maxlength="32">
                                            </div>
                                            <div class="col-md-2 form-control-comment">
                                                Opcional
                                            </div>
                                        </div>
                                        <div class="form-group row ">
                                            <label class="col-md-3 form-control-label" for="field-phone_mobile">
                                                Telefone celular
                                            </label>
                                            <div class="col-md-7">
                                                <input id="field-phone_mobile" class="form-control w-100" name="phone_mobile" type="text" value="" maxlength="32" required="">
                                            </div>
                                        </div>
                                    </section>
                                </form>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="agti-shipping-address-registration-form-submit" class="btn btn-primary form-control-submit float-xs-right" type="submit">Salvar</button>
            </div>
        </div>
    </div>
</div>
