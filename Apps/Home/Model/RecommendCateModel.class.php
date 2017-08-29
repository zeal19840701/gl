<?php
namespace Home\Model;
use Think\Model;
class RecommendCateModel extends Model {
	protected $trueTableName = 'gl_recommend_cate';//要加上完整的表名
	
    public function getCateList($key = null){
    	$result = S('RecommendCate');
    	if(!$result){
    		$resList = M($this->trueTableName)->field('id, cate_initial, cate_name')->where(array('is_show'=>0))->order('sort DESC')->select();
    		$result = array();
    		if($resList){
    			foreach($resList as $k=>$v){
    				$result[$v['cate_initial']] = $v;
	    		}
	    		S('RecommendCate', $result, 86400);
    		}
    	}
    	if(empty($key)){
    		return $result;
    	}else{
    		return $result[$key];
    	}
    }
    
    public function getList($key = null){
    	$result = S('RecommendAllCate');
    	if(!$result){
    		$resList = M($this->trueTableName)->field('id, cate_initial, cate_name')->order('sort DESC')->select();
    		$result = array();
    		if($resList){
    			foreach($resList as $k=>$v){
    				$result[$v['cate_initial']] = $v;
    			}
    			S('RecommendAllCate', $result, 86400);
    		}
    	}
    	if(empty($key)){
    		return $result;
    	}else{
    		return $result[$key];
    	}
    }
}