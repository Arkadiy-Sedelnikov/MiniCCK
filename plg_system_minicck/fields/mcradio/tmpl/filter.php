<div class="control-group">
    <h4>
        <?php echo $data['title']; ?>
    </h4>
    <div class="controls">
        <?php $i = 1; ?>
        <?php foreach($data['params'] as $k => $v) : ?>
            <?php $checked = ($k == $data['selectedValues']) ? ' checked="checked"' : ''; ?>
            <label class="span6" for="<?php echo $data['name'].'_'. $i; ?>">
                <input
                    type="radio"
                    value="<?php echo $k; ?>"
                    id="<?php echo $data['name'].'_'. $i; ?>"
                    name="minicckfilter[<?php echo $data['name']; ?>]"<?php echo $checked; ?>
                />
                <?php echo $v; ?>
            </label>

    <?php if($i % 2 == 0) : ?>
    </div>
    <div class="controls">
    <?php endif; ?>

            <?php $i++; ?>
        <?php endforeach; ?>

    </div>
</div>
