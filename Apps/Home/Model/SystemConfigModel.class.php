<?php
namespace Home\Model;
use Think\Model;
class SystemConfigModel extends Model {
	protected $trueTableName = 'gl_system_config';//要加上完整的表名
	
	public function getInfo($system='01', $code='01'){
		$result = M($this->trueTableName)->field('`value`')->where(array('system'=>$system, 'code'=>$code))->find();
		return $result['value']?$result['value']:'';
	}
	
	public function getList($where = array()){
		$result = M($this->trueTableName)->field('`value`')->where($where)->select();
		return $result;
	}
	
}