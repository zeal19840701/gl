<?php
namespace Home\Model;
use Think\Model;
class ConfigModel extends Model {
	protected $trueTableName = 'gl_config';//要加上完整的表名
	
	public function getList(){
		$result = M($this->trueTableName)->field('key_name, key_value')->select();
		return $result;
	}
	
	public function getConfig($key = null){
		$result = S('reward_config_all');
		if(empty($result)){
			$configList = $this->getList();
			$result = array();
			foreach($configList as $val){
				$result[$val['key_name']] = $val['key_value'];
			}
			S('reward_config_all', $result);
		}
		if(!empty($key)){
			return $result[$key];
		}else{
			return $result;
		}
		
	}
}