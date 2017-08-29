<?php
namespace Home\Controller;
use Think\Controller;
header("Content-type:text/html;charset=utf-8");
class TestController extends CommonController {
    public function index(){
    	//D('MessageReceive')->insertMessage(1, 'SYSTEM', '刘平你收到了吗', '还没有收到要打屁屁', '3a9d6693-2bf9-1e09-5de2-b6d7336e73bc');
    	//echo _passport_decrypt("gl", 'laFPKlRhVzayHArxPKxWDNuC-K8usuuUE8MQpkJ3G94=');
    	//echo _passport_encrypt("gl", '58');
    	set_time_limit(120);//设置时间
    	//$link = "https://mp.weixin.qq.com/s?src=11&timestamp=1503977402&ver=359&signature=AV7iNjnzN*ShaQTo*hhpCqQFHOloH0V89Vh11wtECK8JYgMTl-Mj1Ztbc1be24EPAT6MPBvISTr9uqp4lsewhbME3WKKxxM-hwcBhqlwjebiDjzZAZMRTfSdkHr3*t6k&new=1";
    	$link = "http://mp.weixin.qq.com/s/3Bcca0QYjm1-h8nfn0qJ_A";
    	$newsObj = new \Home\Common\NewsApi();
    	$wechatInfo = $newsObj->getWechatInfo($link);
    	print_r($wechatInfo);
    	//echo $this->_check_wechat("rdr1sd");
    	/* echo date("Y-m-d H:i:s", 140735);
    	echo "<br>";
    	echo strtotime('2017-06-28 15:54:10');
    	echo "<br>";
    	echo strtotime('2017-06-28 15:54:10') + 3600*48;exit; */
    	//echo _passport_decrypt("gl", 'sOsYAoW6JAno3ogTUMVxMA4lNjXipRNmgW4FO4TygFw=');
    	//echo _passport_encrypt("gl", '9');
    	//D('missionStep')->getCount(array('mid'=>22, 'user_id'=>11, 'status'=>array("in", "0,1"), 'create_time'=>array(array("egt", date("Y-m-d 00:00:00", time()) ), array("elt", date("Y-m-d 23:59:59", time()) ), 'and')));
    	//echo C('FINISH_STATUS');
    	//echo get_client_ip(0, true);
    	//echo getSystemConfig('01', '01');
    	/* echo $start = microtime(TRUE);
    	echo "<br>";
    	$i=0;
    	while($i<1000){
    		echo randString(6, 'NUMBERCHAR');
    		//echo randStr(6, 'NUMBERCHAR');
    		$i++;
    	} */
    	
    	/* echo "<br>";
    	echo $end = microtime(TRUE);
    	echo "<br>";
    	echo $end-$start; */
    	//pr(11);
    	//$newsObj = new \Home\Common\NewsApi();
    	//$newsObj->getList();
    	//SU('aaa', 111);
        //$this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
    }
    
    public function sss(){
    	S('sdfadkaldjaldksdfa', 1);
    }
    
    public function test(){//phpinfo();
    	/* $nowTime = date("Y-m-d H:i:s", time());
    	$param = array(
    		'id' => \Org\Util\String::uuid(false, false),
    		'mobile' => '13890131036',
    		'password' => md5('123456'),
    		'nickname' => 'wesley',
    		'user_mac' => \Org\Util\String::uuid(false, false),
    		'gender' => 1,
    		'head_pic' => '',
    		'total_coin' => 100,
    		'coin' => 100,
    		'cdate' => $nowTime,
   			'udate' => $nowTime,
    		
    	); */
    	//D('User')->insertUser($param);
    }
    
    public function test1(){
    	//phpinfo();
    	/* $ts = array(
    		'title' => '测试用的',
	    	'intro' => '测试用的',
	    	'award' => '20',
	    	'copies' => '20',
	    	'start_time' => '2017-1-17 11:14:00',
	    	'end_time' => '2017-1-27 11:14:00',
	    	'city' => 'shanghai',
	    	'equipment' => '设备',
	    	'total_number' => '10',
	    	'day_number' => '1',
	    	'is_equipment' => '无',
	    	
    	);
    	echo json_encode($ts); */
    }
    
    public function test2(){
    	/* $this->assign('page', $show);
    	$this->display(); */
    }
    
    public function test3(){
    	//$res = rewardConfig('first_use');
    	//pr($res);
    	//$arrSsoVerify = \Home\Common\Sms::getmverif('13641833211','');
    	//print_r($arrSsoVerify);
    	
    	//echo _passport_encrypt('gl', '12');
    	
    	//D('MissionStep')->updateData(array('id'=>14, array('flag'=>1)));
    	
    	//echo $this->adm('72f498bd-09fc-d952-c6d9-6084510101a8');
    	//D('MissionUserStepImg')->deleteData(array( 'user_id' => 1212, 'mid'=>12 , 'muid' => 1121, 'step' => 1 ));
    }
    
