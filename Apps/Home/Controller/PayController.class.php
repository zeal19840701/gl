<?php
namespace Home\Controller;
use Think\Controller;
class PayController extends CommonController {
	
	/**
	 * 在类初始化方法中，引入相关类库
	 */
	public function _initialize(){
		vendor('Alipay.Corefunction');
		vendor('Alipay.Md5function');
		vendor('Alipay.Notify');
		vendor('Alipay.Submit');
	}

	//doalipay方法
	/*该方法其实就是将接口文件包下alipayapi.php的内容复制过来
	 然后进行相关处理
	 */
	public function doalipay(){
		/*********************************************************
		 把alipayapi.php中复制过来的如下两段代码去掉，
		 第一段是引入配置项，
		 第二段是引入submit.class.php这个类。
		 为什么要去掉？？
		 第一，配置项的内容已经在项目的Config.php文件中进行了配置，我们只需用C函数进行调用即可；
		 第二，这里调用的submit.class.php类库我们已经在PayAction的_initialize()中已经引入；所以这里不再需要；
		 *****************************************************/
		// require_once("alipay.config.php");
		// require_once("lib/alipay_submit.class.php");

		//这里我们通过TP的C函数把配置项参数读出，赋给$alipay_config；
		$alipay_config=C('alipay_config');
		/**************************请求参数**************************/
		$payment_type = "1"; //支付类型 //必填，不能修改
		$notify_url = C('alipay.notify_url'); //服务器异步通知页面路径
		$return_url = C('alipay.return_url'); //页面跳转同步通知页面路径
		$seller_email = C('alipay.seller_email');//卖家支付宝帐户必填

  
		/*     $out_trade_no = $_POST['trade_no'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
		 $subject = $_POST['ordsubject'];  //订单名称 //必填 通过支付页面的表单进行传递
		 $total_fee = $_POST['ordtotal_fee'];   //付款金额  //必填 通过支付页面的表单进行传递
		 $body = $_POST['ordbody'];  //订单描述 通过支付页面的表单进行传递
		 $show_url = $_POST['ordshow_url'];  //商品展示地址 通过支付页面的表单进行传递
		 $anti_phishing_key = "";//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
		 $exter_invoke_ip = get_client_ip(); //客户端的IP地址  */
		 
		$out_trade_no = '11111';//商户订单号 通过支付页面的表单进行传递，注意要唯一！
		$subject = '我靠我测试测试';  //订单名称 //必填 通过支付页面的表单进行传递
		$total_fee ='100';   //付款金额  //必填 通过支付页面的表单进行传递
		$body = '缪果';  //订单描述 通过支付页面的表单进行传递
		$show_url = '11';  //商品展示地址 通过支付页面的表单进行传递
		$anti_phishing_key = "";//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
		$exter_invoke_ip = get_client_ip(); //客户端的IP地址
		 
		/************************************************************/
		 
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "create_direct_pay_by_user",
				"partner" => trim($alipay_config['partner']),
				"payment_type"    => $payment_type,
				"notify_url"    => $notify_url,
				"return_url"    => $return_url,
				"seller_email"    => $seller_email,
				"out_trade_no"    => $out_trade_no,
				"subject"    => $subject,
				"total_fee"    => $total_fee,
				"body"            => $body,
				"show_url"    => $show_url,
				"anti_phishing_key"    => $anti_phishing_key,
				"exter_invoke_ip"    => $exter_invoke_ip,
				"_input_charset"    => trim(strtolower($alipay_config['input_charset']))
		);
		//建立请求
		
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"post", "确认");
		echo $html_text;
	}
	 
	 
	/******************************
	 服务器异步通知页面方法
	 其实这里就是将notify_url.php文件中的代码复制过来进行处理
	  
	 *******************************/
	function notifyurl(){
		/*
		 同理去掉以下两句代码；
		 */
		//require_once("alipay.config.php");
		//require_once("lib/alipay_notify.class.php");
		 
		//这里还是通过C函数来读取配置项，赋值给$alipay_config
		$alipay_config=C('alipay_config');
		//计算得出通知验证结果
		$alipayNotify = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {
			//验证成功
			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
			$out_trade_no   = $_POST['out_trade_no'];      //商户订单号
			$trade_no       = $_POST['trade_no'];          //支付宝交易号
			$trade_status   = $_POST['trade_status'];      //交易状态
			$total_fee      = $_POST['total_fee'];         //交易金额
			$notify_id      = $_POST['notify_id'];         //通知校验ID。
			$notify_time    = $_POST['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
			$buyer_email    = $_POST['buyer_email'];       //买家支付宝帐号；
			$parameter = array(
					"out_trade_no"     => $out_trade_no, //商户订单编号；
					"trade_no"     => $trade_no,     //支付宝交易号；
					"total_fee"     => $total_fee,    //交易金额；
					"trade_status"     => $trade_status, //交易状态
					"notify_id"     => $notify_id,    //通知校验ID。
					"notify_time"   => $notify_time,  //通知的发送时间。
					"buyer_email"   => $buyer_email,  //买家支付宝帐号；
			);
			if($_POST['trade_status'] == 'TRADE_FINISHED') {
				//
			}else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {                           if(!checkorderstatus($out_trade_no)){
				orderhandle($parameter);
				//进行订单处理，并传送从支付宝返回的参数；
			}
			}
			echo "success";        //请不要修改或删除
		}else {
			//验证失败
			echo "fail";
		}
	}
	 
	/*
	 页面跳转处理方法；
	 这里其实就是将return_url.php这个文件中的代码复制过来，进行处理；
	 */
	function returnurl(){
		//头部的处理跟上面两个方法一样，这里不罗嗦了！
		$alipay_config=C('alipay_config');
		$alipayNotify = new \AlipayNotify($alipay_config);//计算得出通知验证结果
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result) {
			//验证成功
			//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
			$out_trade_no   = $_GET['out_trade_no'];      //商户订单号
			$trade_no       = $_GET['trade_no'];          //支付宝交易号
			$trade_status   = $_GET['trade_status'];      //交易状态
			$total_fee      = $_GET['total_fee'];         //交易金额
			$notify_id      = $_GET['notify_id'];         //通知校验ID。
			$notify_time    = $_GET['notify_time'];       //通知的发送时间。
			$buyer_email    = $_GET['buyer_email'];       //买家支付宝帐号；
			 
			$parameter = array(
					"out_trade_no"     => $out_trade_no,      //商户订单编号；
					"trade_no"     => $trade_no,          //支付宝交易号；
					"total_fee"      => $total_fee,         //交易金额；
					"trade_status"     => $trade_status,      //交易状态
					"notify_id"      => $notify_id,         //通知校验ID。
					"notify_time"    => $notify_time,       //通知的发送时间。
					"buyer_email"    => $buyer_email,       //买家支付宝帐号
			);
			 
			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				if(!checkorderstatus($out_trade_no)){
					orderhandle($parameter);  //进行订单处理，并传送从支付宝返回的参数；
				}
				$this->redirect(C('alipay.successpage'));//跳转到配置项中配置的支付成功页面；
			}else {
				echo "trade_status=".$_GET['trade_status'];
				$this->redirect(C('alipay.errorpage'));//跳转到配置项中配置的支付失败页面；
			}
		}else {
			//验证失败
			//如要调试，请看alipay_notify.php页面的verifyReturn函数
			echo "支付失败！";
		}
	}
	
	public function sc(){
		$alipay_config = array(
				'appid' =>'2016080400168640',//商户密钥
				'rsaPrivateKey' =>'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCKEO8n+mN5IXMdGVXoCqRLcskQpd3nlxYVoBBneduDkE1tQgwBi44793IT2z2/2Zpx8GYLDF5OmQZjPi39GktbDA1aICecpO75ZnWc7R1ZmzD+Axv4dcvNnNRiPgun/9zeuHOOPr/IK564tv+dv5BzQ0OjFL2+XGSovxpzy775bkY7D+eBVVU1b+98s1GUc8xoBV5XCGotoJMlGRQksJTJsCvs0ahIxpda4ZXaq4QW1jXLCAGRuuHAzBTZqjXN6m7RKjeT9CAgYiCvp3Yk0Gfh2aM46nJh21RRy1hgiXz7q3FFVT7Jy4nNzS3wWHtWqsKpXkkO4QMpm0wvDPYOthaRAgMBAAECggEAJ14iw1R7DqwCBdcWjr6SEE1flZamCoYjLvFgHBE8DWyVF0VQ5RPk76Zj6dOG7PBgQILapeXV8jViA+RT+qqHuCNcn0kx4lGJLqYPfTqDNuywcI10aUk1XqCMUqL8cd/cG8mecX3k82+0p1jO1C0uRB925sZgQCpaLV+obEI5ZyeleTyJj8jkyD30g8+1fMR6akdAddfRSDQgwnFzKhhMWLxyYec++0aiPbThK03ibjznVm9ucYEXEQc0i0mjpbO4NoDY3YwcHMdttYIVlMU1kKzkErAh1rIPoRYRbi6T2qdur2FLZ866I05GVPlAY4548PNfncZtKmNLEfsgmi03zQKBgQDTpSxsNU+nQRVALOMwfj7affQ8YFR7Ji8+K8ZZb2Msuu9INSGZbPS7P5/IxZHpR08fGVidtdb4v1z9heLM2uPsoSsrcoE9TxIWEo3j/ZwpM2rreHHDjhZDvZ2BQKiJBNAojUBNuifXZ0MOgY6Tsa4HRTQXP0ZaaH67ewQGtbIoMwKBgQCnADkQdwSr89nYe0csML4yRvsGhcZ0GrnNVCWmbPoivQahoIN0WaBUWtWJZ7FXrp4DdJ3UQ1qwhYF7RWRgzqYJ7nGqN0WuyfnhJYVetso8DXxyr3e6/p9tm/NkSwSVpBhE28FWXdHkUhEpkSAAGbiuMWeE07ogoFYfqoLZ7nNSKwKBgB3TpVMKoFMd5PTKXqoy4IDCR21K1h0U4IuOd8Ga5QskvRwcAMQyirro2Ife8BVEK25ikA6J1eXwchZ4i+H65ywt+nuBA520SwQ5US3US/GygVr1+nJoz9J0IoJYrmfA+eT9IxbRKVQ+BUFCGOnPnIsBwbmU/UE4gMPDLmoTqSvpAoGBAIujbROuYTKiuMIB+rYhn7eMkHOYwiLtAfWFaeHp95G6euQEwkY0dxJxzWGSWcBWWvvKs/n7w8YcK/N2R4OG52EghtxsURDhA9bVt9pFf36NTETdIYEzTP2qy+5Np+y0BDL3iYb89fczA/S8y4Qp/blxx/xRya1kQ/9AZR9y+ex3AoGBAMxwEmWlyaw8BDNeGcezaYTVQTis4Aoqaoosep/NLJ1blTor2KMVNdLXSSum4YmUrbqp65tfyYN6hRZu0cvC6pkl1lEuz2Gaop1bCD7HAPHFjU3G4yWWiH6Dk+PyZZxGOazUfGqFF0kJsi0FoSNW9d7xlE7QX4nUrhrmrGKJJ3n6',//私钥
				'alipayrsaPublicKey'=>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAihDvJ/pjeSFzHRlV6AqkS3LJEKXd55cWFaAQZ3nbg5BNbUIMAYuOO/dyE9s9v9macfBmCwxeTpkGYz4t/RpLWwwNWiAnnKTu+WZ1nO0dWZsw/gMb+HXLzZzUYj4Lp//c3rhzjj6/yCueuLb/nb+Qc0NDoxS9vlxkqL8ac8u++W5GOw/ngVVVNW/vfLNRlHPMaAVeVwhqLaCTJRkUJLCUybAr7NGoSMaXWuGV2quEFtY1ywgBkbrhwMwU2ao1zepu0So3k/QgIGIgr6d2JNBn4dmjOOpyYdtUUctYYIl8+6txRVU+ycuJzc0t8Fh7VqrCqV5JDuEDKZtMLwz2DrYWkQIDAQAB',//公钥(自己的程序里面用不到)
				'partner'=>'2088421540577515',//(商家的参数,新版本的好像用不到)
				'input_charset'=>strtolower('utf-8'),//编码
				'notify_url' =>'http://zeal5566.xicp.net/alipay1/notify_url.php',//回调地址(支付宝支付成功后回调修改订单状态的地址)
				'payment_type' =>1,//(固定值)
				'seller_id' =>'fwrnqp4000@sandbox.com',//收款商家账号
				'charset'    => 'utf-8',//编码
				'sign_type' => 'RSA2',//签名方式
				'timestamp' =>date("Y-m-d Hi:i:s"),
				'version'   =>"1.0",//固定值
				'url'       => 'https://openapi.alipay.com/gateway.do',//固定值
				'method'    => 'alipay.trade.app.pay',//固定值
		);
		 
		//构造业务请求参数的集合(订单信息)
		$content = array();
		$content['body'] = 'ceshi';
		$content['subject'] = 'funbutton11';//商品的标题/交易标题/订单标题/订单关键字等
		$content['out_trade_no'] = date("Y-m-d H:i:s", time()) . mt_rand(100000, 999999);//商户网站唯一订单号
		$content['timeout_express'] = '1d';//该笔订单允许的最晚付款时间
		$content['total_amount'] = floatval(1);//订单总金额(必须定义成浮点型)
		$content['seller_id'] = 'fwrnqp4000@sandbox.com';//收款人账号
		$content['product_code'] = 'QUICK_MSECURITY_PAY';//销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
		$content['store_id'] = 'BJ_001';//商户门店编号
		$con = json_encode($content);//$content是biz_content的值,将之转化成字符串
		 
		//公共参数
		require_once(APP_ROOT."/Apps/Home/Library/alipay/AopClient.php");
		$param = array();
		$client = new \Home\Library\alipay\AopClient();//实例化支付宝sdk里面的AopClient类,下单时需要的操作,都在这个类里面
		$param['app_id'] = '2016080400168640';//支付宝分配给开发者的应用ID
		$param['method'] = '	alipay.trade.app.pay';//接口名称
		$param['charset'] = 'utf-8';//请求使用的编码格式
		$param['sign_type'] = 'RSA2';//商户生成签名字符串所使用的签名算法类型
		//$param['sign'] = 'PkwXgfvK8ZwrZC+6MTAeOociHLQd7/Hza7IFX01FXUd0hs6u1kuUj9ClZCIHzavG8J9jJfp/5CYBaH7Lq0aMtuEEdZmAuaYyGYYk/JuxKtp+RMhPCoTDzdAg0oFxchK4oFp7BWP/I/3UMhGghYl6p34T2aGdRfuVHX0WMBNuiBoHAU18IJHips45sr6n5DF61gmtQDJHtIaA0Mr/Mvhy6K5Uk1kVmEOFStk3lOZkb+d9YgpJc2mnKz4OyaMUEcLAgjyNO9wmW5xqM+vVV+1wsiSmFD4z+ZnSVK8td0pf0HW4nm3ICbyb5VZF4M4f7U8Hxj+hKRy6iqJnBne/Uawozg==';//商户生成签名字符串所使用的签名算法类型
		$param['timestamp'] = date("Y-m-d H:i:s", time());//发送请求的时间
		$param['version'] = '1.0';//调用的接口版本，固定为：1.0
		$param['notify_url'] = 'http://zeal5566.xicp.net/alipay1/notify_url';//支付宝服务器主动通知地址
		$param['biz_content'] = $con;//业务请求参数的集合,长度不限,json格式
		 
		/*app_id=2016080400168640&biz_content={"button":[{"actionParam":"ZFB_HFCZ","actionType":"out","name":"话费充值"},{"name":"查询","subButton":[{"actionParam":"ZFB_YECX","actionType":"out","name":"余额查询"},{"actionParam":"ZFB_LLCX","actionType":"out","name":"流量查询"},{"actionParam":"ZFB_HFCX","actionType":"out","name":"话费查询"}]},{"actionParam":"http://m.alipay.com","actionType":"link","name":"最新优惠"}]}&charset=UTF-8&method=alipay.trade.app.pay&sign_type=RSA2&timestamp=2017-06-02 16:01:20&version=1.0*/
		 
		$paramStr = $client->getSignContent($param);
		$sign = $client->alonersaSign($paramStr, $alipay_config['rsaPrivateKey'], "RSA2");
		 
		$param['sign'] = $sign;
		$str = $client->getSignContentUrlencode($param);
		if($str){
			$this->returnApiData ($str);
		}else{
			$this->returnApiMsg ('1067', '请求报错');
		}
	}
	
	
}