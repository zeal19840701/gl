<?php
namespace Admin\Model;
use Think\Model;
class UserModel extends Model{
	protected $trueTableName = 'gl_user';//要加上完整的表名
	protected $connection = 'DB_GOLD_LOCK';
	
	/**
	 * 获取采集信息
	 */
	public function getInfo($param){
		$res = M($this->trueTableName, '', $this->connection)->field($param['field'])->where($param['where'])->find();
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $res;
	}
	
	/**
	 * 获取多条采集信息
	 */
	public function getList($param){
		$res = M($this->trueTableName, '', $this->connection)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $res;
	}
	
	/**
	 * 查询记录数量
	 */
	public function getCount($where){
		$res = M($this->trueTableName, '', $this->connection)->where($where)->count();
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $res;
	}
	
	/**
	 * 查询sql
	 * @param unknown $sql
	 */
	public function getQuery($sql){
		$res = M($this->trueTableName, '', $this->connection)->query($sql);
		return $res;
	}
	
	/**
	 * 插入数据
	 */
	public function insertData($param){
		$res = M($this->trueTableName, '', $this->connection)->add($param);
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $res;
	}
	
	public function updateData($where, $param){
		$res = M($this->trueTableName, '', $this->connection)->where($where)->save($param);
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $res;
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
		$userInfo = $this->getInfo($param);//查询用户记录
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
			$result = $this->updateData($where, $data);
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
		$userInfo = $this->getInfo($param);//查询用户记录
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
			$result = $this->updateData($where, $data);
		}else{
			$result = false;
		}
		return $result;
	}
}