<?php
namespace Home\Model;
use Think\Model;
class RecommendShareModel extends Model {
	protected $trueTableName = 'gl_recommend_share';//要加上完整的表名
	
	public function getInfo($param){
		$result = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param['order'])->find();
		return $result;
	}
	
    public function getList($param){
    	$result = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function getCount($param){
    	$result = M($this->trueTableName)->where($param)->count();
    	return $result;
    }
    
    public function insertRecommend($data){
    	$result = M($this->trueTableName)->add($data);
    	return $result;
    }
    
    public function updateRecommend($where, $data){
    	$result = M($this->trueTableName)->where($where)->save($data);
    	return $result;
    }
    
    public function delRecommend($where){
    	$result = M($this->trueTableName)->where($where)->delete();
    	return $result;
    }
    
    public function getRecommendForUserList($param){
    	$result = M($this->trueTableName)->alias('a')->join($param['join'])->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function getRecommendForUserInfo($param){
    	$result = M($this->trueTableName)->alias('a')->join($param['join'])->field($param['field'])->where($param['where'])->find();
    	return $result;
    }
    
    public function getQuery($sql){
    	$result = M($this->trueTableName)->query($sql);
    	return $result;
    }
}