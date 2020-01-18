<form id="<?php echo $form['id'] ?>" method="POST" class="ui form">
    <?php wp_nonce_field(ArContactUsAdmin::NONCE ) ?>
    <?php foreach ($fields as $attr => $params){?>
        <div class="field <?php echo $params['form_group_class'] ?>">
            <?php if ($params['type'] == 'switch'){ ?>
                <div class="ui toggle checkbox">
                    <input id="<?php echo $params['id'] ?>_OFF" name="<?php echo $params['name'] ?>" value="0" tabindex="0" autocomplete="off" class="hidden" type="hidden">
                    <input id="<?php echo $params['id'] ?>" name="<?php echo $params['name'] ?>" value="1" tabindex="0" autocomplete="off" <?php echo $params['value']? 'checked="true"' : '' ?> class="hidden" type="checkbox">
                    <label for="<?php echo $params['id'] ?>"><?php echo $params['label'] ?></label>
                </div>
            <?php } ?>
            <?php if ($params['type'] == 'text'){ ?>
                <label for="<?php echo $params['id'] ?>"><?php echo $params['label'] ?></label>
                <?php if ($params['suffix']){?>
                    <div class="ui right labeled input">
                        <input id="<?php echo $params['id'] ?>" name="<?php echo $params['name'] ?>" value="<?php echo $params['value'] ?>" placeholder="<?php echo $params['placeholder'] ?>" type="text">
                        <div class="ui basic label"><?php echo $params['suffix'] ?></div>
                    </div>
                <?php }else{ ?>
                    <input id="<?php echo $params['id'] ?>" name="<?php echo $params['name'] ?>" value="<?php echo $params['value'] ?>" placeholder="<?php echo $params['placeholder'] ?>" type="text">
                <?php } ?>
            <?php } ?>
            <?php if ($params['type'] == 'textarea'){ ?>
                <label for="<?php echo $params['id'] ?>"><?php echo $params['label'] ?></label>
                <textarea rows="3" id="<?php echo $params['id'] ?>" name="<?php echo $params['name'] ?>" placeholder="<?php echo $params['placeholder'] ?>"><?php echo $params['value'] ?></textarea>
            <?php } ?>      
            <?php if ($params['type'] == 'color'){ ?>
                <label for="<?php echo $params['id'] ?>"><?php echo $params['label'] ?></label>
                <input id="<?php echo $params['id'] ?>" name="<?php echo $params['name'] ?>" class="jscolor" value="<?php echo $params['value'] ?>" placeholder="<?php echo $params['placeholder'] ?>" type="text">
            <?php } ?>
            <?php if ($params['type'] == 'select'){ ?>
                <label for="<?php echo $params['id'] ?>"><?php echo $params['label'] ?></label>
                <?php if ($params['multiple']){?>
                    <select autocomplete="off" id="<?php echo $params['id'] ?>" class="ui dropdown" name="<?php echo $params['name'] ?>" id="" multiple="">
                        <?php foreach ($params['options']['values'] as $key => $title){?>
                            <option <?php echo in_array($key, $params['value'])? 'selected="selected"' : '' ?> value="<?php echo $key ?>"><?php echo $title ?></option>
                        <?php } ?>
                    </select>
                <?php }else{ ?>
                    <select autocomplete="off" id="<?php echo $params['id'] ?>" class="ui dropdown" name="<?php echo $params['name'] ?>">
                        <?php foreach ($params['options']['values'] as $key => $title){?>
                            <option <?php echo $key == $params['value']? 'selected="selected"' : '' ?> value="<?php echo $key ?>"><?php echo $title ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>
            <?php } ?>
            <?php if ($params['type'] == 'iconDropdown'){ ?>
                <label for="<?php echo $params['id'] ?>"><?php echo $params['label'] ?></label>
                <div class="ui fluid selection search dropdown iconed" id="<?php echo $params['id'] ?>-dropdown">
                    <input value="<?php echo $params['value'] ?>" name="<?php echo $params['name'] ?>" id="<?php echo $params['id'] ?>" data-default="" autocomplete="off" data-serializable="true" type="hidden">
                    <i class="dropdown icon"></i>
                    <div class="default text"><?php echo __('Select icon', AR_CONTACTUS_TEXT_DOMAIN) ?></div>
                    <div class="menu">
                        <?php foreach ($params['options']['values'] as $key => $svg){?>
                            <div class="item" data-value="<?php echo $key ?>">
                                <?php echo $svg ?>
                                <?php echo $key ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <?php if ($params['type'] == 'html'){ ?>
                <?php echo $params['html_content'] ?>
            <?php } ?>
            <?php if ($params['desc']){?>
                <div class="help-block">
                    <?php echo $params['desc'] ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    <div class="text-right">
        <input name="<?php echo $form['id'] ?>" class="button button-primary button-large" value="<?php echo __('Save', AR_CONTACTUS_TEXT_DOMAIN) ?>" type="submit" />
    </div>
</form>
<script>
    window.addEventListener('load', function(){
        jQuery('#<?php echo $form['id'] ?> .ui.checkbox').checkbox();
        jQuery('#<?php echo $form['id'] ?> .ui.dropdown').dropdown({
            allowAdditions: true
        });
    });
</script>