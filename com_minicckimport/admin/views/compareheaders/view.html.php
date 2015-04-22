<?php
/**
 * @version 1.5 stable $Id: view.html.php 1193 2012-03-14 09:20:15Z emmanuel.danan@gmail.com $
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 * 
 * FLEXIcontent is a derivative work of the excellent QuickFAQ component
 * @copyright (C) 2008 Christoph Lukes
 * see www.schlu.net for more information
 *
 * FLEXIcontent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_BASE.'/components/com_minicckimport/controllers/main.php');

/**
 * View class for the FLEXIcontent categories screen
 *
 * @package Joomla
 * @subpackage FLEXIcontent
 * @since 1.0
 */
class MinicckimportViewCompareHeaders extends JViewLegacy
{

	function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_minicckimport.uploaddata');

		//create the toolbar
		JToolBarHelper::title( JText::_('COM_MINICCKIMPORT_COMPARE_FIELDS'), 'import' );

        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $params = json_decode($plugin->params);

		$lists['fields'] = $params->customfields;
		$lists['article_fields'] = array(
            'title' =>      JText::_('COM_MINICCKIMPORT_HEADER'),
            'alias' =>      JText::_('COM_MINICCKIMPORT_ALIAS'),
            'introtext' =>  JText::_('COM_MINICCKIMPORT_INTROTEXT'),
            'fulltext' =>   JText::_('COM_MINICCKIMPORT_FULLTEXT'),
            'state' =>      JText::_('COM_MINICCKIMPORT_STATE'),
            'images' =>     JText::_('COM_MINICCKIMPORT_IMAGES'),
            'urls' =>       JText::_('COM_MINICCKIMPORT_URLS'),
            'metakey' =>    JText::_('COM_MINICCKIMPORT_METAKEY'),
            'metadesc' =>   JText::_('COM_MINICCKIMPORT_METADESC'),
            'featured' =>   JText::_('COM_MINICCKIMPORT_FEATURED'),
            'language' =>   JText::_('COM_MINICCKIMPORT_LANGUAGE'),
        );

        $options = array();
        $options[] = JHTML::_('select.option', '', JText::_('JSELECT'));
        foreach($lists['fields'] as $result) {
            $options[] = JHTML::_('select.option', $result->name, $result->title);
        }
        $lists['fieldsOptions'] =  $options;


        $import = new MinicckimportControllerMain();

        $headers = ($data['file_ext'] == 'csv') ? $import->load_headers_csv() :$import->load_headers_xls();

        if(!is_array($headers) || count($headers)<2)
        {
            $app->redirect('index.php?option=com_minicckimport&view=main', JText::_('COM_MINICCKIMPORT_ERROR_READ_HEADERS'), 'error');
        }

        $app->setUserState('com_minicckimport.headers', $headers);

        $options = array();
        $options[] = JHTML::_('select.option', '', JText::_('JSELECT'));
        foreach($headers as $k => $v)
        {
            $options[] = JHTML::_('select.option', $v, $v);
        }
        $lists['headers'] = $options;

        $this->identifier = '';
        if(in_array('id', $headers)){
            $this->identifier = 'id';
        }
        else if(in_array('title', $headers)){
            $this->identifier = 'title';
        }

        $options = array();
        $options[] = JHTML::_('select.option', '', JText::_('JSELECT'));
        $options[] = JHTML::_('select.option', 'title', 'Title');
        $options[] = JHTML::_('select.option', 'id', 'Id');
        $lists['identifier'] = $options;

        $lists['data'] = $data;

		//assign data to template
		$this->assignRef('lists', $lists);

        $this->loadHelper( 'minicckimport' );
        if ( $this->getLayout() !== 'modal' ) {
            $this->addToolbar();
            minicckimportHelper::addSubmenu( 'main' );
            $this->sidebar = JHtmlSidebar::render();
        }

		parent::display($tpl);
	}

    /**
     * Method to display the toolbar
     */
    protected function addToolbar()
    {
        JToolBarHelper::title( JText::_( 'COM_MINICCKIMPORT' ) );
        $canDo = minicckimportHelper::getActions( 'main' );

        if ( $canDo->get( 'core.create' ) || ( count( $this->user->getAuthorisedCategories( 'com_minicckimport', 'core.create' ) ) ) > 0 )
        {
            JToolBarHelper::addNew( 'main.import', 'COM_MINICCKIMPORT_IMPORT' );
            JToolbarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
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
?>
