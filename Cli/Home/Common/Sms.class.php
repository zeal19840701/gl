<?php
namespace Home\Common;

class Sms{
	const URL = 'http://tyunsso.71360.com/';	//宝发网地址
	private static $format = 'json';
	/**
	 * 获取手机号码
	 * @param moblie string 手机号
	 * @param msg string 验证提示信息 代表验证码{0}
	 * @return array
	 */
	public static function getmverif($moblie,$msg='验证码{0}'){
		if(empty($moblie)){
			return self::resposed('',false,1001,'手机号码错误');
		}
		$url = self::URL.'api/SMS/GetMobileCheckCode';
		$data = array('Mobile'=>$moblie,'SMSContentFormat'=>$msg);
		$result = \Home\Common\Http::http_post_data($url,json_encode($data));
		return $result;
	}
	/**
	 * 获取用户数据,需要带有用户的cookie或者相关的验证
	 */
	public static function getuserinfo($userinfo){
		$url = self::URL.'api/User/LoginByTicket';
		$data = array('LoginName'=>$userinfo['LoginName'],'Ticket'=>$userinfo['Ticket']);
		return '';
	}
	
	/**
	 * 输出 处理 ,返回参数 
	 * @param unknown_type $data
	 * @param unknown_type $ack
	 * @param unknown_type $error
	 */
	private function resposed($data,$ack='true',$code,$error=null){
		if($ack!='true') $ack='false';
		$r=array('ack'=>$ack,'time'=>time());
		if(!empty($data)){
			$r['data']=$data;
		}
		if($error){
			$r['error']=$error;
		}
		$r['code'] = $code;
		if(self::$format=='json'){
			echo json_encode($r);
		}
		return ;
	}
	/**
	 * 写日志，用于测试,可以开启关闭
	 * @param data mixed
	 */
	private function logs($data){
		return true;
		$file = 'api_'.date('Y-m-d').'.txt';
		@file_put_contents($file,'---------------------------------'.PHP_EOL.var_export($data,true).PHP_EOL,FILE_APPEND);
	}
}
