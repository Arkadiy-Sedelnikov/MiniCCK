<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
?>
<div class="mygallery" style="width: <?php echo $data['extraparams']->width; ?>; height: <?php echo $data['extraparams']->heigth; ?>;">
    <div class="tn3 album">
        <ol>
            <?php foreach($data['value'] as $v) : ?>
                <?php if(!empty($v->image)) : ?>

                    <li>
                        <?php if(!empty($v->alt)) : ?>
                        <h4><?php echo $v->alt; ?></h4>
                        <?php endif; ?>
                        <a href="<?php echo JUri::root().$v->image; ?>">
                            <img src="<?php echo JUri::root(); ?>plugins/system/minicck/fields/minigallery/classes/phpthumb/phpThumb.php?src=/<?php echo JUri::root(true).$v->image; ?>&w=35&h=35&zc=1"/>
                        </a>
                    </li>

                <?php endif; ?>
            <?php endforeach; ?>
<!--            <li>-->
<!--                <h4>Isolated sandy cove</h4>-->
<!---->
<!--                <div class="tn3 description">Zakynthos island, Greece</div>-->
<!--                <a href="images/620x378/2.jpg">-->
<!--                    <img src="images/35x35/2.jpg"/>-->
<!--                </a>-->
<!--            </li>-->
        </ol>
    </div>
</div>