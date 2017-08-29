<?php
namespace Home\Model;
use Think\Model;
class NewsCategoryModel extends Model {
	protected $trueTableName = 'gl_news_category';//要加上完整的表名
	
    public function getList($param){
    	$result = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param['order'])->select();
    	return $result;
    }
}