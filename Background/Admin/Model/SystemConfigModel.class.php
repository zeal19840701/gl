<?php
namespace Admin\Model;
use Think\Model;
class SystemConfigModel extends Model {
	protected $trueTableName = 'gl_system_config';//要加上完整的表名
	protected $connection = 'DB_GOLD_LOCK';
	
	public function getInfo($system='01', $code='01'){
		$result = M($this->trueTableName, '', $this->connection)->field('`value`')->where(array('system'=>$system, 'code'=>$code))->find();
		//echo M($this->trueTableName, '', $this->connection)->_sql();
		return $result['value']?$result['value']:'';
	}
	
	public function getList($where = array()){
		$result = M($this->trueTableName, '', $this->connection)->field('`value`')->where($where)->select();
		return $result;
	}
	
}