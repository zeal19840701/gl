<?php
namespace Home\Controller;
use Think\Controller;
header("Content-type: text/html; charset=utf-8");
class ShareController extends CommonController {
	
	/**
	 * 分享
	 */
    public function index(){
    	$id = addslashes(I('id'));//推荐id
    	$id = _passport_decrypt('gl', $id);//解密id
    	$userid = addslashes(I ( 'userid' )); // 用户名
    	$distid = I('distid', '');//区分分享次数
    	if(!$id){
    		echo "推荐ID不存在";
    		exit();
    	}
    	if(!$userid){
    		echo "用户ID不存在";
    		exit();
    	}
    	if(!$distid){
    		echo "分享不存在";
    		exit();
    	}
    	//查询有没有该用户
    	$paramUser = array(
    		'where'=>array(
    			'id' => $userid,
    		),
    		'field' => 'id',
    	);
    	$userInfo = D('User')->getUserInfo($paramUser);
    	if(empty($userInfo)){
    		echo "用户不存在";
    		exit();
    	}
    	//查询推荐的信息
    	$paramRecommend = array(
    		'where' => array(
    			'id' => $id,
    		),
    	);
    	$recommendInfo = D('Recommend')->getInfo($paramRecommend);
    	$ip = get_client_ip();
    	$nowTime = date("Y-m-d H:i:s", time());
    	$this->_logs(array("进入分享页index:", $userInfo, "ip地址:", $ip));
    	//判断有则进入
    	if(!empty($recommendInfo)){
    		$shareInfo = D("RecommendShare")->getInfo(array("where"=>array("user_id"=>$userid, "rec_id"=>$id, "dist"=>$distid)));
    		if(empty($shareInfo)){
    			$shareNumber = $this->_getRecommendStatDistinctCount($id);
    			D("Recommend")->updateRecommend(array("id"=>$id), array("share_number"=>$shareNumber,'update_time'=>$nowTime));//分享的数量统计
    			$dataRecommendShare = array(
    					'user_id'=>$userid,
    					'rec_id'=>$id,
    					'read_number'=>0,
    					'coin'=>$recommendInfo['award'],//多少金币
    					'create_time'=>$nowTime,
    					'dist'=>$distid,
    			);
    			$res = D('RecommendShare')->insertRecommend($dataRecommendShare);
    		}
    		$this->_logs(array("判断有则进入:", $shareInfo, "dist:", $distid, $dataRecommendShare));
    		$ip_user_agent = md5($ip . $_SERVER['HTTP_USER_AGENT']);
    		$paramRecommendShareLog = array(
    			'where' => array(
    				'user_id'=>$userid,
    				'rec_id'=>$id,
    				"ip"=>$ip,
    				"ip_user_agent"=>$ip_user_agent,
    				"dist"=>$distid,
    			),
    		);
    		$shareLogInfo = D('RecommendShareLog')->getInfo($paramRecommendShareLog);//查询推荐是否被分享过
    		//分享记录存在进入
    		$status = $this->_check_status($recommendInfo);//推荐返回状态,只有在推广中才进行扣金币
    		$checkIp = $this->_check_ip_exist($ip);//检查ip地址是否存在列表中
    		$this->_logs(array("获取ip地址", $ip));
    		$this->_logs(array("获取服务器地址", $_SERVER));
    		$this->_logs(array("检查后的checkIp返回:", $checkIp));
    		if( empty($shareLogInfo) && (!$checkIp) && ($recommendInfo['copies'] > 0) && ($status == 2) ){
    			$param = array(
    					"user_id" => $userid,
    					"rec_id" => $id,
    					"coin" => $recommendInfo["award"],
    					"ip" => $ip,
    					"ip_user_agent"=>$ip_user_agent,
    					"create_time" => $nowTime,
    					"dist" => $distid,
    			);
    			D("RecommendShareLog")->insertRecommend($param);//浏览的记录
    			if($userInfo['id'] != $recommendInfo['user_id']){//发布者与分享者不是同一个才记录金币
    				$this->_calculateCoin($recommendInfo, $userid, $id, $distid);//计算扣金币
    			}
    		}
    		$recommendInfo['addition_content'] = str_replace(array("\r\n", "\n", "\r"), "%0D%0A", $recommendInfo['addition_content']);
    		$recUrl = "/index.php?m=home&c=share&a=preview&content=" . $recommendInfo['addition_content'] . "&data=" . urlencode($recommendInfo['addition_style']);
    		if($recommendInfo['type'] == 1){
    			header("Location:" . $recommendInfo['link']);
    		}else if($recommendInfo['type'] == 2){
    			$tempData = $recommendInfo['addition_style'];
    			$content = $recommendInfo['addition_content'];//文字内容
    			$tempData = htmlspecialchars_decode($tempData);
    			$tempData = json_decode($tempData, true);
    			//print_r($tempData);
    			$content = str_replace(array("\r\n", "\n", "\r"), "%0D%0A", $content);
    			$data = array();
    			$data['text'] = $content?$content:"";//文字内容
    			$data['fonttype'] = $tempData['fonttype']?$tempData['fonttype']:"微软雅黑";//字体设置
    			$data['fontsize'] = $tempData['fontsize']?$tempData['fontsize']:"12";//字体大小
    			$data['color'] = $tempData['color']?$tempData['color']:"FF0000";//颜色
    			$data['bold'] = $tempData['bold']?$tempData['bold']:"";//粗体
    			$data['oblique'] = $tempData['oblique']?$tempData['oblique']:"";//斜体
    			$data['linewidth'] = $tempData['linewidth']?$tempData['linewidth']:0;//描边
    			$data['strokecolor'] = $tempData['strokecolor']?$tempData['strokecolor']:"";//描边颜色
    			$data['gradient'] = $tempData['gradient']?$tempData['gradient']:0;//渐变色是否启用，1为启用
    			$data['gradientstart'] = $tempData['gradientstart']?$tempData['gradientstart']:"000000";//开始渐变颜色
    			$data['gradientend'] = $tempData['gradientend']?$tempData['gradientend']:"000000";//结束渐变颜色
    			$data['shadow'] = $tempData['shadow']?$tempData['shadow']:0;//阴影是否启用，1为启用
    			$data['shadowcolor'] = $tempData['shadowcolor']?$tempData['shadowcolor']:"000000";//阴影颜色
    			$data['shadowblur'] = $tempData['shadowblur']?$tempData['shadowblur']:0;//模糊
    			$data['shadowx'] = $tempData['shadowx']?$tempData['shadowx']:0;//模糊x轴
    			$data['shadowy'] = $tempData['shadowy']?$tempData['shadowy']:0;//模糊y轴
    			$data['spacing'] = $tempData['spacing']?$tempData['spacing']:0;//间距大小
    			$data['linespacing'] = $tempData['linespacing']?$tempData['linespacing']:5;//行间距
    			$data['vertical'] = $tempData['vertical']?$tempData['vertical']:0;//排版(0,1)0为横排，1为竖排
    			$this->assign('data', $data);
    			//复制原文
    			$this->assign('title', '微信分享');
    			$this->assign('rs', $recommendInfo);
    			$this->assign('recUrl', $recUrl);
    			$this->display();
    		}
    	}else{
    		//不存在则这条记录
    		echo "推荐不存在";
    		exit();
    	}
    }
    
