<?php
ArContactUsLoader::loadModel('ArContactUsQuery');

abstract class ArContactUsModelAbstract
{
    const FIELD_INT = 'int';
    const FIELD_STRING = 'string';
    
    protected $errors = array();
    protected $isNewRecord = true;
    
    /**
     * @return wpdb
     */
    public static function getDb()
    {
        global $wpdb;
        return $wpdb;
    }
    
    public static function dbPrefix()
    {
        return self::getDb()->prefix;
    }
    
    public static function primaryKey()
    {
        return 'id';
    }
    
    public static function tableName()
    {
        return '';
    }
    
    public static function createTable()
    {
        return '';
    }
    
    public static function dropTable()
    {
        return '';
    }
    
    public function scheme()
    {
        return array();
    }
    
    public function rules()
    {
        return array();
    }
    
    public function filters()
    {
        return array();
    }
    
    public function save()
    {
        $class = get_called_class();
        $data = $this->getAttributes();
        if ($this->isNewRecord){
            if ($res = $this->insert($data)){
                $pk = $class::primaryKey();
                $this->$pk = self::getDb()->insert_id;
                $this->isNewRecord = false;
            }
            return $res;
        }
        if ($res = $this->update($data)){
            $this->isNewRecord = false;
        }
        return $res === false? false : true;
    }
    
    public static function updateAll($attributes, $condition)
    {
        $class = get_called_class();
        $tableName = $class::tableName();
        self::getDb()->update($tableName, $attributes, $condition);
    }
    
    public static function deleteAll($condition)
    {
        $class = get_called_class();
        $tableName = $class::tableName();
        self::getDb()->delete($tableName, $condition);
    }
    
    public function update($data)
    {
        $pk = self::primaryKey();
        $class = get_called_class();
        $tableName = $class::tableName();
        return self::getDb()->update($tableName, $data, array($pk => $this->$pk));
    }
    
    public function delete()
    {
        $class = get_called_class();
        $pk = self::primaryKey();
        return self::getDb()->delete($class::tableName(), array($pk => $this->$pk));
    }
    
    protected function insert($data)
    {
        $class = get_called_class();
        $tableName = $class::tableName();
        return self::getDb()->insert($tableName, $data);
    }
    
