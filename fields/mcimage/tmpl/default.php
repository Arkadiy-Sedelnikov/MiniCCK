<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

$attr = '';
$attr .= (!empty($data['extraparams']->heigth)) ? ' height="'.$data['extraparams']->heigth.'"' : '';
$attr .= (!empty($data['extraparams']->width)) ? ' width="'.$data['extraparams']->width.'"' : '';
$attr .= (!empty($data['extraparams']->border)) ? ' border="'.$data['extraparams']->border.'"' : '';
?>

<img src="<?php echo $data['value']; ?>"<?php echo $attr; ?>/>