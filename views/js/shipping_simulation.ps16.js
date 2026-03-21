$(function(){
	//corpo da tabela em que a simulação é exibida
	var tbody;
	var jqxhr;

	var is_quickview;

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
			return;
		}
		
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

				if (typeof agcliente_mask === 'undefined' || agcliente_mask) {
					$(id).mask('00000-000');
				}
			},
			error : function(){
				$(`${_class} button`).removeAttr('disabled');
			}
		});
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

		jqxhr = $.ajax({
			url : agti_url_simulate,
			data : $('#buy_block').serialize() + '&postcode=' + postcode,
			type : 'get',
			dataType : 'json',
			success : function(data){
				$(_class).html(data.simulation);

				if (typeof agcliente_mask === 'undefined' || agcliente_mask) {
					$(id).mask('00000-000');
				}
			},
			error : function(){
				$(`${_class} button`).removeAttr('disabled');
			}
		});
	}

	function simulate()
	{
		$('.agti_shipping_simulation button').attr('disabled', 'disabled');		

		if ($(this).closest('.quickview').length > 0) {
			is_quickview = true;
		} else {
			is_quickview = false;
		}

		//página do produto ou página de visualização rápida do produto
		if ($('body').is('#product') || is_quickview) {
			simulateProduct();
		} else if ($('body').is('#cart') || $('body').is('#order') || $('body').is('#order-opc')) {
			//página do carrinho de compras

			//aguarda alguns instantes para que o carrinho de compras seja atualizado
			//antes de a simulação ser realizada
			simulateCart();			
		}
	}

	$(document).on('click', '.agti_shipping_simulation button, .product_quantity_down, .product_quantity_up', function(e){
		simulate();

		var table = '';
		if($(this).closest('.product.agti_shipping_simulation').length > 0) {
			table = '.product';
		} else if($(this).closest('.cart.agti_shipping_simulation').length > 0) {
			table = '.cart';
		}
		
		tbody = $(`${table}.agti_shipping_simulation table tbody`);
		tbody.empty();

		e.stopPropagation();
		return false;
	});

	//botão de editar quantidade dos produtos na tela do carrinho de compras
	$(document).on('change', '.js-cart-line-product-quantity', function(){
		is_quickview = false;

		tbody = $('.cart.agti_shipping_simulation table tbody');
		tbody.empty();

		$('.cart.agti_shipping_simulation button').attr('disabled', 'disabled');

		setTimeout(simulateCart, 800);
	});

	//botão de remover produto na tela do carrinho de compras
	$(document).on('click', '.remove-from-cart', function(){
		is_quickview = false;

		tbody = $('.cart.agti_shipping_simulation table tbody');
		tbody.empty();

		$('.cart.agti_shipping_simulation button').attr('disabled', 'disabled');

		setTimeout(simulateCart, 800);
	});

	//reaplica a simulação de frete quando a combinação do produto for modificada
	function monitorAttributeChange()
	{
		if (typeof monitorAttributeChange.id_product_attribute === 'undefined') {
			monitorAttributeChange.id_product_attribute = $('#idCombination').val();
			return;
		}

		if (monitorAttributeChange.id_product_attribute != $('#idCombination').val()) {
			simulate();
		}

		monitorAttributeChange.id_product_attribute = $('#idCombination').val();
	}
	
	setInterval(function(){
		monitorAttributeChange();
	}, 300)
});