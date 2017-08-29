<?php
namespace Home\Model;
use Think\Model;
class NewsModel extends Model{
	protected $trueTableName = 'gl_news';//要加上完整的表名
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
	public function insertInfo($sql){
		$res = M($this->trueTableName, '', $this->connection)->query($sql);
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $res;
	}
	
	/**
	 * 插入新闻
	 */
	public function insertNews($param){
		$res = M($this->trueTableName, '', $this->connection)->add($param);
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $res;
	}
}