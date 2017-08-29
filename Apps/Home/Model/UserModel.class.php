<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model {
	protected $trueTableName = 'gl_user';//要加上完整的表名
	
    public function getUserInfo($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
    	//echo M($this->trueTableName)->_sql();
    	return $res;
    }
    
    public function insertUser($data){
    	$res = M($this->trueTableName)->add($data);
    	return $res;
    }
    
    public function updateUser($where, $data){
    	$res = M($this->trueTableName)->where($where)->save($data);
    	//echo M($this->trueTableName)->_sql();
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
    
    /**
     * 增加金币
     * @param string $userId 用户id
     * @param int $coin 要增加的金币
     * @param string $type 类型(1:收入,2:支出,3:充值,4:提现)
     * @param string $intro 说明
     */
    public function increaseCoin($userId, $coin, $type, $intro){
    	$where = array(
    		'id' => $userId,
    		'status' => 0,
    	);
    	$param = array(
    		'field' => 'id,mobile,coin',
    		'where' => $where,
    	);
    	$userInfo = $this->getUserInfo($param);//查询用户记录
    	$nowTime = date("Y-m-d H:i:s", time());
    	if($userInfo){
    		D("UserConsume")->UserConsume($userId, $coin, $userInfo['coin'], $type, $intro);//记录收支信息
    		$data = array(
    			'total_coin' => array('exp', 'total_coin+'.$coin),//总金币
    			'coin' => array('exp', 'coin+'.$coin),//剩余余币
    			'udate'=>$nowTime,
    		);
    		if(('收入' == $type) || (1 == $type)){//只有收入才算收益
    			$data['today_coin'] = array('exp', 'today_coin+'.$coin);//今日收益金币
    			D("UserRevenueRank")->UserRevenueRankChange($userId, $coin);//收益排行表，只记录收入
    		}
    		//修改用户金币
    		$result = $this->updateUser($where, $data);
    	}else{
    		$result = false;
    	}
    	return $result;
    }
    
    
    /**
     * 减少积分
     * @param string $userId 用户id
     * @param int $coin 要增加的金币
     * @param string $type 类型(1:收入,2:支出,3:充值,4:提现)
     * @param string $intro 说明
     */
    public function decreaseCoin($userId, $coin, $type, $intro){
    	$where = array(
    		'id' => $userId,
    		'status' => 0,
    	);
    	$param = array(
    		'field' => 'id,mobile,coin',
    		'where' => $where,
    	);
    	$userInfo = $this->getUserInfo($param);//查询用户记录
    	$nowTime = date("Y-m-d H:i:s", time());
    	if($userInfo){
    		D("UserConsume")->UserConsume($userId, $coin, $userInfo['coin'], $type, $intro);//记录收支信息
    		$data = array(
    			'total_coin' => array('exp', 'total_coin-'.$coin),//总金币
    			'coin' => array('exp', 'coin-'.$coin),//剩余余币
    			'udate'=>$nowTime,
    		);
    		if(('提现' == $type) || (4 == $type)){//只有收入才算收益
    			$data['use_coin'] = array('exp', 'use_coin+'.$coin);//今日收益金币
    		}
    		//修改用户金币
    		$result = $this->updateUser($where, $data);
    	}else{
    		$result = false;
    	}
    	return $result;
    }
}