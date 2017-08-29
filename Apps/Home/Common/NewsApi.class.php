<?php
namespace Home\Common;
/**
 * 新闻API
 * Internet Short Message Gateway
 * @package helper
 */
class NewsApi{
	
	/**
	 * 获取新闻列表
	 * @param string $type
	 * @param integer $page
	 */
	public function getList($type='RM', $page=1){
		$url = C('NEWS_URL') . 'queryListByColumnTypeInterface' . "?columnType=".$type.'&cpage='.$page;//获取url
		$data = json_decode($this->httpGetRequest($url . $param), true);
		if(!empty($data)){
			return $data;
		}else{
			return array();
		}
	}
	
	/**
	 * 根据id获取新闻详情
	 * @param string $id
	 */
	public function getDetail($id){
		$url = C('NEWS_URL') . 'getNewsDetailInterface' . "?id=".$id;//获取url
		$data = json_decode($this->httpGetRequest($url . $param), true);
		if(!empty($data)){
			return $data;
		}else{
			return array();
		}
	}
	
	/**
	 * 获取微信信息
	 * @param string $url
	 */
	public function getWechatInfo($url){
		$url = C('NEWS_URL') . 'getWechatDetailInterface' . "?url=".$url;//获取url
		$data = json_decode($this->httpGetRequest($url), true);
		//$this->_logs($data);
		if(!empty($data)){
			if($data['code'] == 'ok'){
				return $data['mapItem'];
			}else{
				return array();
			}
		}else{
			return array();
		}
	}
	
	
	
	/**
	 * curl	提交形式
	 * @param 提交地址 $url
	 * @return 结果
	 * @author Jeff
	 */
	private function httpGetRequest($url){
	    $curl = curl_init();
	    curl_setopt_array($curl, array(
	    		CURLOPT_URL => $url,
	    		CURLOPT_RETURNTRANSFER => 1,//返回值
	    		CURLOPT_HEADER => 0,
	    ));
	    $response = curl_exec($curl);
	    //$this->_logs($response);
	    curl_close($curl);
	    return $response;
	}
	
	/**
	 * 写日志，用于测试,可以开启关闭
	 * @param data mixed
	 */
	private function _logs($data, $file = 'logs_'){
		$year	= date("Y");
		$month	= date("m");
		$dir	= './Logs/' . $year . '/' . $month . '/';
		if(!is_dir($dir)) {
			mkdir($dir,0755,true);
		}
		$file = $dir . $file . date('Y-m-d').'.txt';
		@file_put_contents($file, '----------------' . date('H:i:s') . '--------------------'.PHP_EOL.var_export($data,true).PHP_EOL, FILE_APPEND);
	}
}