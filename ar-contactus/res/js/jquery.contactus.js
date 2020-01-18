"use strict";

(function($){
    function ArContactUs(element, options){
        this._initialized = false;
        this.settings = null;
        this.options = $.extend({}, ArContactUs.Defaults, options);
        this.$element = $(element);
        this.init();
        this.x = 0;
        this.y = 0;
        this._interval;
        this._menuOpened = false;
        this._callbackOpened = false;
        this.countdown = null;
    };
    ArContactUs.Defaults = {
        align: 'right',
        mode: 'regular',
        countdown: 0,
        drag: false,
        buttonText: 'Contact us',
        buttonSize: 'large',
        menuSize: 'normal',
        buttonIcon: '<svg width="20" height="20" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Canvas" transform="translate(-825 -308)"><g id="Vector"><use xlink:href="#path0_fill0123" transform="translate(825 308)" fill="#FFFFFF"/></g></g><defs><path id="path0_fill0123" d="M 19 4L 17 4L 17 13L 4 13L 4 15C 4 15.55 4.45 16 5 16L 16 16L 20 20L 20 5C 20 4.45 19.55 4 19 4ZM 15 10L 15 1C 15 0.45 14.55 0 14 0L 1 0C 0.45 0 0 0.45 0 1L 0 15L 4 11L 14 11C 14.55 11 15 10.55 15 10Z"/></defs></svg>',
        ajaxUrl: 'server.php',
        action: 'callback',
        phonePlaceholder: '+X-XXX-XXX-XX-XX',
        callbackSubmitText: 'Waiting for call',
        reCaptcha: false,
        reCaptchaAction: 'callbackRequest',
        reCaptchaKey: '',
        errorMessage: 'Connection error. Please try again.',
        callProcessText: 'We are calling you to phone',
        callSuccessText: 'Thank you.<br>We are call you back soon.',
        items: [],
        iconsAnimationSpeed: 800,
        theme: '#000000',
        callbackFormText: 'Please enter your phone number<br>and we call you back soon',
        closeIcon: '<svg width="12" height="13" viewBox="0 0 14 14" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Canvas" transform="translate(-4087 108)"><g id="Vector"><use xlink:href="#path0_fill" transform="translate(4087 -108)" fill="currentColor"></use></g></g><defs><path id="path0_fill" d="M 14 1.41L 12.59 0L 7 5.59L 1.41 0L 0 1.41L 5.59 7L 0 12.59L 1.41 14L 7 8.41L 12.59 14L 14 12.59L 8.41 7L 14 1.41Z"></path></defs></svg>',
        callbackStateIcon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M493.4 24.6l-104-24c-11.3-2.6-22.9 3.3-27.5 13.9l-48 112c-4.2 9.8-1.4 21.3 6.9 28l60.6 49.6c-36 76.7-98.9 140.5-177.2 177.2l-49.6-60.6c-6.8-8.3-18.2-11.1-28-6.9l-112 48C3.9 366.5-2 378.1.6 389.4l24 104C27.1 504.2 36.7 512 48 512c256.1 0 464-207.5 464-464 0-11.2-7.7-20.9-18.6-23.4z"></path></svg>'
    };
    ArContactUs.prototype.init = function(){
        if (this._initialized){
            return false;
        }
        this.destroy();
        this.settings = $.extend({}, this.options);
        this.$element.addClass('arcontactus-widget').addClass('arcontactus-message');
        if (this.settings.align === 'left'){
            this.$element.addClass('left');
        }else{
            this.$element.addClass('right');
        }
        if (this.settings.items.length){
            this.$element.append('<!--noindex-->');
            this._initCallbackBlock();
            if (this.settings.mode == 'regular'){
                this._initMessengersBlock();
            }
            this._initMessageButton();
            this._initPrompt();
            this._initEvents();
            this.startAnimation();
            this.$element.append('<!--/noindex-->');
            this.$element.addClass('active');
        }else{
            console.info('jquery.contactus:no items');
        }
        this._initialized = true;
        this.$element.trigger('arcontactus.init');
    };
    ArContactUs.prototype.destroy = function(){
        if (!this._initialized){
            return false;
        }
        this.$element.html('');
        this._initialized = false;
        this.$element.trigger('arcontactus.destroy');
    };
    ArContactUs.prototype._initCallbackBlock = function(){
        var $container = $('<div>', {
            class: 'callback-countdown-block',
            style: this._colorStyle()
        });
        var $close = $('<div>', {
            class: 'callback-countdown-block-close'
        });
        $close.append(this.settings.closeIcon);
        
        var $formBlock = $('<div>', {
            class: 'callback-countdown-block-phone'
        });
        $formBlock.append('<p>' + this.settings.callbackFormText + '</p>');
        
        var $form = $('<form>', {
            action: this.settings.ajaxUrl,
            method: 'POST'
        });
        
        var $formGroup = $('<div>', {
            class: 'callback-countdown-block-form-group'
        });
        var $inputAction = $('<input>', {
            name: 'action',
            type: 'hidden',
            value: this.settings.action
        });
        var $inputGtoken = $('<input>', {
            name: 'gtoken',
            class: 'ar-g-token',
            type: 'hidden',
            value: ''
        });
        var $inputPhone = $('<input>', {
            name: 'phone',
            class: 'arcontactus-message-callback-phone',
            required: '',
            type: 'tel',
            value: '',
            placeholder: this.settings.phonePlaceholder
        });
        var $inputSubmit = $('<input>', {
            id: 'arcontactus-message-callback-phone-submit',
            type: 'submit',
            style: this._backgroundStyle(),
            value: this.settings.callbackSubmitText
        });
        
        $formGroup.append($inputAction);
        $formGroup.append($inputGtoken);
        $formGroup.append($inputPhone);
        $formGroup.append($inputSubmit);
        
        var $timerBlock = $('<div>', {
            class: 'callback-countdown-block-timer'
        });
        
        var $timerText = $('<p>' + this.settings.callProcessText + '</p>');
        var $timer = $('<div>', {
            class: 'callback-countdown-block-timer_timer'
        });
        
        $timerBlock.append($timerText);
        $timerBlock.append($timer);
        
        var $blockSuccess = $('<div>', {
            class: 'callback-countdown-block-sorry'
        });
        
        var $blockSuccessText = $('<p>' + this.settings.callSuccessText + '</p>');
        $blockSuccess.append($blockSuccessText);
        
        $form.append($formGroup);
        $formBlock.append($form);
        
        $container.append($close);
        $container.append($formBlock);
        $container.append($timerBlock);
        $container.append($blockSuccess);
        this.$element.append($container);
    };
    ArContactUs.prototype._initMessengersBlock = function(){
        var $container = $('<div>', {
            class: 'messangers-block'
        });
        if (this.settings.menuSize === 'normal' || this.settings.menuSize === 'large'){
            $container.addClass('lg');
        }
        if (this.settings.menuSize === 'small'){
            $container.addClass('sm');
        }
        this._appendMessengerIcons($container);
        this.$element.append($container);
    };
    ArContactUs.prototype._appendMessengerIcons = function($container){
        $.each(this.settings.items, function(i){
            
            if (this.href == 'callback'){
                var $item = $('<div>', {
                    class: 'messanger call-back ' + (this.class? this.class : '')
                });
            }else{
                
                var $item = $('<a>', {
                    class: 'messanger ' + (this.class? this.class : ''),
                    id: (this.id? this.id : null),
                    rel: 'nofollow noopener',
                    href: this.href,
                    target: (this.target? this.target : '_blank')
                });
                if (this.onClick){
                    var $this = this;
                    $item.on('click', function(e){
                        $this.onClick(e);
                    });
                }
            }
            var $icon = $('<span>', {
                style: (this.color? ('background-color:' + this.color) : null)
            });
            $icon.append(this.icon);
            $item.append($icon);
            $item.append('<p>' + this.title + '</p>');
            $container.append($item);
        });
    };
    ArContactUs.prototype._initMessageButton = function(){
        var $this = this;
        var $container = $('<div>', {
            class: 'arcontactus-message-button',
            style: this._backgroundStyle()
        });
        if (this.settings.buttonSize === 'large'){
            this.$element.addClass('lg');
        }
        if (this.settings.buttonSize === 'huge'){
            this.$element.addClass('hg');
        }
        if (this.settings.buttonSize === 'medium'){
            this.$element.addClass('md');
        }
        if (this.settings.buttonSize === 'small'){
            this.$element.addClass('sm');
        }
        var $static = $('<div>', {
            class: 'static'
        });
        
        $static.append(this.settings.buttonIcon);
        if (this.settings.buttonText !== false){
            $static.append('<p>' + this.settings.buttonText + '</p>');
        }else{
            $container.addClass('no-text');
        }
        
        var $callBackState = $('<div>', {
            class: 'callback-state',
            style: $this._colorStyle()
        });
        
        $callBackState.append(this.settings.callbackStateIcon);
        
        var $icons = $('<div>', {
            class: 'icons hide'
        });
        
        var $iconsLine = $('<div>', {
            class: 'icons-line'
        });
        
        $.each(this.settings.items, function(i){
            var $icon = $('<span>', {
                style: $this._colorStyle()
            });
            $icon.append(this.icon);
            $iconsLine.append($icon);
        });
        
        $icons.append($iconsLine);
        
        
        var $close = $('<div>', {
            class: 'arcontactus-close'
        });
        
        $close.append(this.settings.closeIcon);
        
        var $pulsation = $('<div>', {
            class: 'pulsation',
            style: $this._backgroundStyle()
        });
        
        var $pulsation2 = $('<div>', {
            class: 'pulsation',
            style: $this._backgroundStyle()
        });
        
        $container.append($static).append($callBackState).append($icons).append($close).append($pulsation).append($pulsation2);
        
        this.$element.append($container);
    };
    
    ArContactUs.prototype._initPrompt = function(){
        var $container = $('<div>', {
            class: 'arcontactus-prompt'
        });
        var $close = $('<div>', {
            class: 'arcontactus-prompt-close',
            style: this._colorStyle()
        });
        $close.append(this.settings.closeIcon);
        
        var $inner = $('<div>', {
            class: 'arcontactus-prompt-inner',
        });
        
        $container.append($close).append($inner);
        
        this.$element.append($container);
    };
    
    ArContactUs.prototype._initEvents = function(){
        var $el = this.$element;
        var $this = this;
        $el.find('.arcontactus-message-button').on('mousedown', function(e) {
            $this.x = e.pageX;
            $this.y = e.pageY;
        }).on('mouseup', function(e) {
            if (e.pageX === $this.x && e.pageY === $this.y) {
                if ($this.settings.mode == 'regular'){
                    $this.toggleMenu();
                }else{
                    $this.openCallbackPopup();
                }
                e.preventDefault();
            }
        });
        if (this.settings.drag){
            $el.draggable();
            $el.get(0).addEventListener('touchmove', function(event) {
                var touch = event.targetTouches[0];
                // Place element where the finger is
                $el.get(0).style.left = touch.pageX-25 + 'px';
                $el.get(0).style.top = touch.pageY-25 + 'px';
                event.preventDefault();
            }, false);
        }
        $(document).on('click', function(e) {
            $this.closeMenu();
        });
        $el.on('click', function(e){
            e.stopPropagation(); 
        });
        $el.find('.call-back').on('click', function() {
            $this.openCallbackPopup();
        });
        $el.find('.callback-countdown-block-close').on('click', function() {
            if ($this.countdown != null) {
                clearInterval($this.countdown);
                $this.countdown = null;
            }
            $this.closeCallbackPopup();
        });
        $el.find('.arcontactus-prompt-close').on('click', function() {
            $this.hidePrompt();
        });
        $el.find('form').on('submit', function(event) {
            event.preventDefault();
            $el.find('.callback-countdown-block-phone').addClass('ar-loading');
            if ($this.settings.reCaptcha) {
                grecaptcha.execute($this.settings.reCaptchaKey, {
                    action: $this.settings.reCaptchaAction
                }).then(function(token) {
                    $el.find('.ar-g-token').val(token);
                    $this.sendCallbackRequest();
                });
            }else{
                $this.sendCallbackRequest();
            }
        });
        setTimeout(function(){
            $this._processHash();
        },500);
        $(window).on('hashchange', function(event){
            $this._processHash();
        });
    };
    ArContactUs.prototype._processHash = function(){
        var hash =  window.location.hash;
        var $this = this;
        switch(hash){
            case '#callback-form':
            case 'callback-form':
                $this.openCallbackPopup();
                break;
            case '#callback-form-close':
            case 'callback-form-close':
                $this.closeCallbackPopup();
                break;
            case '#contactus-menu':
            case 'contactus-menu':
                $this.openMenu();
                break;
            case '#contactus-menu-close':
            case 'contactus-menu-close':
                $this.closeMenu();
                break;
            case '#contactus-hide':
            case 'contactus-hide':
                $this.hide();
                break;
            case '#contactus-show':
            case 'contactus-show':
                $this.show();
                break;
        }
    },
    ArContactUs.prototype._callBackCountDownMethod = function(){
        var secs = this.settings.countdown;
        var $el = this.$element;
        var $this = this;
        var ms = 60;
        $el.find('.callback-countdown-block-phone, .callback-countdown-block-timer').toggleClass('display-flex');
        this.countdown = setInterval(function() {
            ms = ms - 1;
            var fsecs = secs;
            var fms = ms;
            if (secs < 10) {
                fsecs = "0" + secs;
            }
            if (ms < 10) {
                fms = "0" + ms;
            }
            var format = fsecs + ":" + fms;
            $el.find('.callback-countdown-block-timer_timer').html(format);
            if (ms === 0 && secs === 0) {
                clearInterval($this.countdown);
                $this.countdown = null;
                $el.find('.callback-countdown-block-sorry, .callback-countdown-block-timer').toggleClass('display-flex');
            }
            if (ms === 0) {
                ms = 60;
                secs = secs - 1;
            }
        }, 20);
    };
    ArContactUs.prototype.sendCallbackRequest = function(){
        var $this = this;
        var $el = $this.$element;
        this.$element.trigger('arcontactus.beforeSendCallbackRequest');
        $.ajax({
            url: $this.settings.ajaxUrl,
            type: "POST",
            dataType: 'json',
            data: $el.find('form').serialize(),
            success: function(data) {
                if ($this.settings.countdown){
                    $this._callBackCountDownMethod();
                }
                $el.find('.callback-countdown-block-phone').removeClass('ar-loading');
                if (data.success) {
                    if (!$this.settings.countdown){
                        $el.find('.callback-countdown-block-sorry, .callback-countdown-block-phone').toggleClass('display-flex');
                    }
                } else {
                    if (data.errors){
                        var errors = data.errors.join("\n\r");
                        alert(errors);
                    }else{
                        alert($this.settings.errorMessage);
                    }
                }
                $this.$element.trigger('arcontactus.successCallbackRequest', data);
            },
            error: function(){
                $el.find('.callback-countdown-block-phone').removeClass('ar-loading');
                alert($this.settings.errorMessage);
                $this.$element.trigger('arcontactus.errorCallbackRequest');
            }
        });
    };
    ArContactUs.prototype.show = function(){
        this.$element.addClass('active');
        this.$element.trigger('arcontactus.show');
    };
    ArContactUs.prototype.hide = function(){
        this.$element.removeClass('active');
        this.$element.trigger('arcontactus.hide');
    };
    ArContactUs.prototype.openMenu = function(){
        if (this.settings.mode == 'callback'){
            console.log('Widget in callback mode');
            return false;
        }
        var $el = this.$element;
        if (!$el.find('.messangers-block').hasClass('show-messageners-block')) {
            this.stopAnimation();
            $el.find('.messangers-block, .arcontactus-close').addClass('show-messageners-block');
            $el.find('.icons, .static').addClass('hide');
            $el.find('.pulsation').addClass('stop');
            this._menuOpened = true;
            this.$element.trigger('arcontactus.openMenu');
        }
    };
    ArContactUs.prototype.closeMenu = function(){
        if (this.settings.mode == 'callback'){
            console.log('Widget in callback mode');
            return false;
        }
        var $el = this.$element;
        if ($el.find('.messangers-block').hasClass('show-messageners-block')) {
            $el.find('.messangers-block, .arcontactus-close').removeClass('show-messageners-block');
            $el.find('.icons, .static').removeClass('hide');
            $el.find('.pulsation').removeClass('stop');
            this.startAnimation();
            this._menuOpened = false;
            this.$element.trigger('arcontactus.closeMenu');
        }
    };
    ArContactUs.prototype.toggleMenu = function(){
        var $el = this.$element;
        this.hidePrompt();
        if ($el.find('.callback-countdown-block').hasClass('display-flex')){
            return false;
        }
        if (!$el.find('.messangers-block').hasClass('show-messageners-block')) {
            this.openMenu();
        }else{
            this.closeMenu();
        }
        this.$element.trigger('arcontactus.toggleMenu');
    };
    ArContactUs.prototype.openCallbackPopup = function(){
        var $el = this.$element;
        $el.addClass('opened');
        this.closeMenu();
        this.stopAnimation();
        $el.find('.icons, .static').addClass('hide');
        $el.find('.pulsation').addClass('stop');
        $el.find('.callback-countdown-block').addClass('display-flex');
        $el.find('.callback-countdown-block-phone').addClass('display-flex');
        $el.find('.callback-state').addClass('display-flex');
        this._callbackOpened = true;
        this.$element.trigger('arcontactus.openCallbackPopup');
    };
    ArContactUs.prototype.closeCallbackPopup = function(){
        var $el = this.$element;
        $el.removeClass('opened');
        $el.find('.messangers-block').removeClass('show-messageners-block');
        $el.find('.arcontactus-close').removeClass('show-messageners-block');
        $el.find('.icons, .static').removeClass('hide');
        $el.find('.pulsation').removeClass('stop');
        $el.find('.callback-countdown-block, .callback-countdown-block-phone, .callback-countdown-block-sorry, .callback-countdown-block-timer').removeClass('display-flex');
        $el.find('.callback-state').removeClass('display-flex');
        this.startAnimation();
        this._callbackOpened = false;
        this.$element.trigger('arcontactus.closeCallbackPopup');
    };
    ArContactUs.prototype.startAnimation = function(){
        var $el = this.$element;
        var $container = $el.find('.icons-line');
        var $static = $el.find('.static');
        var width = $el.find('.icons-line>span:first-child').width();
        var offset = width + 40;
        if (this.settings.buttonSize === 'huge'){
            var xOffset = 2;
            var yOffset = 0;
        }
        if (this.settings.buttonSize === 'large'){
            var xOffset = 2;
            var yOffset = 0;
        }
        if (this.settings.buttonSize === 'medium'){
            var xOffset = 4;
            var yOffset = -2;
        }
        if (this.settings.buttonSize === 'small'){
            var xOffset = 4;
            var yOffset = -2;
        }
        var iconsCount = $el.find('.icons-line>span').length;
        var step = 0;
        this.stopAnimation();
        if (this.settings.iconsAnimationSpeed === 0){
            return false;
        }
        this._interval = setInterval(function(){
            if (step === 0){
                $container.parent().removeClass('hide');
                $static.addClass('hide');
            }
            var x = offset * step;
            var translate = 'translate(' + (-(x+xOffset)) + 'px, ' + yOffset + 'px)';
            $container.css({
                "-webkit-transform":translate,
                "-ms-transform":translate,
                "transform":translate
            });
            step++;
            if (step > iconsCount){
                if (step > iconsCount + 1){
                    step = 0;
                }
                $container.parent().addClass('hide');
                $static.removeClass('hide');
                var translate = 'translate(' + (-xOffset) + 'px, ' + yOffset + 'px)';
                $container.css({
                    "-webkit-transform":translate,
                    "-ms-transform":translate,
                    "transform":translate
                });
            }
        }, this.settings.iconsAnimationSpeed);
    };
    ArContactUs.prototype.stopAnimation = function(){
        clearInterval(this._interval);
        var $el = this.$element;
        var $container = $el.find('.icons-line');
        var $static = $el.find('.static');
        $container.parent().addClass('hide');
        $static.removeClass('hide');
        var translate = 'translate(' + (-2) + 'px, 0px)';
        $container.css({
            "-webkit-transform":translate,
            "-ms-transform":translate,
            "transform":translate
        });
    };
    ArContactUs.prototype.showPrompt = function(data){
        var $promptContainer = this.$element.find('.arcontactus-prompt');
        if (data && data.content){
            $promptContainer.find('.arcontactus-prompt-inner').html(data.content);
        }
        $promptContainer.addClass('active');
        this.$element.trigger('arcontactus.showPrompt');
    };
    ArContactUs.prototype.hidePrompt = function(){
        var $promptContainer = this.$element.find('.arcontactus-prompt');
        $promptContainer.removeClass('active');
        this.$element.trigger('arcontactus.hidePrompt');
    };
    ArContactUs.prototype.showPromptTyping = function(){
        var $promptContainer = this.$element.find('.arcontactus-prompt');
        $promptContainer.find('.arcontactus-prompt-inner').html('');
        this._insertPromptTyping();
        this.showPrompt({});
        this.$element.trigger('arcontactus.showPromptTyping');
    };
    ArContactUs.prototype._insertPromptTyping = function(){
        var $promptContainer = this.$element.find('.arcontactus-prompt-inner');
        var $typing = $('<div>', {
            class: 'arcontactus-prompt-typing'
        });
        var $item = $('<div>');
        $typing.append($item);
        $typing.append($item.clone());
        $typing.append($item.clone());
        $promptContainer.append($typing);
    };
    ArContactUs.prototype.hidePromptTyping = function(){
        var $promptContainer = this.$element.find('.arcontactus-prompt');
        $promptContainer.removeClass('active');
        this.$element.trigger('arcontactus.hidePromptTyping');
    };
    ArContactUs.prototype._backgroundStyle = function(){
        return 'background-color: ' + this.settings.theme;
    };
    ArContactUs.prototype._colorStyle = function(){
        return 'color: ' + this.settings.theme;
    };
    $.fn.contactUs = function(option){
        var args = Array.prototype.slice.call(arguments, 1);
        return this.each(function() {
            var $this = $(this),
                data = $this.data('ar.contactus');

            if (!data) {
                data = new ArContactUs(this, typeof option == 'object' && option);
                $this.data('ar.contactus', data);
            }

            if (typeof option == 'string' && option.charAt(0) !== '_') {
                data[option].apply(data, args);
            }
        });
    };
    $.fn.contactUs.Constructor = ArContactUs;
}(jQuery));