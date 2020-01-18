<div class="ui segment arconfig-panel <?php echo ($activeSubmit == 'ArContactUsConfigLiveChat')? '' : 'hidden' ?>" id="arcontactus-livechat">
    <?php if ($buttonConfig->mode == 'callback') {?>
        <div class="ui error message">
            <?php echo __('Current button mode is "Callback only". Menu is not displaying in this mode.', AR_CONTACTUS_TEXT_DOMAIN) ?>
        </div>
    <?php } ?>
    <?php echo $liveChatsConfig->getFormHelper()->render() ?>
</div>