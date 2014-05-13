<fieldset title="<?php echo $data['title']; ?>">
    <h4><?php echo $data['title']; ?></h4>
    <select name="minicckfilter[<?php echo $data['name']; ?>]" id="<?php echo $data['name']; ?>">
        <?php foreach($data['params'] as $k => $v) : ?>
            <?php $checked = ($k == $data['selectedValues']) ? ' selected="selected"' : ''; ?>
            <option value="<?php echo $k; ?>" <?php echo $checked; ?>><?php echo $v; ?></option>
        <?php endforeach; ?>
    </select>
</fieldset>