<?php
namespace Home\Controller;
use Think\Controller;
class AppController extends CommonController {
	
	/**
	 * 更新版本
	 */
    public function updateVersion(){
    	$res = M('gl_versions')->where()->order('update_time desc')->find();
    	if($res){
			$this->returnApiData ($res );
		}else{
			$this->returnApiMsg ( '1059', '没有数据' );
		}
		exit();
    }
    
    /**
     * 单页面
     */
    public function sp(){
    	$name = addslashes(I('name'));
    	//S('app_sp_' . $name, null);
    	// @todo
    	//$ret = S('app_sp_' . $name);
    	if(!$ret){
    		$ret = D('Single')->getInfo(array('field'=>'id,title,ename as name,content', 'where'=>array('ename'=>$name)));
    		$ret['content'] = htmlspecialchars_decode($ret['content']);
    	    S('app_sp_' . $name, $ret, 3600);
    	}
    	$this->assign('ret', $ret);
    	$this->display();
    }
    
    /**
     * 单页面列表
     */
    public function pagelist(){
    	$name = addslashes(I('name', ''));
    	$ret = D('SingleList')->getList(array('field'=>'id,title,ename', 'where'=>array('ename'=>$name)));
    	if($ret){
    		foreach($ret as $k=>$v){
    			$ret[$k]['url'] = C('GL_HOST_URL') . '/index.php?m=home&c=app&a=pagedetail&id=' . $v['id'];
    		}
    	}
    	$this->returnApiData(array('items'=>$ret));
    }
    
    /**
     * 单页面详情
     */
    public function pagedetail(){
    	$id = I('id');
    	$ret = D('Single')->getInfo(array('field'=>'id,title,ename as name,content', 'where'=>array('sid'=>$id)));
    	$ret['content'] = htmlspecialchars_decode($ret['content']);
    	$this->assign('ret', $ret);
    	$this->display('sp');
    }
    
    /**
     * 充值回调方法
     */
    public function notify_url(){
    	$this->_logs("支付宝进入回调地址");
    	$alipay_config = C('alipay_config');
    	$config = array();
    	$this->_logs(array("回调过来的值", $_POST));
    	require_once(APP_ROOT."/Apps/Home/Library/alipay/AopClient.php");
    	$aop = new \Home\Library\alipay\AopClient;
    	$aop->alipayrsaPublicKey = $alipay_config['alipayrsaPublicKey'];
    	//此处验签方式必须与下单时的签名方式一致
    	$result = $aop->rsaCheckV1($_POST, NULL, "RSA");
    	//验签通过后再实现业务逻辑，比如修改订单表中的支付状态。
    	/**
    	 * ①验签通过后核实如下参数out_trade_no、total_amount、seller_id
    	 * ②修改订单表
    	 */
    	//$seller_id = $_POST['seller_id'];
    	$this->_logs(array("支付宝验证签名开始:", $result));
    	if($result){// && ($seller_id == $_POST['seller_id'])
    	//if(true){
    		$flag = true;
    		$model = D("PayOrder");//充值记录表
    		$modelUserConsume = D("UserConsume");//收支记录表
    		//$modelUserRevenueRank = D("UserRevenueRank");
    		$modelMessage = D("Message");//信息通知表
    		$out_trade_no = $_POST['out_trade_no'];
    		$total_amount = $_POST['total_amount'];//获取订单的金额
    		$bs = getSystemConfig("03", "01");//倍数
    		$coin = (int)($total_amount * $bs);//最终得到的金币
    		$nowDate = date("Y-m-d H:i:s",time());//当前时间
    		//查询订单
    		$payOrderInfo = $model->getInfo(array('where'=>array("order_id"=>$out_trade_no)));
    		$model->startTrans();//任务事务开启
    		//商户订单号
    		//$out_trade_no = $_POST['out_trade_no']?$_POST['out_trade_no']:'201706141554004542603997';
    		//$total_amount = $_POST['total_amount']?$_POST['total_amount']:'25.00';//获取订单的金额
    		
    		//修改充值状态为已支付
    		$resPayOrderUpdate = $model->updateData(array("order_id"=>$out_trade_no), array("status"=>2));
    		$this->_logs(array("resPayOrderUpdate:", $resPayOrderUpdate));
    		if(!$resPayOrderUpdate){
    			$flag = false;
    		}
    		//插入充值记录表
    		/* $resUserConsumeInsert = $modelUserConsume->insertData(array('user_id'=>$payOrderInfo['user_id'], "coin"=>$coin, "type"=>"充值", "intro"=>"用户充值,充值金额:".$total_amount, "cdate"=>$nowDate));
    		$this->_logs(array("resUserConsumeInsert:", $resUserConsumeInsert));
    		if(!$resUserConsumeInsert){
    			$flag = false;
    		} */
    		//用户账户增加充值对应的金币
			/* $resUserUpdate = D("User")->updateUser(array("id"=>$payOrderInfo['user_id']), array("total_coin"=>array("exp", "`total_coin` + " . $coin), "coin"=>array("exp", "`coin` + " . $coin), "today_coin"=>array("exp", "`today_coin` + " . $coin), "udate"=>$nowDate));
    		$this->_logs(array("resUserUpdate:", $resUserUpdate));
    		if(!$resUserUpdate){
    			$flag = false;
    		} */
    		if($flag){
    			$model->commit();//任务事务提交
    			D('User')->increaseCoin($payOrderInfo['user_id'], $coin, C('RECHARGE_TYPE'), '用户充值,充值金额:'.$total_amount);
    			//发送消息提示
    			D('MessageReceive')->insertMessage(1, 'SYSTEM', "充值到账", "您的充值了" . $coin . "金币已经到账，请在收益余额中查看", $payOrderInfo['user_id']);
    			//$this->_send_message($payOrderInfo['user_id'], "充值到账", "您的充值了" . $coin . "金币已经到账，请在收益余额中查看");
    			$this->_logs("充值成功，进入事务提交了");
    			echo "success";//打印success，应答支付宝。必须保证本界面无错误。只打印了success，否则支付宝将重复请求回调地址。
    		}else{
    			$model->rollback();//任务事务回滚
    			$this->_logs("充值失败，进入事务回滚");
    			echo "failure";
    		}
    	}else{
    		$this->_logs("验证失败");
    		echo "error";
    	}
    	exit();
    }
    
