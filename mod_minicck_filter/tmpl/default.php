<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<form id="mod-finder-searchform" action="<?php echo $action; ?>" method="post" class="form-search">
	<div class="minicck_filter">
        <?php foreach($fields as $v) : ?>
            <?php echo $v; ?>
            <div style="clear: both;"></div>
        <?php endforeach; ?>
        <input type="submit" title="Submit" class="btn btn-primary btn-block"/>
        <button class="btn btn-info btn-block" onclick="jQuery(':input','#mod-finder-searchform').not(':button, :submit, :reset, :hidden').val('').removeAttr('checked').removeAttr('selected');">
            <?php echo JText::_('MOD_MINICCK_RESET'); ?>
        </button>
	</div>
</form>
