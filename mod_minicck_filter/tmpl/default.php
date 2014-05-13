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

<form id="mod-finder-searchform" action="/" method="post" class="form-search">
	<div class="minicck_filter">
        <?php foreach($fields as $v) : ?>
            <?php echo $v; ?>
            <div class="clear"></div>
        <?php endforeach; ?>
        <input type="submit" title="Submit">
	</div>
</form>
