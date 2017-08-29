<?php
namespace Home\Model;
use Think\Model;
class PayOrderModel extends Model {
	protected $trueTableName = 'gl_pay_order';//要加上完整的表名
	
    public function getInfo($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
    	//echo M($this->trueTableName)->_sql();
    	return $res;
    }
    
    public function insertData($data){
    	$res = M($this->trueTableName)->add($data);
    	return $res;
    }
    
    public function updateData($where, $data){
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