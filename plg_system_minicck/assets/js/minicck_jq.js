function fieldAdd()
{
    var numFields = 0;
    var fieldName = 1;

    while (jQuery('#field_'+(numFields)).length > 0) {
        numFields ++;
    }

    while (jQuery('#name_field_'+fieldName).length > 0) {
        fieldName ++;
    }

    var field 	 = jQuery('div.field_contayner:first');
    var newField = field.clone();

    jQuery('input.name', newField)
        .val('field_'+(fieldName))
        .attr('name', 'jform[params][customfields]['+numFields+'][name]')
        .attr('id', 'name_field_'+fieldName)
        .removeAttr('readonly');

    jQuery('input.title', newField)
        .val('')
        .attr('name', 'jform[params][customfields]['+numFields+'][title]');

    jQuery('.chzn-container', newField).remove();

    jQuery('select.type', newField)
        .attr('name', 'jform[params][customfields]['+numFields+'][type]')
        .attr('onchange', 'loadExtraFields(this, '+numFields+')')
        .prop('selectedIndex',0)
        .show();

    jQuery('.extra_params', newField)
        .attr('id', 'extra_params_'+numFields)
        .text('');


    jQuery('textarea.params', newField)
        .text('')
        .attr('name', 'jform[params][customfields]['+numFields+'][params]');

    jQuery('input.del-button', newField)
        .attr('onclick', 'fieldDel("field_'+numFields+'")');

    newField.attr('id', 'field_'+numFields);

    jQuery('#numFields').before(newField);

    jQuery('select.type', '#field_'+numFields)
        .chosen({
            disable_search_threshold : 10,
            allow_single_deselect : true
        });
    window.location.hash = 'field_'+numFields;
    anchor();
}


function fieldDel(id)
{
    var numFields = jQuery('.field_contayner').length;
    if(numFields > 1){
        jQuery('#'+id).remove();
    }
}

//anchor
function anchor(){
    var hash = window.location.hash;
    var scroll = jQuery(window).scrollTop();
    if(hash != null){
        jQuery(window).scrollTop(scroll-20);
    }
}

function contentTypeAdd(){
    var numFields = jQuery('.content_type_contayner').length;
    var type = jQuery('div.content_type_contayner:first');
    var newContentType = type.clone();

    while (jQuery('#content_type_'+numFields).length > 0)
    {
        numFields ++;
    }

    jQuery('input.name', newContentType)
        .val('content_type_'+(numFields+1))
        .attr('name', 'jform[params][content_types]['+numFields+'][name]')
        .removeAttr('readonly');

    jQuery('input.title', newContentType)
        .val('')
        .attr('name', 'jform[params][content_types]['+numFields+'][title]');

    jQuery('.chzn-container', newContentType).remove();

    var select = jQuery('select.content_type_tmpl', newContentType);
    jQuery('option:selected', select).removeAttr('selected');
    select.attr('name', 'jform[params][content_types]['+numFields+'][content_tmpl]')
        .show()
        .chosen({
            disable_search_threshold : 10,
            allow_single_deselect : true
        });

    select = jQuery('select.category_type_tmpl', newContentType);
    jQuery('option:selected', select).removeAttr('selected');
    select.attr('name', 'jform[params][content_types]['+numFields+'][category_tmpl]')
        .show()
        .chosen({
            disable_search_threshold : 10,
            allow_single_deselect : true
        });

    jQuery('input.field_name', newContentType).each(function()
        {
            var cb = jQuery(this);
            var name_suffix = cb.attr('id').split('-');
            cb.removeAttr('checked')
                .attr('name', 'jform[params][content_types]['+numFields+'][fields]['+name_suffix[0]+']['+name_suffix[1]+']');
        }
    );

    jQuery('input.del-button', newContentType)
        .attr('onclick', 'contentTypeDel("content_type_'+numFields+'")');

    newContentType.attr('id', 'content_type_'+numFields);

    jQuery('#numTypes').before(newContentType);

    window.location.hash = 'content_type_'+numFields;
    anchor();
}

function contentTypeDel(id)
{
    var numFields = jQuery('.content_type_contayner').length;
    if(numFields > 1)
    {
        jQuery('#'+id).remove();
    }
}

