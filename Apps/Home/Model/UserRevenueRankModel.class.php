<?php
namespace Home\Model;
use Think\Model;
class UserRevenueRankModel extends Model {
	protected $trueTableName = 'gl_user_revenue_rank';//要加上完整的表名
	
    public function getUserRevenueRankInfo($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param["order"])->find();
    	//echo M($this->trueTableName)->_sql();
    	return $res;
    }
    
    public function insertUserRevenueRank($data){
    	$res = M($this->trueTableName)->add($data);
    	//echo M($this->trueTableName)->_sql();
    	return $res;
    }
    
    public function updateUserRevenueRank($where, $data){
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
     * 收益排行变化
     * @param unknown $userId
     * @param unknown $coin
     * @return unknown
     */
    public function UserRevenueRankChange($userId, $coin){
    	$param = array(
    		'field' => 'id,user_id',
    		'where' => array(
    			'user_id' => $userId,
    		)
    	);
    	$userInfo = $this->getUserRevenueRankInfo($param);
    	$nowDate = date("Y-m-d H:i:s", time());
    	if($userInfo){
    		$where = array(
    			'user_id'=>$userId,
    		);
    		$data = array(
    			'week_revenue'=>array('exp', 'week_revenue+'.$coin),
    			'total_revenue'=>array('exp', 'total_revenue+'.$coin),
    			'udate'=>$nowDate,
    		);
    		$res = $this->updateUserRevenueRank($where, $data);
    	}else{
    		$data = array(
    			'user_id'=>$userId,
    			'last_week'=>0,
    			'week_revenue'=>$coin,
    			'total_revenue'=>$coin,
    			'udate'=>$nowDate,
    			'cdate'=>$nowDate,
    		);
    		$res = $this->insertUserRevenueRank($data);
    	}
    	return $res;
    }
}