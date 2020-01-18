<style type="text/css">
    <?php if ($menuConfig->menu_bg){?>
        .arcontactus-widget .messangers-block{
            background-color: #<?php echo $menuConfig->menu_bg ?>;
        }
        .arcontactus-widget .messangers-block::before{
            border-top-color: #<?php echo $menuConfig->menu_bg ?>;
        }
    <?php } ?>
    <?php if ($menuConfig->menu_color){?>
        .messangers-block .messanger p{
            color:  #<?php echo $menuConfig->menu_color ?>;
        }
    <?php } ?>
    <?php if ($menuConfig->menu_hcolor){?>
        .messangers-block .messanger:hover p{
            color:  #<?php echo $menuConfig->menu_hcolor ?>;
        }
    <?php } ?>
    <?php if ($menuConfig->menu_hbg){?>
        .messangers-block .messanger:hover{
            background-color:  #<?php echo $menuConfig->menu_hbg ?>;
        }
    <?php } ?>
    #arcontactus-message-callback-phone-submit{
        font-weight: normal;
    }
    <?php if ($popupConfig->hide_recaptcha){?>
        .grecaptcha-badge{
            display: none;
        }
    <?php } ?>
    <?php if ($buttonConfig->x_offset){?>
        .arcontactus-widget.<?php echo $buttonConfig->position ?>.arcontactus-message{
            <?php if ($buttonConfig->position == 'left'){?>
                left: <?php echo (int)$buttonConfig->x_offset ?>px;
            <?php } ?>
            <?php if ($buttonConfig->position == 'right'){?>
                right: <?php echo (int)$buttonConfig->x_offset ?>px;
            <?php } ?>
        }
    <?php } ?>
    <?php if ($buttonConfig->y_offset){?>
        .arcontactus-widget.<?php echo $buttonConfig->position ?>.arcontactus-message{
            bottom: <?php echo (int)$buttonConfig->y_offset ?>px;
        }
    <?php } ?>
    .arcontactus-widget .arcontactus-message-button .pulsation{
        -webkit-animation-duration:<?php echo $buttonConfig->pulsate_speed / 1000 ?>s;
        animation-duration: <?php echo $buttonConfig->pulsate_speed / 1000 ?>s;
    }
    #ar-zalo-chat-widget{
        display: none;
    }
    #ar-zalo-chat-widget.active{
        display: block;
    }
</style>
<div id="arcontactus"></div>
<script src="<?php echo AR_CONTACTUS_PLUGIN_URL ?>res/js/jquery.contactus.min.js?version=<?php echo AR_CONTACTUS_VERSION ?>"></script>
<?php if ($popupConfig->phone_mask_on && $popupConfig->maskedinput){?>
    <script src="<?php echo AR_CONTACTUS_PLUGIN_URL ?>res/js/jquery.maskedinput.min.js?version=<?php echo AR_CONTACTUS_VERSION ?>"></script>
<?php } ?>
<?php if ($vkChat){ ?>
    <script type="text/javascript" src="https://vk.com/js/api/openapi.js?157"></script>
    <?php if (!$isMobile) {?>
        <style type="text/css">
            #vk_community_messages{
                <?php if ($buttonConfig->position == 'right') { ?>
                    right: -10px !important;
                <?php }else{ ?>
                    left: -10px !important;
                <?php } ?>
            }
        </style>
    <?php } ?>
    <div id="vk_community_messages"></div>
