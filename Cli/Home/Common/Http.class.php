<?php
namespace Home\Common;

class Http{
	public static function http_post_data($url, $data_string){
		if(!extension_loaded('curl')){
			throw new Exception('curl error');
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json; charset=utf-8',
				'Content-Length: ' . strlen($data_string),
				'Referer: http://sso.tyun.71360.com/',)  //$_SERVER['HTTP_HOST']    http://bbb.mytcloud.com/
				);
		$return_content = curl_exec($ch);
		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		return array($return_code, $return_content);
	}
}
