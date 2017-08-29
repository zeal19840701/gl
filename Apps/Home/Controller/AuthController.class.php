<?php
namespace Home\Controller;
use Think\Controller;
class AuthController extends CommonController {
	protected $userid = ''; // 用户名
	protected $token = ''; // token串
	
	public function __construct() {
		$this->userid = addslashes(I ( 'userid' )); // 用户名
		$this->token = addslashes(I ( 'token' )); // token
		
		if (! $this->userid) {
			$this->_logs('获取userid:' . $this->userid. ' ' . '返回结果:1021  未传入userid');
			$this->_logs('请求地址：' . $_GET["_URL_"] . ' ' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			$this->returnApiMsg ( '1021', '未传入用户userid' ); // 未传入userid
		}
		if (! $this->token) {
			$this->_logs('获取token:' . $this->token. ' ' . '返回结果:1022   未传入token');
			$this->_logs('请求地址：' . $_GET["_URL_"] . ' ' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			$this->returnApiMsg ( '1022', '未传入token'); // 未传入token
		}
		$userData = $this->adm ( $this->userid ); // 获取用户数据
		if (empty ( $userData )) {
			$this->_logs('获取用户数据失败:' . $userData. ' ' . '返回结果:1023 您还未登录');
			$this->_logs('请求地址：' . $_GET["_URL_"] . ' ' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			$this->returnApiMsg ( '1023', '您还未登录' ); // 还未登录
		}
		$flag = $this->_check_token ( $this->userid, $this->token );
		if (! $flag) {
			$this->_logs('获取token:' . $flag . ' ' . '返回结果:1024 token验证失败');
			$this->_logs('请求地址：' . $_GET["_URL_"] . ' ' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			$this->returnApiMsg ( '1024', 'token验证失败' ); // token验证失败
		}
		parent::__construct ();
		
	}
}