<?php
namespace Home\Controller;
use Think\Controller;
class LogoutController extends CommonController {
	
	/**
	 * 退出登录
	 */
    public function index(){
    	$userid = addslashes(I('userid'));
    	$token = addslashes(I('token'));
    	if(!$userid){
    		$this->returnApiMsg ( '1021', '未传入用户名' );
    	}
    	if(!$token){
    		$this->returnApiMsg ( '1022', '未传入token' );
    	}
    	$param = array(
    		'type' => 'remove',
    		'userid' => $userid,
    		'token' => $token,
    	);
    	$res = $this->_create_login_flag($param);
    	if($res){
    		$this->_logs($userid . '退出登录！' . ' ' . '返回结果:0退出成功');
    		$this->returnApiMsg ( '0', '退出成功' ); // 退出成功
    	}else{
    		$this->_logs($userid . '退出失败！' . ' ' . '返回结果:1048 退出失败');
    		$this->returnApiMsg ( '1048', '退出失败' ); // 退出失败
    	}
    }
}