<div class="ui segment arconfig-panel <?php echo ($activeSubmit == 'ArContactUsConfigMenu')? '' : 'hidden' ?>" id="arcontactus-menu">
    <?php if ($buttonConfig->mode == 'callback') {?>
        <div class="ui error message">
            <?php echo __('Current button mode is "Callback only". Menu is not displaying in this mode.', AR_CONTACTUS_TEXT_DOMAIN) ?>
        </div>
    <?php } ?>
    <?php echo $menuConfig->getFormHelper()->render() ?>
</div>