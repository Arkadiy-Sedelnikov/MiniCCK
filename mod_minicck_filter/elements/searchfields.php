<?php
defined('_JEXEC') or die('Restricted access');

class JFormFieldSearchfields extends JFormField
{
    public $type = 'Searchfields';

    protected function getInput()
    {
        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $params = new JRegistry($plugin->params);
        $customfields = $params->get('customfields', array());

        $options = array();

        if(is_array($customfields) && count($customfields))
        {
            foreach ($customfields as $v)
            {
                $options[] = JHtml::_('select.option', $v->name, $v->title);
            }
        }

        $ctrl = $this->name;
        $value = empty($this->value) ? '' : $this->value;

        return JHTML::_('select.genericlist', $options, $ctrl, 'class="inputbox" size="10" multiple="multiple"', 'value', 'text', $value);
    }
}

?>
