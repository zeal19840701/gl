<?php
namespace Home\Model;
use Think\Model;
class SingleModel extends Model {
	protected $trueTableName = 'gl_single';//要加上完整的表名
	
	public function getInfo($param){
		$result = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
		//echo M($this->trueTableName)->_sql();
		return $result;
	}
}