    public function test4(){
    	/* $aa = array(
    			'head_pic'=>'/images/default1.jpg',
    			'nickname'=>'jeff',
    			'wechat_account'=>'13641833211',
    			'alipay_account'=>'13641833211',
    			'gender'=>2,
    			'address'=>'上海普陀',
    			'age'=>'20',
    			'profession'=>'打杂',
    			'marital'=>'未婚',
    	);
    	echo json_encode($aa); */
    }
    
    public function cj(){
    	/* $link = I('link');
    	$newsObj = new \Home\Common\NewsApi();
    	$wechatInfo = $newsObj->getWechatInfo($link);
    	print_r($wechatInfo);
    	exit(); */
    }
    
    public function step(){
    	$this->display();
    }
    
    public function invite(){
    	while(true){
    		echo $invitedcode = randString(6);
    		$resInvitCode = D('User')->getUserInfo(array('field'=>'id,invitedcode', 'where'=>array('invitedcode'=>$invitedcode)));
    		if(empty($resInvitCode)){
    			break;
    		}
    	}
    	echo $invitedcode;
    }
    
    public function pay(){
    	echo U('Pay/doalipay');
    }
    
    public function ss(){
    	$this->display();
    }
    
    public function pp(){
    	echo 'alipay_pp';
    }
    
    public function rpp(){
    	echo 'alipay_rpp';
    }
    
    public function madd(){
    	$this->display();
    }
    
    public function medit(){
    	$id = I("id");
    	$id = _passport_encrypt('gl', $id);
    	$this->assign("id", $id);
    	$this->display();
    }
    
    public function radd(){
    	$this->display();
    }
    
    public function ttt(){
    	//date_default_timezone_set("PRC");
    	//require_once("../Library/alipay/aop/AopClient.php");
    	//require_once("../Library/alipay/AopSdk.php");
    	
    	
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
    	$param['notify_url'] = 'http://zeal5566.xicp.net/alipay1/notify_url.php';//支付宝服务器主动通知地址
    	$param['biz_content'] = $con;//业务请求参数的集合,长度不限,json格式
    	
    	/*app_id=2016080400168640&biz_content={"button":[{"actionParam":"ZFB_HFCZ","actionType":"out","name":"话费充值"},{"name":"查询","subButton":[{"actionParam":"ZFB_YECX","actionType":"out","name":"余额查询"},{"actionParam":"ZFB_LLCX","actionType":"out","name":"流量查询"},{"actionParam":"ZFB_HFCX","actionType":"out","name":"话费查询"}]},{"actionParam":"http://m.alipay.com","actionType":"link","name":"最新优惠"}]}&charset=UTF-8&method=alipay.trade.app.pay&sign_type=RSA2&timestamp=2017-06-02 16:01:20&version=1.0*/
    	
    	$paramStr = $client->getSignContent($param);
    	$sign = $client->alonersaSign($paramStr, $alipay_config['rsaPrivateKey'], "RSA2");
    	
    	$param['sign'] = $sign;
    	echo $str = $client->getSignContentUrlencode($param);
    }
    
    //删除文件夹
    private function _deleteDir($R){
    	//打开一个目录句柄
    	$handle = opendir($R);
    	//读取目录,直到没有目录为止
    	while(($item = readdir($handle)) !== false){
    		//跳过. ..两个特殊目录
    		if($item != '.' and $item != '..'){
    			//如果遍历到的是目录
    			if(is_dir($R.'/'.$item)){
    				//继续向目录里面遍历
    				$this->_deleteDir($R.'/'.$item);
    			}else{
    				//如果不是目录，删除该文件
    				if(!unlink($R.'/'.$item))
    					die('error!');
    			}
    		}
    	}
    	//关闭目录
    	closedir( $handle );
    	//删除空的目录
    	return rmdir($R);
    }
    
