<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
?>

<div class="infoblock twocols">
    <ul>
        <?php
        foreach ($result as $attr => $value) :
            $field = $fields[$attr];
            $label = $field['title'];
            $val = $this->getValue($attr, $value);
            ?>
            <li>
                <div>
                    <div class="minicck-label"><?php  echo $label; ?></div>
                    <div class="minicck-value"><?php  echo $val; ?></div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>