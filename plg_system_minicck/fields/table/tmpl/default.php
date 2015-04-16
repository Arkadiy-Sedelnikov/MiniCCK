<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

$class = (!empty($params->class)) ? ' class="'.$params->class.'"' : '';


?>
<table<?php echo $class ?>>
    <thead>
        <tr>
            <?php foreach($data['head'] as $head) : ?>
                <th><?php echo $head; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach($data['body'] as $row) : ?>
        <tr>
            <?php foreach($row as $cell) : ?>
                <td><?php echo htmlspecialchars_decode($cell); ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>