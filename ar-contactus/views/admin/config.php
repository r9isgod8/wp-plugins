<?php 
if (empty($activeSubmit) && (isset($_GET['paged']) || isset($_GET['orderby']) || isset($_GET['arcontactus_requests']))){
    $activeSubmit = 'arcontactus-requests';
}?>

<div id="arcontactus-plugin-container">
    <div class="arcontactus-masthead">
        <div class="arcontactus-masthead__inside-container">
            <div class="arcontactus-masthead__logo-container">
                <?php echo sprintf(__('All-in-one contact button %swith call-back request feature%s', AR_CONTACTUS_TEXT_DOMAIN), '<small>', '</small>') ?>
            </div>
        </div>
    </div>
    <div class="arcontactus-body">
        <?php if ($success){?>
            <div class="ui success message">
                <?php echo $success ?>
            </div>
        <?php } ?>
        <?php if ($errors){?>
            <?php foreach ($errors as $fieldErrors){?>
                <?php foreach ($fieldErrors as $error){?>
                    <div class="ui negative message">
                        <?php echo $error ?>
                    </div>
                <?php } ?>
            <?php } ?>
        <?php } ?>
        <div class="ui grid">
            <div class="four wide column">
                <div class="ui vertical fluid pointing menu" id="acrontactus-menu">
                    <a class="item <?php echo ($activeSubmit == 'ArContactUsConfigGeneral' || empty($activeSubmit))? 'active' : '' ?>" data-target="#arcontactus-general">
                        <?php echo __('General configuration', AR_CONTACTUS_TEXT_DOMAIN) ?>
                    </a>
                    <a class="item <?php echo ($activeSubmit == 'ArContactUsConfigButton')? 'active' : '' ?>" data-target="#arcontactus-button">
                        <?php echo __('Button settings', AR_CONTACTUS_TEXT_DOMAIN) ?>
                    </a>
                    <a class="item <?php echo ($activeSubmit == 'ArContactUsConfigMenu')? 'active' : '' ?>" data-target="#arcontactus-menu">
                        <?php echo __('Menu settings', AR_CONTACTUS_TEXT_DOMAIN) ?>
                    </a>
                    <a class="item <?php echo ($activeSubmit == 'ArContactUsConfigPopup')? 'active' : '' ?>" data-target="#arcontactus-callback">
                        <?php echo __('Callback popup settings', AR_CONTACTUS_TEXT_DOMAIN) ?>
                    </a>
                    <a class="item <?php echo ($activeSubmit == 'ArContactUsConfigPrompt')? 'active' : '' ?>" data-target="#arcontactus-prompt">
                        <?php echo __('Prompt settings', AR_CONTACTUS_TEXT_DOMAIN) ?>
                    </a>
                    <a class="item <?php echo ($activeSubmit == 'ArContactUsConfigLiveChat')? 'active' : '' ?>" data-target="#arcontactus-livechat">
                        <?php echo __('Live chat integrations', AR_CONTACTUS_TEXT_DOMAIN) ?>
                    </a>
                    <a class="item" data-target="#arcontactus-prompt-items">
                        <?php echo __('Prompt messages', AR_CONTACTUS_TEXT_DOMAIN) ?>
                    </a>
                    <a class="item" data-target="#arcontactus-items">
                        <?php echo __('Menu items', AR_CONTACTUS_TEXT_DOMAIN) ?>
                    </a>
                    <a class="item <?php echo ($activeSubmit == 'arcontactus-requests')? 'active' : '' ?>" href="<?php echo admin_url('admin.php?page=ar-contactus-key-requests') ?>">
                        <?php echo __('Callback requests', AR_CONTACTUS_TEXT_DOMAIN) ?>
                    </a>
                    <a class="item" data-target="#arcontactus-about">
                        <?php echo __('About', AR_CONTACTUS_TEXT_DOMAIN) ?>
                    </a>
                </div>
            </div>
            <div class="twelve wide stretched column" id="arcontactus-tabs">
                <span class="hidden"></span>
                <?php echo ArContactUsAdmin::render('/admin/_general.php', array(
                    'generalConfig' => $generalConfig,
                    'activeSubmit' => $activeSubmit
                )) ?>
                <?php echo ArContactUsAdmin::render('/admin/_button.php', array(
                    'buttonConfig' => $buttonConfig,
                    'activeSubmit' => $activeSubmit
                )) ?>
                <?php echo ArContactUsAdmin::render('/admin/_menu.php', array(
                    'menuConfig' => $menuConfig,
                    'buttonConfig' => $buttonConfig,
                    'activeSubmit' => $activeSubmit
                )) ?>
                <?php echo ArContactUsAdmin::render('/admin/_callback.php', array(
                    'popupConfig' => $popupConfig,
                    'activeSubmit' => $activeSubmit
                )) ?>
                <?php echo ArContactUsAdmin::render('/admin/_live_chats.php', array(
                    'liveChatsConfig' => $liveChatsConfig,
                    'buttonConfig' => $buttonConfig,
                    'activeSubmit' => $activeSubmit
                )) ?>
                <?php echo ArContactUsAdmin::render('/admin/_prompt.php', array(
                    'promptConfig' => $promptConfig,
                    'activeSubmit' => $activeSubmit
                )) ?>
                <?php echo ArContactUsAdmin::render('/admin/_items.php', array(
                    'items' => $items,
                    'buttonConfig' => $buttonConfig,
                )) ?>
                <?php echo ArContactUsAdmin::render('/admin/_prompt_items.php', array(
                    'items' => $promptItems
                )) ?>
                <?php echo ArContactUsAdmin::render('/admin/_requests.php', array(
                    'callbackList' => $callbackList,
                    'activeSubmit' => $activeSubmit
                )) ?>
                <?php echo ArContactUsAdmin::render('/admin/_about.php') ?>
                <span class="hidden"></span>
            </div>
        </div>
    </div>
