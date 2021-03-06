/**
 * Created by ArkadiyS on 21.03.14.
 */

function jInsertFieldValue(value, id) {
    var old_value = document.id(id).value;
    if (old_value != value) {
        var elem = document.id(id);
        elem.value = value;
        elem.fireEvent("change");
        if (typeof(elem.onchange) === "function") {
            elem.onchange();
        }
        jMediaRefreshPreview(id);
    }
}

function sliderADDField(element, $id, $fieldname, $name, $directory) {
    var parent = jQuery(element).parents('div.control-group');
    var $k = jQuery('div.minicck_slider', parent).length;
    var div = '' +
        '<div class="minicck_slider" style="margin-bottom: 5px">' +
        '<span style="cursor: move;" class="sortable-handler">' +
        '<span class="icon-menu"></span>' +
        '</span>' +
        '<input type="text" placeholder="image" id="'+$id+'_'+$k+'_image" name="'+$fieldname+'['+$k+'][image]" value="" class="input-big '+$name+'"/>' +
        '<a class="modal btn" ' +
        'title="Select Image" ' +
        'href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=com_content&amp;author=&amp;fieldid=' + $id+ '_' + $k + '_image&amp;folder=' + $directory + '" ' +
        'rel="{handler: \'iframe\', size: {x: 800, y: 600}}">Select</a>' +
        '<a class="btn hasTooltip" title="Delete" ' +
        'href="#" onclick="sliderDeleteField(this); return false;">' +
        '<i class="icon-remove"></i>' +
        '</a>' +
        '<input type="text" placeholder="alt" id="'+$id+'_'+$k+'_alt" name="'+$fieldname+'['+$k+'][alt]" value="" class="input-big '+$name+'"/>' +
        '</div>';

    jQuery('div.sortable-minicck-slider', parent).append(div).sortable();

    SqueezeBox.initialize({});
    SqueezeBox.assign(jQuery('a.modal:last', parent).get(), {
        parse: 'rel'
    });
}

function sliderDeleteField(element) {
    jQuery(element).parents('div.minicck_slider').remove();
}