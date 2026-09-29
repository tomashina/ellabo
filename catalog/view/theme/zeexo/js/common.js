$(window).on("load resize", function () {
	if($(window).width() < 768) {
		if($(".mobile-search .search_form").length < 1) {
			$(".search_form").appendTo(".mobile-search");
			$(".switchers").appendTo(".mobile-switcher");
			$(".wrap-menu2").appendTo(".mobile-menu");
			$(".wrap-menu").appendTo(".mobile-menu");
			$("#cart_block").appendTo(".mobile-cart");
		}
	} else {	
		if($(".desktop-search .search_form").length < 1) {
			$(".search_form").appendTo(".desktop-search");
			$(".switchers").appendTo(".desktop-switcher");
			$(".wrap-menu").appendTo(".desktop-menu");
			$(".wrap-menu2").appendTo(".desktop-menu2");
			$("#cart_block").appendTo(".desktop-cart");
		}
	}

	$(".product-center .modal").appendTo("body");

	if($(".header-type-17").length > 0) {
		if($(window).width() > 767) {
			$("#top .top-center .desktop-menu .wrap-menu").css("margin-left", "0px");
			if($("#top .top-center .desktop-menu .wrap-menu").offset().left < $(".desktop-search .search_form").offset().left) {
				$("#top .top-center .desktop-menu .wrap-menu").css("margin-left", $(".desktop-search .search_form").offset().left-$("#top .top-center .desktop-menu .wrap-menu").offset().left);
			}
		} else {
			$("#top .top-center .desktop-menu .wrap-menu").css("margin-left", "0px");
		}
	}
});

var widthProductSticky = 0;

$(window).on("load resize", function () {
	if($(".product-info.product-type-5").length > 0 && $(window).width() > 767) {
		$(".product-info.product-type-5 .custom-sticky").css("width", "auto").css("position", "relative").css("top", "0px").css("bottom", "auto");
		var widthProductSticky = $(".product-info.product-type-5 .custom-sticky").outerWidth();
		if($(".product-info.product-type-5 .product-image").outerHeight() > $(".product-info.product-type-5 .custom-sticky").outerHeight()) {
			if($(".product-info.product-type-5 .product-image").offset().top + $(".product-info.product-type-5 .product-image").outerHeight() < $(window).scrollTop() + $(window).outerHeight()) {
				$(".product-info.product-type-5 .custom-sticky").css("position", "absolute").css("top", "auto").css("bottom", "0px").css("width", widthProductSticky);
			} else {
				$(".product-info.product-type-5 .custom-sticky").css("position", "fixed").css("top", "auto").css("bottom", "20px").css("width", widthProductSticky);
				if($(".product-info.product-type-5 .custom-sticky").offset().top < $(".product-info.product-type-5 .product-image").offset().top) {
					$(".product-info.product-type-5 .custom-sticky").css("width", widthProductSticky).css("position", "relative").css("top", "0px").css("bottom", "auto");
				}
			}
		} else {
			$(".product-info.product-type-5 .custom-sticky").css("width", "auto").css("position", "relative").css("top", "0px").css("bottom", "auto");
		}
		$(window).scroll(function() {
			if($(".product-info.product-type-5 .product-image").outerHeight() > $(".product-info.product-type-5 .custom-sticky").outerHeight()) {
				if($(".product-info.product-type-5 .product-image").offset().top + $(".product-info.product-type-5 .product-image").outerHeight() < $(window).scrollTop() + $(window).outerHeight()) {
					$(".product-info.product-type-5 .custom-sticky").css("position", "absolute").css("top", "auto").css("bottom", "0px").css("width", widthProductSticky);
				} else {
					$(".product-info.product-type-5 .custom-sticky").css("position", "fixed").css("top", "auto").css("bottom", "20px").css("width", widthProductSticky);
					if($(".product-info.product-type-5 .custom-sticky").offset().top < $(".product-info.product-type-5 .product-image").offset().top) {
						$(".product-info.product-type-5 .custom-sticky").css("width", widthProductSticky).css("position", "relative").css("top", "0px").css("bottom", "auto");
					}
				}
			} else {
				$(".product-info.product-type-5 .custom-sticky").css("width", "auto").css("position", "relative").css("top", "0px").css("bottom", "auto");
			}
		});
	}
});	

