<?php
$params = $data['extraparams'];

jimport( 'joomla.image.image' );
jimport ( 'joomla.filesystem.folder' );


$new_w = (!empty($params->width)) ? $params->width : 620;
$new_h = (!empty($params->heigth)) ? $params->heigth : 425;

$asset_dir = JPath::clean( JPATH_SITE.'/images/thumbs_'.$new_w.'/' );
if( !JFolder::exists( $asset_dir ) ){
    JFolder::create( $asset_dir );
    JFile::copy( JPATH_SITE.'/images/index.html', $asset_dir.'index.html');
}

foreach($data['value'] as $v)
{
    $native_dest = JPATH_SITE.'/'.$v->image;
    $imageName = basename($native_dest);
    $nativeProps = Jimage::getImageFileProperties( $native_dest );
    $thumbnail_dest = JPATH_SITE.'/images/thumbs_'.$new_w.'/'.$imageName;
    if( !file_exists($thumbnail_dest) ){
        $jimage	= new JImage();
        $jimage->loadFile( $native_dest );
        $thumbnail = $jimage->cropResize($new_w, $new_h, true);
        $thumbnail->toFile( $thumbnail_dest, $nativeProps->type );
    }
    $v->thumb = '/images/thumbs_'.$new_w.'/'.$imageName;
    $v->thumb_h = $new_h;
    $v->thumb_w = $new_w;
}
$x = $y = $z = 0;

JHtml::_('jquery.framework');
$doc = JFactory::getDocument();
$doc->addScript(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/js/uikit.min.js');
$doc->addScript(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/js/components/slideshow.min.js');
$doc->addScript(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/js/components/lightbox.min.js');

$doc->addStyleSheet(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/css/uikit.min.css');
$doc->addStyleSheet(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/css/components/slideshow.almost-flat.min.css');
$doc->addStyleSheet(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/css/components/slidenav.almost-flat.min.css');
$doc->addStyleSheet(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/css/components/dotnav.almost-flat.min.css');
$doc->addStyleDeclaration('
#uk-slideshow-'.$params->id.' li {width: 100%;}
#uk-dotnav-'.$params->id.' li {width: 30px; margin-bottom: 5px;}
');

?>
<div class="uk-slidenav-position" data-uk-slideshow>
    <ul id="uk-slideshow-<?php echo $params->id; ?>" class="uk-slideshow" data-uk-slideshow="{autoplay:<?php echo $params->autoplay; ?>,autoplayInterval:<?php echo $params->delay; ?>000}">
        <?php foreach($data['value'] as $v) : ?>

            <?php if(!empty($v->image)) : ?>
                <li onmouseout="jQuery('.uk-overlay-icon', this).hide();" onmouseover="jQuery('.uk-overlay-icon', this).show();">
                    <a href="<?php echo JUri::root().$v->image; ?>"
                       data-uk-lightbox="{group:'<?php echo $params->id; ?>'}"
                       title="<?php if(!empty($v->alt)) echo $v->alt; ?>"
                       class="uk-overlay">
                        <img class="lazy"
                             src="<?php echo $v->thumb; ?>"
                             alt="<?php if(!empty($v->alt)) echo $v->alt; ?>">
                        <div class="uk-overlay-panel uk-overlay-icon" style="display: none"></div>
                    </a>
                </li>
            <?php endif; ?>

            <?php $x++; ?>
        <?php endforeach; ?>
    </ul>
    <a href="#" class="uk-slidenav uk-slidenav-contrast uk-slidenav-previous" data-uk-slideshow-item="previous"></a>
    <a href="#" class="uk-slidenav uk-slidenav-contrast uk-slidenav-next" data-uk-slideshow-item="next"></a>
    <ul id="uk-dotnav-<?php echo $params->id; ?>" class="uk-dotnav uk-dotnav-contrast uk-position-bottom uk-flex-center">
        <?php for($i=0;$i<$x;$i++) : ?>
            <li data-uk-slideshow-item="<?php echo $i; ?>"><a href="#"></a></li>
        <?php endfor; ?>

    </ul>
</div>