</div>

<div class="ui modal small" id="arcontactus-prompt-modal">
    <i class="close icon"></i>
    <div class="header" id="arcontactus-prompt-modal-title">
        <?php echo __('Add item', AR_CONTACTUS_TEXT_DOMAIN) ?>
    </div>
    <form id="arcontactus-prompt-form" method="POST" onsubmit="arCU.prompt.save(); return false;">
        <input type="hidden" id="arcontactus_prompt_id" name="id" data-serializable="true" autocomplete="off" data-default=""/>
        <div class="ui form" style="padding: 20px;">
            <div class="ui grid">
                <div class="row">
                    <div class="three wide column">
                    </div>
                    <div class="ten wide column">
                        <div class="field required">
                            <label><?php echo __('Message', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <textarea placeholder="" rows="3" id="arcontactus_prompt_message" data-default="" autocomplete="off" data-serializable="true" name="message" type="text"></textarea>
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="actions">
            <button type="button" class="button-large black deny button">
                <?php echo __('Cancel', AR_CONTACTUS_TEXT_DOMAIN) ?>
            </button>
            <button type="submit" class="button button-primary button-large icon">
                <?php echo __('Save', AR_CONTACTUS_TEXT_DOMAIN) ?>
                <i class="checkmark icon"></i>
            </button>
        </div>
    </form>
</div>

<div class="ui modal small" id="arcontactus-modal">
    <i class="close icon"></i>
    <div class="header" id="arcontactus-modal-title">
        <?php echo __('Add item', AR_CONTACTUS_TEXT_DOMAIN) ?>
    </div>
    <form id="arcontactus-form" method="POST" onsubmit="arCU.save(); return false;">
        <input type="hidden" id="arcontactus_id" name="id" data-serializable="true" autocomplete="off" data-default=""/>
        <div class="ui form" style="padding: 20px;">
            <div class="ui grid">
                <div class="row">
                    <div class="three wide column">
                    </div>
                    <div class="ten wide column">
                        <div class="field required">
                            <label><?php echo __('Title', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <input placeholder="" id="arcontactus_title" data-default="" data-serializable="true" name="title" type="text">
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>            
                
                <div class="row">
                    <div class="three wide column"></div>
                    <div class="ten wide column">
                        <div class="field required">
                            <label><?php echo __('Icon', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <div class="ui fluid selection search dropdown iconed" id="arcontactus-icon-dropdown">
                                <input name="icon" id="arcontactus_icon" data-default="" autocomplete="off" data-serializable="true" type="hidden">
                                <i class="dropdown icon"></i>
                                <div class="default text"><?php echo __('Select icon', AR_CONTACTUS_TEXT_DOMAIN) ?></div>
                                <div class="menu">
                                    <?php foreach (ArContactUsConfigModel::getIcons() as $key => $svg){?>
                                        <div class="item" data-value="<?php echo $key ?>">
                                            <?php echo $svg ?>
                                            <?php echo $key ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="three wide column">
                    </div>
                    <div class="ten wide column">
                        <div class="field required">
                            <label><?php echo __('Color', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <input class="jscolor" id="arcontactus_color" data-jscolor="{value:'000000'}" data-default="000000" autocomplete="off" data-serializable="true" name="color" type="text">
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="three wide column">
                    </div>
                    <div class="ten wide column">
                        <div class="field required">
                            <label><?php echo __('Display on', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <select id="arcontactus_display" name="display" class="form-control arcontactus-control" data-serializable="true" data-default="1">
                                <option value="1"><?php echo __('Desktop and mobile', AR_CONTACTUS_TEXT_DOMAIN) ?></option>
                                <option value="2"><?php echo __('Desktop only', AR_CONTACTUS_TEXT_DOMAIN) ?></option>
                                <option value="3"><?php echo __('Mobile only', AR_CONTACTUS_TEXT_DOMAIN) ?></option>
                            </select>
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="three wide column">
                    </div>
                    <div class="ten wide column">
                        <div class="field required">
                            <label><?php echo __('Action', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <select id="arcontactus_type" name="type" class="form-control arcontactus-control" data-serializable="true" data-default="0">
                                <option value="0"><?php echo __('Link', AR_CONTACTUS_TEXT_DOMAIN) ?></option>
                                <option value="1"><?php echo __('Integration', AR_CONTACTUS_TEXT_DOMAIN) ?></option>
                                <option value="2"><?php echo __('Custom JS code', AR_CONTACTUS_TEXT_DOMAIN) ?></option>
                                <option value="3"><?php echo __('Callback form', AR_CONTACTUS_TEXT_DOMAIN) ?></option>
                            </select>
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
                
                <div class="row arcu-link-group">
                    <div class="three wide column">
                    </div>
                    <div class="ten wide column">
                        <div class="field required">
                            <label><?php echo __('Link', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <input placeholder="" id="arcontactus_link" data-default="" autocomplete="off" data-serializable="true" name="link" type="text">
                            <div class="errors"></div>
                            <div class="help-block">
                                <?php echo sprintf(__('You can set absolute or relative URL. Also you can use %scallback%s tag to generate callback request form.', AR_CONTACTUS_TEXT_DOMAIN), '<b>', '</b>') ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row arcu-link-group">
                    <div class="three wide column">
                    </div>
                    <div class="ten wide column">
                        <div class="field required">
                            <label><?php echo __('Target', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <select id="arcontactus_target" name="target" class="form-control arcontactus-control" data-serializable="true" data-default="0">
                                <option value="0"><?php echo __('New window', AR_CONTACTUS_TEXT_DOMAIN) ?></option>
                                <option value="1"><?php echo __('Same window', AR_CONTACTUS_TEXT_DOMAIN) ?></option>
                            </select>
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
                
                <div class="row" id="arcu-integration-group">
                    <div class="three wide column">
                    </div>
                    <div class="ten wide column">
                        <div class="field required">
                            <label><?php echo __('Integration', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <select id="arcontactus_integration" name="integration" class="form-control arcontactus-control" data-serializable="true" data-default="0">
                                <?php foreach ($integrations as $id => $integration) {?>
                                    <option value="<?php echo $id ?>"><?php echo $integration ?></option>
                                <?php } ?>
                            </select>
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
                
                <div class="row" id="arcu-js-group">
                    <div class="three wide column">
                    </div>
                    <div class="ten wide column">
                        <div class="field">
                            <label><?php echo __('Custom JS code', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <textarea placeholder="" rows="3" id="arcontactus_js" data-default="" autocomplete="off" data-serializable="true" name="js" type="text"></textarea>
                            <div class="errors"></div>
                            <div class="help-block">
                                <?php echo __('JavaScript code to run onclick. Please type here JavaScript code without <b>&lt;script&gt;</b> tag.', AR_CONTACTUS_TEXT_DOMAIN) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row hidden" id="arcu-shortcode-group">
                    <div class="three wide column">
                    </div>
                    <div class="ten wide column">
                        <div class="field">
                            <label><?php echo __('Shortcode', AR_CONTACTUS_TEXT_DOMAIN) ?></label>
                            <input placeholder="" rows="3" readonly=""id="arcontactus_shortcode" class="disabled" data-default="" autocomplete="off" type="text" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="actions">
            <button type="button" class="button-large black deny button">
                <?php echo __('Cancel', AR_CONTACTUS_TEXT_DOMAIN) ?>
            </button>
            <button type="submit" class="button button-primary button-large icon">
                <?php echo __('Save', AR_CONTACTUS_TEXT_DOMAIN) ?>
                <i class="checkmark icon"></i>
            </button>
        </div>
    </form>
</div>

<script>
    window.addEventListener('load', function(){
        if (jQuery.fn.modal && jQuery.fn.modal.noConflict){
            jQuery.fn.bsModal = jQuery.fn.modal.noConflict();
        }
        if (jQuery.fn.dropdown && jQuery.fn.dropdown.noConflict){
            jQuery.fn.bsDropdown = jQuery.fn.dropdown.noConflict();
        }
        
        arCU.ajaxUrl = ajaxurl;
        arCU.nonce = '<?php echo wp_create_nonce('arcontactus-key') ?>';
        arCU.editTitle = '<?php echo __('Edit item', AR_CONTACTUS_TEXT_DOMAIN) ?>';
        arCU.addTitle = '<?php echo __('Add item', AR_CONTACTUS_TEXT_DOMAIN) ?>';
        arCU.init();
        arCU.callback.updateCounter();
        setInterval(function(){
            arCU.callback.updateCounter();
        }, 5000);
        jQuery('#acrontactus-menu a').on('click', function(){
            var target = jQuery(this).data('target');
            if (!target){
                return true;
            }
            jQuery('#acrontactus-menu .active').removeClass('active');
            jQuery(this).addClass('active');
            jQuery('.arconfig-panel').addClass('hidden');
            jQuery(target).removeClass('hidden');
        });
        jQuery('.ui.checkbox').checkbox();
        jQuery('#arcontactus-tabs').addClass('active');
        jQuery('#arcontactus-icon-dropdown').dropdown();
        arContactUsSwitchFields();
        jQuery('.ui.toggle.checkbox').on('click', function(){
            arContactUsSwitchFields();
        });
        
        jQuery('#arcontactus_type').change(function(){
            arcontactusChangeType();
        });
        arcontactusChangeType();
    });
    
    function arcontactusChangeType(){
        var val = jQuery('#arcontactus_type').val();
        switch(val){
            case "0": // link
                jQuery('.arcu-link-group').removeClass('hidden');
                //jQuery('#arcu-js-group').addClass('hidden');
                jQuery('#arcu-integration-group').addClass('hidden');
                break;
            case "1": // integration
                jQuery('.arcu-link-group').addClass('hidden');
                //jQuery('#arcu-js-group').addClass('hidden');
                jQuery('#arcu-integration-group').removeClass('hidden');
                break;
            case "2": // js
                jQuery('.arcu-link-group').addClass('hidden');
                //jQuery('#arcu-js-group').removeClass('hidden');
                jQuery('#arcu-integration-group').addClass('hidden');
                break;
            case "3": // callback
                jQuery('.arcu-link-group').addClass('hidden');
                //jQuery('#arcu-js-group').addClass('hidden');
                jQuery('#arcu-integration-group').addClass('hidden');
                break;
        }
    }
    
    function arContactUsSwitchFields(){
        if (jQuery('.field_email #ARCUP_EMAIL').is(':checked')){
            jQuery('.field_email_list').removeClass('hidden');
        }else{
            jQuery('.field_email_list').addClass('hidden');
        }
        if (jQuery('.field_recaptcha #ARCUP_RECAPTCHA').is(':checked')){
            jQuery('.field_key, .field_secret, .field_hide_recaptcha').removeClass('hidden');
        }else{
            jQuery('.field_key, .field_secret, .field_hide_recaptcha').addClass('hidden');
        }
        if (jQuery('.field_onesignal #ARCUP_ONESIGNAL').is(':checked')){
            jQuery('.field_onesignal_app_id, .field_onesignal_api_key, .field_onesignal_title, .field_onesignal_message').removeClass('hidden');
        }else{
            jQuery('.field_onesignal_app_id, .field_onesignal_api_key, .field_onesignal_title, .field_onesignal_message').addClass('hidden');
        }
        if (jQuery('.field_loop #ARCUPR_LOOP').is(':checked')){
            jQuery('.field_close_last').addClass('hidden');
        }else{
            jQuery('.field_close_last').removeClass('hidden');
        }
        
        if (jQuery('.field_twilio #ARCUP_TWILIO').is(':checked')){
            jQuery('.field_twilio_api_key, .field_twilio_auth_token, .field_twilio_phone, .field_twilio_tophone, .field_twilio_message').removeClass('hidden');
        }else{
            jQuery('.field_twilio_api_key, .field_twilio_auth_token, .field_twilio_phone, .field_twilio_tophone, .field_twilio_message').addClass('hidden');
        }
        
        if (jQuery('#ARCUL_TAWK_TO_ON').is(':checked')){
            jQuery('.field_tawk_to_site_id, .field_tawk_to_widget').removeClass('hidden');
        }else{
            jQuery('.field_tawk_to_site_id, .field_tawk_to_widget').addClass('hidden');
        }
        
        if (jQuery('#ARCUL_CRISP_ON').is(':checked')){
            jQuery('.field_crisp_site_id').removeClass('hidden');
        }else{
            jQuery('.field_crisp_site_id').addClass('hidden');
        }
        
        if (jQuery('#ARCUL_INTERCOM_ON').is(':checked')){
            jQuery('.field_intercom_app_id').removeClass('hidden');
        }else{
            jQuery('.field_intercom_app_id').addClass('hidden');
        }
        
        if (jQuery('#ARCUL_FB_ON').is(':checked')){
            jQuery('.field_fb_page_id, .field_fb_init, .field_fb_lang, .field_fb_color').removeClass('hidden');
        }else{
            jQuery('.field_fb_page_id, .field_fb_init, .field_fb_lang, .field_fb_color').addClass('hidden');
        }
        
        if (jQuery('#ARCUL_VK_ON').is(':checked')){
            jQuery('.field_vk_page_id').removeClass('hidden');
        }else{
            jQuery('.field_vk_page_id').addClass('hidden');
        }
        
        if (jQuery('#ARCUL_ZOPIM_ON').is(':checked')){
            jQuery('.field_zopim_id').removeClass('hidden');
        }else{
            jQuery('.field_zopim_id').addClass('hidden');
        }
        
        if (jQuery('#ARCUL_SKYPE_ON').is(':checked')){
            jQuery('.field_skype_type, .field_skype_id, .field_skype_message_color').removeClass('hidden');
        }else{
            jQuery('.field_skype_type, .field_skype_id, .field_skype_message_color').addClass('hidden');
        }
        
        if (jQuery('#ARCUL_ZALO_ON').is(':checked')){
            jQuery('.field_zalo_id, .field_zalo_welcome, .field_zalo_width, .field_zalo_height').removeClass('hidden');
        }else{
            jQuery('.field_zalo_id, .field_zalo_welcome, .field_zalo_width, .field_zalo_height').addClass('hidden');
        }
        
        if (jQuery('#ARCUP_PHONE_MASK_ON').is(':checked')){
            jQuery('.field_phone_mask, .field_maskedinput').removeClass('hidden');
        }else{
            jQuery('.field_phone_mask, .field_maskedinput').addClass('hidden');
        }
        
        if (jQuery('#ARCUP_TG').is(':checked')){
            jQuery('.field_tg_token, .field_tg_chat_id, .field_tg_text').removeClass('hidden');
        }else{
            jQuery('.field_tg_token, .field_tg_chat_id, .field_tg_text').addClass('hidden');
        }
        
        if (jQuery('#ARCUG_SANDBOX').is(':checked')){
            jQuery('.field_allowed_ips').removeClass('hidden');
        }else{
            jQuery('.field_allowed_ips').addClass('hidden');
        }
    }
</script>