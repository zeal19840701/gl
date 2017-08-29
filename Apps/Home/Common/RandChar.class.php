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

class RandChar{ 
    
	/**
	 * 获取任意长度的随机的字符串
	 * @param number $length
	 */
    public static function getRandChar($length=8) {
        $str = '';
        $strPol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $strMaxLength = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
        	$getPostion = mt_rand(0, $strMaxLength);
        	$str .= $strPol[$getPostion];
        }
        return $str;
    }
}
