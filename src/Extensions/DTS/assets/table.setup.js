$(document).ready(() => {

    $('.datatable').each(function() {

        var columns    = [{ targets: "no-sort", searchable: !1, orderable: !1 }],
            ajax_url   = $(this).data('serverside'),
            serverside = (typeof ajax_url !== 'undefined' && ajax_url.length > 0) ? true : false,
            id         = $(this).attr('id'),
            ajax       = false,
            order      = [];

        if( serverside == true ){
            ajax = {
                url: ajax_url,
                type: "POST",
                data: {
                    _token: $("meta[name='_token']").attr("content"),
                    filter: function(){return $('[dt_filter="'+id+'"]').val()},
                    params: function(){return $('#'+ id + '-param').val()}
                }
            }
        }

        $(this).find('thead tr th').each(function( index ) {
            columns.push({ className: $(this).attr('class'), "targets": [ index ] });
            if($(this).data('order') !== undefined && $(this).data('order').length > 0){
                order.push([index, $(this).data('order')]);
            }
        });

        $(this).DataTable({
            language: {
                infoEmpty: dt_lang.empty,
                zeroRecords: dt_lang.zeroRecords,
                lengthMenu: dt_lang.lengthMenu,
                info: dt_lang.info,
                infoFiltered: dt_lang.infoFiltered,
                search: dt_lang.search,
                processing: dt_lang.processing,
                loadingRecords: dt_lang.loadingRecords,
                paginate: {
                    first: dt_lang.first,
                    last: dt_lang.last,
                    next: dt_lang.next,
                    previous: dt_lang.prev,
                }
            },
            order: order.length > 0 ? order : [[0, "desc"]],
            processing: serverside,
            columnDefs: columns,
            pageLength: 10,
            serverSide: serverside,
            dataType: "json",
            ajax: ajax
        });
    });

    $('.datatable').on('click', '[tw_dt_action_btn]', function(event){
        event.preventDefault();
        let param = {
            action: $(this).attr('tw_dt_action_btn'), 
            url: $(this).attr('href'),
            table: $(this).parents('.datatable').attr('id')
        };
        if(param.action == 'destroy'){
            if (confirm('Are you sure! to delete this?')) {
                return make_ajax(param);
            }
        }else{
            return make_ajax(param);
        }
    });

    $('[dt_filter]').each(function(){
        $(this).on('change', function(){
            resetDataTable($(this).attr('dt_filter'));
        });
    });


    var ajax_proccessing = false;
    function make_ajax(param) {
        
        if (ajax_proccessing) return;
        
        $.ajax({
            url: param.url,
            type: 'post',
            dataType: 'json',
            beforeSend: function(xhr) {
                ajax_proccessing = true
            },
            data: {
                action: param.action,
                _token: $("meta[name='_token']").attr("content"),
                _method: param.action == 'destroy' ? 'delete' : 'options'
            }
        })
        .done(function(resp) {
            window.SweetAlert('success', resp.message);
            resetDataTable( param.table );

        })
        .fail(function(response) {
            let resp = response.responseJSON
            if (resp && resp.message) {
                window.SweetAlert('error', resp.message);
            } else {
                window.SweetAlert('error', 'Internal Server Error');
            }
        })
        .always(function() {
            ajax_proccessing = false;
        });
    }


    function resetDataTable( target ){
        var target = $('#' + target), table = target.DataTable(), reload = target.data('serverside');
        if(typeof reload !== 'undefined' && reload == false){
            setTimeout(function(){
                location.reload();
            }, 1500);
        }else{
            table.ajax.reload(null, false);
        }
    }

});