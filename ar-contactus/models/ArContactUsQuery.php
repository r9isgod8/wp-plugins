<?php
/**
 * @property ArContactUsModel $model
 */
class ArContactUsQuery
{
    protected $model;
    protected $condition = array();
    protected $order = array();
    protected $offset = null;
    protected $limit = null;

    public function __construct($model)
    {
        $this->model = $model;
    }
    
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }
    
    public function orderBy($order)
    {
        $this->order[] = $order;
        return $this;
    }
    
    public function andWhere($condition)
    {
        $this->condition[] = ' AND ' . $this->buildCondition($condition);
        return $this;
    }
    
    public function where($condition)
    {
        $this->condition[] = $this->buildCondition($condition);
        return $this;
    }
    
    public function orWhere($condition)
    {
        $this->condition[] = ' OR ' . $this->buildCondition($condition, 'OR');
        return $this;
    }
    
    public function one()
    {
        $q = $this->model->getDb()->prepare($this->buildSelectQuery() . ' LIMIT 1', array());
        $data = $this->model->getDb()->get_row($q);
        return $this->getResult($data);
    }
    
    public function all()
    {
        $result = array();
        $q = $this->buildSelectQuery();
        $data = $this->model->getDb()->get_results($q);
        foreach ($data as $row){
            $result[] = $this->getResult($row);
        }
        return $result;
    }
    
    public function count()
    {
        $q = $this->buildCountQuery();
        $data = $this->model->getDb()->get_results($q);
        if ($data){
            return $data[0]->c;
        }
        return 0;
    }
    
    protected function getResult($data)
    {
        if (!$data){
            return null;
        }
        $className = get_class($this->model);
        $model = new $className();
        $pk = $className::primaryKey();
        foreach ($data as $k => $v){
            if ($model->isAttributeSafe($k) || $k === $pk) {
                $model->setAttribute($k, $v);
            }
        }
        $model->setIsNewRecord(false);
        return $model;
    }
    
    public function buildCountQuery()
    {
        $tableName = $this->model->getTableName();
        if ($this->condition){
            $q = "SELECT COUNT(1) c FROM `{$tableName}` WHERE " . implode(' ', $this->condition);
        }else{
            $q = "SELECT COUNT(1) c FROM `{$tableName}` ";
        }
        if ($this->order){
            $q .= ' ORDER BY ' . implode(', ', $this->order);
        }
        if ($this->limit || $this->offset){
            $q .= ' LIMIT ' . (int)$this->offset . ', ' . (int)$this->limit;
        }
        return $q;
    }

    public function buildSelectQuery()
    {
        $tableName = $this->model->getTableName();
        if ($this->condition){
            $q = "SELECT * FROM `{$tableName}` WHERE " . implode(' ', $this->condition);
        }else{
            $q = "SELECT * FROM `{$tableName}` ";
        }
        if ($this->order){
            $q .= ' ORDER BY ' . implode(', ', $this->order);
        }
        if ($this->limit || $this->offset){
            $q .= ' LIMIT ' . (int)$this->offset . ', ' . (int)$this->limit;
        }
        return $q;
    }


    protected function buildCondition($conditions, $operator = 'AND')
    {
        $condition = array();
        if (is_array($conditions)){
            foreach ($conditions as $field => $part) {
                if (is_array($part)) {
                    $condition[] = "(`{$field}` IN (" . implode(',', $part) . "))";
                }else{
                    if ($this->model->getAttributeType($field) == 'int'){
                        $condition[] = $this->model->getDb()->prepare("(`{$field}` = %d)", $part);
                    }elseif($this->model->getAttributeType($field) == 'string'){
                        $condition[] = $this->model->getDb()->prepare("(`{$field}` = %s)", $part);
                    }
                }
            }
        }elseif(is_string($conditions)){
            $condition[] = $conditions;
        }
        
        return implode(" {$operator} ", $condition);
    }
}
