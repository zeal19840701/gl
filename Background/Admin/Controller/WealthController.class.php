<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * 财富管理
 */
class WealthController extends BaseController
{
    /**
     * 兑换记录
     * 
     */
    public function conversion()
    {
    	$param = array();
    	$key = I("key");//传入key值
    	$p = I('p', 1);
    	$listRows = 20;
    	$where = array();
    	//$where['flag'] = 0;//1表示插入过数据。0表示没有
    	if($key === ""){
    		//$model = D('Acquisition');
    		$param['where'] = $where;
    	}else{
    		$where['mobile'] = array('like',"%$key%");
    		$where['exchange_account'] = array('like',"%$key%");
    		$where['exchange_name'] = array('like',"%$key%");
    		$where['_logic'] = 'or';
    		$param['where'] = $where;
    		//$model = D('Acquisition')->where($where);
    	}
    	 
    	$model = D('UserExchangeRecord');
    	$total = $model->getCount($param['where']);
    	
    	$page = new \Admin\Common\Page($total, $listRows, array('key'=>$key));// 加载分页类
    	//$param['limit'] = $page->firstRow.','.$page->listRows;
    	$param['limit'] = ($p-1) * $listRows;
    	//echo $page->firstRow . ' ' .$page->listRows;
    	$getIdArr = $model->getList(array('field'=>'*', 'where'=>$param['where'], 'limit'=>$param['limit'] . "," . $listRows));
    	$getIdMoneySum = 0;
    	$getIdGoldSum = 0;
    	if($getIdArr){
    		foreach($getIdArr as $k=>$v){
    			$getIdMoneySum += $v['exchange_money'];
    			$getIdGoldSum += $v['exchange_gold'];
    		}
    	}
    	/* $getIdList = array();
    	if($getIdArr){
    		foreach($getIdArr as $k=>$v){
    			$getIdList[] = $v['id'];
    		}
    		$param['where']['id'] = array('in', $getIdList);
    		$newsList = $model->getList($param);
    	}
    	if($newsList){
    		foreach($newsList as $k=>$v){
    			$newsList[$k]['content'] = strip_tags($v['content']);
    			$newsList[$k]['original'] = $v['original']==1?'原创':'非原创';
    		}
    	}else{
    		$newsList = array();
    	} */
    	
    	$show = $page->show();
    	//print_r($newsList);exit;
    	$this->assign('model', $getIdArr);
    	$this->assign('page', $show);
    	$this->assign('key', $key);
    	$this->assign('getIdMoneySum', $getIdMoneySum);
    	$this->assign('getIdGoldSum', $getIdGoldSum);
    	$this->display();
    	
    }
    