function checkEnter(element)
{
    var el = jQuery(element);

    if(el.attr('readonly') == 'readonly')
        return;

    var value = el.val();
    value = translit(value);
    el.val(value);
}

function translit(value)
{
    en_to_ru = {
        'а': 'a',  'б': 'b',   'в': 'v',  'г': 'g', 'д': 'd',
        'е': 'e',  'ё': 'jo',  'ж': 'zh', 'з': 'z', 'и': 'i',
        'й': 'j',  'к': 'k',   'л': 'l',  'м': 'm', 'н': 'n',
        'о': 'o',  'п': 'p',   'р': 'r',  'с': 's', 'т': 't',
        'у': 'u',  'ф': 'f',   'х': 'h',  'ц': 'c', 'ч': 'ch',
        'ш': 'sh', 'щ': 'sch', 'ъ': '',   'ы': 'y', 'ь': '',
        'э': 'je', 'ю': 'ju',  'я': 'ja', ' ': '-', 'і': 'i',
        'ї': 'i'
    };
    value = value.toLowerCase();
    value = trim(value);
    value = value.split("");
    var trans = new String();
    for (i = 0; i < value.length; i++) {
        for (var key in en_to_ru) {
            val = en_to_ru[key];
            if (key == value[i]) {
                trans += val;
                break;
            } else if (key == "ї") {
                trans += value[i];
            }
        }
    }
    return trans;
}

function trim(string)
{
    string = string.replace(/'|"|<|>|\!|\||@|#|$|%|^|\^|\$|\\|\/|&|\*|\(\)|\|\/|;|\+|№|,|\?|:|{|}|\[|\]/g, "");
    string = string.replace(/(^\s+)|(\s+$)/g, "");
    return string;
}

function reloadMinicckFields(element){
    var type = jQuery(element).val();
    if (type == ''){
        return;
    }

    var typeFields = minicckTypeFields[type];

    var count = 0;
    for (var key in typeFields){count++;}
    if(count == 0)
        return;

    jQuery('div.control-group', '#minicck')
        .not('#minicck_content_type_contayner')
        .hide();
    jQuery('input', '#minicck')
        .not(' #minicck_multi_categories_chzn input')
        .attr('disabled','disabled');
    jQuery('select', '#minicck')
        .not('#minicckcontent_type, #minicck_multi_categories')
        .attr('disabled','disabled');
    jQuery('textarea', '#minicck')
        .attr('disabled','disabled');
    for (var i=0; i < typeFields.length; i++)
    {
        var cont = jQuery('div.control-group.'+typeFields[i]);
        cont.show();
        jQuery('input', cont).removeAttr('disabled');
        jQuery('select', cont).removeAttr('disabled').trigger("liszt:updated");
        jQuery('textarea', cont).removeAttr('disabled');
    }
}

function loadExtraFields(select, id)
{
    var field = jQuery(select).val();
    var extraParamsDiv = jQuery('#extra_params_'+id);
    extraParamsDiv.text('');

    if(!fieldsExtraOptions[field]){
        return;
    }

    var extraFields = fieldsExtraOptions[field][0];

    for(var i = 0; i < extraFields.length; i++)
    {
        var element;
        var parent;

        if(extraFields[i].type == 'textarea')
        {
            element = jQuery('<textarea/>');
            element.html(extraFields[i].value);
            element.attr('aria-invalid', false);
        }
        else if(extraFields[i].type == 'select')
        {
            element = jQuery('<select/>');
            var selectValues = extraFields[i].options;
            jQuery.each(selectValues, function(key, value) {
                element.append(jQuery('<option>', {value : key}).text(value));
            });
        }
        else
        {
            element = jQuery('<input/>');
            element.attr('value', extraFields[i].value);
        }

        element.attr('name', 'jform[params][customfields]['+id+'][extraparams]['+extraFields[i].name+']');

        for(var k in extraFields[i].attr)
        {
            element.attr(k, extraFields[i].attr[k]);
        }

        parent = jQuery('<div class="control-group">' +
                           '<div class="control-label">' +
                               '<label>'+extraFields[i].title+'</label>' +
                           '</div>' +
                           '<div class="controls">' +
                           '</div>' +
                        '</div>');


        jQuery('.controls', parent).append(element);
        extraParamsDiv.append(parent);
    }
}