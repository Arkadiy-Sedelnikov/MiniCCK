<?php

// No direct access
defined( '_JEXEC' ) or die;

/**
 * View to display a list of items
 * @author Arkadiy
 */
class MinicckimportViewMain extends JViewLegacy
{
	public function display( $tpl = null )
	{
        $params = JComponentHelper::getParams('com_minicckimport');
        $this->langSelect = $this->get('LangSelect');
        $this->typeSelect = $this->get('TypeSelect');
        $catOptions = JHtmlCategory::options('com_content');
        $this->firstCat = JHTML::_('select.genericlist', $catOptions, 'first_cat', 'class="inputbox" ', 'value', 'text', $params->get('dafault_cat', ''));
        $this->secondCat = JHTML::_('select.genericlist', $catOptions, 'second_cat[]', 'class="inputbox" multiple="multiple" ', 'value', 'text', $params->get('dafault_second_cat', ''));
        $this->field_delimiter = $params->get('field_delimiter', '^');
        $this->text_delimiter = $params->get('text_delimiter', '~');
        $this->header_row = $params->get('header_row', '1');
        $this->content_row = $params->get('content_row', '2');
        $this->maincat_col = $params->get('maincat_col', '1') ? ' checked="checked"' : '';
        $this->seccats_col = $params->get('seccats_col', '1') ? ' checked="checked"' : '';

        $this->loadHelper( 'minicckimport' );
		if ( $this->getLayout() !== 'modal' ) {
			$this->addToolbar();
			minicckimportHelper::addSubmenu( 'main' );
			$this->sidebar = JHtmlSidebar::render();
		}
		parent::display( $tpl );
	}

	protected function addToolbar()
	{
		JToolBarHelper::title( JText::_( 'COM_MINICCKIMPORT' ) );
		$canDo = minicckimportHelper::getActions( 'main' );

		if ( $canDo->get( 'core.create' ) || ( count( $this->user->getAuthorisedCategories( 'com_minicckimport', 'core.create' ) ) ) > 0 )
        {
			JToolBarHelper::addNew( 'main.upload', 'Загрузить' );
		}

		if ( $canDo->get( 'core.edit.state' ) )
        {
			if ( $canDo->get( 'core.admin' ) )
            {
				JToolBarHelper::preferences( 'com_minicckimport' );
				JToolBarHelper::divider();
			}
		}		
	}

}