// Full width
$(document).ready(function() {
	function force_full_width() {
		if($("header .slider-header #top .menu-logo").length > 0) {
			$(".advanced-full-width").each(function () {
                var p = $(".standard-body .full-width").find(this);
                if(p.size() > 0) {
                     p.width($('#main').width());
                     p.css("left", "0px");
                     var position = p.offset();
                     var position_left = position.left - $("#top").outerWidth();
                     p.css("left", "-" + position_left + "px");
                     p.find(".container").css("padding-left", position_left);
                     p.find(".container").css("padding-right", position_left);
                }
                
                var s = $(".standard-body .fixed").find(this);
                if(s.size() > 0) {
                     s.width($('.standard-body .fixed .pattern').width());
                     s.css("left", "0px");
                     var position = s.offset();
                     var position2 = $('.standard-body .fixed .pattern').offset();
                     var position3 = position.left-position2.left;
                     s.css("left", "-" + position3 + "px");
                     s.find(".container").css("padding-left", position3);
                     s.find(".container").css("padding-right", position3);
                }
                
                var c = $(".fixed-body").find(this);
                if(c.size() > 0) {
                     c.width($('.fixed-body .main-fixed').width());
                     c.css("left", "0px");
                     var position = c.offset();
                     var position2 = $('.fixed-body .main-fixed').offset();
                     var position3 = position.left-position2.left;
                     c.css("left", "-" + position3 + "px");
                     c.find(".container").css("padding-left", position3);
                     c.find(".container").css("padding-right", position3);
                }
			});
		} else {
			$(".advanced-full-width").each(function () {
			   var p = $(".standard-body .full-width").find(this);
			   if(p.size() > 0) {
			        p.width($('body').width());
			        p.css("left", "0px");
			        var position = p.offset();
			        p.css("left", "-" + position.left + "px");
			        p.find(".container").css("padding-left", position.left);
			        p.find(".container").css("padding-right", position.left);
			   }
			   
			   var s = $(".standard-body .fixed").find(this);
			   if(s.size() > 0) {
			        s.width($('.standard-body .fixed .pattern').width());
			        s.css("left", "0px");
			        var position = s.offset();
			        var position2 = $('.standard-body .fixed .pattern').offset();
			        var position3 = position.left-position2.left;
			        s.css("left", "-" + position3 + "px");
			        s.find(".container").css("padding-left", position3);
			        s.find(".container").css("padding-right", position3);
			   }
			   
			   var c = $(".fixed-body").find(this);
			   if(c.size() > 0) {
			        c.width($('.fixed-body .main-fixed').width());
			        c.css("left", "0px");
			        var position = c.offset();
			        var position2 = $('.fixed-body .main-fixed').offset();
			        var position3 = position.left-position2.left;
			        c.css("left", "-" + position3 + "px");
			        c.find(".container").css("padding-left", position3);
			        c.find(".container").css("padding-right", position3);
			   }
			});
		}
	}

	force_full_width();

	$(window).resize(function() {
	   force_full_width();
	});
});

