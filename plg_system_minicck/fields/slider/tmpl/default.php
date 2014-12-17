<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
?>
<div id="<?php echo $data['extraparams']->id; ?>_slider">
   <?php foreach($data['value'] as $v) : ?>
       <?php if(!empty($v->image)) : ?>
           <img src="<?php echo JUri::root().$v->image; ?>" alt="<?php if(!empty($v->alt)) echo $v->alt; ?>">
       <?php endif; ?>
   <?php endforeach; ?>
</div>