    public function alipay(){
    	$qStr = I('qStr');
    	$id = I('id');
    	if(!$id){
    		$this->error('id不能为空！');
    		exit();
    	}
    	$res = D('UserExchangeRecord')->getInfo(array('where'=>array('id'=>$id)));
    	$base_url = C('WEB_FRONT_URL');
    	/* $CFG =& load_class('Config', 'core');
    	$base_url = $CFG->item('web_url'); */
    	$nowDate = date("Y-m-d H:i:s", time());
    	$account_alipay = D('SystemConfig')->getInfo('02', '06');
    	if (!$account_alipay){
    		$this->error('系统参数：支付宝账号未设定！', "/bg.php?m=Admin&c=wealth&a=conversion", 5);
    		exit();
    	}
    	if($res['status']!=4){
    		// 更新中间状态
    		$result = D('UserExchangeRecord')->updateIngStatus($id, $this->userid);
    		if (!$result){
    			header("Content-type:text/html;charset=utf-8");
    			//echo "更新中间状态失败！";
    			$this->error("更新中间状态失败！", "/bg.php?m=Admin&c=wealth&a=conversion", 5);
    			exit;
    		}
    		if (($res['exchange_channel'] == 3) || ($res['exchange_channel'] == 4)){
    			header("Content-type:text/html;charset=gbk");
    			$accountInfo = $this->gettariffeAccount();
    			$output = $this->tariffe($res, $accountInfo);
    			$resTariffe = $this->_xmlParseToArray($output);
    			$oFeiPay = C("OFEIPAY");
    			if(!$resTariffe['items'][3]['value']){
    				$resUserUpdate = D("User")->updateData(array("id"=>$res['user_id'], "coin"=>array("egt", $res['exchange_gold'])), array("use_coin"=>array("exp", "`use_coin`+" . $res['exchange_gold']), "coin"=>array("coin", "`coin`-" . $res['exchange_gold'])));
    				if($resUserUpdate){
    					D("UserExchangeRecord")->updateData(array("id"=>$id), array("status"=>4));//原来成功状态为2，现在为4
    					//D("UserConsume")->insertData(array("user_id"=>$res['user_id'], "coin"=>$res['exchange_gold'], "type"=>"提现", "intro"=>"提现成功了"));
						D("UserConsume")->UserConsume($res['user_id'], $res['exchange_gold'], 0, "提现", "提现成功了");
    					/* $msgInfo = array(
    							'info_type' => 1,//系统信息
    							'receiver' => $res['mobile'],
    							'info_title' => "提现到账",
    							"info_content" => "你的兑换已经到帐，请在我的->收益余额->提现->提现记录中查看兑换详情。",
    							"create_date" => $nowDate,
    							"creator" => "SYSTEM",
    							"update_date" => $nowDate,
    							"is_del" => 0,
    					);
    					D("Message")->insertData($msgInfo); */
    					D('MessageReceive')->insertMessage(1, 'SYSTEM', '提现到账', '你的兑换已经到帐，请在我的->收益余额->提现->提现记录中查看兑换详情。', $res['user_id']);
    				}
    				$this->success($oFeiPay[$resTariffe['items'][3]['value']], "/bg.php?m=Admin&c=wealth&a=conversion", 5);
    			}else{
    				$this->error($oFeiPay[$resTariffe['items'][3]['value']], "/bg.php?m=Admin&c=wealth&a=conversion", 5);
    			}
    			//echo $output;
    			return;
    		}
    		header("Content-type:text/html;charset=utf-8");
    		/**************************请求参数**************************/
    		require_once(APP_ROOT."/Background/Admin/Library/alipay/alipay.config.php");
    		require_once(APP_ROOT."/Background/Admin/Library/alipay/alipay_submit.class.php");
    		//服务器异步通知页面路径
    		$notify_url = $base_url."/home/app/exnotify_url";//异步地址在前台
    		//需http://格式的完整路径，不允许加?id=123这类自定义参数
    		//付款账号
    		$email = $account_alipay;
    		//必填
    		//付款账户名
    		$account_name = "上海珍岛信息技术有限公司";
    		//必填，个人支付宝账号是真实姓名公司支付宝账号是公司名称
    		//付款当天日期
    		$pay_date = date("Ymd",time());
    		//必填，格式：年[4位]月[2位]日[2位]，如：20100801
    		//批次号
    		$batch_no = date("Ymd",time()).time().rand(100000,999999);
    		//必填，格式：当天日期[8位]+序列号[3至16位]，如：201008010000001
    		//付款总金额
    		$batch_fee = $res['exchange_money'];
    		//必填，即参数detail_data的值中所有金额的总和
    		//付款笔数
    		$batch_num = 1;
    		//必填，即参数detail_data的值中，“|”字符出现的数量加1，最大支持1000笔（即“|”字符出现的数量999个）
    		//付款详细数据
    		//$detail_data = "20100801^18701724876^张森^0.01^Hello world！";
    		$detail_data = $res['exchange_id']."^".$res['exchange_account']."^".$res['exchange_name']."^".$res['exchange_money']."^"."支付兑换";
    		//必填，格式：流水号1^收款方帐号1^真实姓名^付款金额1^备注说明1|流水号2^收款方帐号2^真实姓名^付款金额2^备注说明2....
    		/************************************************************/
    		//构造要请求的参数数组，无需改动
    		$parameter = array(
    				"service" => "batch_trans_notify",
    				"partner" => trim($alipay_config['partner']),
    				"notify_url"	=> $notify_url,
    				"email"	=> $email,
    				"account_name"	=> $account_name,
    				"pay_date"	=> $pay_date,
    				"batch_no"	=> $batch_no,
    				"batch_fee"	=> $batch_fee,
    				"batch_num"	=> $batch_num,
    				"detail_data"	=> $detail_data,
    				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
    		);
    		//print_r($parameter);exit;
    		$alipaySubmit = new \Admin\Library\alipay\AlipaySubmit($alipay_config);
    		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
    		echo $html_text;
    	}else{
    		echo ('支付失败！');
    	}
    	exit();
    }
    
