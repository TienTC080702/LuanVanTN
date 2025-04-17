jQuery(document).ready(function ($) {
    var pCon = $('.page-container');
    var width = $(window).width();

    //List Menu View
    function ListMenuView() {
        $('.leftbar-action').on('click', function (event) {
            event.preventDefault();

            if (pCon.hasClass('list-menu-view')) {
                pCon.removeClass('list-menu-view');
                pCon.addClass('hide-list-menu');
                $('.left-nav-user-name').appendTo('.mobile-userNav');
            } else {
                pCon.removeClass('hide-list-menu');
                pCon.addClass('list-menu-view');
                $('.left-nav-user-name').appendTo('.user-name-main-block');
            }
        });

        $(window).on('resize', function(){
            if (width >= 992) {
                $('.left-nav-user-name').appendTo('.user-name-main-block');
            } else {
                $('.left-nav-user-name').appendTo('.mobile-userNav');
            }
            if (pCon.hasClass('hide-list-menu')) {
                pCon.removeClass('hide-list-menu');
                pCon.addClass('list-menu-view');
            }
        });
    }

    $('.main-container, .logo-block, .btn-navbar-toggle').on('click', function() {
        if(pCon.hasClass('hide-list-menu')) {
            pCon.removeClass('hide-list-menu');
            pCon.addClass('list-menu-view');
        }
    });

    $(document).click(function(e) {
        if (!$(e.target).is('.btn-navbar-toggle')) {
            $('.collapse').collapse('hide');
        }
    });

    $('.dropdown-menu a, .list-accordion a').on('click', function() {
        $zopim(function() {
            $zopim.livechat.window.hide();
        });
    });

    ListMenuView();

    $('#ps_container').perfectScrollbar({
        wheelPropagation: true,
        wheelSpeed: 2

    });

    /*====Left Bar Accordion====*/
    if ($.fn.dcAccordion) {
        $('.list-accordion').each(function () {
            $(this).dcAccordion({
                eventType: 'click',
                hoverDelay: 0,
                autoClose: true,
                saveState: false,
                disableLink: true,
                speed: 'fast',
                showCount: false,
                autoExpand: true,
                cookie: 'dcjq-accordion-1',
                classExpand: 'dcjq-current-parent',
            });
        });
    }

    if (width >= 975 && width <= 1180) {
        if(pCon.hasClass('list-menu-view')) {
            pCon.removeClass('list-menu-view');
            pCon.addClass('iconic-view');
            $('.left-navigation .list-accordion li ul').css('display', 'none');
            $('.left-navigation > ul ul li a').addClass('active');
        }
    }

    $(window).on('resize', function(){
        var width = $(window).width();
         if (width >= 0 && width <= 975) {
             if(pCon.hasClass('iconic-view')) {
                 pCon.removeClass('iconic-view');
                 pCon.addClass('list-menu-view');
             }
         }
         if (width >= 975 && width <= 1180) {
             if(pCon.hasClass('list-menu-view')) {
                 pCon.removeClass('list-menu-view');
                 pCon.addClass('iconic-view');
             }
         }
         if (width >= 1180) {
             if(pCon.hasClass('iconic-view')) {
                 pCon.removeClass('iconic-view');
                 pCon.addClass('list-menu-view');
             }
         }
    });

    $(document).on('mouseleave', '.iconic-view .left-aside', function () {
        //$('.list-accordion .dcjq-parent').removeClass('active');
        $('.left-navigation .list-accordion ul').hide();

    });

    /*--Wiget Loader--*/

    //var ThisLoad;

    /*$(".w-reload").click(function () {
        ThisLoad = $(this);
        $(this).parents('.widget-head')
            .next('.widget-container').mask("Loading...");
        setTimeout(UnMask, 1500);
    });*/


    /*function UnMask() {
        ThisLoad.parents('.widget-head')
            .next('.widget-container').unmask();
    }*/

    /*--
    * switchery.css
    * switchery.js
    --*/
    try {

        var on_off_w = Array.prototype.slice.call(document.querySelectorAll('.w-on-off'));
        on_off_w.forEach(function (html) {
            var switchery = new Switchery(html, {
                size: 'small',
                color: '#15bdc3',
                jackColor: '#fff',
                secondaryColor: '#eee',
                jackSecondaryColor: '#fff'
            });
        });


        var on_off_w_check = document.querySelector('.w-on-off');
        on_off_w_check.onchange = function () {
            var swithToggle = $(this).parents('.widget-head')
                .next('.widget-container')
                .slideToggle(200);
            if (on_off_w_check.checked) {
                swithToggle
            } else {
                swithToggle
            }
        };

    } catch (e) {

    }


    /*--
     * iCheck
     * icheck.js
     * icheck.css
     * --- */
    try {

        $('.w-i-check').iCheck({
            checkboxClass: 'icheckbox_minimal',
            radioClass: 'iradio_minimal',
            increaseArea: '30%' // optional
        });
        var w_check_box = $('.w-i-check');
        w_check_box.on('ifChecked ifUnchecked', function (event) {
            var swithToggle = $(this).parents('.widget-head')
                .next('.widget-container')
                .slideToggle(200);
            if (event.type == 'ifChecked') {
                swithToggle

            } else {
                swithToggle
            }
        });

    } catch (e) {

    }

    $('.progress-bar').each(function () {
        var PbarWidth = $(this).data("progress");
        if (PbarWidth) {
            $(this).css('width', PbarWidth + '%');
            $(this).parents('.progress').parents('.progress-wrap').children('.progress-meta').children('.progress-percent').text(PbarWidth + '%');
        }

    });

    /*$(document).on('mouseleave', '.left-aside', function () {
        $('.list-accordion .dcjq-parent').removeClass('active');
        $('.left-navigation .list-accordion ul').hide();

    });*/

    try {
        $.scrollUp({
            scrollName: 'scrollTop', // Element ID
            topDistance: '300', // Distance from top before showing element (px)
            topSpeed: 300, // Speed back to top (ms)
            animation: 'fade',
            animationInSpeed: 200, // Animation in speed (ms)
            animationOutSpeed: 200, // Animation out speed (ms)
            scrollText: '<i class="icon-arrow-up"></i>', // Text for element
            activeOverlay: false // Set CSS color to display scrollUp active point, e.g '#00FFFF'
        });
    } catch (err) {
        console.log('scrollTop is not found')
    }

});
