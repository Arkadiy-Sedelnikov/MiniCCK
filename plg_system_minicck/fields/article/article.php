<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/system/minicck/classes/fields.class.php';

class JFormFieldArticle extends MiniCCKFields
{
    var $attributes = null;
    var $value = null;
    var $name = null;
    static $columnType = 'text';

    function __construct($name, $attributes, $value){
        $this->attributes = $attributes;
        $this->value = $value;
        $this->name = $name;

    }

    static function getTitle()
    {
        self::loadLang('article');
        return JText::_('PLG_MINICCK_ARTICLE');
    }

    function getInput($entityType='content')
    {
        self::loadLang('article');

        $name = $this->attributes['name'];
        $label = $this->attributes['label'];
        $type = $this->attributes['type'];
        $disabled = ($this->attributes['disabled']) ? ' disabled="disabled"' : '';
        $hidden = ($this->attributes['hidden']) ? ' style="display: none;"' : '';
        $value = json_decode($this->value, true);
        $field = plgSystemMinicck::getCustomField($name, $entityType);
        $fieldname	= $this->name;
        $id = str_replace(array('][',']','['), array('_', '', '_'), $fieldname);
        $html = '<div class="control-group '.$name.'"'.$hidden.'>';
        $html .= '<label for="'.$id.'" class="control-label" title="" >'.$label.'</label>';
        $html .= '<div class="controls">';

        JHtml::_('jquery.framework');
        JHtml::_('behavior.modal', 'a.modal-button');

        JFactory::getDocument()->addScriptDeclaration('
            function minicckFieldArticleAdd_'.$name.'(id, title, catid, object, link, lang)
            {
                var div = jQuery(\'#minicck-field-article-articles-'.$name.'\');
                var span = jQuery(\'<span>\').attr(\'onclick\', \'jQuery(this).parents("div.article").remove();\').text(\' x\');
                var input = jQuery(\'<input>\').attr(\'type\', \'hidden\').attr(\'name\', \''.$fieldname.'[]\').val(id);
                var article = jQuery(\'<div>\').addClass(\'article btn btn-small btn-success\').text(title).append(span).append(input);
                div.append(article);
            }
        ');

        $class = 'btn modal-button';
        $link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;function=minicckFieldArticleAdd_'.$name.'&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
        $options = "{handler: 'iframe', size: {x: 800, y: 500}}";


        $html .= '
            <a class="'.$class.'" href="'.$link.'" rel="'.$options.'">
		        <span class="icon-save-copy"></span> '.JText::_('PLG_MINICCK_ARTICLE_ADD').'
	        </a>
	        <br><br>
            <div id="minicck-field-article-articles-'.$name.'">';
        if(is_array($value) && count($value)){
            $titles = $this->getTitles($value);
            foreach ($value as $id){
                $html .= '
                <div class="article btn btn-small btn-success">'
                    .$titles[$id].'<span onclick="jQuery(this).parents(\'div.article\').remove();"> x</span>
                    <input name="'.$this->name.'[]" value="'.$id.'" type="hidden">
                </div>';
            }
        }
        $html .= '
            </div>
        ';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    static function  getValue($field, $value)
    {
        if(empty($value))
        {
            return '';
        }
        $ids = json_decode($value, true);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, alias, catid, title, language')->from('#__content')->where('id IN ('.implode(',',$ids).')');
        $result = $db->setQuery($query)->loadObjectList('id');

        $return = self::loadTemplate($field, $result);
        return $return;
    }

    public static function prepareToSaveValue($value)
    {
        return json_encode($value);
    }

    private function getTitles($ids){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title')->from('#__content')->where('id IN ('.implode(',',$ids).')');
        $result = $db->setQuery($query)->loadObjectList('id');
        $return = array();
        foreach ($ids as $id){
            $return[$id] = !empty($result[$id]->title) ? $result[$id]->title : '';
        }
        return $return;
    }

}
