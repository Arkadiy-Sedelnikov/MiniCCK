<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

$params = $data['extraparams'];
$script = <<<SCRIPT
        jQuery(document).ready(function ($){
            $('#{$params->id}_slider').slidesjs({
              width: {$params->width},
              height: {$params->heigth},
              play: {
                active: $params->management,
                auto: $params->autoplay,
                interval: $params->interval,
                swap: true
              }
            });
        });
SCRIPT;

JHtml::_('behavior.framework');
$doc = JFactory::getDocument();
$doc->addScript($params->rootUri.'plugins/system/minicck/fields/slider/assets/js/jquery.slides.min.js');
$doc->addStyleSheet($params->rootUri.'plugins/system/minicck/fields/slider/assets/css/style.css');
$doc->addScriptDeclaration($script);
?>

<div id="<?php echo $data['extraparams']->id; ?>_slider">
   <?php foreach($data['value'] as $v) : ?>
       <?php if(!empty($v->image)) : ?>
           <img src="<?php echo JUri::root().$v->image; ?>" alt="<?php if(!empty($v->alt)) echo $v->alt; ?>">
       <?php endif; ?>
   <?php endforeach; ?>
</div>