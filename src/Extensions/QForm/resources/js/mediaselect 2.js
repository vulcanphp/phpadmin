(function($) {
    'use strict';

    $(document).ready(function() {

        $(document).on('click', '.tw_media_select [tw_media_select-label]', function(){

            if (typeof window.tw_media_core_file_manager !== 'undefined') {
                tw_media_clear_selected();
                tw_set_current_dir('');

                window.tw_media_back_to_core_file_manager = window.tw_media_core_file_manager;

                delete window.tw_media_core_file_manager;
            }

            tw_open_media_select_modal($(this).parents('.tw_media_select'));

        });

        if ($('#INIT_CORE_FILEMANAGER').length > 0) {
            
            window.tw_media_core_file_manager = '#INIT_CORE_FILEMANAGER';

            $('#INIT_CORE_FILEMANAGER').html(tw_media_core_html(tw_media_translate('core_title')) + '</div></div>').promise().done(function(){
                tw_sync_current_dir_files();
            });
        }

        $(document).on('click', '[tw_media_select_media_item], [tw_media_select_media_folder], [tw_media_select_media_folder_explore], [tw_media_select_switch_folder]', function(e) {
            e.preventDefault();

            if ($(this).data('id') == '') {

                $('[tw_media_select_delete_selected]').removeAttr('data-id');
                $('[tw_media_select_delete_selected]').addClass('disabled');

            } else {
                $('[tw_media_select_delete_selected]').attr('data-id', $(this).data('id'));

                if ($('[tw_media_select_delete_selected]').hasClass('disabled')) {
                    $('[tw_media_select_delete_selected]').removeClass('disabled');
                }
            }

        });

        $(document).on('click', '#media_insert_from_url_btn', function(){

            tw_media_ajax('insert', {url:  $('#media_insert_from_url').val(), parent: tw_get_current_dir()}, {success: resp => {
                if (resp.status == 200) {
                    tw_sync_current_dir_files();
                } else {
                    alert(resp.message);
                }
            }});

            $('#media_insert_from_url').val('');

        });

        $(document).on('click', '.tw_media_select-modal [tw_media_select_media_item]', function() {

            let resource_id = $(this).data('id'), multiple = $(this).parents('.tw_media_select-modal').data('multiple') === true;

            if ($(this).hasClass('active')) {

                // remove resource from local storage
                $.each(window.tw_media_current_selected_items, function(index, resource) {
                    if (typeof resource !== 'undefined' && resource.id == resource_id) {
                        window.tw_media_current_selected_items.splice(index, 1);
                    }
                });

            } else {
                if (typeof window.tw_media_current_selected_items === 'undefined') {
                    window.tw_media_current_selected_items = [];
                }

                if (multiple) {
                    window.tw_media_current_selected_items.push(tw_media_select_get_resource(resource_id));
                } else {
                    $('[tw_media_select_media_item]').each(function(){
                        if ($(this).data('id') != resource_id) {
                            $(this).removeClass('active');
                        }
                    });
                    window.tw_media_current_selected_items = [tw_media_select_get_resource(resource_id)];
                }
            }

            $(this).toggleClass('active');
            tw_media_check_current_selected_items_output(resource_id);

            if (window.tw_media_current_selected_items.length > 0) {
                tw_media_check_selected_items_output();
            } else {
                $('.tw_media_select-modal .tw_media_select_modal_body-selected').hide();
            }

        });

        $(document).on('click', '[tw_media_select_media_folder], #INIT_CORE_FILEMANAGER [tw_media_select_media_item]', function(e) {
            e.preventDefault();
            tw_media_check_current_selected_items_output($(this).data('id'));

        });

        $(document).on('click', '.tw_media_select-modal [tw_media_select_clear_selected]', function(e) {
            e.preventDefault();
            tw_media_clear_selected();
        });


        $(document).on('dblclick', '[tw_media_select_media_folder]', function(e) {

            e.preventDefault();
            tw_set_current_dir($(this).data('id'));
            tw_sync_current_dir_files();

        });

        $(document).on('click', '[tw_media_select_media_folder_explore]', function(e) {

            e.preventDefault();
            tw_set_current_dir($(this).data('id'));
            tw_sync_current_dir_files();

        });

        $(document).on('click', '.tw_media_select-modal [tw_media_select_modal_close]', function(e) {

            e.preventDefault();
            tw_media_clear_selected();
            tw_set_current_dir('');
            $(document).find('.tw_media_select-modal').remove();

            if (typeof window.tw_media_back_to_core_file_manager !== 'undefined') {
                window.tw_media_core_file_manager = window.tw_media_back_to_core_file_manager;
                delete window.tw_media_back_to_core_file_manager;
            }
        });

        $(document).on('click', '[tw_media_select_switch_folder]', function() {

            tw_set_current_dir($(this).data('id'));

            tw_sync_current_dir_files();

        });

        $(document).on('click', '[tw_media_select_create_folder]', function(e) {
            e.preventDefault();

            let folder = prompt('Enter Folder Name');
            if (folder && folder.length > 0) {
                tw_media_ajax('folder', {parent: tw_get_current_dir(), name: folder}, {success: resp => {
                    if (resp.status == 200) {
                        tw_sync_current_dir_files();
                    } else {
                        alert(resp.message);
                    }
                }});
            }

        });

        $(document).on('click', '[tw_media_select_delete_selected], [tw_media_select_media_delete_explore]', function(e) {
            e.preventDefault();

            let resource =  tw_media_select_get_resource($(this).attr('data-id'));
            if (!$(this).hasClass('disabled') && confirm('Are you sure? to delete: ' + resource.title)) {
                tw_media_ajax('delete', {id: resource.id}, {success: resp => {
                    if (resp.status == 200) {
                        
                        tw_sync_current_dir_files();
                        // clear view
                        $('[tw_media_select_delete_selected]').removeAttr('data-id');
                        $('[tw_media_select_delete_selected]').addClass('disabled');

                        $.each(window.tw_media_current_selected_items, function(index, file) {
                            if (typeof file !== 'undefined' && file.id == resource.id) {
                                window.tw_media_current_selected_items.splice(index, 1);
                            }
                        });

                        parent_media_select_target().find('.tw_media_select_modal_body_right-attachment-info').html('');
                        parent_media_select_target().find('.tw_media_select_modal_body_right-no-attachment').show();

                        if (window.tw_media_current_selected_items.length > 0) {
                            tw_media_check_selected_items_output();
                        } else {
                            $('.tw_media_select-modal .tw_media_select_modal_body-selected').hide();
                        }

                    } else {
                        alert(resp.message);
                    }
                }});
            }

        });

        $(document).on('change', '#tw_media_select_upload_input', function(){

            let files = $(this)[0].files, formData = new FormData();

            if (files.length < 1) {
                return;
            }

            for (var i = 0; i < files.length; i++) {
                formData.append('upload[]', $(this)[0].files[i]);
            }

            formData.append('directory', tw_get_current_dir());

            tw_media_ajax('upload', formData, {
                beforeSend: () => {
                    parent_media_select_target().find('[tw_media_select_uploader_label]').slideUp(100, function(){
                        parent_media_select_target().find('[tw_media_select_uploading_progress]').slideDown(100);
                    });
                },
                xhr: percent => {
                    parent_media_select_target().find('[tw_media_select_uploading_counter]').text(percent.toFixed(2) + '%');
                },
                success: resp => {
                    if (resp.status == 200) {
                        tw_sync_current_dir_files();
                    } else {
                        alert(resp.message);
                    }
                },
                complete: () => {
                    setTimeout(() => {
                        parent_media_select_target().find('[tw_media_select_uploading_progress]').slideUp(100, function(){
                            parent_media_select_target().find('[tw_media_select_uploader_label]').slideDown(100);
                        });   
                    }, 1000);

                    $(document).find('#tw_media_select_upload_input').val('');
                }
            }, {
                contentType: false,
                processData: false,
            });

        });

        $(document).on('click', '.tw_media_select-modal [tw_media_select_insert_selected]', function() {

            let input = '#' + $(document).find('.tw_media_select-modal').data('callback');

            var options = '';
            $.each(window.tw_media_current_selected_items, function(index, file) {
                options += `<option value="${file.location}" selected></option>`;
            });

            $(input).html(options);
            $(input).find('option').each(function(){
                $(this).prop("selected", true);
            });

            $(input).trigger('change');

            var html = '';

            $.each(window.tw_media_current_selected_items, function(index, resource) {
                html += `<div>${tw_media_select_file_avatar(resource)}<small>${resource.title}</small></div>`;
            });

            let output = $(input).parents('.tw_media_select').find('.tw_media_select-attachments');
            output.html(html);
            output.show();

            $(document).find('.tw_media_select-modal [tw_media_select_modal_close]').click();

        });

    });

    function tw_open_media_select_modal(parent) {

        let target = parent.find('select').attr('id'), multiple = parent.hasClass('tw_media_select-multiple');

        var html = `<div class="tw_media_select-modal tw_popup_ignore_blur" data-callback="${target}" data-multiple="${multiple ? 'true' : 'false'}">${tw_media_core_html()}
                    <div class="tw_media_select_modal_body-selected" style="display: none;"><div class="flex w-9/12"><div class="w-2/12"><p>
                    <span tw_media_total_selected_item></span> ${tw_media_translate('selected')}</p><a href="#" tw_media_select_clear_selected>${tw_media_translate('clear')}</a></div>
                    <div class="w-10/12 tw_media_select_modal_body-selected-attachments"></div></div><div class="tw_media_select_modal_body-selected-right w-3/12 text-right">
                    <button tw_media_select_modal_close class="tw-btn tw-btn-outline-red">${tw_media_translate('cancel')}</button>
                    <button tw_media_select_insert_selected class="tw-btn tw-btn-sky ml-1">${tw_media_translate('insert')} (<span tw_media_total_selected_item>1</span>)</button></div></div></div></div></div>`;

        $('body').append(html).promise().done(function(){
            tw_sync_current_dir_files();
        });
    }

    function tw_set_current_dir(dirname) {
        window.tw_media_current_dir_path = dirname;
    }

    function tw_get_current_dir() {

        if (typeof window.tw_media_current_dir_path === 'undefined') {
            window.tw_media_current_dir_path = '';
        }

        return window.tw_media_current_dir_path;
    }

    function tw_update_current_breadcrumb() {
        var html ='';
        if (typeof window.media_select_breadcrumb !== undefined && window.media_select_breadcrumb) {
            html += `<span tw_media_select_switch_folder data-id="">Home</span>`;
            html += `<span class="tw_media_select_current_folder_id">${window.media_select_breadcrumb.title}</span>`;
        }

        parent_media_select_target().find('.tw_media_select_modal_body-folder-breadcrumb').html(html);
    }

    function tw_sync_current_dir_files() {

        if(tw_get_current_dir() != ''){
            let resource = tw_media_select_get_resource(tw_get_current_dir());
            if(resource){
                window.media_select_breadcrumb = resource;
            }
        }else{
            window.media_select_breadcrumb = false;
        }

        tw_media_ajax('json', {path: tw_get_current_dir()}, {success: files => {
            var html = '';
            
            window.tw_media_current_resources = files;

            html += '<div class="tw_media_select_modal_body-flat-folder-items">';

            $.each(files, function(index, file) {

                html += `<div class="tw_media_select_modal_body-flat-folder-item ${tw_media_select_is_choosed(file.id) ? 'active' : ''}" ${file.type == 'folder' ? 'tw_media_select_media_folder' : 'tw_media_select_media_item'} data-id="${file.id}">
                            <div>${tw_media_select_file_avatar(file)}<small>${file.title}</small></div>
                        </div>`;
            });

            html += '</div>';

            tw_update_current_breadcrumb();

            parent_media_select_target().find('.tw_media_select_modal_body_left-attachments').html(html);
        }});


    }

    function tw_media_ajax(action, data, callbacks, pre_setup = {}) {

        if (data instanceof FormData) {
            data.append('_token', $('meta[name="_token"]').attr('content'));
        } else {
            data._token = $('meta[name="_token"]').attr('content');
        }

        let setup = {
            url: tw_media_url + action,
            type: 'post',
            dataType: 'json',
            data: data,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                
                if (typeof callbacks.xhr !== 'undefined') {
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            callbacks.xhr(((evt.loaded / evt.total) * 100));
                        }
                    }, false);
                }

                return xhr;
            },
            beforeSend: function() {
                if (typeof callbacks.beforeSend !== 'undefined') {
                    callbacks.beforeSend();
                }

                parent_media_select_target().find('[tw_media_select_ajax_loading]').addClass('loading');
            },
            complete: function() {
                if (typeof callbacks.complete !== 'undefined') {
                    callbacks.complete();
                }

                parent_media_select_target().find('[tw_media_select_ajax_loading]').removeClass('loading');
            },
            success: function(resp) {
                if (typeof callbacks.success !== 'undefined') {
                    callbacks.success(resp);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.warn('Media Select Response Error:');
                console.log(textStatus);
                console.log(xhr);
                console.log(errorThrown);
                if (typeof callbacks.error !== 'undefined') {
                    callbacks.error(resp);
                }
            }
        };

        $.ajax($.extend(setup, pre_setup));

    }

    function tw_media_select_file_avatar(file) {
        switch (file.type) {
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/png':
            case 'image/gif':
            case 'image/svg':
            case 'image/webp':
            case 'jpeg':
            case 'jpg':
            case 'png':
            case 'gif':
            case 'svg':
            case 'webp':
                return '<img src="'+ (file.url ?? 'https://cdn0.iconfinder.com/data/icons/housing-interface-1/16/no_document-512.png') +'"/>';
                break;
            case 'folder':
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512"><path fill="currentColor" d="M408,96H252.11a23.89,23.89,0,0,1-13.31-4L211,73.41A55.77,55.77,0,0,0,179.89,64H104a56.06,56.06,0,0,0-56,56v24H464C464,113.12,438.88,96,408,96Z"/><path fill="currentColor" d="M423.75,448H88.25a56,56,0,0,1-55.93-55.15L16.18,228.11l0-.28A48,48,0,0,1,64,176h384.1a48,48,0,0,1,47.8,51.83l0,.28L479.68,392.85A56,56,0,0,1,423.75,448ZM479.9,226.55h0Z"/></svg>';
                break;
            case 'text/plain':
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512"><path fill="currentColor" d="M428,224H288a48,48,0,0,1-48-48V36a4,4,0,0,0-4-4H144A64,64,0,0,0,80,96V416a64,64,0,0,0,64,64H368a64,64,0,0,0,64-64V228A4,4,0,0,0,428,224ZM336,384H176a16,16,0,0,1,0-32H336a16,16,0,0,1,0,32Zm0-80H176a16,16,0,0,1,0-32H336a16,16,0,0,1,0,32Z"/><path fill="currentColor" d="M419.22,188.59,275.41,44.78A2,2,0,0,0,272,46.19V176a16,16,0,0,0,16,16H417.81A2,2,0,0,0,419.22,188.59Z"/></svg>';
                break;
            default:
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512"><path fill="currentColor" d="M428,224H288a48,48,0,0,1-48-48V36a4,4,0,0,0-4-4H144A64,64,0,0,0,80,96V416a64,64,0,0,0,64,64H368a64,64,0,0,0,64-64V228A4,4,0,0,0,428,224Z"/><path fill="currentColor" d="M419.22,188.59,275.41,44.78A2,2,0,0,0,272,46.19V176a16,16,0,0,0,16,16H417.81A2,2,0,0,0,419.22,188.59Z"/></svg>';
                break;
        }
    }


    function tw_media_check_current_selected_items_output(last_id) {

        var html = '', resource = tw_media_select_get_resource(last_id);

        if (!resource) {
            console.warn('Media Select File Not Found: ' + last_id);
            return;
        }

        html = `${tw_media_select_file_avatar(resource)}<p>${tw_media_translate('type')} : ${resource.type}</p><p>${tw_media_translate('name')} : ${resource.title}</p>`;

        if (resource.type != 'folder') {
            html += `<p>${tw_media_translate('size')} : ${resource.size}</p><p>${tw_media_translate('datetime')} : ${resource.modified}</p><p>${tw_media_translate('download_url')} : <a target="_blank" href="${resource.url}">${tw_media_translate('click_here')}</a></p>`;
        }

        parent_media_select_target().find('.tw_media_select_modal_body_right-no-attachment').hide();
        parent_media_select_target().find('.tw_media_select_modal_body_right-attachment-info').html(html);

    }

    function tw_media_select_get_resource(id) {

        var found = false;
        $.each(window.tw_media_current_resources, function(index, file) {

            if (id == file.id) {
                found = file;
                return;
            }

        });

        return found;
    }

    function tw_media_select_is_choosed(id) {

        var found = false;

        $.each(window.tw_media_current_selected_items, function(index, file) {

            if (id == file.id) {
                found = true;
                return;
            }

        });

        return found;
    }

    function tw_media_check_selected_items_output() {

        var html = '';

        $.each(window.tw_media_current_selected_items, function(index, resource) {
             html += `<div>${tw_media_select_file_avatar(resource)}<small>${resource.title}</small></div>`;
        });

        $('.tw_media_select-modal .tw_media_select_modal_body-selected-attachments').html(html).promise().done(function(){
            $('.tw_media_select-modal .tw_media_select_modal_body-selected').show();
            $('.tw_media_select-modal [tw_media_total_selected_item]').text(window.tw_media_current_selected_items.length);
        });

    }

    function tw_media_clear_selected() {

        $('[tw_media_select_media_item]').each(function(){
            $(this).removeClass('active');
        });

        window.tw_media_current_selected_items = [];
        $('.tw_media_select-modal .tw_media_select_modal_body-selected').hide();

    }

    function tw_media_translate(key) {

        if (typeof tw_media_lang[key] !== 'undefined') {
            return tw_media_lang[key];
        }

        return key;
    }

    function tw_media_core_html(title = tw_media_translate('title')) {
        return `<div class="tw_media_select_modal-body loader-parent"><div tw_media_select_ajax_loading><div class="loader loader-lg"></div></div>
                <div class="tw_media_select_modal_body-header"><div><h1 tw_media_select_modal_title>${title}</h1><div class="flex mt-2"><a href="#" tw_media_select_create_folder class="tw-btn tw-btn-sm tw-btn-indigo tw-btn-flex mr-2"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 512 512"><line x1="256" y1="112" x2="256" y2="400" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"/><line x1="400" y1="256" x2="112" y2="256" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"/></svg><span>${tw_media_translate('new_folder')}</span></a><a tw_media_select_delete_selected class="tw-btn disabled tw-btn-sm tw-btn-red tw-btn-flex" href="#"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 512 512"><path fill="currentColor" d="M112,112l20,320c.95,18.49,14.4,32,32,32H348c17.67,0,30.87-13.51,32-32l20-320" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"/><line x1="80" y1="112" x2="432" y2="112" style="stroke:currentColor;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px"/><path fill="currentColor" d="M192,112V72h0a23.93,23.93,0,0,1,24-24h80a23.93,23.93,0,0,1,24,24h0v40" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"/><line x1="256" y1="176" x2="256" y2="400" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"/><line x1="184" y1="176" x2="192" y2="400" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"/><line x1="328" y1="176" x2="320" y2="400" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"/></svg><span>${tw_media_translate('delete')}</span></a></div></div>
                    <span tw_media_select_modal_close><svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" viewBox="0 0 512 512"><path fill="currentColor" d="M289.94,256l95-95A24,24,0,0,0,351,127l-95,95-95-95A24,24,0,0,0,127,161l95,95-95,95A24,24,0,1,0,161,385l95-95,95,95A24,24,0,0,0,385,351Z"></path></svg></span>
                </div><div class="tw_media_select_modal_body-row"><div class="tw_media_select_modal_body-left"><div class="tw_media_select_modal_body_left-upload">
                    <input type="file" multiple name="uploads[]" id="tw_media_select_upload_input" style="display: none;"><label tw_media_select_uploader_label for="tw_media_select_upload_input">
                    <span><svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 512 512"><path fill="currentColor" d="M320,367.79h76c55,0,100-29.21,100-83.6s-53-81.47-96-83.6c-8.89-85.06-71-136.8-144-136.8-69,0-113.44,45.79-128,91.2-60,5.7-112,43.88-112,106.4s54,106.4,120,106.4h56" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"></path><polyline points="320 255.79 256 191.79 192 255.79" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"></polyline><line x1="256" y1="448.21" x2="256" y2="207.79" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"></line></svg></span><span>${tw_media_translate('drag_and_upload_files')}</span><small class="block mt-1" style="font-weight:400">${tw_media_translate('max_upload_filesize')}</small>
                    </label><div style="display:none;" tw_media_select_uploading_progress><span tw_media_select_uploading_counter></span><p>${tw_media_translate('uploading_resources')}</p></div>
                    </div><div class="flex mb-2 items-center" style="margin:0 2rem"><input class="tw-input mr-2" id="media_insert_from_url" placeholder="${tw_media_translate('enter_form_url')}"><button id="media_insert_from_url_btn" class="tw-btn tw-btn-sky">${tw_media_translate('insert')}</button></div><div class="tw_media_select_modal_body-folder-breadcrumb"></div><div class="tw_media_select_modal_body_left-attachments" style="height: 350px"></div></div>
                    <div class="tw_media_select_modal_body-right"><div class="tw_media_select_modal_body_right-no-attachment">
                    <span><svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 512 512"><rect x="48" y="80" width="416" height="352" rx="48" ry="48" style="fill:none;stroke:currentColor;stroke-linejoin:round;stroke-width:32px"></rect><circle cx="336" cy="176" r="32" style="fill:none;stroke:currentColor;stroke-miterlimit:10;stroke-width:32px"></circle><path fill="currentColor" d="M304,335.79,213.34,245.3A32,32,0,0,0,169.47,244L48,352" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"></path><path fill="currentColor" d="M224,432,347.34,308.66a32,32,0,0,1,43.11-2L464,368" style="fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"></path></svg></span>
                    <p>${tw_media_translate('file_not_selected')}</p></div><div class="tw_media_select_modal_body_right-attachment-info"></div></div>`;
    }


    function parent_media_select_target() {
        
        if (typeof window.tw_media_core_file_manager !== 'undefined') {
            return $(window.tw_media_core_file_manager);
        }

        return $(document).find('.tw_media_select-modal');

    }

}) (jQuery);
