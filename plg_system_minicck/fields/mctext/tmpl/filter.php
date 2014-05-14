<div class="control-group">
    <label class="" for="<?php echo $data['name']; ?>">
        <?php echo $data['title']; ?>
    </label>
    <div class="controls">
        <input
            type="text"
            name="minicckfilter[<?php echo $data['name']; ?>]"
            id="<?php echo $data['name']; ?>"
            value="<?php echo $data['selectedValues']; ?>"
            class="input-medium search-query"
            placeholder="<?php echo $data['title']; ?>"
            />
    </div>
</div>