    public function alipay_multi(){
    	$qStr = I('qStr');
    	$ids = I('ids');
    	if(!$ids){
    		$this->error('id不能为空！');
    		exit();
    	}
    	if(substr($ids, -1)==','){
    		$ids = substr($ids, 0,-1);
    	}
    	$where['id'] = array('in', $ids);
    	$res = D('UserExchangeRecord')->getInfo(array('where'=>$where));
    	$base_url = C('WEB_FRONT_URL');
    	/* $CFG =& load_class('Config', 'core');
    	$base_url = $CFG->item('web_url'); */
    	
    	
    	
    	/* $sysinfo = $this->application_model->GetAwardBySystem('03','07');
    	if (!$sysinfo){
    		jsRedirect("系统参数：支付宝账号未设定！", '', 0);
    	}
    	$account_alipay = $sysinfo['value']; */
    	$account_alipay = D('SystemConfig')->getInfo('02', '06');
    	if (!$account_alipay){
    		$this->error('系统参数：支付宝账号未设定！', "/bg.php?m=Admin&c=wealth&a=conversion", 5);
    		exit();
    	}
    	//$id_arr = explode(',', $ids);
    	//$res=$this->wealth_model->getConversionByIDS($ids);
    	$res = D('UserExchangeRecord')->getConversionByIDS($ids);
    	if($res){
    		//$result = $this->wealth_model->updatIngIDS($ids);
    		/* if (!$result){
    			header("Content-type:text/html;charset=utf-8");
    			echo "更新中间状态失败！";exit;
    		} */
    		$accountInfo = $this->gettariffeAccount();
    		$output = "";
    		$tariffeCount = 0;
    		foreach($res as $k=>$v){
    			// 话费场合
    			if($v['exchange_channel']==3){
    				$output = $output . $this->tariffe($res, $accountInfo);
    				$output = $output . '<br>';
    				$tariffeCount ++;
    			}
    		}
    	
    		if ($tariffeCount == count($res) && $tariffeCount > 0){
    			header("Content-type:text/html;charset=gbk");
    			echo $output;
    			return;
    		}
    	
    	
    		header("Content-type:text/html;charset=utf-8");
    	
    		/**************************请求参数**************************/
    		/* require_once(APPPATH.'libraries/alipay/alipay.config.php');
    		require_once(APPPATH.'libraries/alipay/alipay_submit.class.php');
    		//服务器异步通知页面路径
    		$notify_url = $base_url."index.php/web/notify_url"; */
    		
    		require_once(APP_ROOT."/Background/Admin/Library/alipay/alipay.config.php");
    		require_once(APP_ROOT."/Background/Admin/Library/alipay/alipay_submit.class.php");
    		//服务器异步通知页面路径
    		$notify_url = $base_url."/home/app/exnotify_url";//异步地址在前台
    		
    		//需http://格式的完整路径，不允许加?id=123这类自定义参数
    		//付款账号
    		$email = $account_alipay;
    		//必填
    		//付款账户名
    		$account_name = "上海珍岛信息技术有限公司";
    		//必填，个人支付宝账号是真实姓名公司支付宝账号是公司名称
    		//付款当天日期
    		$pay_date = date("Ymd",time());
    		//必填，格式：年[4位]月[2位]日[2位]，如：20100801
    		//批次号
    		$batch_no = date("Ymd",time()).time().rand(100000,999999);
    		//必填，格式：当天日期[8位]+序列号[3至16位]，如：201008010000001
    	
    		foreach($res as $k=>$v){
    			if($v['exchange_channel']==1){
    				$batch_fee+=$v['exchange_money'];
    				$detail_data.=$v['exchange_id']."^".$v['exchange_account']."^".$v['exchange_name']."^".$v['exchange_money']."^"."支付兑换"."|";
    			}
    		}
    	
    		$batch_num=count($res) - $tariffeCount;
    	
    		$detail_data=rtrim($detail_data,"|");
    		//付款总金额
    		//$batch_fee = $res['exchangeMoney'];
    		//必填，即参数detail_data的值中所有金额的总和
    		//付款笔数
    		//$batch_num = 1;
    		//必填，即参数detail_data的值中，“|”字符出现的数量加1，最大支持1000笔（即“|”字符出现的数量999个）
    		//付款详细数据
    		//$detail_data = "20100801^18701724876^张森^0.01^Hello world！";
    		//$detail_data = $res['exchangeID']."^".$res['exchangeAccount']."^".$res['exchangeName']."^".$res['exchangeMoney']."^"."支付兑换";
    		//必填，格式：流水号1^收款方帐号1^真实姓名^付款金额1^备注说明1|流水号2^收款方帐号2^真实姓名^付款金额2^备注说明2....
    		/************************************************************/
    		//构造要请求的参数数组，无需改动
    		$parameter = array(
    				"service" => "batch_trans_notify",
    				"partner" => trim($alipay_config['partner']),
    				"notify_url"	=> $notify_url,
    				"email"	=> $email,
    				"account_name"	=> $account_name,
    				"pay_date"	=> $pay_date,
    				"batch_no"	=> $batch_no,
    				"batch_fee"	=> $batch_fee,
    				"batch_num"	=> $batch_num,
    				"detail_data"	=> $detail_data,
    				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
    		);
    	
    		//建立请求
    		$alipaySubmit = new \Admin\Library\alipay\AlipaySubmit($alipay_config);
    		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
    		echo $html_text;
    	}else{
    		//jsRedirect("操作失败！", '', 0);
    		$this->error('操作失败！');
    		exit();
    	}
    }
    
