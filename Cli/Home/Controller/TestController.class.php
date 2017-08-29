<?php
namespace Home\Controller;
use Think\Controller;
class TestController extends Controller {
	/**
	 * 测试发送短信
	 * 
	 * 测试用:php D:\WWW\gold_lock\cli.php home/test/index 13641833211
	 */
    public function index(){
    	$mobile = $_SERVER['argv'][2];
    	if(!$mobile){
    		echo 'mobile not found!';
    		exit();
    	}
    	$result = \Home\Common\Sms::getmverif($mobile, '金锁app客户，我们app全新改版了快来体验吧，验证码{0}');
    	if($result[0] == 200){
    		$rt = json_decode($result[1], true);
    		if($rt['success']){
    			$this->_logs($mobile.'手机号发送成功');
    			echo 'send success';
    		}else{
    			$this->_logs($mobile.'手机号发送失败');
    			echo 'send failure';
    		}
    	}else{
    		$this->_logs($mobile.'手机号发送失败');
    		echo 'send failure';
    	}
    }
    
    public function ss(){
    	$userModel = M('gl_user', '', 'DB_GOLD_LOCK_NEW');
    	$userConsumeModel = M('gl_user_consume', '', 'DB_GOLD_LOCK_NEW');
    	while(true){
    		$sql = "SELECT * FROM `gl_user` WHERE `flag` = 0 limit 0, 100";
    		$userList = $userModel->query($sql);
    		if(empty($userList)){
    			break;
    		}
    		foreach($userList as $k=>$v){
    			if($v['coin']>0){
    				$param = array(
    						'user_id' => $v['id'],
    						'coin' => $v['coin'],
    						'surplus_coin' => $v['coin'],
    						'type' => '收入',
    						'intro' => '老金锁用户结余金币',
    						'cdate' => date("Y-m-d H:i:s", time()),
    				);
    				$r = $userConsumeModel->data($param)->add();
    				if($r){
    					echo $v['id'] . " execute success\n";
    				}
    			}
    			$userModel->where(array('id'=>$v['id']))->save(array('flag'=>1));
    		}
    	}
    	echo "execute finish\n";
    	
    }
    
    public function tt(){
    	$res = D('User')->getQuery("select * from gl_user limit 10");
    	print_r($res);
    }
    
    /**
     * 写日志，用于测试,可以开启关闭
     * @param data mixed
     */
    protected function _logs($data, $file = 'logs_'){
    	$year	= date("Y");
    	$month	= date("m");
    	
    	$dir	= APP_PATH . 'Logs/' . $year . '/' . $month . '/';
    	if(!is_dir($dir)) {
    		mkdir($dir,0755,true);
    	}
    	$file = $dir . $file . date('Y-m-d').'.txt';
    	@file_put_contents($file, '----------------' . date('H:i:s') . '--------------------'.PHP_EOL.var_export($data,true).PHP_EOL, FILE_APPEND);
    }
}