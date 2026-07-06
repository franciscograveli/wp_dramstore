jQuery( function($){

    var $products_sliders = $('.ywcps-wrapper');

    /*************************
     * PRODUCTS SLIDER
     *************************/


    if( $.fn.owlCarousel  && $products_sliders.length ) {
        var product_slider = function(t) {


                var cols = parseInt( t.data( 'n_items' ) ),
                    autoplay = parseInt( t.data('auto_play') ) > 0,
                    is_loop = t.data( 'is_loop' ) ,
                    pag_speed = parseInt( t.data( 'pag_speed' ) ),
                    stop_hov = t.data( 'stop_hov' ),
                    show_nav = t.data( 'show_nav' ) ,
                    en_rtl = t.data( 'en_rtl' ) ,
                    anim_in = t.data( 'anim_in' ),
                    anim_out = t.data( 'anim_out' ),
                    anim_speed = parseInt( t.data( 'anim_speed' ) ),
                    show_dot_nav = t.data( 'show_dot_nav' ) ;

                var owl = t.find('.ywcps-products'),
                    block_params = {
                        message: null,
                        overlayCSS: {
                            background: '#fff',
                            opacity: 0.6
                        },
                        ignoreIfBlocked: true
                    };

                owl.on('initialize.owl.carousel', function(e){
                    var slider_container = e.currentTarget;

                    $(slider_container).parents('.ywcps-wrapper').block(block_params);

                });
                owl.on('initialized.owl.carousel ', function (e) {

                    var slider_container = e.currentTarget;
                    $(slider_container).parents('.ywcps-wrapper').unblock();
                    $(slider_container).parents('.ywcps-slider').css({'visibility':'visible'});

                });

                owl.owlCarousel({
                    animateOut: anim_out,
                    animateIn: anim_in,
                    items: cols,
                    autoplay: autoplay,
                    autoplayTimeout: parseInt( t.data('auto_play') ),
                    autoplayHoverPause: stop_hov,
                    loop: is_loop,
                    rtl: en_rtl,
                    navSpeed: pag_speed,
                    dots: show_dot_nav,
                    nav: false,
                    responsive :{
                        0:{
                            items:1,
                        },
                        480:{
                            items:2
                        },
                        768:{
                            items:cols
                        },

                    }
                });



                var el_prev =   t.find('.ywcps-nav-prev'),
                    el_next =   t.find('.ywcps-nav-next'),
                    id_prev =   el_prev.attr('id'),
                    id_next =   el_next.attr('id');

                if( !show_nav )
                {
                    $('#'+id_prev).hide();
                    $('#'+id_next).hide();
                }

                if( !show_dot_nav )
                   $('.owl-theme .owl-controls').hide();

                // Custom Navigation Events
                t.on('click', '#'+id_next, function () {
                    owl.trigger('next.owl.carousel');
                });

                t.on('click', '#'+id_prev, function () {
                    owl.trigger('prev.owl.carousel');
                });
        };


        // initialize slider in only visible tabs
        $products_sliders.each(function(){
            var t = $(this),
                wc_tab_content = t.closest('.panel.entry-content.wc-tab'),
                id_tab_content = '.'+wc_tab_content.attr('id');


            if( ( ! t.closest('.panel.group').length || t.closest('.panel.group').hasClass('showing') ) &&
                ( !wc_tab_content.length  || wc_tab_content.css('display') == 'block' )  ){

                product_slider( t );
            }
        });

        $('.tabs-container').on( 'tab-opened', function( e, tab ) {
            product_slider( tab.find( $products_sliders ) );
        });


        $('.tabs.wc-tabs').on('click','li a', function(e){
            var class_li =$(this).attr('href'),
                panel = $(this).parents('.woocommerce-tabs');


            product_slider(panel.find(class_li).find( $products_sliders ));
        });



    }

});
