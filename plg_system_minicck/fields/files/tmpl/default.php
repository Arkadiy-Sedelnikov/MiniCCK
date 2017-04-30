<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */
defined('_JEXEC') or die;
if(!empty($data['fpath'])){ ?>
<a href="<?php echo JUri::base().$data['fpath']; ?>">
    <?php echo JText::_('PLG_MINICCK_FILES_DOWNLOAD').' '.$data['fname']; ?>
</a>
<?php } ?>