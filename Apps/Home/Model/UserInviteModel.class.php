<?php
namespace Home\Model;
use Think\Model;
class UserInviteModel extends Model {
	protected $trueTableName = 'gl_user_invite';//要加上完整的表名
	
    public function getInfo($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
    	return $res;
    }
    
    public function getList($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
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