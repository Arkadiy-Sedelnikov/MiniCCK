<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT.'/components/com_content/helpers/route.php';
if(is_array($data) && count($data)){
    $i = 0;
    foreach ($data as $value){
        $slug = $value->alias ? ($value->id . ':' . $value->alias) : $value->id;
        if($i > 0){
            echo ' | ';
        }
        ?><a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($slug, $value->catid, $value->language)); ?>">
            <?php echo $value->title; ?>
        </a><?php
        $i++;
    }

}
?>