$(document).ready(function() {
	// Lookbook
	$(".lookbook .pin .open-pin").click(function () {
		if($(this).parent().hasClass("active")) {
			$(this).parent().removeClass("active");
		} else {
			$(".lookbook .pin").removeClass("active");
			$(this).parent().addClass("active");
		}
		return false;
	});

	$(".lookbook .pin").click(function (event) {
		event.stopPropagation();
	});

	$('html').on('click', function () {
		$(".lookbook .pin").removeClass("active");
	});

	// Open search
	$(".open-search a, .search_form").click(function () {
		$(".search_form").addClass("active");
		return false;
	});

	$('html').on('click', function () {
		$(".search_form").removeClass("active");
	});

	// Add to cart model close
    $("#modalAddToCartProduct").on('click', '.btn-close-popup',  function(e) {
        $(this).closest('.modal-content').find('.modal-header .close').trigger('click');
        return false;
    });

	// Products fixed bar
	if($('.product-bar').length > 0) {
		$(window).scroll(function(){
			if ($(this).scrollTop() > 300 && $(this).scrollTop() > $("#button-cart").offset().top+50) {
		    	$('.product-bar').addClass("active");
		    } else {
		    	$('.product-bar').removeClass("active");
			}
		}); 
	}

	// Carousel Brands
	if($("#carouselBrands").length > 0) {
		$('#carouselBrands').owlCarousel({
			responsive:{
			0:{
				items:3,
			},
			600:{
				items:4,
			},
			1000:{
				items:5
			}
			},
			autoPlay: 3000,
			nav: true,
			dots: false
		});
	}

	// Testimonials
	if($("#carouselTestimonials").length > 0) {
		$("#carouselTestimonials .owl-carousel").owlCarousel({
		  items: 1,
		  nav: false,
		  dots: true
		});
	}

	// Shop by brand
	if($(".shop-by-brand").length > 0) {
		$('#shop-by-brand').owlCarousel({
			responsive:{
				0:{
					items:3,
				},
				600:{
					items:4,
				},
				1000:{
					items:5
				}
			},
			autoPlay: 3000,
			nav: true,
			dots: false
		});
	}
	// Load cart count
	$('.open-cart .cart-count').load('index.php?route=common/cart/info #total_count_ajax');

	// Open menu
	$(".open-menu2").click(function () {
		$(".mobile-menu").addClass("active");
		$(".close-mobile-menu-and-cart").addClass("active");
		$("body").css("overflow", "hidden");
		return false;
	});

	// Open cart
	$(".open-cart2").click(function () {
		$(".mobile-cart").addClass("active");
		$(".close-mobile-menu-and-cart").addClass("active");
		$("body").css("overflow", "hidden");
		return false;
	});

	// Close menu
	$(".close-menu2, .close-mobile-menu-and-cart, .close-cart").click(function () {
		$(".mobile-menu").removeClass("active");
		$(".mobile-cart").removeClass("active");
		$(".close-mobile-menu-and-cart").removeClass("active");
		$("body").css("overflow", "visible");
		return false;
	});

	// Open Filter
	$("body").on("click", ".open-filter", function () {
		$(".aside, .close-aside-box").addClass("active");
		$("body").css("overflow", "hidden");
	});

	$("body").on("click", ".close-aside-box, .close-aside-box2", function () {
		$(".aside, .close-aside-box").removeClass("active");
		$("body").css("overflow", "visible");
	});

	// Highlight any found errors
	$('.text-danger').each(function() {
		var element = $(this).parent().parent();
		
		if (element.hasClass('form-group row')) {
			element.addClass('has-error');
		}
	});

	// footer Menu
	$(".open-footer-menu").click(function () {
		$(this).addClass("active");
		$(".footer-menu").addClass("active");
	});

	// Dropdown on click
	$(document).on('click', '.dropdown-menu', function (e) {
		e.stopPropagation();
	});

	// Menu Background
	setInterval(function () {
		if($(".megamenu .with-sub-menu.active").length > 0 && $(".toolsorange1-shop-by-category.active").length == 0) {
			$("#menu-hover-background").addClass("active");
			$("body").addClass("activeMenu");
		} else {
			$("#menu-hover-background").removeClass("active");
			$("body").removeClass("activeMenu");
		}
	}, 10);

	// Products hover
    $(".box-product .product").hover(function(){
    	$(this).closest(".box-product").addClass("active");
    }, function(){
    	$(this).closest(".box-product").removeClass("active");
    });

	// Instagram feed
	if($(".instagram").length > 0) {
		var userFeed = new Instafeed({
		    get: 'user',
		    limit: 15,
		    resolution: "low_resolution",
		    userId: $(".instagram").attr("data-user-id"),
		    accessToken: $(".instagram").attr("data-access-token")
		});
		userFeed.run();
	}

    // Blog posts
    $(".blog-posts.owl-carousel").each(function () {
    	$(this).owlCarousel({
    		margin: 20,
			responsive:{
			    0:{
			        items:1
			    },
			    600:{
			        items:2
			    },
			    1000:{
			        items:3
			    }
			}
    	});
    });

    $(".blog-posts2.owl-carousel").each(function () {
    	$(this).owlCarousel({
    		margin: 0,
			items: 1,
			nav: true
    	});
    });

	/* Search */
	$('.button-search').bind('click', function() {
		url = $('base').attr('href') + 'index.php?route=product/search';
				 
		var search = $('input[name=\'search2\']').val();
		
		if (search) {
			url += '&search=' + encodeURIComponent(search);
		}

		var category_id = $('header select[name=\'category_id\']').val();
		
		if (category_id) {
			url += '&category_id=' + encodeURIComponent(category_id);
		}
		
		location = url;
	});
	
	$('input[name=\'search2\']').bind('keydown', function(e) {
		if (e.keyCode == 13) {
			url = $('base').attr('href') + 'index.php?route=product/search';
			 
			var search = $('input[name=\'search2\']').val();
			
			if (search) {
				url += '&search=' + encodeURIComponent(search);
			}

			var category_id = $('header select[name=\'category_id\']').val();
			
			if (category_id) {
				url += '&category_id=' + encodeURIComponent(category_id);
			}
			
			location = url;
		}
	});
	
	$(window).scroll(function(){
		if ($(this).scrollTop() > 300) {
	    	$('.scrollup').fadeIn();
	    } else {
			$('.scrollup').fadeOut();
		}
	}); 
	
	$('.scrollup').click(function(){
		$("html, body").animate({ scrollTop: 0 }, 600);
		return false;
	});
	
	/* Search MegaMenu */
	$('.button-search2').bind('click', function() {
		url = $('base').attr('href') + 'index.php?route=product/search';
				 
		var search = $('.container-megamenu input[name=\'search2\']').val();
		
		if (search) {
			url += '&search=' + encodeURIComponent(search);
		}

		var category_id = $('header select[name=\'category_id\']').val();
		
		if (category_id) {
			url += '&category_id=' + encodeURIComponent(category_id);
		}
		
		location = url;
	});
	
	$('.container-megamenu input[name=\'search2\']').bind('keydown', function(e) {
		if (e.keyCode == 13) {
			url = $('base').attr('href') + 'index.php?route=product/search';
			 
			var search = $('.container-megamenu input[name=\'search2\']').val();
			
			if (search) {
				url += '&search=' + encodeURIComponent(search);
			}

			var category_id = $('header select[name=\'category_id\']').val();
			
			if (category_id) {
				url += '&category_id=' + encodeURIComponent(category_id);
			}
			
			location = url;
		}
	});
	
	/* Quatity buttons */
	
	

	$('#q_up2').click(function(){
		var q_val_up=parseInt($("input#quantity_wanted2").val());
		if(isNaN(q_val_up)) {
			q_val_up=0;
		}
		$("input#quantity_wanted2").val(q_val_up+1).keyup(); 
		return false; 
	});
	
	$('#q_down2').click(function(){
		var q_val_up=parseInt($("input#quantity_wanted2").val());
		if(isNaN(q_val_up)) {
			q_val_up=0;
		}
		
		if(q_val_up>1) {
			$("input#quantity_wanted2").val(q_val_up-1).keyup();
		} 
		return false; 
	});

	// tooltips on hover
	$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
	
	// Makes tooltips work on ajax generated content
	$(document).ajaxStop(function() {
		$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
	});	

	// Furniture menu
	$(".furniture1-rooms-menu .menu-right").removeClass("active");
	$(".furniture1-rooms-menu .menu-right").eq(0).addClass("active");
	$(".furniture1-rooms-menu .menu-left ul li").hover(function () {
		$(".furniture1-rooms-menu .menu-left ul li").removeClass("active");
		$(this).addClass("active");
		$(".furniture1-rooms-menu .menu-right").removeClass("active");
		$(".furniture1-rooms-menu .menu-right").eq($(this).index()).addClass("active");		
	});
});

