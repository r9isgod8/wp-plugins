<?php
ArContactUsLoader::loadController('ArContractUsControllerAbstract');
ArContactUsLoader::loadModel('ArContactUsPromptModel');

class ArContactUsPromptController extends ArContractUsControllerAbstract
{
    protected function ajaxActions()
    {
        return array(
            'arcontactus_save_prompt_item' => 'saveItem',
            'arcontactus_reload_prompt_items' => 'reloadItems',
            'arcontactus_reorder_prompt_items' => 'reorderItems',
            'arcontactus_switch_prompt_item' => 'switchItem',
            'arcontactus_edit_prompt_item' => 'editItem',
            'arcontactus_delete_prompt_item' => 'deleteItem'
        );
    }
    
    public function saveItem()
    {
        $id = $_POST['id'];
        if (!$id){
            $model = new ArContactUsPromptModel();
            $model->status = 1;
            $model->position = ArContactUsPromptModel::getLastPostion() + 1;
        }else{
            $model = ArContactUsPromptModel::findOne($id);
        }
        $model->load($_POST['data']);
        if($model->validate()){
            wp_die($this->returnJson(array(
                'success' => $model->save()
            )));
        }else{
            wp_die($this->returnJson(array(
                'success' => 0,
                'errors' => $model->getErrors()
            )));
        }
    }
    
    public function reloadItems()
    {
        wp_die($this->returnJson(array(
            'success' => 1,
            'content' => $this->render('/admin/_prompt_table.php', array(
                'items' => ArContactUsPromptModel::find()->orderBy('`position` ASC')->all()
            ))
        )));
    }
    
    public function reorderItems()
    {
        $data = $_POST['data'];
        foreach ($data as $item) {
            $k = explode('_', $item);
            ArContactUsPromptModel::updateAll(array(
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
        $model = ArContactUsPromptModel::find()->where(array('id' => $id))->one();
        $model->status = $model->status? 0 : 1;
        $model->save();
        wp_die($this->returnJson(array(
            'success' => 1
        )));
    }
    
    public function editItem()
    {
        $id = $_GET['id'];
        $model = ArContactUsPromptModel::find()->where(array('id' => $id))->one();
        wp_die($this->returnJson($model));
    }
    
    public function deleteItem()
    {
        $id = $_POST['id'];
        $model = ArContactUsPromptModel::find()->where(array('id' => $id))->one();
        wp_die($this->returnJson(array(
            'success' => $model->delete()
        )));
    }
}
