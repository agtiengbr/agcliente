$(document).on('DOMContentLoaded', () => {
    $('#invalid_cep').hide();
})

var trElementSelectedCarrier;

function selectCarrier(trElement) {

    // Adquiri o CEP a ser simulado
    id = '#cart_agti_shipping_simulation_postcode';
    _class = '.cart.agti_shipping_simulation';

    postcode = $(id).val();

    // Verifica se o CEP a ser simulado existe na lista de endereços do usuário
    var existe = agti_customer_adresses.some(function(address) {
        return address.postcode === postcode;
    });

    if (existe) {
        selectCarrierRequest(trElement);
    } else {
        openAddressModal(postcode, trElement);
    }
}

function openAddressModal(postcode, trElement) {
    $.each(agti_states_brazil, function (index, state) {
        if (state.active === "1") {
            var option = new Option(state.name, state.id_state);
            $(option).attr('data-iso', state.iso_code);
            $('#agti-shipping-address-registration-modal #field-id_state').append(option);
        }
    });
    $('#agti-shipping-address-registration-modal').modal('show');
    
    $('#agti-shipping-address-registration-modal #field-phone_mobile').mask('(00) 00000-0000');
    $('#agti-shipping-address-registration-modal #field-postcode').mask('00000-000');
    $('#agti-shipping-address-registration-modal #field-postcode').val(postcode);

    fillModalAddressFields(postcode);

    $('.agti_shipping_simulation button').removeAttr('disabled', 'disabled');
    trElementSelectedCarrier = trElement;
    return;
}

function selectAddressModalState(iso_code) {
    var $selectElement = $('#agti-shipping-address-registration-modal #field-id_state');
    
    $selectElement.find('option').each(function() {
        if ($(this).data('iso') === iso_code) {
            $selectElement.val($(this).val()).change();
            return false;
        }
    });
    
}

function fillModalAddressFields(postcode) {
    showAddressLoading();
    
    $.ajax({
        url: `${agcliente_address_search_url}?postcode=${postcode.replace(/\D/g, '')}`,
        type: 'GET',
        success: function(data) {
            var result = JSON.parse(data);
            if (result) {
                $('#agti-shipping-address-registration-modal #field-address1').val(result.street);
                $('#agti-shipping-address-registration-modal #field-address2').val(result.district);
                $('#agti-shipping-address-registration-modal #field-city').val(result.city);

                selectAddressModalState(result.state);
            } else {
                showAddressErrorMessage('O CEP informado não foi encontrado, tente novamente.');
            }
        },
        error: function() {
            showAddressErrorMessage('Ocorreu um erro inesperado, tente novamente mais tarde.');
        }
    }).always(() => {
        hideAddressLoading();
    });
}

function selectCarrierRequest(trElement) {
    var carrierId = $(trElement).data('carrier-id');

    var url = agti_url_simulate + '?set_carrier&id_carrier=' + carrierId + '&postcode=' + $('#cart_agti_shipping_simulation_postcode').val();

    $('.agti_simulation_loading').show();
    $('.agti_shipping_simulation').addClass('table-loading');
    $('.agti_shipping_simulation button').attr('disabled', 'disabled');

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                window.location.reload();
            } else if (data.error === 'O cliente não possui esse CEP cadastrado.') {
                openAddressModal($('#cart_agti_shipping_simulation_postcode').val());
            }
        }
    }).always(() => {
        $('.agti_simulation_loading').hide();
        $('.agti_shipping_simulation').removeClass('table-loading');
        $('.agti_shipping_simulation button').removeAttr('disabled', 'disabled');
    });
}

function showAddressSuccessMessage(message) {
    $('#agti-shipping-address-success-message-box').text(message);
}

function showAddressErrorMessage(message) {
    $('#agti-shipping-address-error-message-box').text(message);
}

function showAddressLoading() {
    $('#agti-shipping-address-registration-modal').addClass('modal-loading');
    $('#agti-shipping-address-loading').show();
}

function hideAddressLoading() {
    $('#agti-shipping-address-registration-modal').removeClass('modal-loading');
    $('#agti-shipping-address-loading').hide();
}

function clearAndHideAddressMessages() {
    $('#agti-shipping-address-error-message-box').text('');
    $('#agti-shipping-address-success-message-box').text('');
}