    public function attributeLabels()
    {
        return array();
    }
    
    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        if (isset($labels[$attribute])) {
            return $labels[$attribute];
        }
        return $attribute;
    }


    /**
     * 
     * @return \ArContactUsQuery
     */
    public static function find()
    {
        $class = get_called_class();
        return new ArContactUsQuery(new $class);
    }
    
    public static function findOne($condition)
    {
        $class = get_called_class();
        $query = new ArContactUsQuery(new $class);
        return $query->where(array($class::primaryKey() => $condition))->one();
    }
    
    public function getTableName()
    {
        $class = get_called_class();
        return $class::tableName();
    }
    
    public function getAttributes()
    {
        $data = array();
        $className = get_called_class();
        $pk = $className::primaryKey();
        foreach ($this as $k => $v){
            if ($this->isAttributeSafe($k) || $k === $pk){
                $data[$k] = $v;
            }
        }
        return $data;
    }
    
    public function getAttributeType($attribute)
    {
        $scheme = $this->scheme();
        if (isset($scheme[$attribute])) {
            return $scheme[$attribute];
        }
        return self::FIELD_STRING;
    }
    
    public function isAttributeSafe($attribute)
    {
        $rules = $this->rules();
        foreach ($rules as $rule) {
            if (isset($rule[0]) && isset($rule[1]) && in_array($attribute, $rule[0]) && $rule[1] != 'unsafe') {
                return true;
            }
        }
        return false;
    }
    
    public function setAttribute($attribute, $value)
    {
        if (property_exists($this, $attribute)){
            $this->$attribute = stripslashes($value);
        }
    }
    
    public function load($data)
    {
        foreach ($data as $attribute => $value){
            if ($this->isAttributeSafe($attribute)){
                $this->setAttribute($attribute, $value);
            }
        }
    }
    
    public function isNewRecord()
    {
        return $this->isNewRecord;
    }
    
    public function setIsNewRecord($bool)
    {
        $this->isNewRecord = $bool;
    }
    
    public function validate($addErrors = true)
    {
        if ($addErrors) {
            $this->errors = array();
        }
        $this->filter();
        $valid = true;
        foreach ($this->getAttributes() as $attr => $value) {
            $valid = $this->validateAttribute($attr, $addErrors) && $valid;
        }
        return $valid;
    }
    
    public function validateAttribute($attr, $addErrors = true)
    {
        $valid = true;
        $value = $this->$attr;
        if ($validators = $this->getAttributeValidators($attr)) {
            foreach ($validators as $validator) {
                $method = $validator['validator'];
                $params = isset($validator['params'])? $validator['params'] : array();
                if ((isset($validator['on']) && $validator['on']) || (!isset($validator['on']) || $validator['on'] === null)) {
                    if (method_exists($this, $method)) {
                        if ($this->$method($value, $params)) {
                            $valid = $valid && $this->$method($value, $params);
                        } else {
                            if ($addErrors) {
                                if (isset($validator['message'])) {
                                    $this->addError($attr, $this->getMessage($validator['message'], $attr, $value));
                                } else {
                                    $this->addError($attr, sprintf(__('Incorrect "%s" value', AR_CONTACTUS_TEXT_DOMAIN), $this->getAttributeLabel($attr)));
                                }
                            }
                            $valid = false;
                        }
                    }else{
                        $valid = $valid && true;
                    }
                } else {
                    $valid = $valid && true;
                }
            }
        }
        return $valid;
    }
    
    public function getAttributeValidators($attribute)
    {
        $rules = $this->rules();
        $validators = array();
        foreach ($rules as $rule) {
            if (isset($rule[0]) && isset($rule[1]) && in_array($attribute, $rule[0]) && $rule[1] != 'unsafe') {
                $validator = array(
                    'validator' => $rule[1],
                    'params' => isset($rule['params'])? $rule['params'] : array(),
                    'message' => isset($rule['message'])? $rule['message'] : null,
                );
                if (isset($rule['on'])) {
                    $validator['on'] = $rule['on'];
                }
                $validators[] = $validator;
            }
        }
        return $validators;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function addError($attribute, $error)
    {
        if (isset($this->errors[$attribute])) {
            $this->errors[$attribute][] = $error;
        } else {
            $this->errors[$attribute] = array($error);
        }
    }
    
    public function filter()
    {
        foreach ($this->getAttributes() as $attr => $value) {
            if ($filters = $this->getAttributeFilters($attr)) {
                foreach ($filters as $filter) {
                    $method = $filter['filter'];
                    if ($this->getMultiLangAttribute($attr) && is_array($value)) {
                        foreach ($value as $k => $v) {
                            if (method_exists($this, $method)) {
                                $this->$attr[$k] = $this->$method($v, $filter['params']);
                            }
                        }
                    } else {
                        if (method_exists($this, $method)) {
                            $this->$attr = $this->$method($value, $filter['params']);
                        }
                    }
                }
            }
        }
    }
    
    public function getAttributeFilters($attribute)
    {
        $rules = $this->filters();
        $filters = array();
        foreach ($rules as $rule) {
            if (isset($rule[0]) && isset($rule[1]) && in_array($attribute, $rule[0])) {
                $filter = array(
                    'filter' => $rule[1],
                    'params' => isset($rule['params'])? $rule['params'] : array()
                );
                if (isset($rule['on'])) {
                    $filter['on'] = $rule['on'];
                }
                $filters[] = $filter;
            }
        }
        return $filters;
    }
    
    public function validateRequired($value, $params = array())
    {
        if (empty($value)){
            return false;
        }
        return true;
    }
}