<?php } ?>
<?php if ($skype) { ?>
    <span 
        class="skype-chat" 
        id="arcontactus-skype"
        style="display: none"
        data-can-close="true" 
        data-can-collapse="true" 
        <?php if ($liveChatsConfig->skype_type == 'skype') {?>
            data-contact-id="<?php echo $liveChatsConfig->skype_id ?>" 
        <?php }else{ ?>
            data-bot-id="<?php echo $liveChatsConfig->skype_id ?>"
        <?php } ?>
        data-color-message="#<?php echo $liveChatsConfig->skype_message_color ?>"
    ></span>
    <script src="https://swc.cdn.skype.com/sdk/v1/sdk.min.js"></script>
<?php } ?>
<?php if ($zalo) { ?>
    <div id="ar-zalo-chat-widget">
        <div class="zalo-chat-widget" data-oaid="<?php echo $liveChatsConfig->zalo_id ?>" data-welcome-message="<?php echo $liveChatsConfig->zalo_welcome ?>" data-autopopup="0" data-width="<?php echo (int)$liveChatsConfig->zalo_width ?>" data-height="<?php echo (int)$liveChatsConfig->zalo_height ?>"></div>
    </div>
    <script src="https://sp.zalo.me/plugins/sdk.js"></script>
<?php } ?>
<script type="text/javascript">
    var zaloWidgetInterval;
    var tawkToInterval;
    <?php if ($promptConfig->enable_prompt && $messages){?>
        var arCuMessages = <?php echo json_encode($messages) ?>;
        var arCuLoop = <?php echo $promptConfig->loop? 'true' : 'false' ?>;;
        var arCuCloseLastMessage = <?php echo $promptConfig->close_last? 'true' : 'false' ?>;
        var arCuPromptClosed = false;
        var _arCuTimeOut = null;
        var arCuDelayFirst = <?php echo (int)$promptConfig->first_delay ?>;
        var arCuTypingTime = <?php echo (int)$promptConfig->typing_time ?>;
        var arCuMessageTime = <?php echo (int)$promptConfig->message_time ?>;
        var arCuClosedCookie = 0;
    <?php } ?>
    var arcItems = [];
    <?php if ($liveChatsConfig->isTawkToIntegrated() && $tawkTo) {?>
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    <?php } ?>
    window.addEventListener('load', function(){
        <?php if ($promptConfig->show_after_close != '-1'){?>
            arCuClosedCookie = arCuGetCookie('arcu-closed');
        <?php } ?>
        jQuery('#arcontactus').on('arcontactus.init', function(){
            <?php if ($popupConfig->phone_mask_on){?>
                jQuery.mask.definitions['#'] = "[0-9]";
                jQuery('#arcontactus .arcontactus-message-callback-phone').mask('<?php echo $popupConfig->phone_mask ?>');
            <?php } ?>
            <?php if ($promptConfig->enable_prompt && $messages){ ?>
                if (arCuClosedCookie){
                    return false;
                }
                arCuShowMessages();
            <?php } ?>
        });
        <?php if ($promptConfig->enable_prompt && $messages){ ?>
            jQuery('#arcontactus').on('arcontactus.openMenu', function(){
                clearTimeout(_arCuTimeOut);
                if (!arCuPromptClosed){
                    arCuPromptClosed = true;
                    jQuery('#arcontact').contactUs('hidePrompt');
                }
            });

            jQuery('#arcontactus').on('arcontactus.hidePrompt', function(){
                clearTimeout(_arCuTimeOut);
                if (arCuClosedCookie != "1"){
                    arCuClosedCookie = "1";
                    <?php if ($promptConfig->show_after_close != '-1'){?>
                        arCuPromptClosed = true;
                        <?php if ($promptConfig->show_after_close == '0'){?>
                            arCuCreateCookie('arcu-closed', 1, 0);
                        <?php }else{ ?>
                            arCuCreateCookie('arcu-closed', 1, <?php echo ((int)$promptConfig->show_after_close) / 1440 ?>);
                        <?php } ?>
                    <?php } ?>
                }
            });
        <?php } ?>
        <?php foreach ($items as $item){?>
            var arcItem = {};
            <?php if ($item['id']){?>
                arcItem.id = '<?php echo $item['id'] ?>';
            <?php } ?>
            <?php if ($item['type'] == ArContactUsModel::TYPE_INTEGRATION){ ?>
                <?php if ($item['integration'] == 'tawkto'){ ?>
                    arcItem.onClick = function(e){
                        e.preventDefault();
                        jQuery('#arcontactus').contactUs('closeMenu');
                        if (typeof Tawk_API == 'undefined'){
                            console.error('Tawk.to integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        Tawk_API.showWidget();
                        Tawk_API.maximize();
                        tawkToInterval = setInterval(function(){
                            checkTawkIsOpened();
                        }, 100);
                        <?php if ($item['js']){ ?>
                            <?php echo $item['js'] ?>
                        <?php } ?>
                    }
                <?php }elseif($item['integration'] == 'crisp'){ ?>
                    arcItem.onClick = function(e){
                        e.preventDefault();
                        jQuery('#arcontactus').contactUs('closeMenu');
                        if (typeof $crisp == 'undefined'){
                            console.error('Crisp integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        $crisp.push(["do", "chat:show"]);
                        $crisp.push(["do", "chat:open"]);
                        <?php if ($item['js']){ ?>
                            <?php echo $item['js'] ?>
                        <?php } ?>
                    }
                <?php }elseif ($item['integration'] == 'intercom'){ ?>
                    arcItem.onClick = function(e){
                        e.preventDefault();
                        jQuery('#arcontactus').contactUs('closeMenu');
                        if (typeof Intercom == 'undefined'){
                            console.error('Intercom integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        Intercom('show');
                        <?php if ($item['js']){ ?>
                            <?php echo $item['js'] ?>
                        <?php } ?>
                    }
                <?php }elseif ($item['integration'] == 'facebook'){ ?>
                    arcItem.onClick = function(e){
                        e.preventDefault();
                        jQuery('#arcontactus').contactUs('closeMenu');
                        if (typeof FB == 'undefined' || typeof FB.CustomerChat == 'undefined'){
                            console.error('Facebook customer chat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        jQuery('#ar-fb-chat').addClass('active');
                        FB.CustomerChat.showDialog();
                        <?php if ($item['js']){ ?>
                            <?php echo $item['js'] ?>
                        <?php } ?>
                    }
                <?php }elseif ($item['integration'] == 'vk'){ ?>
                    arcItem.onClick = function(e){
                        e.preventDefault();
                        jQuery('#arcontactus').contactUs('closeMenu');
                        if (typeof vkMessagesWidget == 'undefined'){
                            console.error('VK chat integration is disabled in module configuration');
                            return false;
                        }
                        vkMessagesWidget.expand();
                        <?php if ($item['js']){ ?>
                            <?php echo $item['js'] ?>
                        <?php } ?>
                    }
                <?php }elseif ($item['integration'] == 'zopim'){ ?>
                    arcItem.onClick = function(e){
                        e.preventDefault();
                        jQuery('#arcontactus').contactUs('closeMenu');
                        <?php if ($liveChatsConfig->isZendeskChat()) {?>
                            if (typeof zE == 'undefined'){
                                console.error('Zendesk integration is disabled in module configuration');
                                return false;
                            }
                            zE('webWidget', 'show');
                            zE('webWidget', 'open');
                        <?php }else{ ?>
                            if (typeof $zopim == 'undefined'){
                                console.error('Zendesk integration is disabled in module configuration');
                                return false;
                            }
                            $zopim.livechat.window.show();
                        <?php } ?>
                        jQuery('#arcontactus').contactUs('hide');
                        <?php if ($item['js']){ ?>
                            <?php echo $item['js'] ?>
                        <?php } ?>
                    }
                <?php }elseif ($item['integration'] == 'skype'){ ?>
                    arcItem.onClick = function(e){
                        e.preventDefault();
                        jQuery('#arcontactus').contactUs('closeMenu');
                        jQuery('#arcontactus-skype').show();
                        SkypeWebControl.SDK.Chat.showChat();
                        <?php if ($item['js']){ ?>
                            <?php echo $item['js'] ?>
                        <?php } ?>
                    }
                <?php }elseif ($item['integration'] == 'zalo'){ ?>
                    arcItem.onClick = function(e){
                        e.preventDefault();
                        jQuery('#arcontactus').contactUs('closeMenu');
                        if (typeof ZaloSocialSDK == 'undefined'){
                            console.error('Zalo integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#ar-zalo-chat-widget').addClass('active');
                        ZaloSocialSDK.openChatWidget();
                        zaloWidgetInterval = setInterval(function(){
                            checkZaloIsOpened();
                        }, 100);
                        <?php if ($item['js']){ ?>
                            <?php echo $item['js'] ?>
                        <?php } ?>
                    }
                <?php } ?>
            <?php }elseif ($item['js']){ ?>
                arcItem.onClick = function(e){
                    <?php if ($item['type'] == ArContactUsModel::TYPE_JS){ ?>
                        e.preventDefault();
                    <?php } ?>
                    <?php echo $item['js'] ?>
                }
            <?php } ?>
            arcItem.class = '<?php echo $item['class'] ?>';
            arcItem.title = "<?php echo $item['title'] ?>";
            arcItem.icon = '<?php echo $item['icon'] ?>';
            <?php if ($item['type'] == ArContactUsModel::TYPE_LINK){ ?>
                arcItem.href = '<?php echo $item['href'] ?>';
            <?php }elseif($item['type'] == ArContactUsModel::TYPE_CALLBACK){ ?>
                arcItem.href = 'callback';
            <?php } ?>
            <?php if ($item['type'] == ArContactUsModel::TYPE_LINK && $item['target'] == ArContactUsModel::TARGET_SAME_WINDOW){ ?>
                arcItem.target = '_self';
            <?php } ?>
            arcItem.color = '<?php echo $item['color'] ?>';
            arcItems.push(arcItem);
        <?php } ?>
        jQuery('#arcontactus').contactUs({
            <?php if ($buttonIcon){ ?>
                buttonIcon: '<?php echo $buttonIcon ?>',
            <?php } ?>
            drag: <?php echo $buttonConfig->drag? 'true' : 'false' ?>,
            mode: '<?php echo $buttonConfig->mode? $buttonConfig->mode : 'regular' ?>',
            buttonIconUrl: '<?php echo AR_CONTACTUS_PLUGIN_URL . 'res/img/msg.svg' ?>',
            align: '<?php echo $buttonConfig->position ?>',
            reCaptcha: <?php echo $popupConfig->recaptcha? 'true' : 'false' ?>,
            reCaptchaKey: '<?php echo ArContactUsTools::escJsString($popupConfig->key) ?>',
            countdown: <?php echo (int)$popupConfig->timeout ?>,
            theme: '#<?php echo $buttonConfig->button_color ?>',
            <?php if ($buttonConfig->text){ ?>
                buttonText: "<?php echo ArContactUsTools::escJsString($buttonConfig->text) ?>",
            <?php }else{ ?>
                buttonText: false,
            <?php } ?>
            buttonSize: '<?php echo ArContactUsTools::escJsString($buttonConfig->button_size) ?>',
            menuSize: '<?php echo ArContactUsTools::escJsString($menuConfig->menu_size) ?>',
            phonePlaceholder: '<?php echo $popupConfig->phone_placeholder ?>',
            callbackSubmitText: '<?php echo ArContactUsTools::escJsString($popupConfig->btn_title) ?>',
            errorMessage: '<?php echo ArContactUsTools::escJsString($popupConfig->fail_message) ?>',
            callProcessText: '<?php echo ArContactUsTools::escJsString($popupConfig->proccess_message) ?>',
            callSuccessText: '<?php echo ArContactUsTools::escJsString($popupConfig->success_message) ?>',
            iconsAnimationSpeed: <?php echo (int)$buttonConfig->icon_speed ?>,
            callbackFormText: '<?php echo ArContactUsTools::escJsString($popupConfig->message) ?>',
            items: arcItems,
            ajaxUrl: '<?php echo admin_url('admin-ajax.php') ?>',
            action: 'arcontactus_request_callback'
        });
        <?php if ($liveChatsConfig->isTawkToIntegrated() && $tawkTo) {?>
            Tawk_API.onLoad = function(){
                if(!Tawk_API.isChatOngoing()){
                    Tawk_API.hideWidget();
                }else{
                    jQuery('#arcontactus').contactUs('hide');
                }
            };
            Tawk_API.onChatMinimized = function(){
                Tawk_API.hideWidget();
                setTimeout(function(){
                    Tawk_API.hideWidget();
                }, 100);
                jQuery('#arcontactus').contactUs('show');
            };
            Tawk_API.onChatEnded = function(){
                Tawk_API.hideWidget();
                jQuery('#arcontactus').contactUs('show');
            };
            (function(){
                var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
                s1.async=true;
                s1.src='https://embed.tawk.to/<?php echo $liveChatsConfig->tawk_to_site_id ?>/<?php echo $liveChatsConfig->tawk_to_widget ?>';
                s1.charset='UTF-8';
                s1.setAttribute('crossorigin','*');
                s0.parentNode.insertBefore(s1,s0);
            })();
        <?php } ?>
        <?php if ($liveChatsConfig->isFacebookChatIntegrated() && $facebook) {?>
            FB.Event.subscribe('customerchat.dialogHide', function(){
                jQuery('#ar-fb-chat').removeClass('active');
                jQuery('#arcontactus').contactUs('show');
            });
        <?php } ?>
    });
    <?php if ($liveChatsConfig->isCrispIntegrated() && $crisp) {?>
        window.$crisp=[];window.CRISP_WEBSITE_ID="<?php echo $liveChatsConfig->crisp_site_id ?>";(function(){
            d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);
        })();
        $crisp.push(["on", "session:loaded", function(){
            $crisp.push(["do", "chat:hide"]);
        }]);
        $crisp.push(["on", "chat:closed", function(){
            $crisp.push(["do", "chat:hide"]);
            jQuery('#arcontactus').contactUs('show');
        }]);
    <?php } ?>
    <?php if ($liveChatsConfig->isIntercomIntegrated() && $intercom) {?>
        window.intercomSettings = {
            app_id: "<?php echo $liveChatsConfig->intercom_app_id ?>",
            alignment: 'right',     
            horizontal_padding: 20, 
            vertical_padding: 20
        };
        (function() {
            var w = window;
            var ic = w.Intercom;
            if (typeof ic === "function") {
                ic('reattach_activator');
                ic('update', intercomSettings);
            } else {
                var d = document;
                var i = function() {
                    i.c(arguments)
                };
                i.q = [];
                i.c = function(args) {
                    i.q.push(args)
                };
                w.Intercom = i;

                function l() {
                    var s = d.createElement('script');
                    s.type = 'text/javascript';
                    s.async = true;
                    s.src = 'https://widget.intercom.io/widget/<?php echo $liveChatsConfig->intercom_app_id ?>';
                    var x = d.getElementsByTagName('script')[0];
                    x.parentNode.insertBefore(s, x);
                }
                if (w.attachEvent) {
                    w.attachEvent('onload', l);
                } else {
                    w.addEventListener('load', l, false);
                }
            }
        })();
        Intercom('onHide', function(){
            jQuery('#arcontactus').contactUs('show');
        });
    <?php } ?>
    <?php if ($vkChat) {?>
        var vkMessagesWidget = VK.Widgets.CommunityMessages("vk_community_messages", <?php echo $liveChatsConfig->vk_page_id ?>, {
            disableButtonTooltip: 1,
            welcomeScreen: 0,
            expanded: 0,
            buttonType: 'no_button',
            widgetPosition: '<?php echo $buttonConfig->position ?>'
        });
    <?php } ?>
    function checkZaloIsOpened(){
        if (jQuery('#ar-zalo-chat-widget>div').height() < 100){ 
            jQuery('#ar-zalo-chat-widget').removeClass('active');
            jQuery('#arcontactus').contactUs('show');
            clearInterval(zaloWidgetInterval);
        }
    }
    function checkTawkIsOpened(){
        console.log('checkTawkIsOpened');
        if (Tawk_API.isChatMinimized()){ 
            Tawk_API.hideWidget();
            jQuery('#arcontactus').contactUs('show');
            clearInterval(tawkToInterval);
        }
    }
</script>
<?php if ($zopim) {?>
    <?php if ($liveChatsConfig->isZendeskChat()) {?>
        <script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=<?php echo $liveChatsConfig->zopim_id ?>"> </script>
        <script type="text/javascript">
            zE('webWidget:on', 'chat:connected', function(){
                zE('webWidget', 'hide');
            });
            zE('webWidget:on', 'open', function(){
                jQuery('#arcontactus').contactUs('hide');
            });
            zE('webWidget:on', 'close', function(){
                zE('webWidget', 'hide');
                jQuery('#arcontactus').contactUs('show');
            });
            zE('webWidget:on', 'chat.unreadMsgs', function(msgs){
                zE('webWidget', 'show');
                zE('webWidget', 'open');
            });
        </script>
    <?php }else { ?>
        <script type="text/javascript">
            window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
            d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
            _.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
            $.src="https://v2.zopim.com/?<?php echo $liveChatsConfig->zopim_id ?>";z.t=+new Date;$.
            type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");
            $zopim(function(){
                $zopim.livechat.hideAll();
                <?php if ($buttonConfig->position == 'left') {?>
                    $zopim.livechat.window.setPosition('bl');
                <?php }else { ?>
                    $zopim.livechat.window.setPosition('br');
                <?php } ?>
                $zopim.livechat.window.onHide(function(){
                    $zopim.livechat.hideAll();
                    jQuery('#arcontactus').contactUs('show');
                });
            });
        </script>
    <?php } ?>
    
<?php } ?>
<?php if ($liveChatsConfig->isFacebookChatIntegrated() && $facebook) {?>
    <style type="text/css">
        <?php if ($buttonConfig->position == 'left'){ ?>
            .fb-customerchat > span > iframe{
                left: 10px !important;
                right: auto !important;
            }
        <?php }else{ ?>
            .fb-customerchat > span > iframe{
                right: 10px !important;
                left: auto !important;
            }
        <?php } ?>
        #ar-fb-chat{
            display: none;
        }
        #ar-fb-chat.active{
            display: block;
        }
    </style>
    <div id="ar-fb-chat">
        <div id="fb-root"></div>
        <?php if ($liveChatsConfig->fb_init){ ?>
            <script>
                (function(d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/<?php echo $liveChatsConfig->fb_lang? $liveChatsConfig->fb_lang : 'en_US' ?>/sdk/xfbml.customerchat.js#xfbml=1&version=v2.12&autoLogAppEvents=1";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
            </script>
        <?php } ?>
        <div class="fb-customerchat"
            attribution=setup_tool
            page_id="<?php echo $liveChatsConfig->fb_page_id ?>"
            greeting_dialog_display="hide"
            <?php if ($liveChatsConfig->fb_color){ ?>
              theme_color="#<?php echo $liveChatsConfig->fb_color ?>"
            <?php } ?>
        ></div>
    </div>
<?php }