(function($) {
    'use strict';
    
    $(document).ready(() => {
        $(document).on('click', event => {
            if (window.opened_sweet_select_box) {
                if (!$(event.target).closest(".tw_select").length) {
                    $('.tw_select').each(function() {
                        bp_close_input_select($(this));
                    });
                }
            }
        });

        $(document).on('click', '.tw_select .tw_select-placeholder', function() {
            let parent = $(this).parents('.tw_select');
            $(document).find('.tw_select').each(function() {
                if ($(this).find('select').attr('id') != parent.find('select').attr('id')) {
                    bp_close_input_select($(this));
                }
            });

            if (parent.hasClass('open')) {
                parent.find('.tw_select-placeholder-navigator').html('<i class="bx bx-chevron-down opacity-75"></i>');
            } else {
                parent.find('.tw_select-placeholder-navigator').html('<i class="bx bx-chevron-up opacity-75"></i>');
            }

            if (parent.hasClass('tw_select-taggable')) {
                parent.find('.tw_select-item-search .tw_select-new-tag input').on('keyup', function(event) {
                    if (event.key == "Enter") {
                        let tag = $(this).val().trim();
                        if (tag.length > 0) {
                            tw_select_add_new_tag(parent, tag);
                        }
                    }
                });

                parent.find('.tw_select-item-search .tw_select-new-tag input').on("keydown", function(event) {
                    if (event.key == "Enter") {
                        event.preventDefault();
                    }
                });
            }

            parent.toggleClass('open');
            tw_select_check_preety_scroll(parent);
            window.opened_sweet_select_box = true;
        });

        $(document).on('keyup', '.tw_select .tw_select-item-search input', function() {
            let parent = $(this).parents('.tw_select'),
                param  = $(this).val().trim().toLowerCase();

            if (parent.hasClass('tw_select-ajax')) {
                tw_select_ajax_matched(parent, param);
            } else {
                tw_select_find_matched(parent, param);
            }
        });

        $(document).on('change', '.tw_select select', function(){
            bp_check_input_selected($(this).parents('.tw_select'));
        });

        $(document).on('click', '.tw_select-options .tw_select-item', function() {
            let parent = $(this).parents('.tw_select'),
                value  = $(this).data('value'), $this = $(this);

            parent.find('select').find('option').each(function(){
                if (!parent.hasClass('tw_select-multiple')) {
                    $(this).prop("selected", false);
                }
                if ($(this).attr('value') == value) {
                    if ($this.hasClass('active')) {
                        $this.removeClass('active');
                        $(this).prop("selected", false);
                        if (!parent.hasClass('tw_select-multiple')) {
                            parent.find('select').val('');
                        }
                    } else {
                        $(this).prop("selected", true);
                    }
                }
            });

            parent.find('select').trigger('change');
        });

        $(document).on('click', '.tw_select .tw_multiple-selected .tw_select-placeholder-icon', function(){
            let parent = $(this).parents('.tw_select'),
                value  = $(this).parent('.tw_multiple-selected').data('value');
            
            parent.find('select option').each(function(){
                if ($(this).attr('value') == value) {
                    $(this).prop("selected", false);
                }
            });

            parent.find('select').trigger('change');
        });
    });

    function bp_close_input_select(target) {
        target.removeClass('open');
        
        if (!target.hasClass('tw_select-ajax')) {
            target.find('.tw_select-item-search input').val('');
        }

        target.find('.tw_select-placeholder-navigator').html('<i class="bx bx-chevron-down opacity-75"></i>');
        target.find('.tw_select-options .tw_select-item').each(function() {
            $(this).show();
        });

        window.opened_sweet_select_box = false;
    }

    function bp_reset_input_select(target) {

        target.find('.tw_select-placeholder').text('<span class="text-slate-400">'+ target.find('.tw_select-placeholder').data('placeholder') +'</span>');

        target.find('.tw_select-options .tw_select-item').each(function() {
            $(this).removeClass('active');
            $(this).find('.tw_select-item-icon').html('');
        });
        
        target.find('select').val('');
        target.find('select').trigger('change');

        bp_close_input_select(target);
    }

    function bp_check_input_selected(parent) {
        var values = parent.find('select').val(), output = '';

        if (!parent.hasClass('tw_select-multiple')) {
            if (values) {
                values = [values];
            } else {
                values = [];
            }
        }

        if (values.length == 0) {
            output = '<span class="text-slate-400">'+ parent.find('.tw_select-placeholder').data('placeholder') +'</span>';
        }

        parent.find('.tw_select-options .tw_select-item').each(function() {
            $(this).find('.tw_select-item-icon').html('');
            $(this).removeClass('active');
        });

        $.each(values, function(i, value) {
            parent.find('.tw_select-options .tw_select-item').each(function() {
                if ($(this).data('value') == value) {
                    $(this).addClass('active');
                    $(this).find('.tw_select-item-icon').html('<i class="bx bx-check text-2xl text-sky-600"></i>');
                    if (parent.hasClass('tw_select-multiple')) {
                        output += '<span class="tw_multiple-selected" data-value="'+ value +'"><span class="tw_select-placeholder-icon tw_popup_ignore_blur"><i class="bx bx-check text-2xl text-sky-600"></i></span><span class="tw_multiple-selected-text">' + $(this).text() + '</span></span>';
                    } else {
                        output += '<span class="tw_select-placeholder-icon"><i class="bx bx-check text-2xl text-sky-600"></i></span>' + $(this).text();
                    }
                }
            });
        });

        parent.find('.tw_select-placeholder').html(output);
        parent.removeClass('open');
    }

    function tw_select_add_new_tag(parent, tag) {
        parent.find('select').append('<option value="'+ tag +'" selected>'+ tag +'</option>');
        parent.find('.tw_select-options').append('<div class="tw_select-item" data-value="'+ tag +'"><span class="tw_select-item-icon"></span><span class="tw_select-item-text">'+ tag +'</span></div>');
        parent.find('.tw_select-item-search .tw_select-new-tag input').val('');
        parent.find('.tw_select-item-search .tw_select-new-tag input').trigger('keyup');
        parent.find('select').trigger('change');
    }

    function tw_select_find_matched(parent, param) {
        var found = 0;
        parent.find('.tw_select-options .tw_select-item').each(function() {
            if ($(this).find('.tw_select-item-text').text().toLowerCase().indexOf(param) != -1) {
                found++;
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        parent.find('.tw_select-options .tw_select-item-not-found').remove();
        if (found == 0) {
            parent.find('.tw_select-options').append('<div class="tw_select-item-not-found tw_select-item"><span class="tw_select-item-text">Result Not Found</span></div>');
        }
    }

    function tw_select_ajax_matched(parent, param) {

        if (window.sweet_select_ajax_loading) {
            return;
        }

        if (param.length <= 1) {
            tw_select_clear_ajax_option(parent);
            return;
        }
        
        let selected = typeof parent.data('ajaxvalue') === 'string' ? parent.data('ajaxvalue').split(',') : [parent.data('ajaxvalue')],
            values   = parent.find('select').val();

        if (typeof values !== 'object') {
            values = [values];
        }

        $.ajax({
            url: parent.data('ajax'),
            type: parent.data('ajaxmethod'),
            dataType: 'json',
            beforeSend: function(xhr){
                window.sweet_select_ajax_loading = true;
                tw_select_clear_ajax_option(parent);
                parent.find('.tw_select-options').append('<div class="tw_select-item-not-found tw_select-item"><span class="tw_select-item-text">Searching...</span></div>');
            },
            data: {q: param,_token: $("meta[name='_token']").attr("content"),},
        })
        .done(function(data) {

            parent.find('.tw_select-item').hide();

            $.each(data, function(index, option) {

                if($.inArray(option.id, selected) !== -1 || $.inArray(option.id, values) !== -1){
                    return;
                }

                parent.find('select').append('<option value="'+ option.id +'" data-ajax>'+ option.text +'</option>');
                parent.find('.tw_select-options').append('<div class="tw_select-item" data-ajax data-value="'+ option.id +'"><span class="tw_select-item-icon"></span><span class="tw_select-item-text">'+ option.text +'</span></div>');

            });

        })
        .fail(function(xhr) {
            window.SweetAlert('error', xhr.responseJSON.message);
        })
        .always(function() {
            window.sweet_select_ajax_loading = false;
            parent.find('.tw_select-options .tw_select-item-not-found').remove();
            tw_select_check_preety_scroll(parent);
        });
        

    }


    function tw_select_clear_ajax_option(parent) {

        parent.find('.tw_select-item').show();

        parent.find('select option[data-ajax]').each(function(){
            if(!$(this).is(':selected')){
                $(this).remove();
            }
        });

        parent.find('.tw_select-options .tw_select-item[data-ajax]').each(function(){
            if (!$(this).hasClass('active')) {
                $(this).remove();
            }
        });
    }

    function tw_select_check_preety_scroll(parent) {
        if (parent.find('.tw_select-options .tw_select-item').length > 5) {
            parent.find('.tw_select-options').css('overflow-y', 'scroll');
        }
    }

}) (jQuery);