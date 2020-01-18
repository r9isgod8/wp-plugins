<?php
ArContactUsLoader::loadModel('ArContactUsModelAbstract');

class ArContactUsPromptModel extends ArContactUsModelAbstract
{
    public $id;
    public $message;
    public $status;
    public $position;
    
    public function rules()
    {
        return array(
            array(
                array(
                    'message',
                    'status',
                    'position'
                ), 'safe'
            ),
            array(
                array(
                    'message'
                ), 'validateRequired'
            )
        );
    }
    
    public function scheme()
    {
        return array(
            'id' => self::FIELD_INT,
            'message' => self::FIELD_STRING,
            'position' => self::FIELD_INT,
            'status' => self::FIELD_INT
        );
    }
    
    public static function tableName()
    {
        return self::dbPrefix().'arcontactus_prompt';
    }
    
    public static function createTable()
    {
        return self::getDb()->query("CREATE TABLE IF NOT EXISTS `" . self::tableName() . "` (
            `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `message` TEXT NOT NULL,
            `status` INT(11) UNSIGNED NOT NULL,
            `position` INT(10) UNSIGNED NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        )
        COLLATE='utf8_general_ci';");
    }
    
    public static function dropTable()
    {
        return self::getDb()->query("DROP TABLE IF EXISTS `" . self::tableName() . "`");
    }
    
    public static function getLastPostion()
    {
        $model = self::find()->orderBy('`position` DESC')->one();
        return $model? $model->position : 0;
    }
    
    public static function getDefaultItems()
    {
        return array(
            array(
                'message' => 'Hello!',
                'status' => 0
            ),
            array(
                'message' => 'Have a questions?',
                'status' => 0
            ),
            array(
                'message' => 'Please use this button to contact us!',
                'status' => 0
            ),
        );
    }
    
    public static function getMessages()
    {
        $res = array();
        $models = self::find()->where(array('status' => 1))->orderBy('`position` ASC')->all();
        foreach ($models as $model){
            $res[] = $model->message;
        }
        return $res;
    }
    
    public static function createDefaultItems()
    {
        foreach (self::getDefaultItems() as $k => $item){
            $model = new self();
            $model->message = $item['message'];
            $model->status = $item['status'];
            $model->position = $k + 1;
            $model->save();
        }
    }
}
