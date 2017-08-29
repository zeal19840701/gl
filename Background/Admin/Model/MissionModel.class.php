<?php
namespace Admin\Model;
use Think\Model;
class MissionModel extends Model {
	protected $trueTableName = 'gl_mission';//要加上完整的表名
	protected $connection = 'DB_GOLD_LOCK';
	
	public function getInfo($param){
		$result = M($this->trueTableName, '', $this->connection)->field($param['field'])->where($param['where'])->find();
		return $result;
	}
	
    public function getList($param){
    	$result = M($this->trueTableName, '', $this->connection)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function getCount($param){
    	$result = M($this->trueTableName, '', $this->connection)->where($param)->count();
    	return $result;
    }
    
    public function insertData($data){
    	$result = M($this->trueTableName, '', $this->connection)->add($data);
    	return $result;
    }
    
    public function updateData($where, $data){
    	$result = M($this->trueTableName, '', $this->connection)->where($where)->save($data);
    	return $result;
    }
    
    public function updateFieldInc($where, $field, $num=1){
    	$result = M($this->trueTableName, '', $this->connection)->where($where)->setInc($field, $num);
    	return $result;
    }
    
    public function updateFieldDec($where, $field, $num=1){
    	$result = M($this->trueTableName, '', $this->connection)->where($where)->setDec($field, $num);
    	return $result;
    }
    
    public function getJoinQuery($param){
    	$result = M($this->trueTableName, '', $this->connection)->table($param['table'])->join($param['join'])->field($param['field'])->order($param['order'])->where($param['where'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function deleteData($where){
    	$result = M($this->trueTableName, '', $this->connection)->where($where)->delete();
    	return $result;
    }
}