    /**
     * 计算金币
     * $recommendInfo 推荐表的信息
     * $userid 分享者
     * $id 推荐id
     */
    public function _calculateCoin($recommendInfo, $userid, $id, $distid){
    	//执行发布者的金币是否够被扣,不够则不扣
    	$this->_logs("进入计算金币函数");
		//$recKey = "recommendShareLock_".$recommendInfo['id'];//推荐锁key
		//$recommendShareLock = S($recKey);//读取锁
		//if(empty($recommendShareLock)){//检查是否有锁,没有锁进来
			if($recommendInfo['copies'] > 0){
				//S($recKey, 1);//进来加锁
				$param = array(
					'where'=>array(
						'id'=>$recommendInfo['user_id'],
						//'coin'=>array('egt', $recommendInfo['award']),
					)
				);
				$userInfo = D('User')->getUserInfo($param);//读取分享者用户金币是否足够被扣
				write_log(array('userInfo', $userInfo, $recommendInfo, $userid, $id, $distid));
				if(!empty($userInfo)){
					$recommendShareInfo = D('RecommendShare')->getInfo(array('field'=>'id,user_id,rec_id,create_time', 'where'=>array('user_id'=>$userid, 'rec_id'=>$id, 'dist'=>$distid)));
					write_log(array('_calculateCoin:' . $userid, $recommendShareInfo, strtotime($recommendShareInfo['create_time'] + 172800), time()));
					if(!empty($recommendShareInfo) && strtotime($recommendShareInfo['create_time'] + 172800) >= time()){
						//发布者推荐份数减1
						$copiesDec = D('Recommend')->updateFieldDec(array('id'=>$id), 'copies', 1);
						//发布者减少积分
						D('User')->decreaseCoin($recommendInfo['user_id'], $recommendInfo['award'], C('EXPEND_TYPE'), '用户阅读分享人的推荐内容');
						//分享者增加积分
						D('User')->increaseCoin($userid, $recommendInfo['award'], C('INCOME_TYPE'), '用户阅读了您的推荐内容');
					}
					//发布者的推荐计入阅读数
					$resRecommendUpdate = D('Recommend')->updateRecommend(array("user_id"=>$recommendInfo['user_id'], "id"=>$recommendInfo['id']), array("read_number"=>array("exp", "`read_number` + 1")));
					//分享者推荐计入阅读数
					$resShareUpdate = D("RecommendShare")->updateRecommend(array("user_id"=>$userid, "rec_id"=>$id, 'dist'=>$distid), array("read_number"=>array("exp", "`read_number` + 1")));
				}
				//S($recKey, null);//释放锁
				return true;
			}
			return false;
		//}
		//return false;
    }
    
