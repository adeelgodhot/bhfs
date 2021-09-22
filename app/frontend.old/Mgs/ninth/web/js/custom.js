require([
	'jquery',
	'waypoints'
], function(jQuery){
	(function($) {
		$.fn.appear = function(fn, options) {

			var settings = $.extend({

				//arbitrary data to pass to fn
				data: undefined,

				//call fn only on the first appear?
				one: true,

				// X & Y accuracy
				accX: 0,
				accY: 0

			}, options);

			return this.each(function() {

				var t = $(this);

				//whether the element is currently visible
				t.appeared = false;

				if (!fn) {

					//trigger the custom event
					t.trigger('appear', settings.data);
					return;
				}

				var w = $(window);

				//fires the appear event when appropriate
				var check = function() {

					//is the element hidden?
					if (!t.is(':visible')) {

						//it became hidden
						t.appeared = false;
						return;
					}

					//is the element inside the visible window?
					var a = w.scrollLeft();
					var b = w.scrollTop();
					var o = t.offset();
					var x = o.left;
					var y = o.top;

					var ax = settings.accX;
					var ay = settings.accY;
					var th = t.height();
					var wh = w.height();
					var tw = t.width();
					var ww = w.width();

					if (y + th + ay >= b &&
						y <= b + wh + ay &&
						x + tw + ax >= a &&
						x <= a + ww + ax) {

						//trigger the custom event
						if (!t.appeared) t.trigger('appear', settings.data);

					} else {

						//it scrolled out of view
						t.appeared = false;
					}
				};

				//create a modified fn with some additional logic
				var modifiedFn = function() {

					//mark the element as visible
					t.appeared = true;

					//is this supposed to happen only once?
					if (settings.one) {

						//remove the check
						w.unbind('scroll', check);
						var i = $.inArray(check, $.fn.appear.checks);
						if (i >= 0) $.fn.appear.checks.splice(i, 1);
					}

					//trigger the original fn
					fn.apply(this, arguments);
				};

				//bind the modified fn to the element
				if (settings.one) t.one('appear', settings.data, modifiedFn);
				else t.bind('appear', settings.data, modifiedFn);

				//check whenever the window scrolls
				w.scroll(check);

				//check whenever the dom changes
				$.fn.appear.checks.push(check);

				//check now
				(check)();
			});
		};

		//keep a queue of appearance checks
		$.extend($.fn.appear, {

			checks: [],
			timeout: null,

			//process the queue
			checkAll: function() {
				var length = $.fn.appear.checks.length;
				if (length > 0) while (length--) ($.fn.appear.checks[length])();
			},

			//check the queue asynchronously
			run: function() {
				if ($.fn.appear.timeout) clearTimeout($.fn.appear.timeout);
				$.fn.appear.timeout = setTimeout($.fn.appear.checkAll, 20);
			}
		});

		//run checks when these methods are called
		$.each(['append', 'prepend', 'after', 'before', 'attr',
			'removeAttr', 'addClass', 'removeClass', 'toggleClass',
			'remove', 'css', 'show', 'hide'], function(i, n) {
			var old = $.fn[n];
			if (old) {
				$.fn[n] = function() {
					var r = old.apply(this, arguments);
					$.fn.appear.run();
					return r;
				}
			}
		});
		$(window).resize(function(){
            /* Main Content Height */
            var windowHeight = $(window).height();
            var headerHeight = $('header').height();
            var footerHeight = $('footer').height();
            var totalHeight = footerHeight + headerHeight;
            if(windowHeight > totalHeight){
                $('.main-wrapper > main').css('min-height', (windowHeight - totalHeight))
            }
        });
		$(document).ready(function(){
			$("[data-appear-animation]").each(function() {
				$(this).addClass("appear-animation");
				if($(window).width() > 767) {
					$(this).appear(function() {

						var delay = ($(this).attr("data-appear-animation-delay") ? $(this).attr("data-appear-animation-delay") : 1);

						if(delay > 1) $(this).css("animation-delay", delay + "ms");
						$(this).addClass($(this).attr("data-appear-animation"));
						$(this).addClass("animated");

						setTimeout(function() {
							$(this).addClass("appear-animation-visible");
						}, delay);

					}, {accX: 0, accY: -150});
				} else {
					$(this).addClass("appear-animation-visible");
				}
			});
            
            $(document).on("click","#show-menu-product-tabs",function(e){
                var toggleElement = $(this).attr('data-menutogle');
                $(toggleElement).slideToggle('slow');
            });
            $(document).on("click",".menu-product-tabs a",function(e){
                if($(window).width() < 992){
                    $(this).parent().parent().slideUp('slow');
                }
            });
            
            $(document).on("click",".cl-mes",function(e){
                $(this).parent().slideUp();
            });
            
            $(document).on("click",".mobile-navigation li.parent > a",function(e){
                var containerIcon = $(".ui-menu-icon");
                if (!containerIcon.is(e.target) && containerIcon.has(e.target).length === 0) {
                    return;
                }else{
                     e.preventDefault();
                    $(this).parent().toggleClass('enb');
                }
            });
            
            /* Login Page */
            var containerHeight = $('.login-container').height();
            $('.login-container > .block').css('min-height', containerHeight);
            /* Parallax Home */
            var controlsParallax = '<div class="controls-parallax"><div class="control-up"><i class="lnr lnr-arrow-up"></i></div><div class="control-down"><i class="lnr lnr-arrow-down"></i></div></div>';
            $('.parallax-home').append(controlsParallax);
            $(document).on("click",".control-up",function() {
                var indexUp = $(this).parent().parent().index('.parallax') - 1;
                var moveElement = $('.parallax-home').eq(indexUp);
                $('html, body').animate({
                    scrollTop: moveElement.offset().top
                }, 500);
            });
            $(document).on("click",".control-down",function() {
                var indexDown = $(this).parent().parent().index('.parallax') + 1;
                var moveElement = $('.parallax-home').eq(indexDown);
                if(!moveElement.length){
                    moveElement = $('.parallax-home').eq(0);
                }
                $('html, body').animate({
                    scrollTop: moveElement.offset().top
                }, 500);
            });
            
			// MEGAMENU JS
			$('.nav-main-menu li.mega-menu-fullwidth.menu-2columns').hover(function(){
				if($(window).width() > 1199){
					var position = $(this).position();
					var widthMenu = $("#mainMenu").width() - position.left;
					$(this).find('ul.dropdown-menu').width(widthMenu);
				}
			});
			// END MEGAMENU
		});
		
        $(window).resize(function(){
            /* Login page */
            $('.login-container > .block').css('min-height', 0);
            var containerHeight = $('.login-container').height();
            $('.login-container > .block').css('min-height', containerHeight);
        });
		
        /* Mobile & tablet click product grid => Show button first */
        $(document).on("click",".products-grid:not(.grid-template-4) .product-top > a",function(e){
            if($(window).width() < 992){
                if(!$(this).hasClass('active')){
                    $('.products-grid .product-item-info .product-top > a.active').removeClass('active');
                    event.returnValue = false;
                    event.preventDefault();
                    $(this).addClass('active');
                }
            }
        });
	})(jQuery);
});

require([
	'jquery',
	'magnificPopup'
], function($){
    $(document).ready(function() {
        $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
            type: "iframe",
            mainClass: "mfp-img-gallery",
            preloader: true,
            fixedContentPos: true,
        });
    });
});

function reInitQuickview($, prodUrl){
	if (!prodUrl.length) {
		return false;
	}
	var url = QUICKVIEW_BASE_URL + 'mgs_quickview/index/updatecart';
	$.magnificPopup.open({
		items: {
			src: prodUrl
		},
		type: 'iframe',
		removalDelay: 300,
		mainClass: 'mfp-fade',
		closeOnBgClick: true,
		preloader: true,
		tLoading: '',
		callbacks: {
			open: function () {
				$('.mfp-preloader').css('display', 'block');
			},
			beforeClose: function () {
				$('[data-block="minicart"]').trigger('contentLoading');
				$.ajax({
					url: url,
					method: "POST"
				});
			},
			close: function () {
				$('.mfp-preloader').css('display', 'none');
			}
		}
	});
}

function setLocation(url){
    require([
        "jquery",
        "mage/mage"
    ], function($){
        $($.mage.redirect(url, "assign", 0));
    });
}
