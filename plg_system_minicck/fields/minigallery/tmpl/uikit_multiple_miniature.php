<?php
$params = $data['extraparams'];
jimport( 'joomla.image.image' );
jimport ( 'joomla.filesystem.folder' );

$new_w = (!empty($params->width)) ? $params->width : 370;

$asset_dir = JPath::clean( JPATH_SITE.'/images/thumbs_'.$new_w.'/' );
if( !JFolder::exists( $asset_dir ) )
{
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
        $thumbnail = $jimage->resize($new_w, 1, false, JImage::SCALE_OUTSIDE );
        $thumbnail->toFile( $thumbnail_dest, $nativeProps->type );
    }
    $newjimage = $sourceImage = new JImage($thumbnail_dest);
    $v->thumb = '/images/thumbs_'.$new_w.'/'.$imageName;
    $v->thumb_h = $newjimage->getHeight();
    $v->thumb_w = $new_w;
}

JHtml::_('jquery.framework');
$doc = JFactory::getDocument();
$doc->addScript(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/js/uikit.min.js');
$doc->addScript(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/js/components/lightbox.min.js');
$doc->addScript(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/js/components/grid.min.js');

$doc->addStyleSheet(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/css/uikit.min.css');
$doc->addStyleSheet(JUri::root().'plugins/system/minicck/fields/minigallery/assets/uikit/css/components/slidenav.almost-flat.min.css');
$doc->addStyleDeclaration('
#uk-slideshow-'.$params->id.' li {width: 100%;}
');
?>
<div class="grid-gallery">
    <div class="uk-grid-width-small-1-2 uk-grid-width-medium-1-3" data-uk-grid="{gutter: 15}">
        <?php foreach($data['value'] as $v) : ?>
            <?php if(!empty($v->image)) : ?>
                <div onmouseout="jQuery('.uk-overlay-icon', this).hide();" onmouseover="jQuery('.uk-overlay-icon', this).show();">
                    <a href="<?php echo JUri::root().$v->image; ?>"
                       data-uk-lightbox="{group:'<?php echo $params->id; ?>'}"
                       title="<?php if(!empty($v->alt)) echo $v->alt; ?>"
                       class="uk-overlay">
                        <img class="lazy preloader"
                             data-src="<?php echo JUri::root(); ?>plugins/system/minicck/fields/minigallery/assets/images/w.png"
                             width="<?php echo $v->thumb_w; ?>"
                             height="<?php echo $v->thumb_h; ?>"
                             src="<?php echo $v->thumb; ?>"
                             alt="<?php if(!empty($v->alt)) echo $v->alt; ?>">

                        <div class="uk-overlay-panel uk-overlay-icon" style="display: none"></div>
                    </a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>