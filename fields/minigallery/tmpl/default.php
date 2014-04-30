<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
?>
<div
    id="<?php echo $data['extraparams']->id; ?>_cont"
    class="minicckGallery"
    style="padding-top: 50px; width: <?php echo $data['extraparams']->width; ?>px; height: <?php echo $data['extraparams']->heigth; ?>px;"
    ></div>
<div id="<?php echo $data['extraparams']->id; ?>_gallery">

   <?php foreach($data['value'] as $v) : ?>
       <?php if(!empty($v->image)) : ?>

           <a class="imgThumb" href="<?php echo JUri::root(); ?>plugins/system/minicck/fields/minigallery/classes/phpthumb/phpThumb.php?src=/<?php echo JUri::root(true).$v->image; ?>&w=70&h=70&zc=1"></a>
           <a class="imgFull" href="<?php echo JUri::root().$v->image; ?>"></a>
           <div class="imgDesc"><?php if(!empty($v->alt)) echo $v->alt; ?></div>

       <?php endif; ?>
   <?php endforeach; ?>

</div>