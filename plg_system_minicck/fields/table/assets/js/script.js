/**
 * Created by ArkadiyS on 21.03.14.
 */

function tableADDRow(element, $id, $fieldname, $name, countColumns) {
    var tbody = jQuery('#'+$id+' tbody');
    var newRowNum = jQuery('tr', tbody).length;
    var row = jQuery('<tr/>');
    var td, input;

    while(tbody.find('#tr_'+$id+'_'+newRowNum).length>0){
        newRowNum++;
    }

    var rowId = 'tr_'+$id+'_'+newRowNum;
    row.attr('id', rowId);

    for(i=0;i<countColumns;i++){
        input = jQuery('<input/>')
            .attr('name', $fieldname+'['+newRowNum+']'+'['+i+']')
            .val('');
        td = jQuery('<td/>').append(input).appendTo(row);
    }
    td = jQuery('<td/>')
        .append(
        jQuery('<a/>')
            .attr('href', '#')
            .addClass('btn')
            .attr('onclick', 'tableDeleteRow(this); return false;')
            .attr('title', 'Delete')
            .html('<i class="icon-remove"></i>')
    ).appendTo(row);
    tbody.append(row);
}

function tableDeleteRow(element) {
    jQuery(element).parents('tr').remove();
}