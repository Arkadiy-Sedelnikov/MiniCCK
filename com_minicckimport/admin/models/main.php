<?php

// No direct access
defined( '_JEXEC' ) or die;

/**
 * @author Arkadiy
 */
class MinicckimportModelMain extends JModelList
{
	public function getLangSelect()
	{
        JLoader::import('joomla.language.helper');
        $languages = JLanguageHelper::getLanguages('lang_code');
        $options = array();
        $options[] = JHTML::_('select.option', '*', JText::_('JALL_LANGUAGE'));
        if (!empty($languages))
        {
            foreach ($languages as $key => $lang)
            {
                $options[] = JHTML::_('select.option', $key, $lang->title);
            }
        }

        return JHTML::_('select.genericlist', $options, 'language', 'class="inputbox" id="type_id" ', 'value', 'text', '*');
    }

	public function getTypeSelect()
	{
        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $params = json_decode($plugin->params);
        $options = array();
        $options[] = JHTML::_('select.option', '', JText::_('JSELECT'));
        if (isset($params->content_types) && is_array($params->content_types) && count($params->content_types))
        {
            foreach ($params->content_types as $v)
            {
                $options[] = JHTML::_('select.option', $v->name, $v->title);
            }
        }

        $default_type = JComponentHelper::getParams('com_minicckimport')->get('default_type', '');

        return JHTML::_('select.genericlist', $options, 'type_id', 'class="inputbox" id="type_id" ', 'value', 'text', $default_type);
    }
}