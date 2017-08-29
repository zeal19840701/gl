<?php
namespace Home\Model;
use Think\Model;
class FeedbackModel extends Model {
	protected $trueTableName = 'gl_feedback';//要加上完整的表名
	
    public function getUserInfo($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
    	return $res;
    }
    
    public function insertUser($data){
    	$res = M($this->trueTableName)->add($data);
    	return $res;
    }
    
    public function updateUser($where, $data){
    	$res = M($this->trueTableName)->where($where)->save($data);
    	return $res;
    }
    
    public function updateFieldInc($where, $field, $num=1){
    	$result = M($this->trueTableName)->where($where)->setInc($field, $num);
    	return $result;
    }
    
    public function updateFieldDec($where, $field, $num=1){
    	$result = M($this->trueTableName)->where($where)->setDec($field, $num);
    	return $result;
    }
    
    public function getQuery($sql){
    	$result = M($this->trueTableName)->query($sql);
    	return $result;
    }
}