function getURLVar(key) {
    var value = [];
    
    var query = String(document.location).split('?');
    
    if (query[1]) {
        var part = query[1].split('&');

        for (i = 0; i < part.length; i++) {
            var data = part[i].split('=');
            
            if (data[0] && data[1]) {
                value[data[0]] = data[1];
            }
        }
        
        if (value[key]) {
            return value[key];
        } else {
            return '';
        }
    }
} 
	
// Cart add remove functions	
var cart = {
	'add': function(product_id, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			success: function(json) {			
				if (json['redirect']) {
					location = json['redirect'];
				}
				
				if (json['success']) {
					$('#modalAddToCartProduct').modal('show');
					$(".product-" + product_id).each(function () {
						if($(this).find(".image img").attr("src") != 'image/catalog/blank.gif' && $(this).find(".image img").attr("src") != 'image/catalog/blank2.gif' && $(this).find(".image img").attr("src") != 'image/catalog/blank3.gif') {
							$('#modalAddToCartProduct .tt-img img').attr('src', $(this).find(".image img").attr("src"));
						}
					});
					$('#modalAddToCartProduct .tt-title').html($("#product-" + product_id).find(".name").html());
					$('#modalAddToCartProduct .tt-product-total .tt-price').html($("#product-" + product_id).find(".price").html());
					$('#modalAddToCartProduct .tt-qty span').html('1');
					$('#modalAddToCartProduct .tt-cart-total .tt-price').html(' ').load('index.php?route=common/cart/info #total_amount_ajax');
					$('#modalAddToCartProduct .tt-cart-total .text-total').text($('#modalAddToCartProduct .tt-cart-total .text-total').text().replace(/[0-9]+/, parseInt($('#total_count_ajax').html())+1));
					     					
					$('#cart_block #cart_content').load('index.php?route=common/cart/info #cart_content_ajax');
					$('#cart_block #total_count_ajax, .open-cart .cart-count').load('index.php?route=common/cart/info #total_count_ajax');
				}
			}
		});
	},
	'update': function(key, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			success: function(json) {
				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart_block #cart_content').load('index.php?route=common/cart/info #cart_content_ajax');
					$('#cart_block #total_count_ajax, .open-cart .cart-count').load('index.php?route=common/cart/info #total_count_ajax');
				}			
			}
		});			
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',			
			success: function(json) {
				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart_block #cart_content').load('index.php?route=common/cart/info #cart_content_ajax');
					$('#cart_block #total_count_ajax, .open-cart .cart-count').load('index.php?route=common/cart/info #total_count_ajax');
				}
			}
		});			
	}
}

