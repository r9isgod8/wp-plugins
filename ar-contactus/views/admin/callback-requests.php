<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo __('Callback requests', AR_CONTACTUS_TEXT_DOMAIN) ?></h1>
    <a href="<?php echo admin_url('options-general.php?page=ar-contactus-key-config') ?>" class="page-title-action"><?php echo __('Settings', AR_CONTACTUS_TEXT_DOMAIN) ?></a>
    <a href="" onclick="arCU.callback.reload(); return false;" class="page-title-action"><?php echo __('Reload table', AR_CONTACTUS_TEXT_DOMAIN) ?></a>
    <?php echo ArContactUsAdmin::render('/admin/_requests.php', array(
        'callbackList' => $callbackList,
        'activeSubmit' => $activeSubmit,
        'noSegment' => true
    )) ?>
</div>
<script>
    window.addEventListener('load', function(){
        arCU.ajaxUrl = ajaxurl;
        arCU.nonce = '<?php echo wp_create_nonce('arcontactus-key') ?>';
        arCU.editTitle = 'Edit item';
        arCU.addTitle = 'Add item';
        arCU.init();
        arCU.callback.updateCounter();
        setInterval(function(){
            arCU.callback.updateCounter();
        }, 5000);
    });
</script>