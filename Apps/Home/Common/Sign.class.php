<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Home\Common;

class Sign{ 
    
	/**
	 * 获取签名
	 * @param array $param 签名数组
	 * @param string $code 安全校验码
	 * @param string $sign_type 签名类型
	 */
	public static function getSign($param, $code, $sign_type="MD5"){
		$param = self::paramFilter($param);//去除数组中的空值和签名参数
		$param = self::paramSort($param);//按键名升序排列数组
		$paramStr = self::paramLinksString($param);//拼接成字符串
		$paramStr = $paramStr . $code;//把拼接后的字符串再与安全校验码直接连接起来
		return self::createSign($paramStr, $sign_type);//创建签名字符串
	}
	
	/**
	 * 校验签名
	 * @param unknown $sign 接收到的签名
	 * @param unknown $param 签名数组
	 * @param unknown $code 安全校验码
	 * @param string $sign_type 签名类型
	 * @return boolean true 正确 ,false 失败
	 */
	public static function checkSign($sign, $param, $code, $sign_type="MD5"){
		return $sign == self::getSign($param, $code,$sign_type);
	}
	
	/**
	 * 去除数组中空值和签名参数
	 * @param array $param
	 * @return array $param_filter 过滤后生成新数组
	 */
	public static function paramFilter($param){
		$param_filter = array();
		if(!empty($param)){
			foreach($param as $k=>$v){
				if($k == 'sign' || $k == 'sign_type' || !strlen($v)){
					continue;
				}else if($k == '_s_'){
					continue;
				}
				$param_filter[$k] = $v;
			}
		}
		return $param_filter;
	}
	
	/**
	 * 按键名升序排序数组
	 * @param array $param
	 * @return array $param
	 */
	public static function paramSort($param){
		if(!empty($param)){
			ksort($param);
			reset($param);
		}
		return $param;
	}
	
	/**
	 * 拼接所有参数元素
	 * @param array $param
	 * @return string str
	 */
	public static function paramLinksString($param){
		$str = '';
		if(!empty($param)){
			foreach($param as $key=>$val){
				$str .= $key . $val;
			}
		}
		return $str;
	}
	
	/**
	 * 创建签名字符串
	 * @param string $param 需要加密的字符串
	 * @param string $type 签名类型 默认值：MD5
	 * @return string 加密签名串
	 */
	private static function createSign($param, $type='MD5'){
		$type = strtolower($type);
		if($type == 'md5'){
			return md5($param);
		}
	}
}
