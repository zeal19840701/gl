<?php
namespace Admin\Controller;
use Think\Controller;

class BaseController extends Controller {
    public function _initialize(){
        $sid = session('adminId');
        //判断用户是否登陆
        if(!isset($sid ) ) {
            redirect(U('Login/index'));
        }

    }
    
    /**
     * 写日志，用于测试,可以开启关闭
     * @param data mixed
     */
    protected function _logs($data, $file = 'logs_'){
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