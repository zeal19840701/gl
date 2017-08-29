<?php
namespace Admin\Model;
use Think\Model;
class WebsiteRecommendModel extends Model{
	protected $trueTableName = 'gl_website_recommend';//要加上完整的表名
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
	 * 获取多条采集信息
	 */
	public function getCount($where){
		$res = M($this->trueTableName, '', $this->connection)->where($where)->count();
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $res;
	}
	
	public function getQuery($sql){
		$res = M($this->trueTableName, '', $this->connection)->query($sql);
		return $res;
	}
	
	/**
	 * 插入新闻
	 */
	public function insertData($data){
		$res = M($this->trueTableName, '', $this->connection)->add($data);
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $res;
	}
	
	/**
	 * 修改新闻
	 */
	public function updateData($where, $data){
		$res = M($this->trueTableName, '', $this->connection)->where($where)->save($data);
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $res;
	}
	
	/**
	 * 删除新闻
	 */
	public function deleteData($where){
		$res = M($this->trueTableName, '', $this->connection)->where($where)->delete();
		//echo M($this->trueTableName, '', $this->connection)->_sql();exit;
		return $res;
	}
}