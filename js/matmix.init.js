$ = jQuery;
(function ($) {

    /**
     * iCheck
     * icheck.js
     * icheck.css
     *  */
    try {

        $('.mail-select,.select-all-email,.task-select,.select-all-task,.note-select,.select-all-note,.todo-select').iCheck({
            checkboxClass: 'icheckbox_minimal',
            radioClass: 'iradio_minimal',
            increaseArea: '30%' // optional
        });

        $('.i-min-check').iCheck({
            checkboxClass: 'icheck',
            radioClass: 'iradio',
            increaseArea: '30%' // optional
        });

        $('.iradio-square').iCheck({
            radioClass: 'iradio-square',
            increaseArea: '30%' // optional
        });

    } catch (e) {

    }


    var wh = $(window).height();

    // var AppsContainer = $('.apps-container');
    // if (AppsContainer.length) {
    //     var AppsTopOfset = AppsContainer.offset().top;
    // }
    // var AppsCalH = wh - AppsTopOfset;

    $("body").scroll(function (e) {
        e.preventDefault()
    });

    $('.left-navigation, .chat-user-list, .server-stats-content, .aside-notifications-wrap').bind('mousewheel DOMMouseScroll', function (e) {
        var scrollTo = null;

        if (e.type == 'mousewheel') {
            scrollTo = (e.originalEvent.wheelDelta * -1);
        } else if (e.type == 'DOMMouseScroll') {
            scrollTo = 40 * e.originalEvent.detail;
        }

        if (scrollTo) {
            e.preventDefault();
            $(this).scrollTop(scrollTo + $(this).scrollTop());
        }
    });

    /*========================
         * ADVANCED FORM ELEMENTS *
     ==========================*/

   /**
     * Bootstrap Datepicker
     * bootstrap-datepicker.js
     * datepicker.css
     **/

    if ($.fn.datepicker) {
        $('.input-daterange').datepicker({
            orientation: "bottom",
            weekStart: 1,
            autoclose: true,
            forceParse: false,
            todayHighlight: true,
            format: "yyyy-mm-dd",
            //toggleActive: true
            immediateUpdates: true,
            keepEmptyValues: true
        });

        $('.filterable-date').inputmask("yyyy-mm-dd", {
            "placeholder": "YYYY-MM-DD",
            "oncleared": function(){
                $('.btn').prop( "disabled", true );
            },
            "oncomplete": function(){
                $('.btn').prop( "disabled", false );
            },
        });

        var date_field = $('.filterable-date');

        $(date_field).bind('keypress', function (e) {
            if (e.which === 13) {
                var tabindex = $(this).attr('tabindex');
                $('.datepicker').hide();
                tabindex++;
                $('[tabindex=' + tabindex + ']').focus();
                return false;
            }
        });

       }

    /**
     * Form Validate
     * jquery.validate.js
     *  */
    if ($.fn.validate) {

        $("#commentForm").validate();

        $("#login").validate()

        // validate the form when it is submitted
        var validator = $("#form1").validate({
            errorPlacement: function (error, element) {
                // Append error within linked label
                $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
            },
            errorElement: "span",
            messages: {
                user: {
                    required: " (required)",
                    minlength: " (must be at least 3 characters)"
                },
                password: {
                    required: " (required)",
                    minlength: " (must be between 5 and 12 characters)",
                    maxlength: " (must be between 5 and 12 characters)"
                }
            }
        });

        $(".cancel").click(function () {
            validator.resetForm();
        });

        $("#form2").validate();
    }


    if ($.fn.floatlabel) {
        $('input.floatlabel').floatlabel({
            labelClass: "login-label",
            slideInput: true,
            labelStartTop: '0px',
            labelEndTop: '0px',
            paddingOffset: '20px',
            transitionDuration: 0.2,
            transitionEasing: 'ease-in-out',
            focusColor: '#838780',
            blurColor: '#2996cc'
        });
    }

    $('#i_agree').on("ifChecked", function() {
       $('#accept_button').attr('disabled', false);
    });
    $('#i_agree').on("ifUnchecked", function() {
        $('#accept_button').attr('disabled', true);
    });


    /* ==============================
    Copy button function and toltip
    ================================= */

    // Tooltip for copy button
    $('button').tooltip({
        trigger: 'click',
        placement: 'top'
    });

    function showTooltip(btn, message) {
        $(btn).tooltip('hide')
            .attr('data-original-title', message)
            .tooltip('show');
    }

    function hideTooltip(btn) {
        setTimeout(function() {
            $(btn).tooltip('hide');
        }, 1500);
    }

    // if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
    //     $('.copy-btn-ios-hide').remove();
    // }

    function iOSMessage(action){
        var actionMsg = '';
        var actionKey = (action==='cut'?'X':'C');
        if(/Mac/i.test(navigator.userAgent)){
            actionMsg='Press ⌘-'+ actionKey+' to '+ action;
        } else {
            actionMsg='Press Ctrl-'+ actionKey+' to '+ action;
        }
        return actionMsg;
    }

    // Clipboard - copy button functions
    var clipboard = new Clipboard('.btn');
    var clipboardText = $('.btn').attr("data-text");

    clipboard.on('success',function(e){
        e.clearSelection();
        showTooltip(e.trigger, clipboardText);
        hideTooltip(e.trigger);
    });

    clipboard.on('error',function(e){
        showTooltip(e.trigger,iOSMessage(e.action));
        hideTooltip(e.trigger);
    });


    /* ==============================
     Load more buttons
     ================================= */
    var items = $("#tags-list .icheck-input").size();

    var loadMoreBtn = $('#loadMore');
    var loadLessBtn = $('#loadLess').hide();

    var defaultItems = 9;
    var itemsToShow = 50;

    $('#tags-list .icheck-input:lt(' + defaultItems + ')').slideDown();

    // Show more
    loadMoreBtn.click(function () {
        defaultItems= (defaultItems + itemsToShow <= items) ? defaultItems + itemsToShow : items;
        $('#tags-list .icheck-input:lt(' + defaultItems + ')').slideDown();
        $(this).hide();
        loadLessBtn.show();
    });

    // Show less
    loadLessBtn.click(function () {
        defaultItems = (defaultItems - itemsToShow < 0) ? 9 : defaultItems - 2;
        $('#tags-list .icheck-input').not(':lt(' + defaultItems + ')').slideUp();
        $(this).hide();
        loadMoreBtn.show();
    });

    $('.equal-height').matchHeight();

    /* ==============================
     Preloader
     ================================= */
    var ThisLoad;

    var submitBtn = $('#submit'),
        loadingText = submitBtn.data('loadingText') || 'Loading...';

     $(submitBtn).on('click' , function () {
         ThisLoad = $(this);
         submitBtn.mask(loadingText);
         var r = $.Deferred();
         setTimeout(function () {
             r.resolve();
             UnMask();
         }, 500);
     });

     function UnMask() {
        ThisLoad.unmask();
     }

    // Link to tab
    var url = document.location.toString();
    if (url.match('#')) {
        $('#widgets-tabs a[href=#'+url.split('#')[1]+']').tab('show');

    }

    // Change hash for page-reload
    $('#widgets-tabs a').on('shown.bs.tab', function (e) {
        e.preventDefault();
        window.location.hash = e.target.hash;
        $("html,body").animate({scrollTop: 0}, 0);
    });

})(jQuery);