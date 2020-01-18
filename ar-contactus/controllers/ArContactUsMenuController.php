<?php
ArContactUsLoader::loadController('ArContractUsControllerAbstract');
ArContactUsLoader::loadModel('ArContactUsModel');

class ArContactUsMenuController extends ArContractUsControllerAbstract
{
    protected function ajaxActions()
    {
        return array(
            'arcontactus_save_menu_item' => 'saveItem',
            'arcontactus_reload_menu_items' => 'reloadItems',
            'arcontactus_reorder_menu_items' => 'reorderItems',
            'arcontactus_switch_menu_item' => 'switchItem',
            'arcontactus_edit_menu_item' => 'editItem',
            'arcontactus_delete_menu_item' => 'deleteItem'
        );
    }
    
    public function saveItem()
    {
        $id = $_POST['id'];
        if (!$id){
            $model = new ArContactUsModel();
            $model->status = 1;
            $model->position = ArContactUsModel::getLastPostion() + 1;
        }else{
            $model = ArContactUsModel::findOne($id);
        }
        $model->load($_POST['data']);
        $model->validate();
        $errors = $model->getErrors();
        
        switch ($model->type){
            case ArContactUsModel::TYPE_LINK:
                if (empty($model->link)){
                    $errors['link'] = array(__('Link field is required'));
                }
                break;
            case ArContactUsModel::TYPE_JS:
                if (empty($model->js)){
                    $errors['js'] = array(__('Custom JS code field is required'));
                }
                break;
            case ArContactUsModel::TYPE_INTEGRATION:
                if (empty($model->integration)){
                    $errors['integration'] = array(__('Integration field is required'));
                }
                break;
        }
        
        if (empty($errors)){
            wp_die($this->returnJson(array(
                'success' => $model->save()
            )));
        }else{
            wp_die($this->returnJson(array(
                'success' => 0,
                'errors' => $errors
            )));
        }
    }
    
    public function reloadItems()
    {
        wp_die($this->returnJson(array(
            'success' => 1,
            'content' => $this->render('/admin/_items_table.php', array(
                'items' => ArContactUsModel::find()->orderBy('`position` ASC')->all()
            ))
        )));
    }
    
    public function reorderItems()
    {
        $data = $_POST['data'];
        foreach ($data as $item) {
            $k = explode('_', $item);
            ArContactUsModel::updateAll(array(
                'position' => (int)$k[1]
            ), array(
                'id'  => (int)$k[0]
            ));
        }
        wp_die($this->returnJson(array()));
    }
    
    public function switchItem()
    {
        $id = $_POST['id'];
        $model = ArContactUsModel::find()->where(array('id' => $id))->one();
        $model->status = $model->status? 0 : 1;
        $model->save();
        wp_die($this->returnJson(array(
            'success' => 1
        )));
    }
    
    public function editItem()
    {
        $id = $_GET['id'];
        $model = ArContactUsModel::find()->where(array('id' => $id))->one();
        $model->shortcode = $model->getShortcode();
        wp_die($this->returnJson($model));
    }
    
    public function deleteItem()
    {
        $id = $_POST['id'];
        $model = ArContactUsModel::find()->where(array('id' => $id))->one();
        wp_die($this->returnJson(array(
            'success' => $model->delete()
        )));
    }
}