    public function cancellation(){
    	$id = I("id");
    	if(!$id){
    		echo ('id不能为空！');
    		exit();
    	}
    	$res = D('UserExchangeRecord')->getInfo(array('where'=>array('id'=>$id)));
    	if($res){
    		if($res['status'] != 4 && $res['status'] != 5 && $res['status'] != 6){
    			$r = D("UserExchangeRecord")->updateData(array('id'=>$id), array("status"=>6));
    			if($r){
    				$userInfo = D("User")->getInfo(array("field"=>"coin", "where"=>array("id"=>$r['id'])));
    				D("User")->updateData(array("id"=>$r['user_id']), array("coin"=>array("exp", "`coin`+" . $r['exchange_gold']), "use_coin"=>array("exp", "`use_coin`-". $r['exchange_gold'])));
    				/*$surplus_coin = (int)($userInfo['coin']) + (int)($r['exchange_gold']);
    				$param = array(
    					"user_id" => $r['user_id'],
    					"coin" => $r['exchange_gold'],
    					"surplus_coin" => $surplus_coin,
    					"type" => "收入",
    					"intro" => "提现打回的金币",
    					"cdate" => date("Y-m-d H:i:s", time()),
    				);*/
    				//D("UserConsume")->insertData($param);
					D("UserConsume")->UserConsume($r['user_id'], $r['exchange_gold'], $userInfo['coin'], "收入", "提现打回的金币");
    				echo "success";
    			}else{
    				echo "作废失败!";
    			}
    		}else{
    			echo "作废失败!";
    		}
    	}else{
    		echo "记录不存在！";
    	}
    	exit();
    }
    
    /**
     * 冻结
     */
    public function freeze(){
    	$id = I("id");
    	if(!$id){
    		echo ('id不能为空！');
    		exit();
    	}
    	$res = D('UserExchangeRecord')->getInfo(array('where'=>array('id'=>$id)));
    	if($res){
    		if($res['status'] != 4 && $res['status'] != 5 && $res['status'] != 6){
    			$r = D("UserExchangeRecord")->updateData(array('id'=>$id), array("status"=>6));
    			if($r){
    				//$userInfo = D("User")->getInfo(array("field"=>"coin", "where"=>array("id"=>$r['id'])));
    				//D("User")->updateData(array("id"=>$r['user_id']), array("coin"=>array("exp", "`coin`+" . $r['exchange_gold']), "use_coin"=>array("exp", "`use_coin`-". $r['exchange_gold'])));
    				//D("UserConsume")->UserConsume($r['user_id'], $r['exchange_gold'], $userInfo['coin'], "收入", "提现打回的金币");
    				echo "success";
    			}else{
    				echo "作废失败!";
    			}
    		}else{
    			echo "作废失败!";
    		}
    	}else{
    		echo "记录不存在！";
    	}
    	exit();
    }
    