    //返回
    public function res(){
    	$id = addslashes(I('id'));//推荐id
    	$id = _passport_decrypt('gl', $id);//解密id
    	$userid = addslashes(I ( 'userid' )); // 用户名
    	$distid = I('distid', '');//区分分享次数
    	if(!$distid){
    		echo "分享不存在";
    		exit();
    	}
    	$nowTime = date("Y-m-d H:i:s", time());
    	$paramRecommend = array(
    		'where' => array(
    			'id' => $id,
    		),
    	);
    	$recommendInfo = D("Recommend")->getInfo($paramRecommend);//查询推荐是否存在
    	if($recommendInfo){
    		$shareInfo = D("RecommendShare")->getInfo(array("where"=>array("user_id"=>$userid, "rec_id"=>$id, "dist"=>$distid)));
    		if(!$shareInfo){
    			$dataRecommendShare = array(
    				'user_id'=>$userid,
    				'rec_id'=>$id,
    				'read_number'=>0,
    				'coin'=>$recommendInfo['award'],//多少金币
    				'create_time'=>$nowTime,
    				'dist'=>$distid,
    			);
    			$res = D('RecommendShare')->insertRecommend($dataRecommendShare);
    			if($res){
    				$shareNumber = $this->_getRecommendStatDistinctCount($id);
    				D("Recommend")->updateRecommend(array("id"=>$id), array("share_number"=>$shareNumber,'update_time'=>$nowTime));//分享的数量统计
    				$this->_logs('分享数据记录结果:' .$res );
    			}
    			$this->returnApiMsg ('0', '分享成功');
    		}else{
    			$this->_logs('已分享过,但又分享一次');
    			$this->returnApiMsg ('0', '分享成功');
    		}
    	}else{
    		$this->returnApiMsg ('1045', '分享失败 ' . $r );
    	}
    }
    
    public function shareDist(){
    	$id = addslashes(I('id'));//推荐id
    	if(!is_numeric($id)){
    		$id = _passport_decrypt('gl', $id);//解密id
    	}
    	$userid = addslashes(I ( 'userid' )); // 用户名
    	$distid = I('distid', '');//区分分享次数
    	if(!$distid){
    		echo "分享不存在";
    		exit();
    	}
    	$nowTime = date("Y-m-d H:i:s", time());
    	$paramRecommend = array(
    		'where' => array(
    			'id' => $id,
    		),
    	);
    	$recommendInfo = D("Recommend")->getInfo($paramRecommend);//查询推荐是否存在
    	if($recommendInfo){
    		//$dist = date("YmdHis", time()) . mt_rand(1,9999);
    		/* $dataRecommendShare = array(
    			'user_id'=>$userid,
    			'rec_id'=>$id,
    			'read_number'=>0,
    			'coin'=>$recommendInfo['award'],//多少金币
    			'create_time'=>$nowTime,
    			'dist'=>$distid,
    		);
    		D('RecommendShare')->insertRecommend($dataRecommendShare);
    		$shareNumber = $this->_getRecommendStatDistinctCount($id);
    		D("Recommend")->updateRecommend(array("id"=>$id), array("share_number"=>$shareNumber,'update_time'=>$nowTime));//分享的数量统计
    		$this->returnApiData (array('dist'=>$dist)); */
    		$shareInfo = D("RecommendShare")->getInfo(array("where"=>array("user_id"=>$userid, "rec_id"=>$id, "dist"=>$distid)));
    		if(!$shareInfo){
    			$dataRecommendShare = array(
    					'user_id'=>$userid,
    					'rec_id'=>$id,
    					'read_number'=>0,
    					'coin'=>$recommendInfo['award'],//多少金币
    					'create_time'=>$nowTime,
    					'dist'=>$distid,
    			);
    			$res = D('RecommendShare')->insertRecommend($dataRecommendShare);
    			if($res){
    				$shareNumber = $this->_getRecommendStatDistinctCount($id);
    				D("Recommend")->updateRecommend(array("id"=>$id), array("share_number"=>$shareNumber,'update_time'=>$nowTime));//分享的数量统计
    				$this->_logs('分享数据记录结果:' .$res );
    			}
    			$this->returnApiMsg ('0', '分享成功');
    		}else{
    			$this->_logs('已分享过,但又分享一次');
    			$this->returnApiMsg ('0', '分享成功');
    		}
    	}else{
    		$this->returnApiMsg ('1045', '推荐不存在');
    	}
    }
    
