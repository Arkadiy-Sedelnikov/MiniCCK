/**
 * Created by arkadiy on 26.04.17.
 */
function minicckFieldArticleAdd(id, title, catid, object, link, lang)
{
    var div = jQuery('#minicck-field-article-articles');
    var span = jQuery('<span>').attr('onclick', 'fieldArticleRemoveArticle(this)').text(' x');
    var input = jQuery('<input>').attr('type', 'hidden').attr('name', minicckFieldArticleName+'[]').val(id);
    var article = jQuery('<div>').addClass('article btn btn-small btn-success').text(title).append(span).append(input);
    div.append(article);
}
function fieldArticleRemoveArticle(element) {
    jQuery(element).parents('div.article').remove();
}