    /**
     * reset
     */
    public function reset(){
    	$id = I("id");
    	if(!$id){
    		echo ('id不能为空！');
    		exit();
    	}
    	$res = D('UserExchangeRecord')->getInfo(array('where'=>array('id'=>$id)));
    	if($res){
    		if($res['status'] == 3){
    			$r = D("UserExchangeRecord")->updateData(array('id'=>$id), array("status"=>1));
    			if($r){
    				//$userInfo = D("User")->getInfo(array("field"=>"coin", "where"=>array("id"=>$r['id'])));
    				//D("User")->updateData(array("id"=>$r['user_id']), array("coin"=>array("exp", "`coin`+" . $r['exchange_gold']), "use_coin"=>array("exp", "`use_coin`-". $r['exchange_gold'])));
    				//D("UserConsume")->UserConsume($r['user_id'], $r['exchange_gold'], $userInfo['coin'], "收入", "提现打回的金币");
    				echo "success";
    			}else{
    				echo "重置失败!";
    			}
    		}else{
    			echo "重置失败!";
    		}
    	}else{
    		echo "记录不存在！";
    	}
    	exit();
    }
    
    /**
     * 审核通过
     */
    public function pass(){
    	$id = I("id");
    	if(!$id){
    		echo ('id不能为空！');
    		exit();
    	}
    	$res = D('UserExchangeRecord')->getInfo(array('where'=>array('id'=>$id)));
    	if($res){
    		if($res['status'] == 0){
    			$r = D("UserExchangeRecord")->updateData(array('id'=>$id), array("status"=>1));
    			if($r){
    				//$userInfo = D("User")->getInfo(array("field"=>"coin", "where"=>array("id"=>$r['id'])));
    				//D("User")->updateData(array("id"=>$r['user_id']), array("coin"=>array("exp", "`coin`+" . $r['exchange_gold']), "use_coin"=>array("exp", "`use_coin`-". $r['exchange_gold'])));
    				//D("UserConsume")->UserConsume($r['user_id'], $r['exchange_gold'], $userInfo['coin'], "收入", "提现打回的金币");
    				echo "success";
    			}else{
    				echo "重置失败!";
    			}
    		}else{
    			echo "重置失败!";
    		}
    	}else{
    		echo "记录不存在！";
    	}
    	exit();
    }
    
    /**
     * 拒绝
     */
    public function refuse(){
    	$id = I("id");
    	if(!$id){
    		echo ('id不能为空！');
    		exit();
    	}
    	$res = D('UserExchangeRecord')->getInfo(array('where'=>array('id'=>$id)));
    	if($res){
    		if($res['status'] == 0){
    			$r = D("UserExchangeRecord")->updateData(array('id'=>$id), array("status"=>2));
    			if($r){
    				//$userInfo = D("User")->getInfo(array("field"=>"coin", "where"=>array("id"=>$r['id'])));
    				//D("User")->updateData(array("id"=>$r['user_id']), array("coin"=>array("exp", "`coin`+" . $r['exchange_gold']), "use_coin"=>array("exp", "`use_coin`-". $r['exchange_gold'])));
    				//D("UserConsume")->UserConsume($r['user_id'], $r['exchange_gold'], $userInfo['coin'], "收入", "提现打回的金币");
    				echo "success";
    			}else{
    				echo "重置失败!";
    			}
    		}else{
    			echo "重置失败!";
    		}
    	}else{
    		echo "记录不存在！";
    	}
    	exit();
    }
    
    public function lation(){
    	$id = I("id");
    	if(!$id){
    		echo ('id不能为空！');
    		exit();
    	}
    	$res = D('UserExchangeRecord')->getInfo(array('where'=>array('id'=>$id)));
    	if($res){
    		if($res['status'] ==3){
    			$r = D("UserExchangeRecord")->updateData(array('id'=>$id), array("status"=>0));
    			if($r){
    				echo "success";
    			}else{
    				echo "生效失败!";
    			}
    		}else{
    			echo "生效失败!";
    		}
    	}else{
    		echo "记录不存在！";
    	}
    	exit();
    }
    
