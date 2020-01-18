<?php if (!isset($noSegment)){?>
<div class="ui segment arconfig-panel <?php echo ($activeSubmit == 'arcontactus-requests')? '' : 'hidden' ?>" id="arcontactus-requests">
<?php } ?>
    <form id="arcontactus-requests-form" method="post" action="<?php echo admin_url('admin.php?page=ar-contactus-key-requests') ?>">
<?php 
$selectedStatus = null;
if (isset($_REQUEST['status'])){
    $selectedStatus = (int)$_REQUEST['status'];
} ?>
        <ul class="subsubsub">
            <li class="all">
                <a href="<?php echo admin_url('admin.php?page=ar-contactus-key-requests') ?>" class="<?php echo $selectedStatus === null? 'current' : '' ?>" aria-current="page"><?php echo __('All', AR_CONTACTUS_TEXT_DOMAIN) ?> <span class="count">(<?php echo ArContactUsCallbackModel::find()->count() ?>)</span></a> |
            </li>
            <li class="publish">
                <a href="<?php echo admin_url('admin.php?page=ar-contactus-key-requests&status=0') ?>" class="<?php echo $selectedStatus === 0? 'current' : '' ?>"><?php echo __('New', AR_CONTACTUS_TEXT_DOMAIN) ?> <span class="count">(<?php echo ArContactUsCallbackModel::find()->where(array('status' => 0))->count() ?>)</span></a> |
            </li>
            <li class="publish">
                <a href="<?php echo admin_url('admin.php?page=ar-contactus-key-requests&status=1') ?>" class="<?php echo $selectedStatus == 1? 'current' : '' ?>"><?php echo __('Done', AR_CONTACTUS_TEXT_DOMAIN) ?> <span class="count">(<?php echo ArContactUsCallbackModel::find()->where(array('status' => 1))->count() ?>)</span></a> |
            </li>
            <li class="publish">
                <a href="<?php echo admin_url('admin.php?page=ar-contactus-key-requests&status=2') ?>" class="<?php echo $selectedStatus == 2? 'current' : '' ?>"><?php echo __('Ignored', AR_CONTACTUS_TEXT_DOMAIN) ?> <span class="count">(<?php echo ArContactUsCallbackModel::find()->where(array('status' => 2))->count() ?>)</span></a>
            </li>
        </ul>
        <?php 
        $callbackList->prepare_items();
        $callbackList->display(); 
        ?>
    </form>
<?php if (!isset($noSegment)){?>
</div>
<?php }