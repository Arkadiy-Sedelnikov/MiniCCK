function fieldAdd(){
    var counter = jQuery('#numFields');
    var numFields = parseInt(counter.val());

    while (jQuery('#field_'+numFields).length > 0) {
        numFields ++;
    }
    var field 	 = jQuery('div.field_contayner:first');
    var newField = field.clone();

    jQuery('input.name', newField)
        .val('field_'+(numFields+1))
        .attr('name', 'jform[params][customfields]['+numFields+'][name]');

    jQuery('input.title', newField)
        .val('')
        .attr('name', 'jform[params][customfields]['+numFields+'][title]');

    jQuery('.chzn-container', newField).remove();

    jQuery('select.type', newField)
        .attr('name', 'jform[params][customfields]['+numFields+'][type]')
        .show();


    jQuery('textarea.params', newField)
        .text('')
        .attr('name', 'jform[params][customfields]['+numFields+'][params]');

    jQuery('input.del-button', newField)
        .attr('onclick', 'fieldDel("field_'+numFields+'")');

    newField.attr('id', 'field_'+numFields);


    counter.before(newField);
    counter.val(numFields+1);
    jQuery('select.type', '#field_'+numFields)
        .chosen({
            disable_search_threshold : 10,
            allow_single_deselect : true
        });
    window.location.hash = 'field_'+numFields;
    anchor();
}

function fieldDel(id){
    var counter = jQuery('#numFields');
    var numFields = parseInt(counter.val());
    if(numFields > 1){
        jQuery('#'+id).remove();
        counter.val(jQuery('.field_contayner').length);
    }
}

//anchor
function anchor(){
    var hash = window.location.hash;
    var scroll = $(window).scrollTop();
    if(hash != null){
        $(window).scrollTop(scroll-20);
    }
}
