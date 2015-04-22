<?php
/** @var $this MinicckimportViewMain */
defined( '_JEXEC' ) or die;// No direct access
JHtml::_( 'bootstrap.tooltip' );
JHtml::_( 'behavior.multiselect' );
JHtml::_( 'formbehavior.chosen', 'select' );
$user = JFactory::getUser();
$userId = $user->get( 'id' );
?>

<script>

    Joomla.submitbutton = function(task)
    {
        var type_id = jQuery("#type_id");
        var maincat = jQuery("#maincat");
        var field_separator = jQuery("#field_separator");
        var enclosure_char = jQuery("#enclosure_char");
        var num_headers = jQuery("#num_headers");
        var num_content = jQuery("#num_content");
        var csvfile = jQuery("#csvfile");

        if(!type_id.val()) {
            alert(<?php echo JText::_('COM_MINICCKIMPORT_SELECT_TYPE'); ?>);
            type_id.focus();
            return;
        }

        if( maincat.val() <=0 && !jQuery("#maincat_col").prop('checked') ) {
            alert(<?php echo JText::_('COM_MINICCKIMPORT_SELECT_CAT'); ?>);
            maincat.focus();
            return;
        }

        if(field_separator.val()=="") {
            alert(<?php echo JText::_('COM_MINICCKIMPORT_SELECT_DELIMITER'); ?>);
            field_separator.focus();
            return;
        }

        if(enclosure_char.val()=="") {
            alert(<?php echo JText::_('COM_MINICCKIMPORT_SELECT_ENCLOSURE'); ?>);
            enclosure_char.focus();
            return;
        }

        if(num_headers.val()=="") {
            alert(<?php echo JText::_('COM_MINICCKIMPORT_SELECT_NUM_HEADERS'); ?>);
            num_headers.focus();
            return;
        }

        if(num_content.val()=="") {
            alert(<?php echo JText::_('COM_MINICCKIMPORT_SELECT_NUM_CONTENT'); ?>);
            num_content.focus();
            return;
        }

        if(csvfile.val()=="") {
            alert(<?php echo JText::_('COM_MINICCKIMPORT_SELECT_FILE'); ?>);
            csvfile.focus();
            return;
        }

        Joomla.submitform(task, document.getElementById("adminForm"));
    };
</script>

<div id="j-main-container" class="j-toggle-main j-toggle-transition span12 expanded">
    <form action="index.php" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm">
        <table cellspacing="10" cellpadding="0" border="0" width="100%">
            <tr>
                <td value="top" colspan="2">
                    <fieldset>
                        <legend><?php echo JText::_('COM_MINICCKIMPORT_SELECT_LANG_HEADER'); ?></legend>
                        <table>
                            <tr valign="top">
                                <td class="key"><label class="fcimport" for="type_id"><?php echo JText::_('COM_MINICCKIMPORT_CONTENT_TYPE'); ?><span style="color:red;"> *</span></label></td>
                                <td><?php echo $this->typeSelect;?></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr valign="top">
                                <td class="key"><label class="fcimport" for="language"><?php echo JText::_('COM_MINICCKIMPORT_LANG'); ?><span style="color:red;"> *</span></label></td>
                                <td><?php echo $this->langSelect;?></td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td valign="top" width="50%">
                    <fieldset>
                        <legend><?php echo JText::_('COM_MINICCKIMPORT_SELECT_CAT_HEADER'); ?></legend>
                        <table>
                            <tr valign="top">
                                <td class="key"><label class="fcimport" for="maincat"><?php echo JText::_('COM_MINICCKIMPORT_CAT'); ?></label></td>
                                <td><?php echo $this->firstCat; ?></td>
                                <td>&nbsp;</td>
                                <td class="key"><label class="fcimport" for="seccats"><?php echo JText::_('COM_MINICCKIMPORT_SECOND_CAT'); ?></label></td>
                                <td><?php echo $this->secondCat; ?></td>
                            </tr>
                            <tr valign="top">
                                <td class="key"><label class="fcimport" for="maincat_col"><?php echo JText::_('COM_MINICCKIMPORT_FILE_CAT'); ?></label></td>
                                <td><input type="checkbox" id="maincat_col" name="maincat_col" value="1"<?php echo $this->maincat_col; ?>></td>
                                <td>&nbsp;</td>
                                <td class="key"><label class="fcimport" for="seccats_col"><?php echo JText::_('COM_MINICCKIMPORT_FILE_SECOND_CAT'); ?></td>
                                <td><input type="checkbox" id="seccats_col" name="seccats_col" value="1"<?php echo $this->seccats_col; ?>></td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <fieldset>
                        <legend><?php echo JText::_('COM_MINICCKIMPORT_FILE_DATA'); ?></legend>
                        <table>
                            <tr>
                                <td class="key">
                                    <label class="fcimport" for="field_separator">
                                        <?php echo JText::_('COM_MINICCKIMPORT_DELIMITER'); ?>
                                        <span style="color:red;"> *</span>
                                    </label>
                                </td>
                                <td>
                                    <input type="text" name="field_separator" id="field_separator" value="<?php echo $this->field_delimiter ?>" /> &nbsp;
                                    <span style='font-weight:bold; color:green'><?php echo JText::_('COM_MINICCKIMPORT_FROM_CSV'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="key">
                                    <label class="fcimport" for="enclosure_char">
                                        <?php echo JText::_('COM_MINICCKIMPORT_ENCLOSURE'); ?>
                                    </label>
                                </td>
                                <td>
                                    <input type="text" name="enclosure_char" id="enclosure_char" value='<?php echo $this->text_delimiter ?>' /> &nbsp;
                                    <span style='font-weight:bold; color:green'><?php echo JText::_('COM_MINICCKIMPORT_FROM_CSV'); ?></span>
                                </td>
                            </tr>

                            <tr>
                                <td class="key">
                                    <label class="fcimport" for="num_headers">
                                        <?php echo JText::_('COM_MINICCKIMPORT_NUM_HEADERS'); ?>
                                        <span style="color:red;"> *</span>
                                    </label>
                                </td>
                                <td>
                                    <input type="text" name="num_headers" id="num_headers" value="<?php echo $this->header_row ?>" /> &nbsp;
                            <span style='font-weight:bold; color:green'>
                                <?php echo JText::_('COM_MINICCKIMPORT_FROM_ALL_FILES'); ?>
                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="key">
                                    <label class="fcimport" for="csvfile">
                                        <?php echo JText::_('COM_MINICCKIMPORT_NUM_CONTENT'); ?>
                                        <span style="color:red;"> *</span>
                                    </label>
                                </td>
                                <td>
                                    <input type="text" name="num_content" id="num_content" value="<?php echo $this->content_row ?>" /> &nbsp;
                            <span style='font-weight:bold; color:green'>
                                <?php echo JText::_('COM_MINICCKIMPORT_FROM_ALL_FILES'); ?>
                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="key">
                                    <label class="fcimport" for="csvfile">
                                        <?php echo JText::_('COM_MINICCKIMPORT_FILE'); ?>
                                        <span style="color:red;"> *</span>
                                    </label>
                                </td>
                                <td>
                                    <input type="file" name="csvfile" id="csvfile" value="" />
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
        </table>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="option" value="com_minicckimport" />
        <?php echo JHTML::_( 'form.token' ); ?>
    </form>
</div>