<?php

namespace core;

use dispatcher\Box;

class Orm extends Box
{
    public $_where;
    public $_select;
    public $_limit;
    public $_sql;
    public $_table;
    public $_params = [];

    public function init()
    {
        $this->_table = $this->table ?? strtolower(end(explode('\\',get_called_class()))).'s';
    }

	protected function find()
	{
        $this->_sql = 'SELECT * FROM '.$this->_table;
	    return $this;    
	}

	public function select($select)
	{
        if (is_array($select)) {
            $select = implode(',',$select);
        }
		$this->_sql = str_replace('*',trim($select),$this->_sql);
	    return $this;
	}

	public function one() 
	{
		$this->_limit = ' LIMIT 1';
	    return current($this->fetch());
	}

	public function all() 
	{
		return $this->fetch();
    }

    public function fetch()
    {
        return Model::prepare($this->_sql.($this->_where ?? ''))
            ->bind($this->_params)
            ->get();
    }

    public function and($where)
    {
         $this->_where .= $this->setCondition($where,'AND');
         return $this;
    }

    public function or($where)
    {
        $this->_where .= $this->setCondition($where,'OR');
        return $this;
    
    }
    public function where($where) 
    {
        $this->_where = $this->setCondition($where,'WHERE');
        return $this;
    }
    private function setCondition($where,$type)
    {
        if (is_string($where)) {
            return " $type $where";
        }
        if (!is_array($where) || count($where) != 3) {
            Throw new Exception("Invalid where condition");
        }
        $keyword = strtoupper($where[0]);
        $field = $where[1];
        $value = $where[2];
        switch($keyword) {
            case 'BETWEEN':
                if (!is_array($value) || count($value) != 2) {
                    Throw new Exception("Invalid value in between");
                }
                $tag = '? and ?';
                $value = [$value[0],$value[1]];
            break;
            default:
                $tag = '?';
                if (is_array($value)) {
                    while(next($value)) {
                        $tag .= ', ?';
                    }
                    $tag = "($tag)";
                }
        }
        $this->_params = array_merge($this->_params,is_array($value) ? $value : [$value]);
        return " $type $field $keyword $tag";
    }
}
