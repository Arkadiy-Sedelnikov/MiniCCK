<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
$separator = !empty($params->separator) ? $params->separator : ' | ';
?>

<?php if(is_array($data)) : ?>
    <?php echo implode($separator, $data); ?>
<?php else : ?>
    <?php echo $data; ?>
<?php endif; ?>