    /**
     * 微信支付回调
     */
    public function wxnotify_url(){
    	require_once(APP_ROOT."/Apps/Home/Library/wxpay/WxPayApi.php");
    	libxml_disable_entity_loader(true);
    	$postStr = WxPayApi::_postData();//接受数据
    	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    	$postArr = json_decode(json_encode($postObj), true);
    	$this->_logs(array("微信微信支付返回数组:", $postArr));
    	ksort($postArr);
    	$str = WxPayApi::_ToUrlParams($postArr);
    	$str = $str . "&key=" . \Home\Library\wxpay\WxPayConfig::KEY;
    	$user_sign = strtoupper(md5($str));
    	if($user_sign == $arr['sign']){//验证成功
    		$out_trade_no = $arr['out_trade_no'];
    		$total_amount = $arr['total_fee'];//获取订单的金额
    		$flag = true;
    		$model = D("PayOrder");//充值记录表
    		$modelUserConsume = D("UserConsume");//收支记录表
    		//$modelUserRevenueRank = D("UserRevenueRank");
    		$modelMessage = D("Message");//信息通知表
    		$bs = getSystemConfig("03", "01");//倍数
    		$coin = (int)($total_amount * $bs);//最终得到的金币
    		$nowDate = date("Y-m-d H:i:s",time());//当前时间
    		//查询订单
    		$payOrderInfo = $model->getInfo(array('where'=>array("order_id"=>$out_trade_no)));
    		$model->startTrans();//任务事务开启
    		//修改充值状态为已支付
    		$resPayOrderUpdate = $model->updateData(array("order_id"=>$out_trade_no), array("status"=>2));
    		$this->_logs(array("resPayOrderUpdate:", $resPayOrderUpdate));
    		if(!$resPayOrderUpdate){
    			$flag = false;
    		}
    		//插入充值记录表
    		/* $resUserConsumeInsert = $modelUserConsume->insertData(array('user_id'=>$payOrderInfo['user_id'], "coin"=>$coin, "type"=>"充值", "intro"=>"用户充值,充值金额:".$total_amount, "cdate"=>$nowDate));
    		$this->_logs(array("resUserConsumeInsert:", $resUserConsumeInsert));
    		if(!$resUserConsumeInsert){
    			$flag = false;
    		} */
    		//用户账户增加充值对应的金币
    		/* $resUserUpdate = D("User")->updateUser(array("id"=>$payOrderInfo['user_id']), array("total_coin"=>array("exp", "`total_coin` + " . $coin), "coin"=>array("exp", "`coin` + " . $coin), "today_coin"=>array("exp", "`today_coin` + " . $coin), "udate"=>$nowDate));
    		$this->_logs(array("resUserUpdate:", $resUserUpdate));
    		if(!$resUserUpdate){
    			$flag = false;
    		} */
    		if($flag){
    			$model->commit();//任务事务提交
    			D('User')->increaseCoin($payOrderInfo['user_id'], $coin, C('RECHARGE_TYPE'), '用户充值,充值金额:'.$total_amount);
    			//发送消息提示
    			//$this->_send_message($payOrderInfo['user_id'], "充值到账", "您的充值了" . $coin . "金币已经到账，请在收益余额中查看");
    			D('MessageReceive')->insertMessage(1, 'SYSTEM', "充值到账", "您的充值了" . $coin . "金币已经到账，请在收益余额中查看", $payOrderInfo['user_id']);
    			$this->_logs("充值成功，进入事务提交了");
    			echo "success";//打印success，应答支付宝。必须保证本界面无错误。只打印了success，否则支付宝将重复请求回调地址。
    		}else{
    			$model->rollback();//任务事务回滚
    			$this->_logs("充值失败，进入事务回滚");
    			echo "failure";
    		}
    	}else{
    		$this->_logs("验证失败");
    		echo "error";
    	}
    	exit();
    }
    
