$(document).ready(function(){
    //-- Add New @row
    $('.dynamictable').on('click', '.TDAddRow', function(){

        var field       = $(this).data('field'),
            target      = $(".DTinit[data-field='"+ field +"']"),
            fields      = $('[dt_form="'+ field +'"]').find('.dt_input'),
            row         = {},
            abort       = false,
            __id        = Math.floor(Math.random() * (500 - 50 + 1) + 50),
            __values    = null,
            group       = null,
            groupfield  = null,
            columns     = JSON.parse($('#DT-'+ (target.data('group') !== undefined ? target.data('group') : field) +'-columns').val()),
            _row        = '<tr scope="row" class="border-b" data-id="'+ __id +'">';

        fields.each(function( index ) {
            if(abort) return;
            let _column = $(this).data('column'),
                _value = $(this).val();

            if(_value != null && _value && _value.length == 0 ){
                if(typeof $(this).attr('required') !== 'undefined'){
                    window.SweetAlert('warning', 'filed: ' + _column + ' is empty, please enter a value for this field.');
                    abort = true;
                    return;
                }
            }else{
                abort = false;
            }
            row[_column] = _value;
        })

        $.each(columns, function(index, column){
            
            if( column === 'remove' ){
                row['id'] = __id;
                _row += `<td class="py-2 px-4">
                            <button type="button" data-id="${__id}" data-field="${field}" class="deleteRow tw-btn tw-btn-red tw-btn-sm">
                                <i class='bx bx-trash'></i>
                            </button>
                        </td>`;
                
                } else {
                    _row += '<td class="py-2 px-4">'+ row[column] +'</td>'
                }        
            });
                
        _row += '</tr>';

        if(abort) return;
        target.find('.no-data').hide();
        target.find('tbody').append(_row);
        fields.val('');
        
        if (target.data('group') !== undefined) {
            group       = target.data('group');
            groupfield  = target.data('groupfield');
            __values    = $("#DT-" + group);
        }else{
            __values    = $("#DT-" + field);
        }

        var _arr = __values.val();

        if(_arr !== null && _arr.length > 0 && typeof _arr === 'string'){
            _arr = JSON.parse(_arr);
        }else{
            _arr = new Array();
        }
        if(group !== null){
            _arr[groupfield].push(row);
        }else{
            _arr.push(row);        
        }
        __values.val( JSON.stringify(_arr) );

        $('[tw_modal-view="'+ field +'"]').hide();
    })

    //-- @Delete row
    $('.dynamictable').on('click', '.deleteRow', function(){

        let id = parseInt($(this).data('id')),
            field   = $(this).data('field'),
            target  = $(".DTinit[data-field='"+ field +"']"),
            values  = null,
            group       = null,
            groupfield  = null;

        if (target.data('group') !== undefined) {
            group       = target.data('group');
            groupfield  = target.data('groupfield');
            values    = $("#DT-" + group);
        }else{
            values    = $("#DT-" + field);
        }

        if (target.find('tr[scope="row"]').length < 2) {
            target.find('.no-data').show();
        }

        var _arr = JSON.parse(values.val());

        if (group !== null) {
            var arr = _arr[groupfield];
            arr = $.grep(arr, function(elm) {
                return elm.id != id;
            });
            _arr[groupfield] = arr;
        }else{
            _arr = $.grep(_arr, function(elm) {
                return elm.id != id;
            });
        }

        values.val(JSON.stringify(_arr));
        target.find("tr[data-id='"+ id +"']").remove();

    })


    // .. Delete Group
    $('.dynamictable').on('click', '.delete-group', function(){
        var group       = $(this).data('group'),
            groupfield  = $(this).data('groupfield'),
            target      = $("#DT-" + group),
            values      = JSON.parse(target.val());

        delete values[groupfield];
        $('.dynamictable').find('.group-table[data-groupfield="'+ groupfield +'"]').remove();

        if ($('#dtroot-' + group).find('.group-table').length < 1) {
            $('#dtroot-' + group).find('.empty_group').show();
        }

        target.val(JSON.stringify(values));
    });

    // .. Add New Group
    $('.dynamictable').on('click', '.create-new-group', function(){
        var parent  = $(this).data('group'),
            title   = $('.group_title_field').val(),
            id      =  parent + '_' + makeSlug(title),
            db      = null,
            target  = $('#dtroot-' + parent),

            view = target.find('.new-grouptable-view').html();
            view = view.replaceAll('{examplegroupfield}', id);
            view = view.replaceAll('{examplegroup}', title);
            view = view.replaceAll('{exampleparent}', parent);

            var html = `<div class="group-table bg-slate-50 border m-4 rounded shadow" data-groupfield="${title}">
                <div class="group-table-header p-2 border-b flex justify-between">
                    <span class="text-lg">${title}</span>
                    <div>
                        <button type="button" class="tw-btn tw-btn-red tw-btn-sm tw-btn-flex delete-group" data-group="${parent}" data-groupfield="${title}">
                            <i class='bx bx-trash' ></i>
                            <span class="ml-1">Delete</span>
                        </button>
                    </div>
                </div>
                ${view}
            </div><div style="height:1px;"></div>`;

            $('.group_title_field').val('');
            $('#dtroot-' + parent).find('.empty_group').hide();

            db          = $("#DT-" + parent),
            values      = JSON.parse(db.val());
            if (values.length < 1) {
                values = {};
            }
            values[title] = [];
            target.append(html);
            db.val(JSON.stringify(values));
            $(this).parents('[tw_modal-view]').hide();
    });
});


//.. Convert String to slug
function makeSlug( _text )
{
    return _text
        .toLowerCase()
        .replace(/[^\w ]+/g,'')
        .replace(/ +/g,'-');
}
