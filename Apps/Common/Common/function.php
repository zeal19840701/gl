<?php
/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
	if(function_exists("mb_substr"))
		$slice = mb_substr($str, $start, $length, $charset);
		elseif(function_exists('iconv_substr')) {
			$slice = iconv_substr($str,$start,$length,$charset);
		}else{
			$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($re[$charset], $str, $match);
			$slice = join("",array_slice($match[0], $start, $length));
		}
		return $suffix ? $slice.'...' : $slice;
}

function getWebTag($tag_id,$url=false,$tag='div',$data=false){
	if($url !== false){
		$data = file_get_contents( $url );
	}
	$charset_pos = stripos($data,'charset');
	if($charset_pos) {
		if(stripos($data,'utf-8',$charset_pos)) {
			$data = iconv('utf-8','utf-8',$data);
		}else if(stripos($data,'gb2312',$charset_pos)) {
			$data = iconv('gb2312','utf-8',$data);
		}else if(stripos($data,'gbk',$charset_pos)) {
			$data = iconv('gbk','utf-8',$data);
		}
	}
	 
	preg_match_all('/<'.$tag.'/i',$data,$pre_matches,PREG_OFFSET_CAPTURE);    //获取所有div前缀
	preg_match_all('/<\/'.$tag.'/i',$data,$suf_matches,PREG_OFFSET_CAPTURE); //获取所有div后缀
	$hit = strpos($data,$tag_id);
	if($hit == -1) return false;    //未命中
	$divs = array();    //合并所有div
	foreach($pre_matches[0] as $index=>$pre_div){
		$divs[(int)$pre_div[1]] = 'p';
		$divs[(int)$suf_matches[0][$index][1]] = 's';
	}
	 
	//对div进行排序
	$sort = array_keys($divs);
	asort($sort);
	 
	$count = count($pre_matches[0]);
	foreach($pre_matches[0] as $index=>$pre_div){
		//<div $hit <div+1    时div被命中
		if(($pre_matches[0][$index][1] < $hit) && ($hit < $pre_matches[0][$index+1][1])){
			$deeper = 0;
			//弹出被命中div前的div
			while(array_shift($sort) != $pre_matches[0][$index][1] && ($count--)) continue;
			//对剩余div进行匹配，若下一个为前缀，则向下一层，$deeper加1，
			//否则后退一层，$deeper减1，$deeper为0则命中匹配，计算div长度
			foreach($sort as $key){
				if($divs[$key] == 'p') $deeper++;
				else if($deeper == 0) {
					$length = $key-$pre_matches[0][$index][1];
					break;
				}else {
					$deeper--;
				}
			}
			$hitDivString = substr($data,$pre_matches[0][$index][1],$length).'</'.$tag.'>';
			break;
		}
	}
	return $hitDivString;
}

