<table class="wp-list-table widefat fixed striped" id="arcontactus-menu-items">
    <thead>
        <tr>
            <th scope="col" class="manage-column column-name column-primary"><?php echo __('Position', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Icon', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Color', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Title', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Type', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Device', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Link', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Active', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"></th>
        </tr>
    </thead>

    <tbody id="the-list">
        <?php foreach ($items as $item){?>
        <tr data-id="<?php echo $item->id ?>">
            <td class="">
                <span class="drag-handle">
                    <i class="icon move"></i>
                    <span class="position">
                        <?php echo $item->position ?>
                    </span>
                </span>
            </td>
            <td>
                <span>
                    <?php echo ArContactUsConfigModel::getIcon($item->icon) ?>
                </span>
            </td>
            <td>
                <span class="lbl-color" style="background: #<?php echo $item->color ?>"><?php echo $item->color ?></span>
            </td>
            <td>
                <?php echo $item->title ?>
            </td>
            <td>
                <?php if ($item->type == 0){
                    echo __('Link', AR_CONTACTUS_TEXT_DOMAIN);
                }elseif ($item->type == 1){
                    echo __('Integration', AR_CONTACTUS_TEXT_DOMAIN) . ':' . $item->integration;
                }elseif ($item->type == 2){
                    echo __('Custom JS code', AR_CONTACTUS_TEXT_DOMAIN);
                }elseif ($item->type == 3){
                    echo __('Callback form', AR_CONTACTUS_TEXT_DOMAIN);
                } ?>
            </td>
            <td>
                <?php if ($item->display == 1){ ?>
                    <span style="color: #00a426" title="<?php echo __('displays on desktop and mobile', AR_CONTACTUS_TEXT_DOMAIN) ?>"> 
                        <svg style="display: inline-block" aria-hidden="true" data-prefix="far" data-icon="desktop-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-desktop-alt fa-w-18 fa-3x"><path fill="currentColor" d="M528 0H48C21.5 0 0 21.5 0 48v288c0 26.5 21.5 48 48 48h480c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zM48 54c0-3.3 2.7-6 6-6h468c3.3 0 6 2.7 6 6v234H48V54zm432 434c0 13.3-10.7 24-24 24H120c-13.3 0-24-10.7-24-24s10.7-24 24-24h98.7l18.6-55.8c1.6-4.9 6.2-8.2 11.4-8.2h78.7c5.2 0 9.8 3.3 11.4 8.2l18.6 55.8H456c13.3 0 24 10.7 24 24z" class=""></path></svg>
                        <svg title="displays on mobile" style="display: inline-block" aria-hidden="true" data-prefix="fas" data-icon="mobile-android-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-mobile-android-alt fa-w-10 fa-3x"><path fill="currentColor" d="M272 0H48C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h224c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zm-64 452c0 6.6-5.4 12-12 12h-72c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h72c6.6 0 12 5.4 12 12v8zm64-80c0 6.6-5.4 12-12 12H60c-6.6 0-12-5.4-12-12V60c0-6.6 5.4-12 12-12h200c6.6 0 12 5.4 12 12v312z" class=""></path></svg>
                    </span>
                <?php }elseif($item->display == 2){ ?>
                    <span style="color: #7c529d" title="<?php echo __('displays on desktop only', AR_CONTACTUS_TEXT_DOMAIN) ?>">
                        <svg aria-hidden="true" data-prefix="far" data-icon="desktop-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-desktop-alt fa-w-18 fa-3x"><path fill="currentColor" d="M528 0H48C21.5 0 0 21.5 0 48v288c0 26.5 21.5 48 48 48h480c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zM48 54c0-3.3 2.7-6 6-6h468c3.3 0 6 2.7 6 6v234H48V54zm432 434c0 13.3-10.7 24-24 24H120c-13.3 0-24-10.7-24-24s10.7-24 24-24h98.7l18.6-55.8c1.6-4.9 6.2-8.2 11.4-8.2h78.7c5.2 0 9.8 3.3 11.4 8.2l18.6 55.8H456c13.3 0 24 10.7 24 24z" class=""></path></svg>
                    </span>
                <?php }elseif($item->display == 3){ ?>
                    <span style="color: #ff8400" title="<?php echo __('displays on mobile only', AR_CONTACTUS_TEXT_DOMAIN) ?>">
                        <svg aria-hidden="true" data-prefix="fas" data-icon="mobile-android-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-mobile-android-alt fa-w-10 fa-3x"><path fill="currentColor" d="M272 0H48C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h224c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zm-64 452c0 6.6-5.4 12-12 12h-72c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h72c6.6 0 12 5.4 12 12v8zm64-80c0 6.6-5.4 12-12 12H60c-6.6 0-12-5.4-12-12V60c0-6.6 5.4-12 12-12h200c6.6 0 12 5.4 12 12v312z" class=""></path></svg>
                    </span>
                <?php } ?>
            </td>
            <td>
                <?php if($item->type == 0){ ?>
                    <a href="<?php echo $item->link ?>" target="_blank">
                        <?php echo $item->link ?>
                    </a>
                <?php } ?>
            </td>
            <td>
                <a href="#" onclick="arCU.toggle(<?php echo $item->id ?>); return false;" class="<?php echo $item->status? 'lbl-success' : 'lbl-default' ?>">
                    <?php echo $item->status? __('Yes', AR_CONTACTUS_TEXT_DOMAIN) : __('No', AR_CONTACTUS_TEXT_DOMAIN) ?>
                </a>
            </td>
            <td>
                <a href="#" title="Edit" onclick="arCU.edit(<?php echo $item->id ?>); return false;" class="edit btn btn-default" data-id="<?php echo $item->id ?>">
                    <i class="icon-pencil"></i> <?php echo __('Edit', AR_CONTACTUS_TEXT_DOMAIN) ?>
                </a>

                <a href="#" title="Delete" onclick="arCU.remove(<?php echo $item->id ?>); return false;" data-id="<?php echo $item->id ?>" class="delete">
                    <i class="icon-trash"></i> <?php echo __('Delete', AR_CONTACTUS_TEXT_DOMAIN) ?>
                </a>
            </td>
        </tr>
        <?php } ?>
    </tbody>

    <tfoot>
        <tr>
            <th scope="col" class="manage-column column-name column-primary"><?php echo __('Position', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Icon', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Color', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Title', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Type', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Device', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Link', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"><?php echo __('Active', AR_CONTACTUS_TEXT_DOMAIN) ?></th>
            <th scope="col" class="manage-column column-description"></th>
        </tr>
    </tfoot>
</table>