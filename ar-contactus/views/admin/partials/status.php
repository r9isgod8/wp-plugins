<a href="#" onclick="arCU.callback.toggle(<?php echo $item['id'] ?>,0); return false;" style="margin-right: 3px" class="<?php echo $item['status'] == 0? 'lbl-danger' : 'lbl-default' ?>">
    <?php echo __('New', AR_CONTACTUS_TEXT_DOMAIN) ?>
</a>

<a href="#" onclick="arCU.callback.toggle(<?php echo $item['id'] ?>,1); return false;" style="margin-right: 3px" class="<?php echo $item['status'] == 1? 'lbl-success' : 'lbl-default' ?>">
    <?php echo __('Done', AR_CONTACTUS_TEXT_DOMAIN) ?>
</a>

<a href="#" onclick="arCU.callback.toggle(<?php echo $item['id'] ?>,2); return false;" class="<?php echo $item['status'] == 2? 'lbl-warning' : 'lbl-default' ?>">
    <?php echo __('Ignored', AR_CONTACTUS_TEXT_DOMAIN) ?>
</a>