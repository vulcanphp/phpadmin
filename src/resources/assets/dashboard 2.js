(function($) {
    'use strict';

    $(document).ready(() => {

        window.checkPreetyScroll();

        $('[copy_link]').each(function(){
            $(this).on('click', function(e){
                e.preventDefault();
                navigator.clipboard.writeText($(this).attr('href'));
                window.SweetAlert('success', 'Copied to Clipboard.');
            });
        });

        $('[tw-slidetoggle]').each(function() {
            let target = $(this).attr('tw-slidetoggle');
            $(this).on('click', () => {
                $(this).parents('[tw-toggleparent]').find('[tw-childnode="' + target + '"]').slideToggle(100, function() {
                    window.checkPreetyScroll();
                });
            });
        });

        $('.tw-checkbox').each(function(){

            let input = $(this).find('input[type="hidden"]');

            $(this).find('input[type="checkbox"]').on('change', function(){
                if ($(this).prop('checked') == true){ 
                    input.val('true');
                }else{
                    input.val('false');
                }
            });

        });

        $(document).on('click', '[tw-toggle-class]', function(event){
            event.preventDefault();
            let target = $(this).attr('tw-target'),
                value = $(this).attr('tw-class'),
                callback = $(this).attr('tw-callback'),
                $this = $(this);

            $(target).toggleClass(value).promise().done(function() {
                if (typeof callback !== 'undefined') {
                    window[callback]();
                }
                if (typeof $this.attr('tw-is-blur') !== 'undefined') {
                    if (typeof $this.attr('tw-blur-event') === 'undefined') {
                        $this.attr('tw-blur-event', '1');
                        if ($(target).hasClass(value)) {
                            $(target).attr('tw-blur-action', 'remove');
                        } else {
                            $(target).attr('tw-blur-action', 'add');
                        }
                        $(target).attr('tw-blur-parent', target);
                    } else {
                        $this.removeAttr('tw-blur-event');
                        $(target).removeAttr('tw-blur-action');
                        $(target).removeAttr('tw-blur-parent');
                    }
                }
            });
        });

        $(document).on('click', '[tw_modal-open]', function(event){
            event.preventDefault();

            let target = $('[tw_modal-view="'+ $(this).attr('tw_modal-open') +'"]');
            target.show();

        });

        $(document).on('click', '[tw_tab-anchor]', function(event){
            event.preventDefault();

            if($(this).hasClass('active')){
                return;
            }

            let target = $(this).attr('tw_tab-anchor');

            $('[tw_tab-anchor]').each(function(){

                if($(this).attr('tw_tab-anchor') == target){
                    $(this).addClass('active');
                    $(this).addClass('bg-slate-900');
                    $(this).addClass('text-slate-300');
                } else {
                    $(this).removeClass('active');
                    $(this).removeClass('bg-slate-900');
                    $(this).removeClass('text-slate-300');
                }
                
            });
            
            $('[tw_tab-content]').each(function() {
                if($(this).attr('tw_tab-content') == target){
                    $(this).show();
                }else{
                    $(this).hide();
                }
            });
        });

        $(document).on('click', '[tw_modal-dismiss]', function(event){
            event.preventDefault();

            let target = $(this).attr('tw_modal-dismiss');
            if(typeof target !== undefined && target.trim().length > 0){
                $('[tw_modal-view="'+ target +'"]').hide();
            } else {
                $(this).parents('[tw_modal-view]').hide();
            }

        });

        $('#sidebar').on('mouseenter', function() {
            if ($(this).hasClass('toggle')) {
                $(this).addClass('toggle-flipped');
                $('#sidebar, #header').removeClass('toggle');
                $('#sidebar, #header-logo').css('width', '235px');
                $('#header-nav').css('width', 'calc(100% - 235px)');

                $('#backtoWidthSidebar').hide();
            }
        });

        $('#sidebar').on('mouseleave', function() {
            if ($(this).hasClass('toggle-flipped')) {
                $(this).removeClass('toggle-flipped');
                $('#sidebar, #header').addClass('toggle');
                $('#sidebar, #header-logo').css('width', '69px');
                $('#header-nav').css('width', 'calc(100% - 69px)');

                $('#backtoWidthSidebar').show();
            }
        });

        $(document).on('click', event => {
            // check blured toggle effects
            $(document).find('[tw-blur-action]').each(function() {
                let target = $(this).attr('tw-blur-parent'),
                    action = $(this).attr('tw-blur-action'),
                    activator = $('[tw-toggle-class][tw-is-blur][tw-target="' + target + '"]'),
                    value = activator.attr('tw-class');

                if (!$(event.target).closest(target).length && !$(event.target).closest('[tw-toggle-class][tw-is-blur][tw-target="' + target + '"]').length && !$(event.target).closest('.tw_popup_ignore_blur').length) {
                    if (action == 'add') {
                        $(target).addClass(value);
                    } else {
                        $(target).removeClass(value);
                    }

                    if (typeof activator.attr('tw-callback') !== 'undefined') {
                        window[activator.attr('tw-callback')]();
                    }

                    $(this).removeAttr('tw-blur-action');
                    $(this).removeAttr('tw-blur-parent');
                    activator.removeAttr('tw-blur-event');
                }
            });
        });

        // remove all notice
        $(document).on('click', '[tw_push_alert-dismiss]', function(){
            $(this).parents('[tw_push_alert]').remove();
        });

    });

    $(window).on('resize', function() {
        window.checkPreetyScroll();
    });

    $(window).on('load', function(){
       
        window.hide_admin_loading();
        
        setTimeout(function(){
            $(document).find('[tw_push_alert]').each(function(){
                $(this).remove();
            });
        }, 3000);

    });

    window.hide_admin_loading = function()
    {
        $('[tw_admin_loader]').removeClass('loading');
    }

    window.show_admin_loading = function()
    {
        $('[tw_admin_loader]').addClass('loading');
    }

    window.checkPreetyScroll = function() {
        $('.tw-preetyscroll-y').each(function() {
            $(this).css('height', 'max-content');
            if ($(this).height() > ($(window).height() - $(this).attr('tw-preetyscroll-except'))) {
                $(this).css('overflow-y', 'scroll');
            } else {
                $(this).css('overflow-y', 'hidden');
            }
            $(this).css('height', '100%');
        });
    }

    window.checkSidebarWidth = function() {
        if ($('#sidebar').hasClass('toggle')) {
            $('#sidebar, #header-logo').css('width', '69px');
            $('#main, #header-nav').css('width', 'calc(100% - 69px)');
            $('#backtoWidthSidebar').show();
            document.cookie = 'toogleSidebar=true;path=/';
        } else {
            $('#sidebar, #header-logo').css('width', '235px');
            $('#main, #header-nav').css('width', 'calc(100% - 235px)');
            $('#backtoWidthSidebar').hide();
            document.cookie = 'toogleSidebar=false;path=/';
        }
    }

    window.SweetAlert = function(type, text){
        $('body').append(`<div tw_push_alert class="${type}">
            <p>${text}</p>
            <span tw_push_alert-dismiss>
                <i class='bx bx-x text-xl'></i>
            </span>
        </div>`);

        setTimeout(function(){
            $(document).find('[tw_push_alert]').each(function(){
                $(this).remove();
            });
        }, 3000);
    }

})(jQuery);