var voucher = {
	'add': function() {
		
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},      
			complete: function() {
				$('#cart > button').button('reset');
			},			
			success: function(json) {
				$('#cart-total').html(json['total']);
				
				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart_block #cart_content').load('index.php?route=common/cart/info #cart_content_ajax');
					$('#cart_block #total_count_ajax, .open-cart .cart-count').load('index.php?route=common/cart/info #total_count_ajax');
				}			
			}
		});	
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {							
				if (json['success']) {
					$.notify({
						message: json['success'],
						target: '_blank'
					},{
						// settings
						element: 'body',
						position: null,
						type: "info",
						allow_dismiss: true,
						newest_on_top: false,
						placement: {
							from: "top",
							align: "right"
						},
						offset: 20,
						spacing: 10,
						z_index: 2031,
						delay: 5000,
						timer: 1000,
						url_target: '_blank',
						mouse_over: null,
						animate: {
							enter: 'animated fadeInDown',
							exit: 'animated fadeOutUp'
						},
						onShow: null,
						onShown: null,
						onClose: null,
						onClosed: null,
						icon_type: 'class',
						template: '<div data-notify="container" class="col-11 col-md-3 alert alert-success" role="alert">' +
							'<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
							'<span data-notify="message"><i class="fa fa-check-circle"></i>&nbsp; {2}</span>' +
							'<div class="progress" data-notify="progressbar">' +
								'<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
							'</div>' +
							'<a href="{3}" target="{4}" data-notify="url"></a>' +
						'</div>' 
					});
				}   
				
				if (json['info']) {
					$.notify({
						message: json['info'],
						target: '_blank'
					},{
						// settings
						element: 'body',
						position: null,
						type: "info",
						allow_dismiss: true,
						newest_on_top: false,
						placement: {
							from: "top",
							align: "right"
						},
						offset: 20,
						spacing: 10,
						z_index: 2031,
						delay: 5000,
						timer: 1000,
						url_target: '_blank',
						mouse_over: null,
						animate: {
							enter: 'animated fadeInDown',
							exit: 'animated fadeOutUp'
						},
						onShow: null,
						onShown: null,
						onClose: null,
						onClosed: null,
						icon_type: 'class',
						template: '<div data-notify="container" class="col-11 col-md-3 alert alert-info" role="alert">' +
							'<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
							'<span data-notify="message"><i class="fa fa-info"></i>&nbsp; {2}</span>' +
							'<div class="progress" data-notify="progressbar">' +
								'<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
							'</div>' +
							'<a href="{3}" target="{4}" data-notify="url"></a>' +
						'</div>' 
					});
				}   
				
				$('#wishlist-total').html(json['total']);
			}
		});
	},
	'remove': function() {
	
	}
}

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {							
				if (json['success']) {
					$.notify({
						message: json['success'],
						target: '_blank'
					},{
						// settings
						element: 'body',
						position: null,
						type: "info",
						allow_dismiss: true,
						newest_on_top: false,
						placement: {
							from: "top",
							align: "right"
						},
						offset: 20,
						spacing: 10,
						z_index: 2031,
						delay: 5000,
						timer: 1000,
						url_target: '_blank',
						mouse_over: null,
						animate: {
							enter: 'animated fadeInDown',
							exit: 'animated fadeOutUp'
						},
						onShow: null,
						onShown: null,
						onClose: null,
						onClosed: null,
						icon_type: 'class',
						template: '<div data-notify="container" class="col-11 col-md-3 alert alert-success" role="alert">' +
							'<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
							'<span data-notify="message"><i class="fa fa-check-circle"></i>&nbsp; {2}</span>' +
							'<div class="progress" data-notify="progressbar">' +
								'<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
							'</div>' +
							'<a href="{3}" target="{4}" data-notify="url"></a>' +
						'</div>' 
					});
					
					$('#compare-total').html(json['total']);
				}   
			}
		});
	},
	'remove': function() {
	
	}
}

