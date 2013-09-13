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
        .attr('name', 'jform[params][customfields]['+numFields+'][name]')
        .removeAttr('readonly');

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
    var scroll = jQuery(window).scrollTop();
    if(hash != null){
        jQuery(window).scrollTop(scroll-20);
    }
}

function contentTypeAdd(){
    var counter = jQuery('#numTypes');
    var numFields = parseInt(counter.val());
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
    select.attr('name', 'jform[params][content_types]['+numFields+'][tmpl]')
        .show()
        .chosen({
            disable_search_threshold : 10,
            allow_single_deselect : true
        });

    jQuery('input.field_name', newContentType).each(function()
        {
            var cb = jQuery(this);
            var name = cb.attr('name');
            name = name.substr((name.lastIndexOf('[')+1));
            cb.removeAttr('checked')
                .attr('name', 'jform[params][content_types]['+numFields+'][fields]['+name);
        }
    );

    jQuery('input.del-button', newContentType)
        .attr('onclick', 'contentTypeDel("content_type_'+numFields+'")');

    newContentType.attr('id', 'content_type_'+numFields);


    counter.before(newContentType);
    counter.val(numFields+1);

    window.location.hash = 'content_type_'+numFields;
    anchor();
}

function contentTypeDel(id)
{
    var counter = jQuery('#numTypes');
    var numFields = parseInt(counter.val());
    if(numFields > 1){
        jQuery('#'+id).remove();
        counter.val(jQuery('.content_type_contayner').length);
    }
}

function checkEnter(element)
{
    var el = jQuery(element);
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
    string = string.replace(/'|"|<|>|\!|\||@|#|$|%|^|\^|\$|\\|\/|&|\*|\(\)|-|\|\/|;|\+|№|,|\?|_|:|{|}|\[|\]/g, "");
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

    jQuery('div.control-group', '#minicck').not('#minicck_content_type_contayner').hide();
    jQuery('input', '#minicck').attr('disabled','disabled');
    jQuery('select', '#minicck').not('#minicckcontent_type').attr('disabled','disabled');
    jQuery('textarea', '#minicck').attr('disabled','disabled');
    for (var i=0; i < typeFields.length; i++)
    {
        var cont = jQuery('div.control-group.'+typeFields[i]);
        cont.show();
        jQuery('input', cont).removeAttr('disabled');
        jQuery('select', cont).removeAttr('disabled');
        jQuery('textarea', cont).removeAttr('disabled');
    }
}