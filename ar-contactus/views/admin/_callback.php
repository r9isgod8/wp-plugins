<div class="ui segment arconfig-panel <?php echo ($activeSubmit == 'ArContactUsConfigPopup')? '' : 'hidden' ?>" id="arcontactus-callback">
    <div class="ui grid">
        <div class="twelve wide stretched column">
            <?php echo $popupConfig->getFormHelper()->render() ?>
        </div>
        <div class="four wide column">
            <h3><?php echo __('Google reCaptcha V3', AR_CONTACTUS_TEXT_DOMAIN) ?></h3>
            <p>
                <?php echo __('This module can be integrated with Google reCaptcha to prevent bots from sending callback requests.', AR_CONTACTUS_TEXT_DOMAIN) ?>
            </p>
            <p>
                <?php echo __('To use this integration you need to get reCaptcha V3 key and secret. Please follow this link:', AR_CONTACTUS_TEXT_DOMAIN) ?>
            </p>
            <p>
                <a href="https://g.co/recaptcha/v3" target="_blank">https://g.co/recaptcha/v3</a>
            </p>
            <hr/>
            <h3><?php echo __('Twilio', AR_CONTACTUS_TEXT_DOMAIN) ?></h3>
            <p>
                <?php echo __('This module can be integrated with Twilio SMS service.', AR_CONTACTUS_TEXT_DOMAIN) ?>
            </p>
            <p>
                <?php echo __('To use this integration you need to signup to Twilio. Please follow this link:', AR_CONTACTUS_TEXT_DOMAIN) ?>
            </p>
            <p>
                <a href="https://www.twilio.com/try-twilio" target="_blank">https://www.twilio.com/try-twilio</a>
            </p>
            <hr/>
            <h3><?php echo __('Onesignal', AR_CONTACTUS_TEXT_DOMAIN) ?></h3>
            <p>
                <?php echo __('This module can be integrated with Onesignal webpush service.', AR_CONTACTUS_TEXT_DOMAIN) ?>
            </p>
            <p>
                <?php echo __('To use this integration you need to signup to Onesignal. Please follow this link:', AR_CONTACTUS_TEXT_DOMAIN) ?>
            </p>
            <p>
                <a href="https://onesignal.com/" target="_blank">https://onesignal.com/</a>
            </p>
            <div class="onesignal-customlink-container"></div>
        </div>
    </div>
</div>