/* Agree to Terms */
$(document).delegate('.agree', 'click', function(e) {
	e.preventDefault();
	
	$('#modal-agree').remove(); 
	
	var element = this;
	
    $.ajax({
        url: $(element).attr('href'),
        type: 'get',
        dataType: 'html',
        success: function(data) {
			html  = '<div id="modal-agree" class="modal fade">';
			html += '  <div class="modal-dialog">';
			html += '    <div class="modal-content">';
			html += '      <div class="modal-header">'; 
			html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
			html += '        <h4 class="modal-title">' + $(element).text() + '</h4>';
			html += '      </div>';
			html += '      <div class="modal-body">' + data + '</div>';
			html += '    </div';
			html += '  </div>';
			html += '</div>';
			
			$('body').append(html);
			
			$('#modal-agree').modal('show');
        }
    });
});

/* Autocomplete */
(function($) {
	function Autocomplete(element, options) {
		this.element = element;
		this.options = options;
		this.timer = null;
		this.items = new Array();

		$(element).attr('autocomplete', 'off');
		$(element).on('focus', $.proxy(this.focus, this));
		$(element).on('blur', $.proxy(this.blur, this));
		$(element).on('keydown', $.proxy(this.keydown, this));

		$(element).after('<ul class="dropdown-menu"></ul>');
		$(element).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));
	}

	Autocomplete.prototype = {
		focus: function() {
			this.request();
		},
		blur: function() {
			setTimeout(function(object) {
				object.hide();
			}, 200, this);
		},
		click: function(event) {
			event.preventDefault();

			value = $(event.target).parent().attr('data-value');

			if (value && this.items[value]) {
				this.options.select(this.items[value]);
			}
		},
		keydown: function(event) {
			switch(event.keyCode) {
				case 27: // escape
					this.hide();
					break;
				default:
					this.request();
					break;
			}
		},
		show: function() {
			var pos = $(this.element).position();

			$(this.element).siblings('ul.dropdown-menu').css({
				top: pos.top + $(this.element).outerHeight(),
				left: pos.left
			});

			$(this.element).siblings('ul.dropdown-menu').show();
			$(this).siblings('ul.dropdown-menu').css("opacity", "1");
			$(this).siblings('ul.dropdown-menu').css("visibility", "visible");
		},
		hide: function() {
			$(this.element).siblings('ul.dropdown-menu').hide();
		},
		request: function() {
			clearTimeout(this.timer);

			this.timer = setTimeout(function(object) {
				object.options.source($(object.element).val(), $.proxy(object.response, object));
			}, 200, this);
		},
		response: function(json) {
			html = '';

			if (json.length) {
				for (i = 0; i < json.length; i++) {
					this.items[json[i]['value']] = json[i];
				}

				for (i = 0; i < json.length; i++) {
					if (!json[i]['category']) {
						html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
					}
				}

				// Get all the ones with a categories
				var category = new Array();

				for (i = 0; i < json.length; i++) {
					if (json[i]['category']) {
						if (!category[json[i]['category']]) {
							category[json[i]['category']] = new Array();
							category[json[i]['category']]['name'] = json[i]['category'];
							category[json[i]['category']]['item'] = new Array();
						}

						category[json[i]['category']]['item'].push(json[i]);
					}
				}

				for (i in category) {
					html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

					for (j = 0; j < category[i]['item'].length; j++) {
						html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
					}
				}
			}

			if (html) {
				this.show();
			} else {
				this.hide();
			}

			$(this.element).siblings('ul.dropdown-menu').html(html);
		}
	};

	$.fn.autocomplete = function(option) {
		return this.each(function() {
			var data = $(this).data('autocomplete');

			if (!data) {
				data = new Autocomplete(this, option);

				$(this).data('autocomplete', data);
			}
		});
	}
})(window.jQuery);

function openPopup(module_id, product_id) {
     product_id = product_id || undefined;
     $.magnificPopup.open({
          items: {
               src: 'index.php?route=extension/module/popup/show&module_id=' + module_id + (product_id ? '&product_id=' + product_id : '')
          },
          mainClass: 'popup-module mfp-with-zoom',
          type: 'ajax',
          removalDelay: 200
     });
}