$(function(){
	//corpo da tabela em que a simulação é exibida
	var tbody;
	var jqxhr;

	var is_quickview;

	if ($('.simulation tbody tr:visible').length == 0) {
		simulate();
	}

	$('#agti_shipping_simulation_postcode').mask('00000-000');

    function agAjaxAddAddress(formData) {
        showAddressLoading();
        return new Promise((resolve, reject) => {
            var url = agti_url_simulate + '?add_address';

            $.ajax({
                url: url,
                data: formData,
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        showAddressSuccessMessage('Endereço salvo com sucesso.');
                        setTimeout(function() {
                            $('#agti-shipping-address-registration-modal').modal('hide');
                            selectCarrierRequest(trElementSelectedCarrier);
                        }, 2000);
                        resolve(data);
                    } else if (data.error) {
                        showAddressErrorMessage(data.error);
                        reject(data.error);
                    } else {
                        showAddressErrorMessage('Ocorreu um erro inesperado.');
                        reject('Ocorreu um erro inesperado.');
                    }
                },
                error: function(){
                    showAddressErrorMessage('Ocorreu um erro inesperado.');
                    reject("Ocorreu um erro inesperado.");
                }
            }).always(() => {
                hideAddressLoading();
            })
        });
    }

	function simulateCart()
	{
		var id = '#agti_shipping_simulation_postcode';
		var _class = '.agti_shipping_simulation';
		if (jqxhr != null) {
			jqxhr.abort();
		}

		var postcode = $(id).val();
		if(postcode === undefined) {
			id = '#cart_agti_shipping_simulation_postcode';
			_class = '.cart.agti_shipping_simulation';

			postcode = $(id).val();
		}

		if (postcode == '') {
			$('.agti_shipping_simulation button').removeAttr('disabled', 'disabled');
			return;
		}

        $('.agti_simulation_loading').show();
        $('.agti_shipping_simulation').addClass('table-loading');
		
		jqxhr = $.ajax({
			url : agti_url_simulate,
			data : {
				cart: true,
				postcode : postcode
			},
			type : 'get',
			dataType : 'json',
			success : function(data){
				$(_class).html(data.simulation);
				$(id).mask('00000-000');
			},
			error : function(){
				$(`${_class} button`).removeAttr('disabled');
			}
		}).always(() => {
            $('.agti_simulation_loading').hide();
            $('.agti_shipping_simulation').removeClass('table-loading');
        });
	}

    function isValidCEP(cep) {
        cep = cep.replace(/\D/g, '');
        if (cep.length !== 8) {
          return false;
        }
      
        const regex = /^[0-9]{8}$/;
      
        if (!regex.test(cep)) {
          return false;
        }
      
        return true;
    }      

	function simulateProduct()
	{
		var id = '#agti_shipping_simulation_postcode';
		var _class = '.agti_shipping_simulation';
		if (jqxhr != null) {
			jqxhr.abort();
		}

		var postcode = $(id).val();
		if(postcode === undefined) {
			id = '#product_agti_shipping_simulation_postcode';
			_class = '.product.agti_shipping_simulation';

			postcode = $(id).val();
		}

		if (postcode == '') {
			$('.agti_shipping_simulation button').removeAttr('disabled', 'disabled');
			return;
		}

        $('.agti_simulation_loading').show();
        $('.agti_shipping_simulation').addClass('table-loading');

		jqxhr = $.ajax({
			url : agti_url_simulate,
			data : $('#add-to-cart-or-refresh').serialize() + '&postcode=' + postcode + '&id_product_attribute=' + $('.agti_shipping_simulation').attr('data-id-product-attribute'),
			type : 'get',
			dataType : 'json',
			success : function(data){
				var simulation = $(data.simulation);

				$(_class).html(simulation.html());
				$(id).mask('00000-000');
			},
			error : function(){
				$(`${_class} button`).removeAttr('disabled');
			}
		}).always(() => {
            $('.agti_simulation_loading').hide();
            $('.agti_shipping_simulation').removeClass('table-loading');
        });
	}

	function simulate()
	{
		$('.agti_shipping_simulation button').attr('disabled', 'disabled');

		//página do produto ou página de visualização rápida do produto
		if ($('body').is('#product') || is_quickview) {
			simulateProduct();
		} else if ($('body').is('#cart') || $('body').is('#order')) {
			//página do carrinho de compras

			//aguarda alguns instantes para que o carrinho de compras seja atualizado
			//antes de a simulação ser realizada
			simulateCart();			
		}
	}

    $(document).on('click', '#agti-shipping-address-registration-form-submit', function (e) {
        e.preventDefault(); // Previne a ação padrão do botão
    
        $('#agti-shipping-address-registration-modal #field-postcode').prop('disabled', false);
        var formDataArray = $('#agti-shipping-address-registration-form').serializeArray();
    
        var formDataObject = formDataArray.reduce(function (accumulator, currentItem) {
            accumulator[currentItem.name] = currentItem.value;
            return accumulator;
        }, {});
    
        $('#agti-shipping-address-registration-modal #field-postcode').prop('disabled', true);
        agAjaxAddAddress(formDataObject)
        console.log(formDataObject);
    });
       

	$(document).on('click', '.agti_shipping_simulation button, .product_quantity_down, .product_quantity_up', function(e){
		if ($(this).closest('.quickview').length > 0) {
			is_quickview = true;
		} else {
			is_quickview = false;
		}

        var table = '';

        if($(this).closest('.product.agti_shipping_simulation').length > 0) {
            table = 'product';
        } else if($(this).closest('.cart.agti_shipping_simulation').length > 0) {
            table = 'cart';
        }

        tbody = $(`.${table}.agti_shipping_simulation table tbody`);
        
        if(isValidCEP($(`#${table}_agti_shipping_simulation_postcode`).val())) {
            $('#invalid_cep').hide();
            $('.address').show();
            simulate();
            tbody.empty();
        } else {
            tbody.empty();
            $('#invalid_cep').show();
            $('.address').hide();
        }

		e.stopPropagation();
		return false;
	});

	//botão de editar quantidade dos produtos na tela do carrinho de compras
	$(document).on('change', '.js-cart-line-product-quantity', function(){
		is_quickview = false;

		tbody = $('.cart.agti_shipping_simulation table tbody');
		tbody.empty();

		$('.cart.agti_shipping_simulation button').attr('disabled', 'disabled');

		setTimeout(simulate, 800);
	});

	//botão de remover produto na tela do carrinho de compras
	$(document).on('click', '.remove-from-cart', function(){
		is_quickview = false;

		tbody = $('.cart.agti_shipping_simulation table tbody');
		tbody.empty();

		$('.cart.agti_shipping_simulation button').attr('disabled', 'disabled');

		setTimeout(simulate, 800);
	});
})
