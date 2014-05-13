<fieldset title="<?php echo $data['title']; ?>">
    <h4><?php echo $data['title']; ?></h4>
    <?php foreach($data['params'] as $k => $v) : ?>
        <?php $checked = ($k == $data['selectedValues']) ? ' checked="checked"' : ''; ?>
        <label class="finder" for="<?php echo $data['name']; ?>"><?php echo $v; ?></label>
        <input type="radio" value="<?php echo $k; ?>" id="<?php echo $data['name']; ?>" name="minicckfilter[<?php echo $data['name']; ?>]"<?php echo $checked; ?>" />
    <?php endforeach; ?>
</fieldset>