<?php
namespace Home\Model;
use Think\Model;
class MissionUserModel extends Model {
	protected $trueTableName = 'gl_mission_user';//要加上完整的表名
	protected $connection = 'DB_GOLD_LOCK';
	
	public function getInfo($param){
		$result = M($this->trueTableName, '', $this->connection)->field($param['field'])->where($param['where'])->find();
		//echo M()->_sql();
		return $result;
	}
	
    public function getList($param){
    	$result = M($this->trueTableName, '', $this->connection)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function getCount($param){
    	$result = M($this->trueTableName, '', $this->connection)->where($param)->count();
    	//echo M($this->trueTableName, '', $this->connection)->_sql();
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
    
    public function deleteData($where){
    	$result = M($this->trueTableName, '', $this->connection)->where($where)->delete();
    	return $result;
    }
    
    public function getQuery($sql){
    	$result = M($this->trueTableName, '', $this->connection)->query($sql);
    	return $result;
    }
    
}