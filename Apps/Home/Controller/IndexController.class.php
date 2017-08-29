<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends AuthController {
    public function index(){
    	//pr(11);
    	//$newsObj = new \Home\Common\NewsApi();
    	//$newsObj->getList();
    	//SU('aaa', 111);
        //$this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
    }
    
    /**
     * 是否登录状态
     */
    public function islogin(){
    	//$this->returnApiMsg ( '0', '您已登录' );
    	$userInfo = D('User')->getUserInfo(array('where'=>array('id'=>$this->userid)));
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
    				'today_coin' => $userInfo['today_coin'], //
    		); */
    		//$userInfo['userid'] = $userInfo['id'];
    		$result = $this->_get_login_flag ( $userInfo );
    		$this->_calcMessageCount($userInfo['cdate'], 2);//获得系统消息
    		$result['message_count'] = $this->_get_message_count();
    		$this->_logs('验证登录成功:' . json_encode($result));
    		$this->returnApiData ( $result); // 返回登录成功
    	}else{
    		$this->_logs('验证登录失败！' . ' ' . '返回结果:1028 登录失败');
    		$this->returnApiMsg ( '1028', '登录失败' ); // 返回登录失败
    	}
    }
    
    /**
     * 获得未读消息数量
     */
    private function _get_message_count(){
    	$sum = D("MessageReceive")->getCount(array("receiver_account"=>$this->userid, "status"=>0));
    	return (int)$sum;
    }
    
    /* public function test(){
    	$newsObj = new \Home\Common\NewsApi();
    	$url = 'http://mp.weixin.qq.com/s?src=3&timestamp=1493000121&ver=1&signature=r8DG84OeLW-r*cWS1ui30s5W7FwGly95tyo4cUc2F3F3tg5jx5CbTYyI4pREF8GtBZRVcdG*KxF30Zdjq4pkp9gRp6jt1jNYos0oD9NNat-rUm3rCzTN7ybhmy6zkSFsqvtn37xOBkD-PppEJHcYaVllhQrzkmkO2guWkcTlVGk=';
    	echo $newList = $newsObj->getWechatInfo($url);
    } */
    
}