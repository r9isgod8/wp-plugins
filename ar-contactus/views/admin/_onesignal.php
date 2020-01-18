<link rel="manifest" href="<?php echo $manifest ?>" />
<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
<script type="text/javascript">
    window.OneSignal = window.OneSignal || [];
    OneSignal.push( function() {
        OneSignal.SERVICE_WORKER_UPDATER_PATH = "OneSignalSDKUpdaterWorker.js.php";
        OneSignal.SERVICE_WORKER_PATH = "OneSignalSDKWorker.js.php";
        OneSignal.SERVICE_WORKER_PARAM = {scope: '/'};
        
        
        OneSignal.setDefaultNotificationUrl('<?php echo $scheme ?>://<?php echo $host ?>');
        
        var oneSignal_options = {};
        
        window._oneSignalInitOptions = oneSignal_options;
        
        oneSignal_options['appId'] = '<?php echo $popupConfig->onesignal_app_id ?>';
        
        oneSignal_options['autoRegister'] = false;
                            
        oneSignal_options['httpPermissionRequest'] = {};
        oneSignal_options['httpPermissionRequest']['enable'] = true;
                                        
        oneSignal_options['welcomeNotification'] = {};
        oneSignal_options['welcomeNotification']['title'] = '';
        oneSignal_options['welcomeNotification']['message'] = "<?php echo __('Thanks for subscribing!', AR_CONTACTUS_TEXT_DOMAIN) ?>";
        oneSignal_options['welcomeNotification']['url'] = '<?php echo $scheme ?>://<?php echo $host ?>';
        oneSignal_options['path'] = '<?php echo AR_CONTACTUS_PLUGIN_URL ?>';
                        
        oneSignal_options['notifyButton'] = {};
        oneSignal_options['notifyButton']['enable'] = false;
        
        oneSignal_options['promptOptions']  = {
            customlink: {
                enabled: true, /* Required to use the Custom Link */
                style: "button", /* Has value of 'button' or 'link' */
                size: "small", /* One of 'small', 'medium', or 'large' */
                color: {
                    button: '#E12D30', /* Color of the button background if style = "button" */
                    text: '#FFFFFF', /* Color of the prompt's text */
                },
                text: {
                    subscribe: "<?php echo __('Subscribe to push notifications', AR_CONTACTUS_TEXT_DOMAIN) ?>", /* Prompt's text when not subscribed */
                    unsubscribe: "<?php echo __('Unsubscribe from push notifications', AR_CONTACTUS_TEXT_DOMAIN) ?>", /* Prompt's text when subscribed */
                    //explanation: "Get updates from all sorts of things that matter to you", /* Optional text appearing before the prompt button */
                },
                unsubscribeEnabled: true, /* Controls whether the prompt is visible after subscription */
            }
        };
        OneSignal.init(window._oneSignalInitOptions);
    });
    
    function documentInitOneSignal() {
        var oneSignal_elements = document.getElementsByClassName("OneSignal-prompt");
        var oneSignalLinkClickHandler = function(event) {
            OneSignal.push(['registerForPushNotifications']);
            event.preventDefault(); 
        };
        for(var i = 0; i < oneSignal_elements.length; i++){
            oneSignal_elements[i].addEventListener('click', oneSignalLinkClickHandler, false);
        }
    }

    if (document.readyState === 'complete') {
        documentInitOneSignal();
    } else {
        window.addEventListener("load", function(event){
            documentInitOneSignal();
        });
    }
</script>