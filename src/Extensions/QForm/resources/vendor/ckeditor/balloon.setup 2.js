// Render Single Inline Html Editor
const EDITOR_DIV = document.querySelector(balloon_conf.target);

if (EDITOR_DIV !== null) {
    BalloonBlockEditor.create(EDITOR_DIV, {
        simpleUpload: {
            // The URL that the images are uploaded to.
            uploadUrl: balloon_conf.media_upload,

            // Enable the XMLHttpRequest.withCredentials property.
            withCredentials: true,

            // Headers sent along with the XMLHttpRequest to the upload server.
            headers: {
                'X-CSRF-TOKEN': $("meta[name='_token']").attr("content")
            }
        },
        autosave: {
            save: async () => {
                return save_ckeditor_data();
            },
            waitingTime: 6000
        }
    }).then( editor => {
        window.editor = editor;
    }).catch(error => {
        console.error('Oops, something went wrong!');
        console.error('Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:');
        console.warn('Build id: 1qqlrhz1pufo-qmj0usngqoo8');
        console.error(error);
    });

    function save_ckeditor_data() {
        $.ajax({
            type: "post",
            url: balloon_conf.save_url,
            data: {
                content: window.editor.getData(),
                _token: $("meta[name='_token']").attr("content")
            },
            dataType: "json",
            beforeSend: function () {
                $('#SaveBalloonChanges').addClass('loading');
                $('#SaveBalloonChanges').html(`<svg style="color:white; height:30px;width:30px;" class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity:0.75" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>`);
            },
            success: function () {
                
            }
        })
        .always(function () {
            $('#SaveBalloonChanges').removeClass('loading');
            $('#SaveBalloonChanges').html(balloon_conf.save_text);
        });
    }

    $(document).on('click', '#SaveBalloonChanges', function () {
        if (!$(this).hasClass('loading')) {
            window.editor.plugins.get('Autosave').save();
        }
    });

    $('body').append(`
        <style>
            @-webkit-keyframes spin {to {transform: rotate(360deg);}}
            @keyframes spin {to {transform: rotate(360deg);}}
            .animate-spin {-webkit-animation: spin 1s linear infinite;animation: spin 0.5s linear infinite;}
            .ck_btn_action{
                opacity:.85;text-decoration:none;border: 1px;background: #7e22ce;color: #fff;display: inline-block;width: 40px;height: 40px;border-radius: 50%;cursor: pointer;box-shadow: 0px 1px 2px 1px #7e22ce94;font-size:16px;display:flex; align-items:center; justify-content:center;
            }
            .ck_btn_action:hover{
                opacity:1;
            }
            .ck_btn_action.ck_btn_action_lg{
                width: 55px; height:55px; background: #0284c7;box-shadow: 0px 1px 2px 1px #0284c769; font-size:30px;
            }
            .ck_btn_action.loading{
                cursor: wait;
                opacity: .65;
            }
        </style>
        <div style="position:fixed;bottom: 20px;left:0;right: 0;margin:auto;display:flex;align-items:center;z-index: 999;width: max-content;">
            <a href="${balloon_conf.back_href}" title="${balloon_conf.back_title}" class="ck_btn_action">${balloon_conf.back_text}</a>
            <button id="SaveBalloonChanges" title="${balloon_conf.save_title}" class="ck_btn_action ck_btn_action_lg" style="margin: 0 15px;">${balloon_conf.save_text}</button>
            <a target="_blank" href="${balloon_conf.forward_href}" title="${balloon_conf.forward_title}" class="ck_btn_action">${balloon_conf.forward_text}</a>
        </div>
    `);
}