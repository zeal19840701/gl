<?php
namespace Admin\Model;
use Think\Model;
class RecommendModel extends Model {
	protected $trueTableName = 'gl_recommend';//要加上完整的表名
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
    
    public function insertRecommend($data){
    	$result = M($this->trueTableName, '', $this->connection)->add($data);
    	return $result;
    }
    
    public function updateRecommend($where, $data){
    	$result = M($this->trueTableName, '', $this->connection)->where($where)->save($data);
    	return $result;
    }
    
    public function delRecommend($where){
    	$result = M($this->trueTableName, '', $this->connection)->where($where)->delete();
    	//echo M($this->trueTableName, '', $this->connection)->_sql();
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
    
    public function getRecommendForUserList($param){
    	$result = M($this->trueTableName, '', $this->connection)->alias('a')->join($param['join'])->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function getRecommendForUserInfo($param){
    	$result = M($this->trueTableName, '', $this->connection)->alias('a')->join($param['join'])->field($param['field'])->where($param['where'])->find();
    	return $result;
    }
}