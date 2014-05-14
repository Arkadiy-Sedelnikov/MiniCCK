<div class="control-group">
    <label class="" for="<?php echo $data['name']; ?>">
        <?php echo $data['title']; ?>
    </label>
    <div class="controls">

        <select
            name="minicckfilter[<?php echo $data['name']; ?>]"
            id="<?php echo $data['name']; ?>"
            class="span12"
            >
            <option value=""><?php echo JText::_('JSELECT'); ?></option>
            <?php foreach($data['params'] as $k => $v) : ?>
                <?php $checked = ($k == $data['selectedValues']) ? ' selected="selected"' : ''; ?>
                <option value="<?php echo $k; ?>" <?php echo $checked; ?>><?php echo $v; ?></option>
            <?php endforeach; ?>
        </select>

    </div>
</div>