    public function isdel(){
    	$id = I("id");
    	if(!$id){
    		echo ('id不能为空！');
    		exit();
    	}
    	$res = D('UserExchangeRecord')->getInfo(array('where'=>array('id'=>$id)));
    	if($res){
    		if($res['is_del'] == 0){
    			$r = D("UserExchangeRecord")->updateData(array('id'=>$id), array("is_del"=>1));
    			if($r){
    				echo "success";
    			}else{
    				echo "删除失败!";
    			}
    		}else{
    			echo "删除失败!";
    		}
    	}else{
    		echo "记录不存在！";
    	}
    	exit();
    }
    
    public function recover(){
    	$id = I("id");
    	if(!$id){
    		echo ('id不能为空！');
    		exit();
    	}
    	$res = D('UserExchangeRecord')->getInfo(array('where'=>array('id'=>$id)));
    	if($res){
    		if($res['is_del'] == 1){
    			$r = D("UserExchangeRecord")->updateData(array('id'=>$id), array("is_del"=>0));
    			if($r){
    				echo "success";
    			}else{
    				echo "删除失败!";
    			}
    		}else{
    			echo "删除失败!";
    		}
    	}else{
    		echo "记录不存在！";
    	}
    	exit();
    }
    
    /**
     * 获取话费账号
     */
    private function gettariffeAccount(){
    	$accountInfo = new \stdClass();
    	$accountInfo->tariffeAccount = getSystemConfig('02', '04');//话费支付账号
    	$accountInfo->tariffePwd = getSystemConfig('02', '05');//话费支付密码
    	return $accountInfo;
    }
    
    /**
     * 调用欧飞API
     * @param array $res
     * @param object $accountInfo
     */
    private function tariffe($res, $accountInfo){
    	$userid = $accountInfo->tariffeAccount;
    	$userpws = strtolower(md5($accountInfo->tariffePwd));
    	//快充或慢充类型   快充：140101，慢充：170101，流量充值：230101
    	if($res['exchange_channel'] == 3){
    		$cardid = 170101;
    	}else if($res['exchange_channel'] == 4){
    		$cardid = 230101;
    	}
    	//$cardid = 170101;
    	//充值金额  可选金额   快充可选面值（1、2、5、10、20、30、50、100、300） 慢充可选面值（30、50、100）
    	$cardnum = strval(intval($res['exchange_money']));
    	$sporder_time = date("YmdHis",time());
    	//根据时间生成订单号
    	$sporder_id = $res['exchange_id'];
    	//需要充值的手机号
    	$game_userid = $res['exchange_account'];
    	//默认秘钥   可联系商家修改
    	$KeyStr = "OFCARD";
    	$md5_str = md5($userid.$userpws.$cardid.$cardnum.$sporder_id.$sporder_time.$game_userid.$KeyStr);
    	$md5_str = strtoupper($md5_str);
    
    	$url = "http://api2.ofpay.com/onlineorder.do";
    	$url = $url . "?userid=".$userid;
    	$url = $url . "&userpws=".$userpws;
    	$url = $url . "&cardid=".$cardid;
    	$url = $url . "&cardnum=".$cardnum;
    	$url = $url . "&mctype=0.5";
    	$url = $url . "&sporder_id=".$sporder_id;
    	$url = $url . "&sporder_time=".$sporder_time;
    	$url = $url . "&game_userid=".$game_userid;
    	$url = $url . "&md5_str=".$md5_str;
    	$url = $url . "&version=6.0";
    	$this->_logs(array('调用欧飞API的url地址:', $url));
    	$ch = curl_init($url);
    	// 获取数据返回
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	// 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
    	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    	$output = curl_exec($ch);
    	$this->_logs(array('调用欧飞API的返回:', $output));
    	curl_close($cu);
    	return $output;
    }
    
    public function _xmlParseToArray($simple){
    	$p = xml_parser_create();
    	xml_parse_into_struct($p, $simple, $vals, $index);
    	xml_parser_free($p);
    	return array('index'=>$index, 'items'=>$vals);
    }
}