    //清除缓存--删除runtime文件夹
    public function delRun () {
    	//获取url的第三项值
    	//$get = $_GET['_URL_'][2];
    	$get = 'delRun';
    	//如果目录是 delRun
    	if($get == 'delRun'){
    		//获取当前的缓存目录
    		//echo $R =RUNTIME_PATH;
    		echo $R = '/data/httpd/gold_lock/Cli/Runtime/';
    		//执行删除函数
    		if($this->_deleteDir($R))
    			//$this->error('删除成功！');
    			die("clear success!");
    	}
    }
    
    
    public function cp(){
    	/* set_time_limit(3600);
    	$input = array(
    			array(array(3,7,9,11), array(13,14,18,22),array(24,28,30,33)),
    			array(array(2,4,6),array(15,20),array(23,24,25,27,31)),
    			array(array(1,4),array(13),array(23,25,31)),
    			array(array(2,5,7,8),array(14,15,18,20,22),array(24,26,29)),
    			array(array(1,5,6,9,11),array(19,20),array(23,30,31)),
    			array(array(9,10,11),array(15,18,20,22),array(24,25,28,31)),
    			array(array(3,6,7,9),array(12,13,16,19,21),array(23,25,26,28,32)),
    	);
    	//4:1:1 3:2:1 2:2:2
    	for($kk=0;$kk<10000;$kk++){
    		$total = array(array(),array(),array());
    		$tbl = array(array(4,1,1),array(3,2,1),array(2,2,2),array(1,2,3));
    		//$tbl = array(array(4,1,1));
    		$rand_tbl = array_rand($tbl, 1);
    		$bl = $tbl[$rand_tbl];
    		for($j=0;$j<3;$j++){
    			$i=0;
    			while(true){
    				$daRandKeys = array_rand($input, 1);
    				$xiaoRandKeys = array_rand($input[$daRandKeys][$j], 1);
    				$getRand1 = $input[$daRandKeys][$j][$xiaoRandKeys];
    				//echo $getRand1 . " | ";
    				//print_r($total[$j]);
    				if(in_array($getRand1, $total[$j])){
    					continue;
    				}else{
    					if($i>=$bl[$j]){
    						break;
    					}
    					$i++;
    					$total[$j][] = $getRand1;
    				}
    				//echo $input[$daRandKeys][$xiaoRandKeys] . "\n";
    			}
    		}
    		$newTotal = array();
    		$totalCount = count($total);
    		for($i=0;$i<$totalCount;$i++){
    			for($j=0;$j<count($total[$i]);$j++){
    				$newTotal[] = $total[$i][$j];
    			}
    		}
    		sort($newTotal, SORT_NUMERIC);
    		$cpstr = implode(",", $newTotal);
    		$check_key = md5($cpstr);
    		$res = M('gl_caipan')->field('check_key')->where(array('check_key'=>$check_key))->find();
    		if(!$res){
    			$ret = M('gl_caipan')->add(array('check_key'=>$check_key, 'cpstr'=>$cpstr));
    			if($ret){
    				echo $cpstr . "添加成功！<br>";
    			}else{
    				echo $cpstr . "添加失败！<br>";
    			}
    		}else{
    			echo $cpstr . "数据库存在！<br>";
    		}
    	}
    	exit(); */
    }
    
    
    public function cp1(){
    	//set_time_limit(3600);
    	//$input = array(1,29,3,10,4,18);
    	//$input = array(2,30,22,10,31,6);
    	/* $input = array(11,31,2,29,23,12);
    	$sum = array_sum($input);
    	$cc = count($input);
    	$gws = array();
    	$qys = array();
    	$result = array();
    	for($i=0;$i<$cc;$i++){
    		$js = ($sum - $input[$i]);
    		$gws[$i] = floor($js/$input[$i])%10;
    		$qys[$i] = floor($js/$input[$i])%$input[$i];
    		$temp = $gws[$i];
    		while($temp<=33){
    			$result[] = $temp;
    			$temp+=10;
    		}
    		$temp1 = $qys[$i];
    		while($temp1<=33){
    			$result[] = $temp1;
    			$temp1+=10;
    		}
    	}
    	$result = array_flip(array_flip($result));
    	sort($result);
    	//print_r($gws);
    	print_r($result);
    	exit(); */
    }
    
    public function tsts(){
    	/* $userInfo = D('User')->getUserInfo(array('field'=>'id,invitedcode', 'where'=>array('invitedcode'=>"")));
    	if(count($userInfo) > 0){
    		$invitedcode = '';
    		while(true){
    			$invitedcode = randString(6);
    			$resInvitCode = D('User')->getUserInfo(array('field'=>'id,invitedcode', 'where'=>array('invitedcode'=>$invitedcode)));
    			if(empty($resInvitCode)){
    				break;
    			}
    		}
    		$res = D('User')->updateUser(array("id"=>$userInfo['id']), array("invitedcode"=>$invitedcode));
    		if($res){
    			echo $invitedcode . "添加成功<br>";
    		}else{
    			echo $invitedcode . "添加失败<br>";
    		}
    	}
    	echo"执行完毕<br>"; */
    	
    }
    
    public function rrr(){
    	//D("User")->increaseCoin('sda','12');
    }
}