    public function preview(){
    	//这是四套json样式
    	//http://js.mytcloud.com/index.php?m=home&c=share&a=preview&content=金锁&data=
    	//样式一 {"fonttype":"\u96b6\u4e66","fontsize":"40","color":"FFFF00","bold":"","oblique":"","linewidth":"1","strokecolor":"FF0000","gradient":0,"gradientstart":"000000","gradientend":"000000","shadow":0,"shadowcolor":"000000","shadowblur":0,"shadowx":0,"shadowy":0,"spacing":2,"vertical":0}
    	//样式二 {"fonttype":"\u534e\u6587\u7425\u73c0","fontsize":"40","color":"FF0000","bold":"","oblique":"","linewidth":"0","strokecolor":"FFFF00","gradient":0,"gradientstart":"00FF00","gradientend":"FF00FF","shadow":1,"shadowcolor":"FFFF00","shadowblur":3,"shadowx":1,"shadowy":1,"spacing":2,"vertical":0}
    	//样式三 {"fonttype":"\u5fae\u8f6f\u96c5\u9ed1","fontsize":"40","color":"0000FF","bold":1,"oblique":0,"linewidth":0,"strokecolor":"FFFF00","gradient":1,"gradientstart":"1FAFFF","gradientend":"0000FF","shadow":0,"shadowcolor":"FFFF00","shadowblur":3,"shadowx":1,"shadowy":1,"spacing":2,"vertical":0}
    	//样式四 {"fonttype":"\u65b9\u6b63\u8212\u4f53","fontsize":"40","color":"00FF00","bold":1,"oblique":0,"linewidth":0,"strokecolor":"FF0000","gradient":0,"gradientstart":"1FAFFF","gradientend":"0000FF","shadow":1,"shadowcolor":"00FF00","shadowblur":1,"shadowx":1,"shadowy":1,"spacing":0,"vertical":0}
    	
    	//修改后
    	//样式一 {"fonttype":"\u96b6\u4e66","fontsize":"40","color":"FFFF00","bold":"","oblique":"","linewidth":"1","strokecolor":"FF0000","gradient":0,"gradientstart":"000000","gradientend":"000000","shadow":0,"shadowcolor":"000000","shadowblur":0,"shadowx":0,"shadowy":0,"spacing":2,"linespacing":5,"vertical":0}
    	//样式二 {"fonttype":"\u534e\u6587\u7425\u73c0","fontsize":"40","color":"FF0000","bold":"","oblique":"","linewidth":"0","strokecolor":"FFFF00","gradient":0,"gradientstart":"00FF00","gradientend":"FF00FF","shadow":1,"shadowcolor":"FFFF00","shadowblur":3,"shadowx":1,"shadowy":1,"spacing":2,"linespacing":5,"vertical":0}
    	//样式三 {"fonttype":"\u5fae\u8f6f\u96c5\u9ed1","fontsize":"40","color":"0000FF","bold":1,"oblique":0,"linewidth":0,"strokecolor":"FFFF00","gradient":1,"gradientstart":"1FAFFF","gradientend":"0000FF","shadow":0,"shadowcolor":"FFFF00","shadowblur":3,"shadowx":1,"shadowy":1,"spacing":2,"linespacing":5,"vertical":0}
    	//样式四 {"fonttype":"\u65b9\u6b63\u8212\u4f53","fontsize":"40","color":"00FF00","bold":1,"oblique":0,"linewidth":0,"strokecolor":"FF0000","gradient":0,"gradientstart":"1FAFFF","gradientend":"0000FF","shadow":1,"shadowcolor":"00FF00","shadowblur":1,"shadowx":1,"shadowy":1,"spacing":0,"linespacing":5,"vertical":0}
    	$tempData = I('data');
    	$content = I('content');//文字内容
    	$tempData = htmlspecialchars_decode($tempData);
    	$tempData = json_decode($tempData, true);
    	//print_r($tempData);
    	$content = str_replace(array("\r\n", "\n", "\r"), "%0D%0A", $content);
    	$data = array();
    	$data['text'] = $content?$content:"";//文字内容
    	$data['fonttype'] = $tempData['fonttype']?$tempData['fonttype']:"微软雅黑";//字体设置
    	$data['fontsize'] = $tempData['fontsize']?$tempData['fontsize']:"12";//字体大小
    	$data['color'] = $tempData['color']?$tempData['color']:"FF0000";//颜色
    	$data['bold'] = $tempData['bold']?$tempData['bold']:"";//粗体
    	$data['oblique'] = $tempData['oblique']?$tempData['oblique']:"";//斜体
    	$data['linewidth'] = $tempData['linewidth']?$tempData['linewidth']:0;//描边
    	$data['strokecolor'] = $tempData['strokecolor']?$tempData['strokecolor']:"";//描边颜色
    	$data['gradient'] = $tempData['gradient']?$tempData['gradient']:0;//渐变色是否启用，1为启用
    	$data['gradientstart'] = $tempData['gradientstart']?$tempData['gradientstart']:"000000";//开始渐变颜色
    	$data['gradientend'] = $tempData['gradientend']?$tempData['gradientend']:"000000";//结束渐变颜色
    	$data['shadow'] = $tempData['shadow']?$tempData['shadow']:0;//阴影是否启用，1为启用
    	$data['shadowcolor'] = $tempData['shadowcolor']?$tempData['shadowcolor']:"000000";//阴影颜色
    	$data['shadowblur'] = $tempData['shadowblur']?$tempData['shadowblur']:0;//模糊
    	$data['shadowx'] = $tempData['shadowx']?$tempData['shadowx']:0;//模糊x轴
    	$data['shadowy'] = $tempData['shadowy']?$tempData['shadowy']:0;//模糊y轴
    	$data['spacing'] = $tempData['spacing']?$tempData['spacing']:0;//间距大小
    	$data['linespacing'] = $tempData['linespacing']?$tempData['linespacing']:5;//行间距
    	$data['vertical'] = $tempData['vertical']?$tempData['vertical']:0;//排版(0,1)0为横排，1为竖排
    	$this->assign('data', $data);
    	$this->display();
    }
    
