if((typeof(CATALOG_AJAX) !== 'undefined') && CATALOG_AJAX){
	define([
		"jquery",
		"jquery/ui",
		"mage/translate",
		"magnificPopup",
		"Magento_Catalog/js/product/list/toolbar"
	], function($, ui) {
		/**
		 * ProductListToolbarForm Widget - this widget is setting cookie and submitting form according to toolbar controls
		 */
		$.widget('mage.productListToolbarForm', $.mage.productListToolbarForm, {

			options:
			{
				modeControl: '[data-role="mode-switcher"]',
				directionControl: '[data-role="direction-switcher"]',
				orderControl: '[data-role="sorter"]',
				limitControl: '[data-role="limiter"]',
				mode: 'product_list_mode',
				direction: 'product_list_dir',
				order: 'product_list_order',
				limit: 'product_list_limit',
				pager: 'p',
				modeDefault: 'grid',
				directionDefault: 'asc',
				orderDefault: 'position',
				limitDefault: '9',
				pagerDefault: '1',
				productsToolbarControl:'.toolbar.toolbar-products',
				productsListBlock: '.products.wrapper',
				layeredNavigationFilterBlock: '.block.filter',
				filterItemControl: '.block.filter .item a, .block.filter .filter-clear,.block.filter .swatch-option-link-layered',
				url: ''
			},

			_create: function () {
				this._super();
				this._bind($(this.options.pagerControl), this.options.pager, this.options.pagerDefault);
				$(this.options.filterItemControl)
					.off('click.'+this.namespace+'productListToolbarForm')
					.on('click.'+this.namespace+'productListToolbarForm', {}, $.proxy(this.applyFilterToProductsList, this))
				;
				//console.log('toolbar');
			},
			_bind: function (element, paramName, defaultValue) {
				/**
				 * Prevent double binding of these events because this component is being applied twice in the UI
				 */
				if (element.is('select')) {
					element.on('change', {
						paramName: paramName,
						'default': defaultValue
					}, $.proxy(this._processSelect, this));
				} else {
					element
						.off('click.'+this.namespace+'productListToolbarForm')
						.on('click.'+this.namespace+'productListToolbarForm', {paramName: paramName, default: defaultValue}, $.proxy(this._processLink, this));
				}
			},
			/**
			 * @param {jQuery.Event} event
			 * @private
			 */
			_processSelect: function (event) {
				this.changeUrlDefault(
					event.data.paramName,
					event.currentTarget.options[event.currentTarget.selectedIndex].value,
					event.data.default
				);
			},
			
			/**
			 * @param {String} paramName
			 * @param {*} paramValue
			 * @param {*} defaultValue
			 */
			changeUrlDefault: function (paramName, paramValue, defaultValue) {
				var decode = window.decodeURIComponent,
					urlPaths = this.options.url.split('?'),
					baseUrl = urlPaths[0],
					urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
					paramData = {},
					parameters, i;

				for (i = 0; i < urlParams.length; i++) {
					parameters = urlParams[i].split('=');
					paramData[decode(parameters[0])] = parameters[1] !== undefined ?
						decode(parameters[1].replace(/\+/g, '%20')) :
						'';
				}
				paramData[paramName] = paramValue;

				if (paramValue == defaultValue) { //eslint-disable-line eqeqeq
					delete paramData[paramName];
				}
				paramData = $.param(paramData);

				location.href = baseUrl + (paramData.length ? '?' + paramData : '');
			},
			
			applyFilterToProductsList: function (evt) {
				var link = $(evt.currentTarget);
				var urlParts = link.attr('href').split('?');
				this.makeAjaxCall(urlParts[0], urlParts[1]);
				evt.preventDefault();
			},
			updateUrl: function (url, paramData) {
				if (!url) {
					return;
				}
				if (paramData && paramData.length > 0) {
					url += '?' + paramData;
				}
				if (typeof history.replaceState === 'function') {
					history.replaceState(null, null, url);
				}
			},

			getParams: function (urlParams, paramName, paramValue, defaultValue) {
				var paramData = {},
					parameters;

				for (var i = 0; i < urlParams.length; i++) {
					parameters = urlParams[i].split('=');
					if (parameters[1] !== undefined) {
						paramData[parameters[0]] = parameters[1];
					} else {
						paramData[parameters[0]] = '';
					}
				}

				paramData[paramName] = paramValue;
				if (paramValue == defaultValue) {
					delete paramData[paramName];
				}
				return window.decodeURIComponent($.param(paramData).replace(/\+/g, '%20'));
			},
			_updateContent: function (content, pageLayout) {
				$(this.options.productsToolbarControl).remove();
				
				if(content.products_list){
					$(this.options.productsListBlock)
						.replaceWith(content.products_list)
					;
				}

				if(content.filters){
					$(this.options.layeredNavigationFilterBlock).replaceWith(content.filters)
				}
				
				if(pageLayout=='1column'){
					$('.category-product-actions:first').remove();
				}

				$('body').trigger('contentUpdated');
				
				setTimeout(this.reInitFunction(), 100);
			},

			updateContent: function (content, pageLayout) {
				$('html, body').animate(
					{
						scrollTop: $(this.options.productsToolbarControl+":first").offset().top
					},
					100,
					'swing',
					this._updateContent(content, pageLayout)
				);
			},


			changeUrl: function (paramName, paramValue, defaultValue) {
				if(paramName=='product_list_dir' || paramName=='product_list_mode'){
					this.changeUrlDefault(
						paramName,
						paramValue,
						defaultValue
					);
				}else{
					var urlPaths = this.options.url.split('?'),
						baseUrl = urlPaths[0],
						urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
						paramData = this.getParams(urlParams, paramName, paramValue, defaultValue);

					this.makeAjaxCall(baseUrl, paramData);
				}
			},

			makeAjaxCall: function (baseUrl, paramData) {
				var self = this;
				$.ajax({
					url: baseUrl,
					data: (paramData && paramData.length > 0 ? paramData + '&catalogajax=1' : 'catalogajax=1'),
					type: 'get',
					dataType: 'json',
					cache: true,
					showLoader: true,
					timeout: 10000
				}).done(function (response) {
					if (response.success) {
						self.updateUrl(baseUrl, paramData);
						self.updateContent(response.html, response.page_layout);
					} else {
						var msg = response.error_message;
						alert(msg);
					}
				}).fail(function (error) {
					alert($.mage.__('Sorry, something went wrong. Please try again later.'));
				});
			},
			
			initAjaxAddToCart: function(tag, actionId, url, data){
				
					data.push({
						name: 'action_url',
						value: tag.attr('action')
					});
						
					$addToCart = tag.find('.tocart').text();
						
					var self = this;
					data.push({
						name: 'ajax',
						value: 1
					});
					
					$.ajax({
						url: url,
						data: $.param(data),
						type: 'post',
						dataType: 'json',
						beforeSend: function(xhr, options) {
							if(ajaxCartConfig.animationType){
								$('#mgs-ajax-loading').show();
							}else{
								if(tag.find('.tocart').length){
									tag.find('.tocart').addClass('disabled');
									tag.find('.tocart').text('Adding...');
									tag.find('.tocart').attr('title','Adding...');
								}else{
									tag.addClass('disabled');
									tag.text('Adding...');
									tag.attr('title','Adding...');
								} 
								
							}
						},
						success: function(response, status) {
							if (status == 'success') {
								if(response.backUrl){
									data.push({
										name: 'action_url',
										value: response.backUrl
									});
									self.initAjaxAddToCart(tag, 'catalog-add-to-cart-' + $.now(), response.backUrl, data);
								}else{
									if (response.ui) {
										if(response.productView){
											$('#mgs-ajax-loading').hide();
												$.magnificPopup.open({
													items: {
														src: response.ui,
														type: 'iframe'
													},
													mainClass: 'success-ajax--popup',
													closeOnBgClick: false,
													preloader: true,
													tLoading: '',
													callbacks: {
														open: function() {
															$('#mgs-ajax-loading').hide();
															$('.mfp-preloader').css('display', 'block');
														},
														beforeClose: function() {
															var url_cart_update = ajaxCartConfig.updateCartUrl;
															$('[data-block="minicart"]').trigger('contentLoading');
															$.ajax({
																url: url_cart_update,
																method: "POST"
															});
														},
														close: function() {
															$('.mfp-preloader').css('display', 'none');
														},
														afterClose: function() {
															if(!response.animationType) {
																var $source = '';
																if(tag.find('.tocart').length){
																	tag.find('.tocart').removeClass('disabled');
																	tag.find('.tocart').text($addToCart);
																	tag.find('.tocart').attr('title',$addToCart);
																	if(tag.closest('.product-item-info').length){
																		$source = tag.closest('.product-item-info');
																		var width = $source.outerWidth();
																		var height = $source.outerHeight();
																	}else{
																		$source = tag.find('.tocart');
																		var width = 300;
																		var height = 300;
																	}
																	
																}else{
																	tag.removeClass('disabled');
																	tag.text($addToCart);
																	tag.attr('title',$addToCart);
																	$source = tag.closest('.product-item-info');
																	var width = $source.outerWidth();
																	var height = $source.outerHeight();
																}
																
																$('html, body').animate({
																	'scrollTop' : $(".minicart-wrapper").position().top
																},2000);
																var $animatedObject = $('<div class="flycart-animated-add" style="position: absolute;z-index: 99999;">'+response.image+'</div>');
																var left = $source.offset().left;
																var top = $source.offset().top;
																$animatedObject.css({top: top-1, left: left-1, width: width, height: height});
																$('html').append($animatedObject);
																var divider = 3;
																var gotoX = $(".minicart-wrapper").offset().left + ($(".minicart-wrapper").width() / 2) - ($animatedObject.width()/divider)/2;
																var gotoY = $(".minicart-wrapper").offset().top + ($(".minicart-wrapper").height() / 2) - ($animatedObject.height()/divider)/2;                                               
																$animatedObject.animate({
																	opacity: 0.6,
																	left: gotoX,
																	top: gotoY,
																	width: $animatedObject.width()/2,
																	height: $animatedObject.height()/2
																}, 2000,
																function () {
																	$(".minicart-wrapper").fadeOut('fast', function () {
																		$(".minicart-wrapper").fadeIn('fast', function () {
																			$animatedObject.fadeOut('fast', function () {
																				$animatedObject.remove();
																			});
																		});
																	});
																});
															}
														}
													}
												});
										}else{
											var $content = '<div class="popup__main popup--result">'+response.ui + response.related + '</div>';
											if(response.animationType) {
												$('#mgs-ajax-loading').hide();
												$.magnificPopup.open({
													mainClass: 'success-ajax--popup',
													items: {
														src: $content,
														type: 'inline'
													},
													callbacks: {
														open: function() {
															$('#mgs-ajax-loading').hide();
														},
														beforeClose: function() {
															var url_cart_update = ajaxCartConfig.updateCartUrl;
															$('[data-block="minicart"]').trigger('contentLoading');
															$.ajax({
																url: url_cart_update,
																method: "POST"
															});
														}  
													}
												});
											}else{
												var $source = '';
												if(tag.find('.tocart').length){
													tag.find('.tocart').removeClass('disabled');
													tag.find('.tocart').text($addToCart);
													tag.find('.tocart').attr('title',$addToCart);
													if(tag.closest('.product-item-info').length){
														$source = tag.closest('.product-item-info');
														var width = $source.outerWidth();
														var height = $source.outerHeight();
													}else{
														$source = tag.find('.tocart');
														var width = 300;
														var height = 300;
													}
													
												}else{
													tag.removeClass('disabled');
													tag.text($addToCart);
													tag.attr('title',$addToCart);
													$source = tag.closest('.product-item-info');
													var width = $source.outerWidth();
													var height = $source.outerHeight();
												}
												
												$('html, body').animate({
													'scrollTop' : $(".minicart-wrapper").position().top
												},2000);
												var $animatedObject = $('<div class="flycart-animated-add" style="position: absolute;z-index: 99999;">'+response.image+'</div>');
												var left = $source.offset().left;
												var top = $source.offset().top;
												$animatedObject.css({top: top-1, left: left-1, width: width, height: height});
												$('html').append($animatedObject);
												var divider = 3;
												var gotoX = $(".minicart-wrapper").offset().left + ($(".minicart-wrapper").width() / 2) - ($animatedObject.width()/divider)/2;
												var gotoY = $(".minicart-wrapper").offset().top + ($(".minicart-wrapper").height() / 2) - ($animatedObject.height()/divider)/2;                                               
												$animatedObject.animate({
													opacity: 0.6,
													left: gotoX,
													top: gotoY,
													width: $animatedObject.width()/2,
													height: $animatedObject.height()/2
												}, 2000,
												function () {
													$(".minicart-wrapper").fadeOut('fast', function () {
														$(".minicart-wrapper").fadeIn('fast', function () {
															$animatedObject.fadeOut('fast', function () {
																$animatedObject.remove();
															});
														});
													});
												});
											}
										}
									}
								}                            
							}
						},
						error: function() {
							$('#mgs-ajax-loading').hide();
							window.location.href = ajaxCartConfig.redirectCartUrl;
						}
					});
			},
			
			reInitFunction: function(){
				var formKey = $("input[name*='form_key']").first().val();
				$("input[name*='form_key']").val(formKey);
				$(".mgs-quickview").bind("click", function() {
					var b = $(this).attr("data-quickview-url");
					b.length && reInitQuickview($, b)
				});
                
                $("img.lazy").unveil(25, function(){
                    var self = $(this);
                    setTimeout(function(){
                        self.removeClass('lazy');
                    }, 0);
                });
				
				var thisClass = this;
				
				$('button.tocart').click(function(event){
					event.preventDefault();
					tag = $(this).parents('form:first');
					
					var data = tag.serializeArray();
					thisClass.initAjaxAddToCart(tag, 'catalog-add-to-cart-' + $.now(), tag.attr('action'), data);
					
					
				});
				
				this._create();
				
				
			}
			
		});

		return $.mage.productListToolbarForm;
	});
}else{
	define([
		'jquery',
		'jquery/ui'
	], function ($) {
		'use strict';

		/**
		 * ProductListToolbarForm Widget - this widget is setting cookie and submitting form according to toolbar controls
		 */
		$.widget('mage.productListToolbarForm', {

			options: {
				modeControl: '[data-role="mode-switcher"]',
				directionControl: '[data-role="direction-switcher"]',
				orderControl: '[data-role="sorter"]',
				limitControl: '[data-role="limiter"]',
				mode: 'product_list_mode',
				direction: 'product_list_dir',
				order: 'product_list_order',
				limit: 'product_list_limit',
				modeDefault: 'grid',
				directionDefault: 'asc',
				orderDefault: 'position',
				limitDefault: '9',
				url: ''
			},

			/** @inheritdoc */
			_create: function () {
				this._bind($(this.options.modeControl), this.options.mode, this.options.modeDefault);
				this._bind($(this.options.directionControl), this.options.direction, this.options.directionDefault);
				this._bind($(this.options.orderControl), this.options.order, this.options.orderDefault);
				this._bind($(this.options.limitControl), this.options.limit, this.options.limitDefault);
			},

			/** @inheritdoc */
			_bind: function (element, paramName, defaultValue) {
				if (element.is('select')) {
					element.on('change', {
						paramName: paramName,
						'default': defaultValue
					}, $.proxy(this._processSelect, this));
				} else {
					element.on('click', {
						paramName: paramName,
						'default': defaultValue
					}, $.proxy(this._processLink, this));
				}
			},

			/**
			 * @param {jQuery.Event} event
			 * @private
			 */
			_processLink: function (event) {
				event.preventDefault();
				this.changeUrl(
					event.data.paramName,
					$(event.currentTarget).data('value'),
					event.data.default
				);
			},

			/**
			 * @param {jQuery.Event} event
			 * @private
			 */
			_processSelect: function (event) {
				this.changeUrl(
					event.data.paramName,
					event.currentTarget.options[event.currentTarget.selectedIndex].value,
					event.data.default
				);
			},

			/**
			 * @param {String} paramName
			 * @param {*} paramValue
			 * @param {*} defaultValue
			 */
			changeUrl: function (paramName, paramValue, defaultValue) {
				var decode = window.decodeURIComponent,
					urlPaths = this.options.url.split('?'),
					baseUrl = urlPaths[0],
					urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
					paramData = {},
					parameters, i;

				for (i = 0; i < urlParams.length; i++) {
					parameters = urlParams[i].split('=');
					paramData[decode(parameters[0])] = parameters[1] !== undefined ?
						decode(parameters[1].replace(/\+/g, '%20')) :
						'';
				}
				paramData[paramName] = paramValue;

				if (paramValue == defaultValue) { //eslint-disable-line eqeqeq
					delete paramData[paramName];
				}
				paramData = $.param(paramData);

				location.href = baseUrl + (paramData.length ? '?' + paramData : '');
			}
		});

		return $.mage.productListToolbarForm;
	});
}