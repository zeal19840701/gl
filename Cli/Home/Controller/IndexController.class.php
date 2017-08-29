<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        //$this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
    }
    
    public function tt(){
    	//echo $_SERVER['argv'][2];
    	//print_r($_SERVER['argv']);
    	//echo $_SERVER['argv'][2] . "tt";
    	//$acqModel = M('data_acquisition', '', 'DB_DATA_GRAB');
    	//$sql = "SELECT * FROM `data_acquisition` WHERE `flag` = 0 limit 0, 5";
    	//$res = D('Acquisition')->getQuery($sql);
    	//$res = $acqModel->query($sql);
    	//print_r($res);
    }
    
    /**
     * 今日收益
     */
    public function todayearning(){
    	$userModel = M('gl_user', '', 'DB_GOLD_LOCK');
    	$userModel->where("1=1")->save(array('today_coin'=>0));
    	//echo $userModel->_sql();
    	echo "Today earning reset!" . "\n";
    }
    
    /**
     * 获得上一周的排名
     */
    public function lastweek(){
    	$userModel = M('gl_user_revenue_rank', '', 'DB_GOLD_LOCK');
    	$userModel->where("1")->save(array('last_week'=>array("exp", "`week_revenue`")));
    	//echo $userModel->_sql();
    	echo "last week success!" . "\n";
    }
    
    //周排行定时清零
    public function weekrank(){
    	$userModel = M('gl_user_revenue_rank', '', 'DB_GOLD_LOCK');
    	$userModel->where("1=1")->save(array('week_revenue'=>0));
    	//echo $userModel->_sql();
    	echo "Week rank reset!" . "\n";
    }
    
    public function lastrevenue(){
    	$userModel = M('gl_user', '', 'DB_GOLD_LOCK');
    	$userModel->where("1=1")->save(array("last_revenue"=>array("exp", "`coin`")));
    	//echo $userModel->_sql();
    	echo "last revenue rank!" . "\n";
    }
    
    /**
     * 创建虚拟账户
     */
    public function createUser(){
    	exit;
    	$userModel = M('gl_user', '', 'DB_GOLD_LOCK');
    	for($i=1;$i<=990;$i++){
    		$nowTime = date("Y-m-d H:i:s", time());
    		$paramUser = array(
    				'id' => \Org\Util\String::uuid(false, false),
    				'mobile' => '',
    				'password' => md5('zhendaoTrueland2017'),
    				'nickname' => createNick(),
    				'user_mac' => 'ZhenDao5Trueland',
    				'gender' => 0,
    				'head_pic' => '/images/default.jpg/images/default.jpg',
    				'total_coin' => 0,
    				'coin' => 0,
    				'cdate' => $nowTime,
    				'udate' => $nowTime,
    				'invitedcode'=>'',
    				'flag'=>1,
    		);
    		//print_r($paramUser);
    		$id= $userModel->data($paramUser)->add();
    		echo $i."\n";
    	}
    	echo 'finish!'."\n";
    }
    
    /**
     * 虚拟账户金币添加
     */
    public function virtualusercoinadd(){
    	$userModel = M('gl_user', '', 'DB_GOLD_LOCK');
    	$userRevenueRankModel = M('gl_user_revenue_rank', '', 'DB_GOLD_LOCK');
    	$userConsumeModel = M('gl_user_consume', '', 'DB_GOLD_LOCK');
    	$userList = $userModel->field('id')->where('flag=1')->limit(1000)->select();
    	foreach($userList as $user){
    		$coin = (mt_rand(1, 10) * 1000) + (mt_rand(1,100) * 10);
    		$nowDate = date("Y-m-d H:i:s", time());
    		$userModel->where(array('id'=>$user['id']))->save(array('total_coin'=>array('exp', 'total_coin+'.$coin), 'coin'=>array('exp', 'coin+'.$coin), 'today_coin'=>array('exp', 'today_coin+'.$coin)));
    		$ret = $userRevenueRankModel->field('id,user_id')->where(array("user_id"=>$user['id']))->find();
    		if($ret){
    			$userRevenueRankModel->where(array('user_id'=>$user['id']))->save(array('week_revenue'=>array('exp', '`week_revenue`+'.$coin), 'total_revenue'=>array('exp', '`total_revenue`+'.$coin), 'udate'=>$nowDate));
    		}else{
    			$userRevenueRankModel->data(array('user_id'=>$user['id'], 'week_revenue'=>$coin, 'total_revenue'=>$coin, 'udate'=>$nowDate, 'cdate'=>$nowDate))->add();
    		}
    		$dataUserConsume = array(
    			'user_id' => $user['id'],
    			'coin'=>$coin,
    			'type'=>'收入',
    			'intro'=>'虚拟账户增加',
    			'cdate'=>$nowDate,
    		);
    		$userConsume = $userConsumeModel->data($dataUserConsume)->add();
    		echo $user['id'] . ' execute success'. "\n";
    	}
    	echo 'finish'. "\n";
    }
    
    /**
     * 自动放弃任务
     */
    public function waiver(){
    	$missionModel = D('Mission');
    	$missionUserModel = D('MissionUser');
    	$missionUserStepModel = D('MissionUserStep');
    	$missionUserStepImgModel = D('MissionUserStepImg');
    	$sql = "SELECT a.id,a.user_id,a.muid,a.mid,a.step,a.flag,b.current_step,b.total_step,b.step_time,c.start_time,c.end_time FROM `gl_mission_user_step` AS a LEFT JOIN `gl_mission_user` AS b ON (a.muid=b.id) LEFT JOIN `gl_mission` AS c ON (b.mid=c.id) WHERE UNIX_TIMESTAMP(b.step_time)+172800<UNIX_TIMESTAMP(NOW()) AND a.flag=3 AND c.id IS NOT NULL LIMIT 100";
    	$res = $missionUserModel->getQuery($sql);
    	if($res){
    		foreach($res as $k=>$v){
    			$musInfo = $missionUserStepModel->getInfo(array("where"=>array("user_id"=>$v['user_id'], "mid"=>$v['mid'], "flag"=>3)));
    			if(!empty($musInfo)){
    				$modMissionUserInfo = $missionUserModel->getInfo(array("where"=>array("user_id"=>$v['user_id'], "mid"=>$v['mid'], "status"=>0)));
    				if(!empty($modMissionUserInfo)){
    					if(($modMissionUserInfo['current_step']==0 && $modMissionUserInfo['total_step']==1) || ($modMissionUserInfo['current_step']!=0 && $modMissionUserInfo['total_step']!=1)){
    						$missionUpdateId = $missionModel->updateData(array("id"=>$v['mid']), array("copies"=>array("exp", "`copies`+1")));//暂时注释
    					}
    				}
    				$muId = $missionUserModel->updateData(array("user_id"=>$v['user_id'], "mid"=>$v['mid'], "status"=>0), array("status"=>2));
    				$musId = $missionUserStepModel->updateData(array("user_id"=>$v['user_id'], "mid"=>$v['mid'], "status"=>0), array("status"=>2));
    				$musiId = $missionUserStepImgModel->updateData(array("user_id"=>$v['user_id'], "mid"=>$v['mid'], "status"=>0), array("status"=>2));
    			}
    		}
    	}
    	/* $missionModel = M('gl_mission', '', 'DB_GOLD_LOCK');
    	$missionUserModel = M('gl_mission_user', '', 'DB_GOLD_LOCK');
    	$missionUserStepModel = M('gl_mission_user_step', '', 'DB_GOLD_LOCK');
    	$missionUserStepImgModel = M('gl_mission_user_step_img', '', 'DB_GOLD_LOCK');
    	$page = 1;
    	$size = 20;
    	while(true){
    		$limit = ($page-1) * $size . "," . $size;
    		$missionUserInfo = $missionUserModel->field("`id`,`user_id`,`mid`,`coin`,`current_step`,`total_step`,`flag`,`create_time`,`status`")->where(array("status"=>0))->limit($limit)->select();
    		//print_r($missionUserInfo);
    		if(empty($missionUserInfo)){
    			break;
    		}
    		if(!empty($missionUserInfo)){
    			foreach ($missionUserInfo as $k=>$v){
    				$nextTime = strtotime($v['step_time']) + 172800;
    				if(($nextTime<=time()) && ($v['current_step'] < $v['total_step'])){
    					$missionUserModel->where(array("user_id"=>$v['user_id'], "mid"=>$v['mid'], "status"=>0))->save(array("status"=>2));
    					$missionUserStepModel->where(array("user_id"=>$v['user_id'], "mid"=>$v['mid'], "status"=>0))->save(array("status"=>2));
    					$missionUserStepImgModel->where(array("user_id"=>$v['user_id'], "mid"=>$v['mid'], "status"=>0))->save(array("status"=>2));
    					//$missionModel->where(array("id"=>$v['mid']))->save(array("copies"=>array("exp", "`copies`+1")));
    					$modMissionUserInfo = $missionUserModel->field("`id`,`user_id`,`mid`,`coin`,`current_step`,`total_step`,`flag`,`create_time`,`status`")->where(array("status"=>2))->limit($limit)->select();
    					if(!empty($modMissionUserInfo)){
    						if(($modMissionUserInfo['current_step']==0 && $modMissionUserInfo['total_step']==1) || ($modMissionUserInfo['current_step']!=0 && $modMissionUserInfo['total_step']!=1)){
    							//D("Mission")->updateData(array("id"=>$v['mid']), array("copies"=>array("exp", "`copies`+1")));//暂时注释
                                $musInfo = $missionUserStepModel->field("`id`, `user_id`, `muid`, `mid`, `step`, `flag`")->where(array("user_id"=>$v['user_id'], "mid"=>$v['mid'], "flag"=>3))->find();//查找拒绝有没有过期的步骤
                                if(empty($musInfo)){
                                    $missionUserStepImgModel->where(array("id"=>$v['mid']))->save(array("copies"=>array("exp", "`copies`+1")));
                                }

    						}
    					}
    					
    					echo "execute success\n";
    				}
    			}
    		}
    		$page++;
    	} */
    	echo "execute finish\n";
    }
    
    public function autoaudit(){
    	$missionModel = D('Mission');
    	$missionUserModel = D('MissionUser');
    	$missionUserStepModel = D('MissionUserStep');
    	//$sql = "SELECT a.id,a.user_id,a.muid,a.mid,a.step,b.current_step,b.total_step FROM `gl_mission_user_step` AS a LEFT JOIN `gl_mission_user` AS b ON (a.muid=b.id) WHERE UNIX_TIMESTAMP(b.step_time)+172800<UNIX_TIMESTAMP(NOW()) AND  b.flag=1";
    	$sql = "SELECT a.id,a.user_id,a.muid,a.mid,a.step,a.flag,b.current_step,b.total_step,b.step_time,c.start_time,c.end_time FROM `gl_mission_user_step` AS a LEFT JOIN `gl_mission_user` AS b ON (a.muid=b.id) LEFT JOIN `gl_mission` AS c ON (b.mid=c.id) WHERE UNIX_TIMESTAMP(b.step_time)+172800<UNIX_TIMESTAMP(NOW()) AND  a.flag=1 AND c.id IS NOT NULL LIMIT 100";//UNIX_TIMESTAMP(c.end_time)>=UNIX_TIMESTAMP(NOW()) AND 
    	echo $sql;
    	echo "\n";
    	//write_log(array('autoAudit:', $sql));
    	$res = D('MissionUser')->getQuery($sql);
    	print_r($res);
    	echo "\n";
    	//write_log(array('res:', $res));
    	$nowDate = date("Y-m-d H:i:s", time());
    	if($res){
    		foreach($res as $k=>$v){
    			$isFinishMission = 0;
    			$updateUserStepId = $missionUserStepModel->updateData(array('id'=>$id), array('flag'=>2));//审核通过
    			print_r($updateUserStepId);
    			$updateUserId = $missionUserModel->updateData(array('id'=>$v['muid']), array('current_step'=>$v['step'], 'step_time'=>$nowDate));//修改当前的任务步数
    			print_r($updateUserId);
    			echo "\n";
    			$userInfo = $missionUserModel->getInfo(array('where'=>array('id'=>$v['muid'])));//查询任务用户信息
    			print_r($userInfo);
    			echo "\n";
    			//判断是否当前是否完成
    			if($v['step'] == $userInfo['total_step']){
    				$isFinishMission = 1;//任务完成
    				//任务完成，计算收益
    				$missionInfo = $missionModel->getInfo(array('where'=>array('id'=>$v['mid'])));
    				//write_log(array('step==total_step,missionInfo', $missionInfo));
    				//判断用户完成状态和添加得到金币数
    				$updateUserId = $missionUserModel->updateData(array('id'=>$v['muid']), array('flag'=>2, 'coin'=>$missionInfo['award'], "status"=>1));//正常完成任务状态为1
    			}
    			//查询未审核的数量
    			$not_audit_num = $missionUserStepModel->getCount(array('mid'=>$v['mid'], 'flag'=>1));//只有审核中的人才能进行审核
    			echo $not_audit_num;
    			echo "\n";
    			D('Mission')->updateData(array('id'=>$v['mid']), array('not_audit_num' => $not_audit_num));//修改审核的数量
    			//$userInfo = $missionUserModel->getInfo(array('where'=>array('id'=>$v['muid'])));
    			
    			D('MessageReceive')->insertMessage(1, $missionInfo['user_id'], '审核通过', '标题为' . $missionInfo['title'] . '得任务审核通过啦', $userInfo['user_id']);//发送通知
    			if(!$isFinishMission){
    				$stepCoin = floor($missionInfo['award']/$userInfo['total_step']);
    				write_log(array('stepCoin', $stepCoin, $missionInfo['award'], $userInfo['total_step']));
    				//获得积分
    				D('User')->increaseCoin($userInfo['user_id'], $stepCoin, '收入', '完成任务:'.$missionInfo['title'].'获得积分');
    				//减去积分
    				D('User')->decreaseCoin($missionInfo['user_id'], $stepCoin, '收入', '参与者已完成任务积分已消耗');
    				D('MessageReceive')->insertMessage(1, $missionInfo['user_id'], '任务部分完成', '标题为' . $missionInfo['title'] . '得步骤'.$userInfo['current_step'].'完成啦', $userInfo['user_id']);
    				}
    			if($isFinishMission){
    				//获得积分
    				$stepCoin = $missionInfo['award'] - ($userInfo['total_step']-1)*(floor($missionInfo['award']/$userInfo['total_step']));
    				D('User')->increaseCoin($userInfo['user_id'], $stepCoin, C('INCOME_TYPE'), '完成任务:'.$missionInfo['title'].'获得积分');
    				//减去积分
    				D('User')->decreaseCoin($missionInfo['user_id'], $stepCoin, '收入', '参与者已完成任务积分已消耗');
    				D('MessageReceive')->insertMessage(1, $missionInfo['user_id'], '任务完成', '标题为' . $missionInfo['title'] . '得任务完成啦', $userInfo['user_id']);
    			}
    			sleep(1);
    		}
    	}
    	echo "exexcute finish!";
    }
    
    //添加邀请码
    public function addinvite(){
    	$userModel = M('gl_user', '', 'DB_GOLD_LOCK');
    	while(true){
    		$res = $userModel->field('id')->where("invitedcode=''")->limit(10)->select();
    		if(empty($res)){
    			break;
    		}else{
    			foreach($res as $k=>$v){
    				$invitedcode = '';
    				while(true){
    					$invitedcode = randString(6);
    					$resInvitCode = $userModel->field('id,invitedcode')->where("invitedcode='" . $invitedcode . "'")->find();
    					if(empty($resInvitCode)){
    						break;
    					}
    				}
    				$data = array(
    					'invitedcode' => $invitedcode,
    				);
    				$resCode = $userModel->where("id='" . $v['id'] . "'")->save($data);
    				if($resCode){
    					echo "USER ID:" . $v['id'] . " execute success, Code:" . $invitedcode . "\n";
    				}
    			}
    		}
    	}
    	echo "execute finish! \n";
    }
    
    public function oldUser(){
    	$userModel = M('gl_user', '', 'DB_GOLD_LOCK');
    	$screenUserModel = M('tbl_usrbaseinfo', '', 'DB_DATA_SCREEN');
    	$begin = microtime(TRUE);
    	while(true){
    		$sql = "SELECT a.*,b.totalAccountGold,b.changeGold,b.currentGold FROM `tbl_usrbaseinfo` AS a LEFT JOIN `tbl_usraccountmgmt` AS b ON (a.accountID=b.accountID) WHERE a.flag=0 LIMIT 10";
    		$res = $screenUserModel->query($sql);
    		if(empty($res)){
    			break;
    		}
    		foreach ($res as $k=>$v){
    			$sql = "SELECT exchangeAccount FROM `tbl_userchangerecord` WHERE accountID='" . $v['accountid'] . "' limit 1";
    			$resRecord = $screenUserModel->query($sql);
    			$glUser = $userModel->field('id,mobile')->where(array('mobile'=>$v['accountid']))->find();
    			if($glUser){
    				$data1 = array('flag'=>1);
    				$screenUserModel->where("accountID='" . $v['accountid'] . "'")->save($data1);
    				continue;
    			}
    			if(empty($v['accountid'])){
    				continue;
    			}
    			$param = array(
    				'id' => \Org\Util\String::uuid(false),
    				'mobile' => $v['accountid'],
    				'password' => $v['password'],
    				'tel' => $v['tel'],
    				'nickname' => $v['nicknm'],
    				'name' => $v['name'],
    				'age' => $v['old'],
    				'gender' => $v['gender'],
    				'address' => $v['area'],
    				'head_pic' => '/images/default.jpg',
    				'udate' => $v['updatedttm'],
    				'cdate' => $v['createdttm'],
    				'status' => $v['isdel'],
    				'profession' => $v['profession'],
    				'marital' => $v['marry'],
    				'invitedcode' => $v['invitedcode'],
    				'inviter' => $v['inviter'],
    				'total_coin' => $v['totalaccountgold'],
    				'use_coin' => $v['changegold'],
    				'coin' => $v['currentgold'],
    				'alipay_account' => isset($resRecord[0]['exchangeaccount'])?$resRecord[0]['exchangeaccount']:'',
    			);
    			$r = $userModel->data($param)->add();
    			if($r){
    				$data1 = array('flag'=>1);
    				$screenUserModel->where("accountID='" . $v['accountid'] . "'")->save($data1);
    				echo "USER ID:" . $v['accountid'] . " execute success" . "\n";
    			}
    		}
    	}
    	$end = microtime(TRUE);
    	echo "total time:". ($end-$begin) ." execute finish! \n";
    }
    
    public function sendsms(){
    	$userModel = M('gl_user', '', 'DB_GOLD_LOCK');
    	while(true){
	    	$getUserList = $userModel->field('id,mobile')->where('flag=0')->limit(5)->select();
	    	if(empty($getUserList)){
	    		break;
	    	}
	    	/* $getUserList = array(
	   			array(
					'id'=>'0004edef-43b3-6f98-020b-2f398ae1449f',
	   				'mobile'=>'13641833211'
	   			),
	    		array(
	    			'id'=>'0017099a-f888-73ad-35ad-d3dae581ce96',
	    			'mobile'=>'13816213753'
	    		),
	    	); */
	    	if($getUserList){
	    		foreach ($getUserList as $k=>$v){
		    		$result = \Home\Common\Sms::getmverif($v['mobile'], "【珍岛集团】各位金主，让您久等了！金锁全新版本发布，老用户尝鲜使用。移驾金锁官网下载：http://jinsuo.71360.com");//金锁app客户，我们app全新改版了快来体验吧，验证码{0}
			    	if($result[0] == 200){
			    		$rt = json_decode($result[1], true);
			    		if($rt['success']){
			    			$userModel->where("mobile='" . $v['mobile'] . "'")->save(array('flag'=>1));
			    			$this->_logs($v['mobile'].'手机号发送成功');
			    			echo "send success" . "\n";
			    			sleep(10);
			    		}else{
			    			$this->_logs($v['mobile'].'手机号发送失败');
			    			echo "send failure" . "\n";
			    			sleep(120);
			    		}
			    	}else{
			    		$this->_logs($v['mobile'].'手机号发送失败');
			    		echo "send failure" . "\n";
			    	}
	    		}
	    		sleep(30);
	    	}
    	}
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