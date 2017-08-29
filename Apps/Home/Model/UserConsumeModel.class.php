<?php
namespace Home\Model;
use Think\Model;
class UserConsumeModel extends Model {
	protected $trueTableName = 'gl_user_consume';//要加上完整的表名
	
    public function getUserInfo($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
    	//echo M($this->trueTableName)->_sql();
    	return $res;
    }
    
    public function getUserList($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param['order'])->group($param['group'])->limit($param['limit'])->select();
    	//echo M($this->trueTableName)->_sql();
    	//write_log(array("gl_user_consume:", M($this->trueTableName)->_sql()));
    	foreach($res as $k=>$v){
    		$res[$k]['coin'] = (int)($v['coin']);
    		$res[$k]['surplus_coin'] = (int)($v['surplus_coin']);
    	}
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
    
    public function getCount($param){
    	$result = M($this->trueTableName)->where($param)->count();
    	return $result;
    }
    
    public function getQuery($sql){
    	$result = M($this->trueTableName)->query($sql);
    	return $result;
    }
    
    /**
     * 记录收支
     * @param string $userId
     * @param int $coin
     * @param int $surplus_coin
     * @param string $type
     * @param string $intro
     * @return int $res 
     */
    public function UserConsume($userId, $coin, $surplus_coin=0, $type='收入', $intro=''){
    	$nowDate = date("Y-m-d H:i:s", time());
    	if(empty($surplus_coin)){
    		//不传值去查询当前金币数量
    		$userInfo = D('User')->getUserInfo(array('field'=>'id,coin', 'where'=>array('id'=>$userId)));
    		if(!empty($userInfo)){
    			$surplus_coin = $userInfo['coin'];
    		}
    	}
    	$data = array(
    		'user_id'=>$userId,
    		'coin'=>$coin,
    		'surplus_coin'=>$surplus_coin,
    		'type'=>$type,
    		'intro'=>$intro,
    		'cdate'=>$nowDate,
    	);
    	$res = $this->insertData($data);
    	return $res;
    }
    
}