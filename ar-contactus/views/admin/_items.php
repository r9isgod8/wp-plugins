<div class="ui segment arconfig-panel hidden" id="arcontactus-items">
    <?php if ($buttonConfig->mode == 'callback') {?>
        <div class="ui error message">
            <?php echo __('Current button mode is "Callback only". Menu is not displaying in this mode.', AR_CONTACTUS_TEXT_DOMAIN) ?>
        </div>
    <?php } ?>
    <p class="text-right">
        <button type="button" class="button button-primary button-large" onclick="arCU.add()">
            <i class="icon plus"></i><?php echo __('Add', AR_CONTACTUS_TEXT_DOMAIN) ?>
        </button>
    </p>
    <?php echo ArContactUsAdmin::render('/admin/_items_table.php', array(
        'items' => $items
    )) ?>
</div>