function pr($data){
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

/**
 * 奖励配置
 * @param string $key
 */
function rewardConfig($key = null){
	$result = D('Config')->getConfig($key);
	return $result;	
}

/**
 * 获取系统配置
 * @param string $system
 * @param string $code
 */
function getSystemConfig($system='01', $code='01'){
	$result = D('SystemConfig')->getInfo($system, $code);
	return $result;
}

function request_curl($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//不输出内容
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$result =  curl_exec($ch);
	curl_close ($ch);
	return $result;
}

/**
 * 随机字符串
 * @param number $len
 * @param string $format
 */
function randString($len=6, $format='NUMBERCHAR'){
	$format = strtoupper($format);
	switch ($format){
		case 'ALL':
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
			break;
		case 'CHAR':
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			break;
		case 'NUMBER':
			$chars = '0123456789';
			break;
		case 'NUMBERCHAR':
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			break;
		default:
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
			break;
	}
	$retStr = '';
	$charsLen = strlen($chars) - 1 ;
	for($i=0;$i<$len;$i++){
		$retStr .= $chars{mt_rand(0, $charsLen)};
	}
	return $retStr;
}

/**
 * 得到秒数
 * @return number
 */
function getMicroSecondtime()
{
	list($usec, $sec) = explode(" ", microtime());
	$second = floor($usec * 10000);
	if($second<10){
		$second = $second * 1000;
	}else if($second<100){
		$second = $second * 100;
	}else if($second<1000){
		$second = $second * 10;
	}
	return $second;
}

function checkorderstatus($ordid){
	$Ord=M('Orderlist');
	$ordstatus=$Ord->where('ordid='.$ordid)->getField('ordstatus');
	if($ordstatus==1){
		return true;
	}else{
		return false;
	}
}

//处理订单函数
//更新订单状态，写入订单支付后返回的数据
function orderhandle($parameter){
	$ordid=$parameter['out_trade_no'];
	$data['payment_trade_no']      =$parameter['trade_no'];
	$data['payment_trade_status']  =$parameter['trade_status'];
	$data['payment_notify_id']     =$parameter['notify_id'];
	$data['payment_notify_time']   =$parameter['notify_time'];
	$data['payment_buyer_email']   =$parameter['buyer_email'];
	$data['ordstatus']             =1;
	$Ord=M('Orderlist');
	$Ord->where('ordid='.$ordid)->save($data);
}

/*-----------------------------------
 2013.8.13更正
 下面这个函数，其实不需要，大家可以把他删掉，
 具体看我下面的修正补充部分的说明
 ------------------------------------*/

//获取一个随机且唯一的订单号；
function getordcode(){
	$Ord=M('Orderlist');
	$numbers = range (10,99);
	shuffle ($numbers);
	$code=array_slice($numbers,0,4);
	$ordcode=$code[0].$code[1].$code[2].$code[3];
	$oldcode=$Ord->where("ordcode='".$ordcode."'")->getField('ordcode');
	if($oldcode){
		getordcode();
	}else{
		return $ordcode;
	}
}

/**
 * 判断是否为微信浏览器访问
 * @return boolean
 */
function is_wx_request(){
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	return (strpos($user_agent, 'MicroMessenger') === false)?false:true;
}

/**
 * 写日志
 * @param unknown $data
 * @param string $file
 */
function write_log($data, $file = 'logs_'){
		$year	= date("Y");
		$month	= date("m");
		$dir	= './Logs/' . $year . '/' . $month . '/';
		if(!is_dir($dir)) {
			mkdir($dir,0755,true);
		}
		$file = $dir . $file . date('Y-m-d').'.txt';
		@file_put_contents($file, '----------------' . date('H:i:s') . '--------------------'.PHP_EOL.var_export($data,true).PHP_EOL, FILE_APPEND);
}

function http_code($num){
    $http = array(
        100 => "HTTP/1.1 100 Continue",
        101 => "HTTP/1.1 101 Switching Protocols",
        200 => "HTTP/1.1 200 OK",
        201 => "HTTP/1.1 201 Created",
        202 => "HTTP/1.1 202 Accepted",
        203 => "HTTP/1.1 203 Non-Authoritative Information",
        204 => "HTTP/1.1 204 No Content",
        205 => "HTTP/1.1 205 Reset Content",
        206 => "HTTP/1.1 206 Partial Content",
        300 => "HTTP/1.1 300 Multiple Choices",
        301 => "HTTP/1.1 301 Moved Permanently",
        302 => "HTTP/1.1 302 Found",
        303 => "HTTP/1.1 303 See Other",
        304 => "HTTP/1.1 304 Not Modified",
        305 => "HTTP/1.1 305 Use Proxy",
        307 => "HTTP/1.1 307 Temporary Redirect",
        400 => "HTTP/1.1 400 Bad Request",
        401 => "HTTP/1.1 401 Unauthorized",
        402 => "HTTP/1.1 402 Payment Required",
        403 => "HTTP/1.1 403 Forbidden",
        404 => "HTTP/1.1 404 Not Found",
        405 => "HTTP/1.1 405 Method Not Allowed",
        406 => "HTTP/1.1 406 Not Acceptable",
        407 => "HTTP/1.1 407 Proxy Authentication Required",
        408 => "HTTP/1.1 408 Request Time-out",
        409 => "HTTP/1.1 409 Conflict",
        410 => "HTTP/1.1 410 Gone",
        411 => "HTTP/1.1 411 Length Required",
        412 => "HTTP/1.1 412 Precondition Failed",
        413 => "HTTP/1.1 413 Request Entity Too Large",
        414 => "HTTP/1.1 414 Request-URI Too Large",
        415 => "HTTP/1.1 415 Unsupported Media Type",
        416 => "HTTP/1.1 416 Requested range not satisfiable",
        417 => "HTTP/1.1 417 Expectation Failed",
        500 => "HTTP/1.1 500 Internal Server Error",
        501 => "HTTP/1.1 501 Not Implemented",
        502 => "HTTP/1.1 502 Bad Gateway",
        503 => "HTTP/1.1 503 Service Unavailable",
        504 => "HTTP/1.1 504 Gateway Time-out",
    );
    header($http[$num]);
}

/*
 加解密函数库
 controllers/encryptor.php
 */
if (!function_exists('_passport_encrypt')){
	function _passport_encrypt($peName, $text, $date=null) {
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$key = _getBaseKey($date).$peName;

		$crypttext = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, '0429'.$text, MCRYPT_MODE_ECB, $iv));

		$crypttext = str_replace('/', '_', $crypttext);
		$crypttext = str_replace('+', '-', $crypttext);
		return $crypttext;
	}

	function _passport_decrypt($peName, $crypttext, $date=null) {
		$crypttext = str_replace('_', '/', ($crypttext));
		$crypttext = str_replace('-', '+', ($crypttext));
		try{
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			$key = _getBaseKey($date).$peName;
			$txt = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($crypttext), MCRYPT_MODE_ECB, $iv);

			if (!empty($txt)&&substr($txt, 0, 4)=='0429'){
				return trim(substr($txt, 4, strlen($txt)-4));
			}else{
				return '';
			}
		}catch(Exception $e){
			return '';
		}
	}

	function _getBaseKey($date=null){
		return 'SaasApp1203';
	}
}