    /**
     * 邀请好友
     * http://gl.dev/index.php?m=home&c=share&a=invite
     */
    public function invite(){
    	$mobile = I('mobile');//手机号
    	$downfile = "/Apps/share/apk/LuckyLockScreen.apk";//下载路径
    	$param = array(
    		'where' => array(
    			'mobile'=>$mobile
    		),
    		'field' => 'id,mobile,invitedcode',
    	);
    	$resUser = D('User')->getUserInfo($param);
    	$invitedcode = '';
    	if($resUser){
    		$invitedcode = $resUser['invitedcode'];//
    	}
    	$resVersion = M('gl_versions')->where()->order('update_time desc')->find();
    	if($resVersion){
    		$android_version = "V" . $resVersion['value'];
    	}else{
    		$android_version = "V2.0";
    	}
    	$iswx = is_wx_request();
    	$this->assign('downfile', $downfile);
    	$this->assign('invitedcode', $invitedcode);
    	$this->assign('android_version', $android_version);
    	$this->assign('iswx', $iswx);
    	$this->display();
    }
    
    /**
     * 统计用户参加的总数
     * @param string $id
     * @return number
     */
    protected function _getRecommendStatDistinctCount($id){
    	$sql = "SELECT COUNT(DISTINCT(`user_id`)) AS cc FROM `gl_recommend_share` WHERE `rec_id`=" . $id ." LIMIT 1";
    	$muInfo = D('RecommendShare')->getQuery($sql);
    	return $muInfo[0]['cc'];
    }
    
}