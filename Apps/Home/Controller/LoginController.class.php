<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends CommonController {
	
	/**
	 * 用户名登录
	 */
    public function index(){
    	$username = addslashes(I('username'));
    	$password = addslashes(I('password'));
    	$jgId = addslashes(I('regid', ''));//极光推送id
    	$versionCode = $_SERVER['HTTP_VERSIONCODE'];
    	if(empty($versionCode)){
    		$versionCode = 2.3;
    	}
    	write_log(array('versionCode:', $versionCode, $_SERVER));
    	$param = array(
    		'where' => array(
	    		'mobile' => $username,
	    		'password' => md5($password),
    			'status' => 0,//状态是未冻结
    		)
    	);
    	$userInfo = D('User')->getUserInfo($param);
    	
    	if(!empty($userInfo)){
    		/* $param = array(
    			'userid' => $userInfo['id'], //获取用户ID标识
    			'username' => $userInfo['mobile'], //获取用户名
    			'nickname' => $userInfo['nickname'], //获取昵称
    			'user_mac' => $userInfo['user_mac'], //用户mac地址
    			'gender' => $userInfo['gender'], //性别
    			'head_pic' => $userInfo['head_pic'], //头像
    			'total_coin' => $userInfo['total_coin'], //总金币
    			'coin' => $userInfo['coin'], //可用金币
    			'today_coin' => $userInfo['today_coin'],
    		); */
    		//$userInfo['userid'] = $userInfo['id'];
    		/* if($jgId){
    			$client = new \Home\Common\JpushApi();
    			$messageData = $client->push_message("没错, 我是接口推送123456", $jgId);
    		} */
    		if($jgId){
    			//if(empty($userInfo['jg_id'])){//数据库没有极光id数据，则写入数据库中
    				D('User')->updateUser(array('id'=>$userInfo['id']), array('jg_id'=>$jgId));
    			//}
    		}else{
    			$jgId = $userInfo['jg_id'];
    		}
    		$result = $this->_create_login_flag ( $userInfo, $jgId, $versionCode );
    		$this->_logs('登录成功:' . json_encode($result));
    		$this->returnApiData ( $result); // 返回登录成功
    	}else{
    		$this->_logs('登录失败！' . ' ' . '返回结果:1028 登录失败');
    		//$this->returnApiMsg ( '1028', '登录失败' ); // 返回登录失败
    		$this->returnApiMsg ( '1028', '用户名或者密码错误' ); // 返回登录失败
    	}
    }
    
    /**
     * 发送短信验证码
     */
    public function sendMobile(){
    	$mobile = addslashes(I('mobile'));//手机号
    	$is_check = I("is_check", 0);//默认为0
    	if(!trim($mobile)){
    		$this->returnApiMsg ( '1036', '请填写手机号' );
    	}
    	//匹配手机号的正则表达式
    	/* if(!preg_match("/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])([0-9]{8})$/", $mobile)){
    		$this->returnApiMsg ( '1037', '手机号填写错误' );
    	} */
    	//正则匹配手机号
    	if(!$this->_check_mobile($mobile)){
    		$this->returnApiMsg ( '1037', '手机号填写错误' );
    	}
    	//是否开启手机号验证
    	if($is_check){
    		//检查手机号是否存在
    		$userInfo = D('User')->getUserInfo(array('where'=>array('mobile'=>$mobile)));
    		if(!empty($userInfo)){
    			$this->returnApiMsg ( '1083', '该手机号码已是注册用户' );
    		}
    	}
    	//给手机发送短信
    	$result = \Home\Common\Sms::getmverif($mobile, '');
    	if($result[0] == 200){//请求成功返回200
    		$rt = json_decode($result[1], true);
    		if($rt['success']){
    			//session('mobileVerify', md5($rt['mobilecheckcode']));//
    			S('mobileVerify_' . $mobile, $rt['mobilecheckcode'], 1800);
    			//echo $rt['mobilecheckcode'];
    			$this->returnApiMsg ( '0', '发送成功' );
    		}else{
    			$this->returnApiMsg ( '1040', '网络错误，请稍等再试' );
    		}
    	}else{
    		$this->returnApiMsg ( '1082', '发送过于频繁，请稍后再发送' );
    	}
    }
    
    /**
     * 注册
     */
    public function reg(){
    	//echo $_SESSION['mobileVerify'];exit;
    	$mobile = addslashes(I('mobile'));//手机号
    	$verify = addslashes(I('verify'));//手机号验证码
    	$password = addslashes(I('password'));//密码
    	$user_mac = addslashes(I('mac'));//设备号
    	
    	if(!trim($mobile)){
    		$this->returnApiMsg ( '1036', '请填写手机号' );
    	}
    	if(!trim($verify)){
    		$this->returnApiMsg ( '1038', '请填写验证码' );
    	}
    	if(!trim($password)){
    		$this->returnApiMsg ( '1039', '请填写密码' );
    	}
    	//匹配手机号的正则表达式
    	/* if(!preg_match("/^(13[0-9]|14[47]|15[0-35-9]|17[6-8]|18[0-9])([0-9]{8})$/", $mobile)){
    		$this->returnApiMsg ( '1037', '手机号填写错误' );
    	} */
    	if(!$this->_check_mobile($mobile)){
    		$this->returnApiMsg ( '1037', '手机号填写错误' );
    	}
    	$mobileVerify = S('mobileVerify_' . $mobile);
    	if(empty($mobileVerify)){
    		$this->returnApiMsg ( '1122', '验证码已过期，重新发送' );
    	}
    	if($mobileVerify != $verify){
    		//S('mobileVerify_' . $mobile, null);//删除缓存
    		$this->returnApiMsg ( '1041', '验证码错误' );
    	}
    	/* if($_SESSION['mobileVerify'] != md5($verify)) {
    		unset($_SESSION['mobileVerify']);
    		$this->returnApiMsg ( '1041', '验证码错误' );
    	} */
    	if(!$user_mac){
    		$this->returnApiMsg ( '1029', '未传入设备号'); // 未传入设备号
    	}
    	$nowTime = date("Y-m-d H:i:s", time());
    	$userInfo = D('User')->getUserInfo(array('where'=>array('mobile'=>$mobile)));
    	if(!empty($userInfo)){
    		$this->returnApiMsg ( '1044', '注册手机号已存在'); // 手机号存在
    	}
    	//$firstUse = rewardConfig('first_use');//初次使用获得金币
    	$firstUse = getSystemConfig('01', '01');//初次使用获得金币
    	$userInfo = D('User')->getUserInfo(array('where'=>array('user_mac'=>$user_mac)));
    	//判断记录存在，那么会有两个情况。一是用游客登录过，二是注册登录过
    	if(!empty($userInfo)){
    		//首先先判断用户是否用手机号注册过
    		//$userInfo1 = D('User')->getUserInfo(array('where'=>array('mobile'=>$mobile)));
    		if(empty($userInfo['mobile'])){
    			//游客登录过
    			//去绑定手机号和密码
    			$isUserSave = D('User')->updateUser(array('user_mac'=>$user_mac), array('mobile'=>$mobile, 'password'=>md5($password)));
    			if($isUserSave){
    				//领取奖励
    				D('User')->increaseCoin($userInfo['id'], $firstUse, C('INCOME_TYPE'), '注册用户赠送' . $firstUse . '积分');
    				$userInfo1 = D('User')->getUserInfo(array('where'=>array('mobile'=>$mobile, 'user_mac'=>$user_mac)));
    				$result = $this->_create_login_flag ( $userInfo1 );
    				$this->_logs('注册成功(修改记录):' . json_encode($result));
    				$this->returnApiData ( $result); // 返回登录成功
	    		}else{
	    			//$this->returnApiMsg ( '1042', '该手机已被其他手机号码注册过');
	    			$this->returnApiMsg ( '1043', '注册失败');
	    		}
    		}else{
    			$this->_logs("该手机MAC已被其他号绑定过，请换一台设备：" . $user_mac);
    			$this->returnApiMsg ( '1042', '该手机MAC已被其他号绑定过，请换一台设备');//之前提示 该手机已被其他手机号码注册过
    		}
    	}else{
    		//未注册过
    		$invitedcode = '';
    		while(true){
    			$invitedcode = randString(6);
    			$resInvitCode = D('User')->getUserInfo(array('field'=>'id,invitedcode', 'where'=>array('invitedcode'=>$invitedcode)));
    			if(empty($resInvitCode)){
    				break;
    			}
    		}
    		$paramUser = array(
    				'id' => \Org\Util\String::uuid(false, false),
    				'mobile' => $mobile,
    				'password' => md5($password),
    				'nickname' => $mobile,
    				'user_mac' => $user_mac,
    				'gender' => 0,
    				'head_pic' => '/images/default.jpg/images/default.jpg',
    				'total_coin' => 0,
    				'coin' => 0,
    				'cdate' => $nowTime,
    				'udate' => $nowTime,
    				'invitedcode'=>$invitedcode,
    		);
    		$res = D('User')->insertUser($paramUser);
    		if($res){
    			$userInfo1 = D('User')->getUserInfo(array('where'=>array('mobile'=>$mobile, 'user_mac'=>$user_mac)));
    			D('User')->increaseCoin($userInfo1['id'], $firstUse, C('INCOME_TYPE'), '注册用户赠送' . $firstUse . '积分');
    			$userInfo1 = D('User')->getUserInfo(array('where'=>array('id'=>$userInfo1['id'])));
    			$result = $this->_create_login_flag ( $userInfo1 );
    			$this->_logs('注册成功:' . json_encode($result));
    			$this->returnApiData ( $result); // 返回登录成功
    		}else{
    			$this->_logs('注册失败:1043 新记录');
    			$this->returnApiMsg ( '1043', '注册失败');
    		}
    	}
    	
    	//echo 111;exit;
    	
    	
    }
    
    /**
     * 忘记密码
     */
    public function forgetPwd(){
    	$mobile = addslashes(I('mobile'));//手机号
    	$verify = addslashes(I('verify'));//手机号验证码
    	$pwd = addslashes(I('pwd'));//密码
    	
    	if(!trim($mobile)){
    		$this->returnApiMsg ( '1036', '请填写手机号' );
    	}
    	if(!trim($verify)){
    		$this->returnApiMsg ( '1038', '请填写验证码' );
    	}
    	if(!trim($pwd)){
    		$this->returnApiMsg ( '1039', '请填写密码' );
    	}
    	//匹配手机号的正则表达式
    	/* if(!preg_match("/^(13[0-9]|14[47]|15[0-35-9]|17[6-8]|18[0-9])([0-9]{8})$/", $mobile)){
    		$this->returnApiMsg ( '1037', '手机号填写错误' );
    	} */
    	if(!$this->_check_mobile($mobile)){
    		$this->returnApiMsg ( '1037', '手机号填写错误' );
    	}
    	$mobileVerify = S('mobileVerify_' . $mobile);
    	if(empty($mobileVerify)){
    		$this->returnApiMsg ( '1122', '验证码已过期，重新发送' );
    	}
    	if($mobileVerify != $verify){
    		$this->returnApiMsg ( '1041', '验证码错误' );
    	}
    	$param = array(
    		'where' => array(
    			'mobile' => $mobile,
    		),
    		'field' => 'id,mobile',
    	);
    	$getUserInfo = D('User')->getUserInfo($param);
    	if(!$getUserInfo){
    		$this->returnApiMsg ( '1056', '手机号不存在' );
    	}
    	$where = array(
    		'mobile' => $mobile,
    	);
    	$data = array(
    		'password' => md5($pwd),
    	);
    	$res = D('User')->updateUser($where, $data);
    	if($res){
    		$this->returnApiMsg ( '0', '操作成功' );
    	}else{
    		$this->returnApiMsg ( '1057', '操作失败' );
    	}
    }
    
    /**
     * 游客登录
     */
    public function visitor(){
    	$user_mac = addslashes(I('mac'));
    	$jgId = addslashes(I('regid', ''));//极光推送id
    	if(!$user_mac){
    		$this->returnApiMsg ( '1029', '未传入设备号'); // 未传入设备号
    	}
    	$versionCode = $_SERVER['HTTP_VERSIONCODE'];
    	write_log(array('versionCode:', $versionCode, $_SERVER));
    	$nowTime = date("Y-m-d H:i:s", time());
    	$userInfo = D('User')->getUserInfo(array('where'=>array('user_mac'=>$user_mac)));
    	if(!empty($userInfo)){
    		/* $paramArr = array(
    				'userid' => $userInfo['id'], //获取用户ID标识
    				'username' => $userInfo['mobile']?$userInfo['mobile']:'', //获取用户名
    				'nickname' => $userInfo['nickname'], //获取昵称
    				'user_mac' => $userInfo['user_mac'], //用户mac地址
    				'gender' => $userInfo['gender'], //性别
    				'head_pic' => $userInfo['head_pic'], //头像
    				'total_coin' => $userInfo['total_coin'], //总金币
    				'coin' => $userInfo['coin'], //可用金币
    				'today_coin' => $userInfo['today_coin'], //
    		); */
    		if($jgId){
    			//if(empty($userInfo['jg_id'])){//数据库没有极光id数据，则写入数据库中
    			D('User')->updateUser(array('id'=>$userInfo['id']), array('jg_id'=>$jgId));
    			//}
    		}else{
    			$jgId = $userInfo['jg_id'];
    		}
    		$result = $this->_create_login_flag ( $userInfo, $jgId );
    		$this->_logs('登录成功:' . json_encode($result));
    		$this->returnApiData ( $result); // 返回登录成功
    	}else{
    		$invitedcode = '';
    		while(true){
    			$invitedcode = randString(6);
    			$resInvitCode = D('User')->getUserInfo(array('field'=>'id,invitedcode', 'where'=>array('invitedcode'=>$invitedcode)));
    			if(empty($resInvitCode)){
    				break;
    			}
    		}
    		//$firstUse = rewardConfig('first_use');//初次使用获得金币
    		//$firstUse = getSystemConfig('01', '01');//初次使用获得金币
    		$param = array(
    				'id' => \Org\Util\String::uuid(false, false),
    				'mobile' => NULL,
    				'password' => NULL,
    				'nickname' => 'gl' .\Home\Common\RandChar::getRandChar(8),
    				'user_mac' => $user_mac,
    				'gender' => 0,
    				'head_pic' => '/images/default.jpg',
    				'total_coin' => 0,
    				'coin' => 0,
    				'cdate' => $nowTime,
    				'udate' => $nowTime,
    				'invitedcode'=>$invitedcode,
    				'jg_id' => $jgId,
    		);
    		$res = D('User')->insertUser($param);
    		if($res){
    			$userInfo1 = D('User')->getUserInfo(array('where'=>array('user_mac'=>$user_mac)));
    			/* $param1 = array(
	    			'userid' => $userInfo1['id'], //获取用户ID标识
	    			'username' => $userInfo1['mobile']?$userInfo1['mobile']:'', //获取用户名
	    			'nickname' => $userInfo1['nickname'], //获取昵称
	    			'user_mac' => $userInfo1['user_mac'], //用户mac地址
	    			'gender' => $userInfo1['gender'], //性别
	    			'head_pic' => $userInfo1['head_pic'], //头像
	    			'total_coin' => $userInfo1['total_coin'], //总金币
	    			'coin' => $userInfo1['coin'], //可用金币
    				'today_coin' => $userInfo1['today_coin'], //
	    		); */
	    		$result = $this->_create_login_flag ( $userInfo1 );
	    		$this->_logs('游客登录成功:' . json_encode($result));
	    		$this->returnApiData ( $result); // 返回登录成功
    		}else{
    			$this->returnApiMsg ( '1030', '游客登录失败');
    		}
    	}
    	
    }
    
}