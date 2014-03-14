/*--------------------------------------------------------------------------/
* @Author		KulerThemes.com http://www.kulerthemes.com
* @Copyright	Copyright (C) 2012 - 2013 KulerThemes.com. All rights reserved.
* @License		KulerThemes.com Proprietary License
/---------------------------------------------------------------------------*/

var _cartTimer;
function addToCart(product_id, quantity) {
    quantity = typeof(quantity) != 'undefined' ? quantity : 1;

    clearTimeout(_cartTimer);

    $.ajax({
        url: 'index.php?route=checkout/cart/add',
        type: 'post',
        data: 'product_id=' + product_id + '&quantity=' + quantity,
        dataType: 'json',
        success: function(json) {
            $('.success, .warning, .attention, .information, .error').remove();

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['success']) {
                $('#notification')
                    .html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>')
                    .addClass('active');

                $('.success').fadeIn('slow', function () {
                    _cartTimer = setTimeout(function () {
                        $('#notification').removeClass('active');
                    }, 2500);
                });

                $('#cart-total').html(json['total']);
            }
        }
    });
}

var _wishListTimer;
function addToWishList(product_id) {
    clearTimeout(_wishListTimer);

    $.ajax({
        url: 'index.php?route=account/wishlist/add',
        type: 'post',
        data: 'product_id=' + product_id,
        dataType: 'json',
        success: function(json) {
            $('.success, .warning, .attention, .information').remove();

            if (json['success']) {
                $('#notification')
                    .html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>')
                    .addClass('active');

                $('.success').fadeIn('slow', function () {
                    _wishListTimer = setTimeout(function () {
                        $('#notification').removeClass('active');
                    }, 500);
                });

                $('#wishlist-total').html(json['total']);
            }
        }
    });
}

var _compareTimer;
function addToCompare(product_id) {
    clearTimeout(_compareTimer);

    $.ajax({
        url: 'index.php?route=product/compare/add',
        type: 'post',
        data: 'product_id=' + product_id,
        dataType: 'json',
        success: function(json) {
            $('.success, .warning, .attention, .information').remove();

            if (json['success']) {
                $('#notification')
                    .html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>')
                    .addClass('active');

                $('.success').fadeIn('slow', function () {
                    _compareTimer = setTimeout(function () {
                        $('#notification').removeClass('active');
                    }, 500);
                });

                $('#compare-total').html(json['total']);
            }
        }
    });
}

$(document).ready(function () {

	// Product hover
	$('#container .box .product-grid > div').hover(function () {
		var totalHeight = 0;
		$(this).find('.details').children().each(function() {
			totalHeight = totalHeight + $(this).height();
		});
		$(this).find('.details').height(totalHeight);
		$(this).css('marginBottom', -1 * totalHeight);
	}, function () {
		$(this).css('marginBottom', 0);
		$(this).find('.details').height(0);
	});

	$('.megamenu .product-grid > div').hover(function () {
		var totalHeight = 0;
		$(this).find('.details').children().each(function() {
			totalHeight = totalHeight + $(this).height();
		});
		$(this).find('.details').height(totalHeight);
		$(this).css('marginBottom', -1 * totalHeight);
	}, function () {
		$(this).css('marginBottom', 0);
		$(this).find('.details').height(0);
	});

	// Setup mobile main menu
	$('#btn-mobile-toggle').toggle(
		function() {
			$(this).parent().removeClass('collapse').addClass('expand').find('.item').slideDown();
		},
		function() {
			$(this).parent().removeClass('expand').addClass('collapse').find('.item:not(.active)').slideUp();
		}
	);

	// Setup mobile tabs
	$('#btn-tabs-toggle').toggle(
		function() {
			$(this).parent().removeClass('collapse').addClass('expand').find('.ui-state-default').slideDown();
		},
		function() {
			$(this).parent().removeClass('expand').addClass('collapse').find('.ui-state-default:not(.ui-state-active)').slideUp();
		}
	);

	//Smooth scroll to on page element
	$(".review a").click(function(event){
		event.preventDefault();
		//calculate destination place
		var dest=0;
		if($(this.hash).offset().top > $(document).height()-$(window).height()){
			dest=$(document).height()-$(window).height();
		} else {
			dest=$(this.hash).offset().top;
		}
		//go to destination
		$('html,body').animate({scrollTop:dest}, 500,'swing');
     });

	// Smooth drop down for IE
	if ($.browser.msie) {
		
		// Main menu
		$('.primary-define .mainmenu li').hover(
			function() {
				$(this).find('.dropdown').slideDown(200);
			},
			function() {
				$(this).find('.dropdown').slideUp(200);
			}
		);

		// Mini cart
		$('.primary-define #header #cart').hover(
			function() {
				$(this).find('.inner').slideDown(200);
			},
			function() {
				$(this).find('.inner').slideUp(200);
			}
		);
	}

});