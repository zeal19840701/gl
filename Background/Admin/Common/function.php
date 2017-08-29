<?php 
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