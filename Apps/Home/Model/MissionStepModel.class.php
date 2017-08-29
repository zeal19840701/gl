<?php
namespace Home\Model;
use Think\Model;
class MissionStepModel extends Model {
	protected $trueTableName = 'gl_mission_step';//要加上完整的表名
	
	public function getInfo($param){
		$result = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
		return $result;
	}
	
    public function getList($param){
    	$result = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function getCount($param){
    	$result = M($this->trueTableName)->where($param)->count();
    	//echo M($this->trueTableName)->_sql();
    	return $result;
    }
    
    public function insertData($data){
    	$result = M($this->trueTableName)->add($data);
    	return $result;
    }
    
    public function updateData($where, $data){
    	$result = M($this->trueTableName)->where($where)->save($data);
    	return $result;
    }
    
    public function deleteData($where){
    	$result = M($this->trueTableName)->where($where)->delete();
    	return $result;
    }
}