    /**
     * 提取回调方法
     */
    public function exnotify_url(){
    	header("Content-type:text/html;charset=utf-8");
    	require_once(APP_ROOT."/Apps/Home/Library/exalipay/alipay.config.php");
    	require_once(APP_ROOT."/Apps/Home/Library/exalipay/alipay_notify.class.php");
    	
    	//require_once(APPPATH.'libraries/alipay/alipay.config.php');
    	//require_once(APPPATH.'libraries/alipay/alipay_notify.class.php');
    	//计算得出通知验证结果
    	$alipayNotify = new \Home\Library\alipay\AlipayNotify($alipay_config);
    	$verify_result = $alipayNotify->verifyNotify();
    	if($verify_result) {//验证成功
    		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    		//请在这里加上商户的业务逻辑程序代
    	
    		 
    		//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    		 
    		//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
    		 
    		//批量付款数据中转账成功的详细信息
    	
    		$success_details = $_POST['success_details'];
    		//log_message('error', "支付宝正确结果: ".$success_details);
    		$this->_logs(array("支付宝正确结果: ", $success_details));
    		if($success_details){
    			$pl_details=explode("|",$success_details);
    			foreach($pl_details as $plk=>$plv){
    				$pl_info=explode("^",$plv);
    	
    				$exchangeID.=$pl_info[0].",";
    	
    			}
    			$exchangeID=rtrim($exchangeID,",");
    			//$res=$this->wealth_model->updateIDS($exchangeID);
    			$res = D("UserExchangeRecord")->updateIDS($exchangeID);
    		}
    		//die;
    		//$res=$this->wealth_model->getConversionByIDS($ids);
    		//批量付款数据中转账失败的详细信息
    		$fail_details = $_POST['fail_details'];
    		log_message('error', "支付宝错误结果: ".$fail_details);
    		if($fail_details){
    			$pls_details=explode("|",$fail_details);
    			foreach($pls_details as $plsk=>$plsv){
    				$pls_info=explode("^",$plsv);
    	
    				$pls_exchangeID.=$pls_info[0].",";
    	
    			}
    	
    			$pls_exchangeID=rtrim($pls_exchangeID,",");
    			//$pls_res=$this->wealth_model->updateplsIDS($pls_exchangeID);
    			$pls_res = D("UserExchangeRecord")->updateplsIDS($pls_exchangeID);
    		}
    		//判断是否在商户网站中已经做过了这次通知返回的处理
    		//如果没有做过处理，那么执行商户的业务程序
    		//如果有做过处理，那么不执行商户的业务程序
    		 
    		echo "success";		//请不要修改或删除
    	
    		//调试用，写文本函数记录程序运行情况是否正常
    		//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    	
    		//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
    		 
    		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    	}
    	else {
    		//验证失败
    		echo "fail";
    	
    		//调试用，写文本函数记录程序运行情况是否正常
    		//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    	}
    }
    
    public function return_url(){
    	
    }
    
    public function tt(){
    	/* $jpushConfig = C('JPUSH_CONFIG');
    	$client = new \JPush\Client($jpushConfig['app_key'], $jpushConfig['master_secret']);
    	$result = $client->push()
    	->setPlatform(array('android'))
    	->addAllAudience()
    	->setNotificationAlert('Hi, 我又一条从接口推送的')
    	->send();
    	print_r($result); */
    	//echo 'Result=' . json_encode($result) . $br;
    	$client = new \Home\Common\JpushApi();
    	$messageData = $client->push_notification("没错, 我是接口推送123456");
    	print_r($messageData);
    }
    
    /**
     * 
     */
    public function systemMessage(){
    	$client = new \Home\Common\JpushApi();
    	$messageData = $client->push_notification("没错, 我是接口推送");
    	print_r($messageData);
    }
    
    /**
     * 上周排行
     */
    public function lastWeekRank(){
    	//$rankData = S('my_last_week_rank');
    	//if(empty($rankData)){
    		$sql = "SELECT a.last_week AS coin, b.nickname FROM `gl_user_revenue_rank` as a left join `gl_user` as b on (a.user_id=b.id) ORDER BY a.last_week DESC LIMIT 10";
    		//$sql = "SELECT last_revenue as week_revenue, nickname FROM `gl_user` ORDER BY last_revenue DESC LIMIT 10";
    		$rankData = D('User')->getQuery($sql);
    		//S('my_last_week_rank', $rankData, 3600);
    	//}
    	$result['items'] = $rankData;
    	$this->returnApiData($result);
    }
    
    /**
     * 本周排行
     */
    public function weekRank(){
    	//$rankData = S('my_week_rank');
    	//if(empty($rankData)){
    		$sql = "SELECT a.week_revenue AS coin, b.nickname FROM `gl_user_revenue_rank` as a left join `gl_user` as b on (a.user_id=b.id) ORDER BY a.week_revenue DESC LIMIT 10";
    		//$sql = "SELECT last_revenue as week_revenue, nickname FROM `gl_user` ORDER BY last_revenue DESC LIMIT 10";
    		$rankData = D('User')->getQuery($sql);
    		//S('my_week_rank', $rankData, 3600);
    	//}
    	$result['items'] = $rankData;
    	$this->returnApiData($result);
    }
    
    /**
     * 总排行
     */
    public function totalRank(){
    	//$rankData = S('my_total_rank');
    	//if(empty($rankData)){
    		$sql = "SELECT a.total_revenue AS coin, b.nickname FROM `gl_user_revenue_rank` as a left join `gl_user` as b on (a.user_id=b.id) ORDER BY a.total_revenue DESC LIMIT 10";
    		//$sql = "SELECT coin as total_revenue, nickname FROM `gl_user` ORDER BY coin DESC LIMIT 10";
    		$rankData = D('User')->getQuery($sql);
    		//S('my_total_rank', $rankData, 3600);
    	//}
    	$result['items'] = $rankData;
    	$this->returnApiData($result);
    }

    /**
     * 有米调用方法
     */
    public function ym()
    {
        $ymInfo = I("get.");//有米
        write_log(array('有米传入的参数：', $ymInfo, $_GET));
        $dev_server_secret = C('YOUMI_DEV_SERVER_SECRET');//有米服务器密钥
        $order = $ymInfo['order'];
        $app = $ymInfo['app'];
        $user = urldecode($ymInfo['user']);
        $chn = $ymInfo['chn'];
        $ad = urldecode($ymInfo['ad']);
        $points = $ymInfo['points'];
        $sig = substr(md5($dev_server_secret . "||" . $order . "||" . $app . "||" . $user . "||" . $chn . "||" . $ad . "||" . $points), 12, 8);//生成加密串
        //检查sig
        if (empty($order)) {
            http_code(407);//检查订单是否为空，下次再来
        }
        if (empty($user)) {
            http_code(407);//检查用户id是否为空，下次再来
        }
        if ($sig === $ymInfo['sig']) {
            $ymLock = S('youmi_ym_' . $order);
            if (empty($ymLock)) {
                S('youmi_ym_' . $order, 1, 600);
                $youmiInfo = D('Youmi')->getInfo(array('field' => 'id,order,user,ad', 'where' => array('order' => $order)));
                if (empty($youmiInfo)) {
                    $coinBs = getSystemConfig('03', '01');//积分兑换的倍数 1000
                    $youmiBs = 100;//有米兑换的倍数 100
                    $coin = (int)($points * ($coinBs / $youmiBs));
                    write_log(array('有米的得到金币：', $coinBs, $youmiBs, $coin));
                    $param = array(
                        'order' => $order,
                        'app' => $app,
                        'ad' => $ad,
                        'user' => $user,
                        'chn' => $chn,
                        'points' => $points,
                        'coin' => $coin,
                        'adid' => $ymInfo['adid'],
                        'pkg' => $ymInfo['pkg'],
                        'device' => $ymInfo['device'],
                        'xg_time' => $ymInfo['time'],
                        'price' => $ymInfo['price'],
                        'trade_type' => $ymInfo['trade_type'],
                        'create_time' => time(),
                    );
                    write_log(array('有米的参数：', $param));
                    $res = D('Youmi')->insertData($param);
                    S('youmi_ym_' . $order, null);//解锁
                    write_log(array('有米插入数据是否成功：', $res));
                    D('User')->increaseCoin($user, $coin, '收入', '完成积分墙' . $ad . '的任务获得' . $coin . '积分');//给用户添加金币
                    D('MessageReceive')->insertMessage(1, 'SYSTEM', '完成积分墙' . $ad . '任务', '完成积分墙' . $ad . '的任务获得' . $coin . '积分', $user);//给用户通知完成积分墙信息
                    http_code(200);//返回200成功
                } else {
                    S('youmi_ym_' . $order, null);//解锁
                    http_code(403);//判断订单重复，不来了
                }
            } else {
                http_code(407);//有锁，下次再来
            }
        } else {
            http_code(417);//验证失败，下次再来
        }
    }
}