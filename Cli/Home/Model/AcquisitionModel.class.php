<?php
namespace Home\Model;
use Think\Model;
class AcquisitionModel extends Model{
	protected $trueTableName = 'data_acquisition';//要加上完整的表名
	protected $connection = 'DB_DATA_GRAB';
	
	/**
	 * 获取采集信息
	 */
	public function getInfo($param){
		$res = M($this->trueTableName, '', $this->connection)->field($param['field'])->where($param['where'])->find();
		return $res;
	}
	
	/**
	 * 获取多条采集信息
	 */
	public function getList($param){
		$res = M($this->trueTableName, '', $this->connection)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
		return $res;
	}
	
	/**
	 * 获取多条采集信息
	 */
	public function getCount($where){
		$res = M($this->trueTableName, '', $this->connection)->where($where)->count();
		return $res;
	}
	
	public function getQuery($sql){
		$res = M($this->trueTableName, '', $this->connection)->query($sql);
		return $res;
	}
	
	public function updateAcquisition($where, $data){
		$res = M($this->trueTableName, '', $this->connection)->where($where)->save($data);
		return $res;
	}
}