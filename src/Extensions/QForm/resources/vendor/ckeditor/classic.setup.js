// Render Minimal Editors

const UploadAdapter = {
    // The URL that the images are uploaded to.
    uploadUrl: ck_media_upload_url,

    // Enable the XMLHttpRequest.withCredentials property.
    withCredentials: true,

    // Headers sent along with the XMLHttpRequest to the upload server.
    headers: {
        'X-CSRF-TOKEN': $("meta[name='_token']").attr("content")
    }
};

for (const ELEMENT of document.querySelectorAll('.html_minimal_editors')) {

    let items = [];
    if(ELEMENT.getAttribute('editor') === 'tiny'){
        items = ['bold','italic','link','bulletedList','|','highlight','fontSize','fontColor'];
    }else if(ELEMENT.getAttribute('editor') === 'text'){
        items = ['heading','|','bold','italic','link','bulletedList','insertImage','mediaEmbed','highlight','pageBreak'];
    }else{
        items = ['heading','|','bold','italic','link','blockQuote','underline','bulletedList','alignment','insertTable','insertImage','mediaEmbed','highlight','fontColor','fontSize','pageBreak', 'sourceEditing'];
    }

    ClassicEditor.create(ELEMENT, {
        simpleUpload: UploadAdapter,
        toolbar: {
            items: items
        }
    }).then( editor => {
        editor.editing.view.change(writer => {
            writer.setStyle('height', ELEMENT.getAttribute('data-height') + 'px', editor.editing.view.document.getRoot());
        });
    }).catch(error => {
        console.warn( 'Failed! Mount Minimal Editor:' );
    });
}

// Render Single Html Editor
const EDITOR_DIV = document.querySelector('#html_init_editor');

if (EDITOR_DIV !== null) {
    ClassicEditor.create(EDITOR_DIV, {
        simpleUpload: UploadAdapter,
        autosave: {
            save: async () => {
                return save_ckeditor_data();
            },
            waitingTime: 6000
        }
    }).then( editor => {
        editor.editing.view.change(writer => {
            writer.setStyle('height', EDITOR_DIV.getAttribute('data-height') + 'px', editor.editing.view.document.getRoot());
        });
        window.editor = editor;
    }).catch( error => {
        console.error( 'Oops, something went wrong!' );
        console.error( 'Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:' );
        console.warn( 'Build id: 1qqlrhz1pufo-qmj0usngqoo8' );
        console.error( error );
    });

    let save_btn = EDITOR_DIV.getAttribute('save_btn');

    if (typeof save_btn !== 'undefined' && save_btn !== null) {
        document.querySelector(save_btn).addEventListener('click', () => {
            window.editor.plugins.get('Autosave').save();
        });
    }

    function save_ckeditor_data() {
        var show_on_saving = EDITOR_DIV.getAttribute('show_on_saving'),
            hide_on_saving = EDITOR_DIV.getAttribute('hide_on_saving'),
            message_box    = EDITOR_DIV.getAttribute('message_box');
    
        $.ajax({
            type: "post",
            url: EDITOR_DIV.getAttribute('server_url'),
            data: {
                content: window.editor.getData(),
                _token: $("meta[name='_token']").attr("content")
            },
            dataType: "json",
            beforeSend: function () {
                if (typeof show_on_saving !== 'undefined' && show_on_saving !== null) {
                    $(show_on_saving).show();
                }
    
                if (typeof hide_on_saving !== 'undefined' && hide_on_saving !== null) {
                    $(hide_on_saving).hide();
                }
            },
            success: function (resp) {
                if (typeof message_box !== 'undefined' && message_box !== null) {
                    $(message_box).html(resp.message);
                    $(message_box).removeClass('error');
                    $(message_box).removeClass('success');
                    $(message_box).addClass(resp.status);
                    $(message_box).slideDown(100);
    
                    setTimeout(() => {
                        $(message_box).slideUp(100);
                    }, 3500);
                }
            }
        })
        .always(function() {
            
            if (typeof show_on_saving !== 'undefined' && show_on_saving !== null) {
                $(show_on_saving).hide();
            }
    
            if (typeof hide_on_saving !== 'undefined' && hide_on_saving !== null) {
                $(hide_on_saving).show();
            }
        });
    }
}

// Remove Loader after editor loaded..
$(window).on('load', () => {
    $('.html_editor_loading').removeClass('loading');
});