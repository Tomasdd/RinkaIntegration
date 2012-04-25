<div id="insertForm_imagesBlock">
    <div class="insertForm_imagesBlock_imageField">
        <input type="file" name="images[]" />
            <?php
            if (isset($validationErrors['images'])) {
                echo '<img src="'.$this->config->getUrl('assets').'/images/stop.png" alt="Klaida!" />';
            }
            ?>
    </div>
</div>
<a href="javascript:void(0)" onclick="addAdditionalElement('insertForm_imagesBlock', 'insertForm_imageField_clone')">
    <img src="<?php echo $this->config->getUrl('assets'); ?>/images/add.gif" alt="Pridėti dar vieną paveikslėlį" />
    Pridėti dar vieną
</a>