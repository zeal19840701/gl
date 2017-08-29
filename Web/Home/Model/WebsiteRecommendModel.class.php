<?php
namespace Home\Model;
use Think\Model;
class WebsiteRecommendModel extends Model {
	protected $trueTableName = 'gl_website_recommend';//要加上完整的表名
	
    public function getInfo($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
    	return $res;
    }
    
    public function getList($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
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
    
    public function getQuery($sql){
    	$result = M($this->trueTableName)->query($sql);
    	return $result;
    }
    
}