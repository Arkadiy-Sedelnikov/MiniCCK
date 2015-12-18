<?php
/**
 * @version        1.1 from Arkadiy Sedelnikov
 * @copyright      Copyright (C) 2013 Arkadiy Sedelnikov. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

$params = $data['extraparams'];

$autoplay = (!empty($params->autoplay)) ? 'true' : 'false';
$raster = (!empty($params->raster)) ? 'true' : 'false';

$params->width = (!empty($params->width)) ? $params->width : 620;
$params->heigth = (!empty($params->heigth)) ? $params->heigth : 425;
$skin = (!empty($params->skin)) ? $params->skin : 'black';
$rootUri = JUri::root();

$script = <<<SCRIPT
        jQuery(document).ready(function ($) {
            $('#{$params->id}_gallery').mbGallery({
                galleryTitle:"{$params->title}",
                maskBgnd:'#ccc',
                overlayOpacity:.9,
                containment:'{$params->id}_cont',
                minWidth: 100,
                minHeight: 100,
                maxWidth: {$params->width},
			    maxHeight: {$params->heigth},
                cssURL:"{$rootUri}plugins/system/minicck/fields/minigallery/assets/css/",
                skin:'$skin',
                exifData:false,
                addRaster:$raster,
                slideTimer: {$params->delay}000,
			    autoSlide: $autoplay,
            });
        });
SCRIPT;

JHtml::_('behavior.framework');
$doc = JFactory::getDocument();
$doc->addScript($rootUri.'plugins/system/minicck/fields/minigallery/assets/inc/mbGallery.js');
$doc->addScriptDeclaration($script);
?>
<div
    id="<?php echo $params->id; ?>_cont"
    class="minicckGallery"
    style="padding-top: 50px; width: <?php echo $data['extraparams']->width; ?>px; height: <?php echo $data['extraparams']->heigth; ?>px;"
    ></div>
<div id="<?php echo $params->id; ?>_gallery">

   <?php foreach($data['value'] as $v) : ?>
       <?php if(!empty($v->image)) : ?>

           <a class="imgThumb" href="<?php echo JUri::root(); ?>plugins/system/minicck/fields/minigallery/classes/phpthumb/phpThumb.php?src=/<?php echo JUri::root(true).$v->image; ?>&w=70&h=70&zc=1"></a>
           <a class="imgFull" href="<?php echo JUri::root().$v->image; ?>"></a>
           <div class="imgDesc"><?php if(!empty($v->alt)) echo $v->alt; ?></div>

       <?php endif; ?>
   <?php endforeach; ?>

</div>