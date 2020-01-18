<div class="ui segment arconfig-panel hidden" id="arcontactus-prompt-items">
    <p class="text-right">
        <button type="button" class="button button-primary button-large" onclick="arCU.prompt.add()">
            <i class="icon plus"></i><?php echo __('Add', AR_CONTACTUS_TEXT_DOMAIN) ?>
        </button>
    </p>
    <?php echo ArContactUsAdmin::render('/admin/_prompt_table.php', array(
        'items' => $items
    )) ?>
</div>