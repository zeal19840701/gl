<?php
namespace Home\Model;
use Think\Model;
class SingleListModel extends Model {
	protected $trueTableName = 'gl_single_list';//要加上完整的表名
	
    public function getInfo($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
    	return $res;
    }
    
    public function getList($param){
    	$result = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	//echo M($this->trueTableName)->_sql();
    	return $result;
    }
    
    public function getCount($param){
    	$result = M($this->trueTableName)->where($param)->count();
    	return $result;
    }
    
    public function getJoinQuery($param){
    	$result = M($this->trueTableName)->table($param['table'])->join($param['join'])->field($param['field'])->order($param['order'])->where($param['where'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function getJoinQueryInfo($param){
    	$result = M($this->trueTableName)->table($param['table'])->join($param['join'])->field($param['field'])->where($param['where'])->find();
    	//echo M($this->trueTableName)->_sql();
    	return $result;
    }
    
    public function getJoinQueryCount($param){
    	$result = M($this->trueTableName)->table($param['table'])->join($param['join'])->order($param['order'])->where($param['where'])->count();
    	//echo M($this->trueTableName)->_sql();
    	return $result;
    }
    
    public function insertData($data){
    	$res = M($this->trueTableName)->add($data);
    	return $res;
    }
    
    public function updateData($where, $data){
    	$res = M($this->trueTableName)->where($where)->save($data);
    	//echo M($this->trueTableName)->getLastSql();
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