<?php
/**
 * @version 1.5 stable $Id: import.php 1193 2012-03-14 09:20:15Z emmanuel.danan@gmail.com $
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

defined('_JEXEC') or die('Restricted access');
?>

<div class="j-toggle-main j-toggle-transition span12 expanded">
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <h3><?php echo JText::_('COM_MINICCKIMPORT_COMPARE_KEY_FIELDS_HEADER'); ?></h3>
    <table class="table table-striped">
        <tr>
            <th><?php echo JText::_('COM_MINICCKIMPORT_FIELD_COMTENT'); ?></th>
            <th><?php echo JText::_('COM_MINICCKIMPORT_FIELD_FILE'); ?></th>
        </tr>
        <tr>
            <td>
                <?php echo JHTML::_('select.genericlist', $this->lists['identifier'], 'content_key', '', 'value', 'text', $this->identifier);?>
            </td>
            <td>
                <?php echo JHTML::_('select.genericlist', $this->lists['headers'], 'file_key', '', 'value', 'text', $this->identifier);?>
            </td>
        </tr>
    </table>



    <h3><?php echo JText::_('COM_MINICCKIMPORT_COMPARE_FIELDS_HEADER'); ?></h3>
	<table class="table table-striped">
        <tr>
            <th><?php echo JText::_('COM_MINICCKIMPORT_FIELD_COMTENT'); ?></th>
            <th><?php echo JText::_('COM_MINICCKIMPORT_FIELD_FILE'); ?></th>
        </tr>

        <?php if($this->lists['data']['maincat_col']) : ?>
        <tr>
            <td><?php echo JText::_('COM_MINICCKIMPORT_COMPARE_CAT'); ?></td>
            <td>
                <?php echo JHTML::_('select.genericlist', $this->lists['headers'], 'fields[catid]', ' ', 'value', 'text', 'catid');?>
            </td>
        </tr>
        <?php endif; ?>

        <?php if($this->lists['data']['seccats_col']) : ?>
        <tr>
            <td><?php echo JText::_('COM_MINICCKIMPORT_COMPARE_SECOND_CAT'); ?></td>
            <td>
                <?php echo JHTML::_('select.genericlist', $this->lists['headers'], 'fields[cck][cid]', '', 'value', 'text', 'cid');?>
            </td>
        </tr>
        <?php endif; ?>

        <tr>
            <th colspan="2"><h4><?php echo JText::_('COM_MINICCKIMPORT_COMPARE_CONTENT_FIELDS_HEADER'); ?></h4></th>
        </tr>

        <?php foreach($this->lists['article_fields'] as $key => $field) : ?>
            <tr>
                <td><?php echo $field;?> [<?php echo $key;?>]</td>
                <td>
                    <?php echo JHTML::_('select.genericlist', $this->lists['headers'], 'fields['.$key.']', '', 'value', 'text', $key);?>
                </td>
            </tr>
        <?php endforeach; ?>

        <tr>
            <th colspan="2"><h4><?php echo JText::_('COM_MINICCKIMPORT_COMPARE_CCK_FIELDS_HEADER'); ?></h4></th>
        </tr>
        <tr>
            <td><?php echo JText::_('COM_MINICCKIMPORT_COMPARE_CONTENT_TYPE'); ?></td>
            <td>
                <?php echo JHTML::_('select.genericlist', $this->lists['headers'], 'fields[cck][content_type]', '', 'value', 'text', 'content_type');?>
            </td>
        </tr>
        <?php foreach($this->lists['fields'] as $field) : ?>
        <tr>
            <td><?php echo $field->title;?> [<?php echo $field->name;?>]</td>
            <td>
                <?php echo JHTML::_('select.genericlist', $this->lists['headers'], 'fields[cck]['.$field->name.']', '', 'value', 'text', $field->name);?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_minicckimport" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>