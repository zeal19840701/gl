<?php
namespace Admin\Model;
use Think\Model;
class UserConsumeModel extends Model{
	protected $trueTableName = 'gl_user_consume';//要加上完整的表名
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
	 * 记录收支
	 * @param string $userId
	 * @param int $coin
	 * @param int $surplus_coin
	 * @param string $type
	 * @param string $intro
	 * @return int $res
	 */
	public function UserConsume($userId, $coin, $surplus_coin=0, $type='收入', $intro='')
    {
        $nowDate = date("Y-m-d H:i:s", time());
        if(empty($surplus_coin)){
        	$userInfo = D('User')->getInfo(array('field'=>'id,coin', 'where'=>array('id'=>$userId)));
        	if(!empty($userInfo)){
        		$surplus_coin = $userInfo['coin'];
        	}
        }
        $data = array(
            'user_id' => $userId,
            'coin' => $coin,
            'surplus_coin' => $surplus_coin,
            'type' => $type,
            'intro' => $intro,
            'cdate' => $nowDate,
        );
        $res = $this->insertData($data);
        return $res;
    }

    /**
     * 用户收益统计报表
     */
	public function userEarningsReport($where='', $order='a.`cdate` DESC', $limit){
	    $sql = "SELECT LEFT(a.`cdate`, 10) AS `cdate`, a.user_id, COUNT(a.coin) AS coin, SUM(a.coin) AS `sum`, a.`type`,b.`mobile`,b.`nickname` FROM `gl_user_consume` AS a LEFT JOIN `gl_user` AS b ON (a.`user_id`=b.`id`) WHERE b.`flag`=0 " . $where . " GROUP BY a.user_id,a.`type`,LEFT(a.`cdate`, 10) ORDER BY " . $order . " LIMIT " . $limit;//不含有虚拟账户
	    $res = $this->getQuery($sql);
	    return $res;
    }
    
    /**
     * 用户收益统计报表
     */
    public function userEarningsReportCount($where='', $order='a.`cdate` DESC', $limit){
    	$sql = "SELECT COUNT(t.user_id) as count FROM (SELECT a.user_id, COUNT(a.coin) AS coin FROM `gl_user_consume` AS a LEFT JOIN `gl_user` AS b ON (a.`user_id`=b.`id`) WHERE b.`flag`=0 " . $where . " GROUP BY a.user_id,a.`type`,LEFT(a.`cdate`, 10)) t";//不含有虚拟账户
    	$res = $this->getQuery($sql);
    	return $res[0]['count'];
    }
    
    /**
     * 用户收益统计明细
     */
    public function userEarningsItem($where='', $order='a.`cdate` DESC', $limit){
    	$sql = "SELECT a.user_id, a.coin, a.`type`,a.`intro`, b.`mobile`,b.`nickname` FROM `gl_user_consume` AS a LEFT JOIN `gl_user` AS b ON (a.`user_id`=b.`id`) WHERE b.`flag`=0 " . $where . " ORDER BY " . $order . " LIMIT " . $limit;//不含有虚拟账户
    	$res = $this->getQuery($sql);
    	return $res;
    }
    
    /**
     * 用户收益统计明细
     */
    public function userEarningsItemCount($where='', $order='a.`cdate` DESC', $limit){
    	$sql = "SELECT COUNT(a.id) AS `count` FROM `gl_user_consume` AS a LEFT JOIN `gl_user` AS b ON (a.`user_id`=b.`id`) WHERE b.`flag`=0 " . $where;//不含有虚拟账户
    	$res = $this->getQuery($sql);
    	return $res[0]['count'];
    }
}