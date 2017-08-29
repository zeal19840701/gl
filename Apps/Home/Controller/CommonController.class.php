<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
	protected $sign = ''; // sign串
	protected $sign_code = 'sdfesdcf';//校验码
	
	public function __construct() { // $username, $token
		/* if(!((strtolower(MODULE_NAME) == 'home' && strtolower(CONTROLLER_NAME) == 'news' && strtolower(ACTION_NAME) == 'detail'))){
			$get_param = I('');//获取所有get、post参数
			$this->sign = $get_param['sign'];//获取sign串
			
			if (!$this->sign) {
				$this->_logs('获取sign:' . $this->sign. ' ' . '返回结果:1025 sign未传入');
				$this->returnApiMsg ( '1025', 'sign未传入' ); // sign未传入
			}
			foreach($get_param as $key=>$val){
				if($key == 'publishData'){
					$publishData = json_decode(htmlspecialchars_decode($val), true);//把json转成数组形式
					unset($get_param['publishData']);
					$get_param = array_merge($get_param, $publishData);
				}else if($key == 'missionData'){
					$missionData = json_decode(htmlspecialchars_decode($val), true);//把json转成数组形式
					unset($get_param['missionData']);
					$get_param = array_merge($get_param, $missionData);
				}
			}
			$this->_logs('get_param');
			$this->_logs($get_param);
			$checkSign = \Home\Common\Sign::checkSign($this->sign, $get_param, $this->sign_code);
			if(!$checkSign){
				$this->_logs('获取token:' . $checkSign . ' ' . '返回结果:1027' . ' 验签失败' );
				$this->returnApiMsg ( '1027', '验签失败' ); // token验证失败
			}
		} */
		
		parent::__construct ();
	
	}
	
	/**
	 * 发送消息
	 * @param string $userid
	 * @param string $title
	 * @param string $content
	 * @param string $sender
	 *
	 */
	protected function _send_message($userid, $title, $content, $sender="SYSTEM"){
		$userInfo = SU(md5($userid));
		$messageData = array();
		if($userInfo['jg_id']){
			//向极光推送充值消息
			$client = new \Home\Common\JpushApi();
			$messageData = $client->push_message($content, $userInfo['jg_id']);
		}
		$nowDate = date("Y-m-d H:i:s", time());
		$msgInfo = array(
			'info_type' => 1,//个人消息
			"sender" => $sender,//表示系统发送
			'info_title' => $title,
			"info_content" => $content,
			"create_date" => $nowDate,
			"is_del" => 0,
		);
		$resMessageInsert = $modelMessage->insertData($msgInfo);
		$this->_logs(array("resMessageInsert:", $resMessageInsert));
		if(!$resMessageInsert){
			$msgReceiveInfo = array(
				'message_id' => $resMessageInsert,
				'receiver_account' => $userid,
				'sendno' => isset($messageData['sendno'])?$messageData['sendno']:'',
				'msg_id' => isset($messageData['msg_id'])?$messageData['msg_id']:'',
				'status' => 0,
			);
			return D("MessageReceive")->insertData($msgReceiveInfo);
		}else{
			return false;
		}
	}
	
	/**
	 * 统计信息数量
	 * @param number $type
	 * @param number $limit
	 */
	protected function _calcMessageCount($cdate, $type, $limit=10){
		$paramMessage = array(
				'where'=>array(
						'info_type'=>$type,
						'is_del'=>0,
						'create_date' => array('gt', $cdate)
				),
				'order'=>'id DESC',
				'field'=>'id,info_type as type,info_title,info_content,create_date',
				'limit'=>$limit,
		);
		$messageList = D('Message')->getList($paramMessage);
		if(!empty($messageList)){
			$messageStr = '';
			foreach ($messageList as $k=>$v){
				$messageStr .= " (message_id='" . $v['id'] . "' AND receiver_account='" . $this->userid . "') OR";
			}
			$messageStr = trim($messageStr, "OR");
			if(!empty($messageStr)){
				$MessageReceiveList = D('MessageReceive')->getQuery("SELECT * FROM `gl_message_receive` WHERE " . $messageStr . " ORDER BY message_id DESC");
			}else{
				$MessageReceiveList = D('MessageReceive')->getQuery("SELECT * FROM `gl_message_receive` WHERE 0");
			}
			if(!empty($messageStr)){//查询是否有消息
				$newMessageReceiveList = array();
				foreach($MessageReceiveList as $k=>$v){
					$newMessageReceiveList[$v['message_id']] = $v['message_id'];
				}
				foreach($messageList as $k=>$v){
					if(in_array($v['id'], $newMessageReceiveList)){
						unset($messageList[$k]);
					}
				}
				if(!empty($messageList)){
					$mrArr = array();
					$i=0;
					foreach ($messageList as $k=>$v){
						$mrArr[$i]['message_id'] = $v['id'];
						$mrArr[$i]['receiver_account'] = $this->userid;
						$mrArr[$i]['sendno'] = '';
						$mrArr[$i]['msg_id'] = '';
						$mrArr[$i]['send_date'] = date("Y-m-d H:i:s");
						$mrArr[$i]['status'] = 0;
						$i++;
					}
					$res = D('MessageReceive')->batchInsertData($mrArr);
				}
			}
		}
		return true;
	}
    
	/**
	 * 根据token返回登录信息
	 * @param string $token
	 */
	protected function adm($userid) {
		return SU ( md5 ( $userid ) );
	}
	
	/**
	 * 注册登录时调用
	 * @param array $params
	 */
	protected function _create_login_flag($params, $jgId="", $versionCode="") {
		if ($params['type'] == 'remove') {
			$userKey = md5 ( $params['userid'] ); // 生成用户唯一代码key
			$getUserKey = SU($userKey);//查看缓存文件是否存在
			if(empty($getUserKey)){//不存在则直接给退出
				return true;
			}
			$flag = $this->_check_token ( $params['userid'], $params['token'] ); // 验证token是否正确
			if ($flag) {
				SU ( $userKey, null ); // 退出时把token删除
				return true;
			}
			return false;
		} else {
			//$token = $this->_create_token( $params['userid'] ); // 随机生成token
			$token = $this->_create_token( $params['id'] ); // 随机生成token
			//$params['token'] = $token;
			$data = array (
				'userid' => $params['id'],
				'username' => $params['mobile']?$params['mobile']:'',
				'nickname' => $params['nickname'],
				'user_mac' => $params['user_mac'], //用户mac地址
				'gender' => $params['gender'], //性别
				'head_pic' => C('DATA_IMG_URL') . $params['head_pic'], //头像
				'total_coin' => $params['total_coin'], //总金币
				'coin' => $params['coin'], //可用金币
				'today_coin' => $params['today_coin'], //
				
				'wechat_account' => $params['wechat_account']?$params['wechat_account']:'',//微信号
				'alipay_account' => $params['alipay_account']?$params['alipay_account']:'',//支付宝
				'address' => $params['address']?$params['address']:'',//地址
				'age' => $params['age']?$params['age']:'',//年龄段
				'profession' => $params['profession']?$params['profession']:'',//职业
				'marital' => $params['marital']?$params['marital']:'',//婚姻
				'token' => $token,
				'jg_id' => $jgId,
				'version_code' => $versionCode,
			);
			$rankArr = $this->_calcRank($params['id'], 'week_revenue');
			$myRank = $rankArr['rank'];
			$data['my_rank'] = $myRank;
			//SU ( md5 ( $params['userid'] ), $params ); // 设置不过期，除非重新登录、退出或者删除缓存
			SU ( md5 ( $data['userid'] ), $data ); // 设置不过期，除非重新登录、退出或者删除缓存
			unset($data['jg_id']);//返回app不需要带出去
			unset($data['version_code']);//版本号
			return $data;
		}
	}
	
	/**
	 * 验证登录时调用
	 * @param array $params
	 */
	protected function _get_login_flag($params) {
		$data = array (
			'userid' => $params['id'],
			'username' => $params['mobile']?$params['mobile']:'',
			'nickname' => $params['nickname'],
			'user_mac' => $params['user_mac'], //用户mac地址
			'gender' => $params['gender'], //性别
			'head_pic' => C('DATA_IMG_URL') . $params['head_pic'], //头像
			'total_coin' => $params['total_coin'], //总金币
			'coin' => $params['coin'], //可用金币
			'today_coin' => $params['today_coin'], //
			
			'wechat_account' => $params['wechat_account']?$params['wechat_account']:'',//微信号
			'alipay_account' => $params['alipay_account']?$params['alipay_account']:'',//支付宝
			'address' => $params['address']?$params['address']:'',//地址
			'age' => $params['age']?$params['age']:'',//年龄段
			'profession' => $params['profession']?$params['profession']:'',//职业
			'marital' => $params['marital']?$params['marital']:'',//婚姻
		);
		$rankArr = $this->_calcRank($params['id'], 'week_revenue');
		$myRank = $rankArr['rank'];
		$data['my_rank'] = $myRank;
		$oldData = SU ( md5 ( $data['userid'] ) ); // 读取用户数据
		$data['token'] = $oldData['token'];// 先把token获取到
		$data['jg_id'] = isset($oldData['jg_id'])?$oldData['jg_id']:'';
		$data['version_code'] = $oldData['version_code'];
		SU ( md5 ( $data['userid'] ), null );//先删除
		SU ( md5 ( $data['userid'] ), $data ); // 再添加
		unset($data['jg_id']);////返回app不需要带出去
		unset($data['version_code']);
		return $data;
	}
	
	/**
	 * 计算排名
	 * @param string $field
	 * @return array
	 */
	protected function _calcRank($userId, $field="total_revenue"){
		$key = 'get_my_rank_'. $field .'_' . $userId;
		$result = S($key);
		if(!$result){
			$nowDate = date("Y-m-d H:i:s", time());
			$userRevenueRankInfo = D("UserRevenueRank")->getUserRevenueRankInfo(array("where"=>array("user_id"=>$userId)));
			if(empty($userRevenueRankInfo)){
				D("UserRevenueRank")->insertUserRevenueRank(array("user_id"=>$userId, "last_week"=>0, "week_revenue"=>0, "total_revenue"=>0, "udate"=>$nowDate, "cdate"=>$nowDate));
				$userRevenueRankInfo = D("UserRevenueRank")->getUserRevenueRankInfo(array("field"=>$field, "where"=>array("user_id"=>$userId)));
			}
			$rankList = D("UserRevenueRank")->getUserRevenueRankInfo(array("field"=>"COUNT(*) AS rank", "where"=>array($field=>array("gt", $userRevenueRankInfo[$field])), "order"=>"udate DESC"));
			$userInfo = D("User")->getUserInfo(array("field"=>"nickname", "where"=>array("id"=>$userId)));
			$myRank = ($rankList['rank'] * 100) + mt_rand(1,100);
			$result = array("rank"=>$myRank, "coin"=>$userRevenueRankInfo[$field], "nickname"=>$userInfo['nickname']);
			if($field == 'last_week'){
				$lastWeekTime = strtotime("next Monday")-time();
				S($key, $result, $lastWeekTime);
			}else if($field == 'week_revenue'){
				S($key, $result, 900);
			}else if($field == 'total_revenue'){
				S($key, $result, 900);
			}
		}
		return $result;
	}
	
	/**
	 * 验证token是否一致
	 * @param string $userid
	 * @param string $token
	 * @return boolean
	 */
	protected function _check_token($userid, $token) {
		$data = SU ( md5 ( $userid ) ); // 读取用户数据
		// 判断token是否一致
		if ($data ['token'] == $token) {
			return true;
		}
		return false;
	}
	
	/**
	 * 随机生成token
	 * @param string $userid
	 * @return string $token
	 */
	protected function _create_token($userid) {
		 $nowDate = date ( "YmdHis", time () );
		 $rand = mt_rand(1000, 9999);
		 $rand_token = $userid . $nowDate . $rand;
		 $token = md5 ( $rand_token );
		 return $token;
	 }
	 
	 /**
	  * 正则验证微信号
	  * @param string $wechat
	  * @return boolean
	  */
	 protected function _check_wechat($wechat){
	 	if(preg_match("/^[a-zA-Z]{1}[a-zA-Z\d_-]{5,19}$/", $wechat)){
	 		return true;
	 	}
	 	return false;
	 }
	 
	 /**
	  * 正则验证手机号
	  * @param string $mobile
	  * @return boolean
	  */
	 protected function _check_mobile($mobile){
	 	if(preg_match("/^1[34578]{1}\d{9}$/", $mobile)){
	 		return true;
	 	}
	 	return false;
	 }
	 
	 /**
	  * 正则验证邮箱
	  * @param string $email
	  */
	 protected function _check_email($email){
	 	if(preg_match("/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/", $email)){///^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/  /^[a-z0-9]([a-z0-9]*[-_\.]?[a-z0-9]+)*@[a-z0-9]*([-_\.]?[a-z0-9]+)+[\.][a-z0-9]{2,3}([\.][a-z0-9]{2})?$/i
	 		return true;
	 	}
	 	return false;
	 }
	 
	 /**
	  * 检查任务状态
	  * @param array $list
	  */
	 protected function _check_status($list){
	 	$nowTime = time();
	 	if($list['flag'] == 2){
	 		$status = C('PAUSE_STATUS');//已暂停
	 	}else if(strtotime($list['start_time']) > $nowTime){
	 		$status = C('NOT_BEGIN_STATUS');//未开始
	 	}else if(strtotime($list['start_time']) <= $nowTime && strtotime($list['end_time']) >= $nowTime){
	 		$status = C('ON_GOING_STATUS');//进行中
	 	}else{
	 		$status = C('FINISH_STATUS');//已结束
	 	}
	 	return $status;
	 }
	 
	 /**
	  * 检查ip是否存在
	  * @param string $ip 
	  * @return $result 存在就返回true,否则就是false
	  */
	 protected function _check_ip_exist($ip){
	 	$ipList = array(
	 		"/^101\.226\.[0-9]{1,3}\.[0-9]{1,3}$/",
	 		"/^180\.163\.2\.[0-9]{1,3}$/",
	 		"/^180\.163\.81\.[0-9]{1,3}$/",
	 		"/^140\.207\.54\.[0-9]{1,3}$/",
	 		"/^140\.207\.124\.[0-9]{1,3}$/",
	 		"/^140\.207\.88\.[0-9]{1,3}$/",
	 		"/^140\.207\.185\.[0-9]{1,3}$/",
	 		"/^61\.151\.226\.[0-9]{1,3}$/",
	 		"/^61\.151\.217\.[0-9]{1,3}$/",
	 		"/^61\.151\.218\.[0-9]{1,3}$/",
	 		"/^182\.254\.11\.[0-9]{1,3}$/",
 			"/^112\.65\.193\.[0-9]{1,3}$/",
 			"/^117\.185\.27\.[0-9]{1,3}$/",
	 		//"/^123\.126\.56\.237$/",
	 		//"/^113\.108\.67\.24$/",
	 		//"/^183\.57\.53\.177$/",
	 	);
	 	$result = false;
	 	foreach ($ipList as $v){
	 		if(preg_match($v, $ip)){
	 			$result = true;
	 			break;
	 		}
	 	}
	 	return $result;
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