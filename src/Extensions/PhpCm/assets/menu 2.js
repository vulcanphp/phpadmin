$(document).ready(function(){

    $(".phpcm_menu").sortable();

    $('#phpcm_save_menu').on('click', function(){
        var menus = [];
        $('ol.phpcm_menu').find('li').each(function(index){
            menu = {
                id: $(this).data('id'),
                position: index,
                parent: $(this).parents('li').data('id') ?? null
            };

            menus.push(menu);
        });

        $.ajax({
            type: "post",
            data: {_token: $('meta[name="_token"]').attr('content'), action:'save_menu', menus: menus},
            dataType: "json",
            success: function (resp) {
                window.SweetAlert('success', resp.message);
            }
        }).fail(function(response) {
            let resp = response.responseJSON
            if (resp && resp.message) {
                window.SweetAlert('error', resp.message);
            } else {
                window.SweetAlert('error', 'Internal Server Error');
            }
        });
    });

    $(document).on('click', 'span[phpcm_del_menu]', function(){

        let id = $(this).parents('li').data('id');

        $.ajax({
            type: "post",
            data: {_token: $('meta[name="_token"]').attr('content'), action:'delete_menu', id: id},
            dataType: "json",
            success: function (resp) {
                window.SweetAlert('success', resp.message);
                $('.phpcm_menu').find('li[data-id="'+ id +'"]').remove();
            }
        }).fail(function(response) {
            let resp = response.responseJSON
            if (resp && resp.message) {
                window.SweetAlert('error', resp.message);
            } else {
                window.SweetAlert('error', 'Internal Server Error');
            }
        });

    });

    $('#menu_location').on('change', function(){
        window.location = '?active=' + $(this).val();
    });

});