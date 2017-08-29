<?php

namespace Home\Controller;

use Think\Controller;

class MyController extends AuthController
{

    /**
     * 修改个人信息
     */
    public function modifyUserInfo()
    {
        //echo $this->userid;
        $userData = I('userData');//传入数据
        $userData = json_decode(htmlspecialchars_decode($userData), true);//把json转成数组形式
        $newUserData = array();
        /* if(isset($userData['head_pic']) && $userData['head_pic']){
            $newUserData['head_pic'] = $userData['head_pic'];
        } */
        $nowDate = date("Y-m-d H:i:s", time());
        if (isset($userData['nickname']) && $userData['nickname']) {
            $newUserData['nickname'] = $userData['nickname'];
        }
        if (isset($userData['wechat_account']) && $userData['wechat_account']) {
            $newUserData['wechat_account'] = $userData['wechat_account'];
        }
        if (isset($userData['alipay_account']) && $userData['alipay_account']) {
            $newUserData['alipay_account'] = $userData['alipay_account'];
        }
        if (isset($userData['gender']) && $userData['gender']) {
            $newUserData['gender'] = $userData['gender'];
        }
        if (isset($userData['address']) && $userData['address']) {
            $newUserData['address'] = $userData['address'];
        }
        if (isset($userData['age']) && $userData['age']) {
            $newUserData['age'] = $userData['age'];
        }
        if (isset($userData['profession']) && $userData['profession']) {
            $newUserData['profession'] = $userData['profession'];
        }
        if (isset($userData['marital']) && $userData['marital']) {
            $newUserData['marital'] = $userData['marital'];
        }
        $newUserData['udate'] = $nowDate;
        $basePath = APP_ROOT . '/Public';
        $path = '/data/headpic/' . date("Y-m", time()) . '/' . date("d", time()) . '/';
        $destinationFolder = $basePath . $path;
        if (!is_dir($destinationFolder)) {
            mkdir($destinationFolder, 0755, true);
        }
        if (!is_readable($destinationFolder)) {
            chmod($destinationFolder, 0755);
        }
        //上传图片
        if (isset($_FILES['uploadfile'])) {
            $headPic = '';
            if (UPLOAD_ERR_OK == $_FILES['uploadfile']['error']) {
                $tmpName = $_FILES['uploadfile']['tmp_name'];
                $arrUploadFileName = explode('.', $_FILES['uploadfile']['name']);
                $ufnCount = count($arrUploadFileName) - 1;
                $uploadFileName = $arrUploadFileName[$ufnCount];
                //如果没有后缀名，默认以jpg为后缀
                if (empty($uploadFileName)) {
                    $uploadFileName = 'jpg';
                }
                $mtRand = mt_rand(100, 999);
                $unUploadFileName = uniqid() . $mtRand . '.' . $uploadFileName;
                $uploadFile = $destinationFolder . $unUploadFileName;
                $isSave = move_uploaded_file($tmpName, $uploadFile);
                if ($isSave) {
                    $headPic = $path . $unUploadFileName;
                }
            }
            if ($headPic) {
                $newUserData['head_pic'] = $headPic;//头像
            }
        }
        if (!empty($newUserData)) {
            $res = D('User')->updateUser(array('id' => $this->userid), $newUserData);
            if ($res !== false) {
                $code = '0';
                $msg = '修改个人信息成功';
            } else {
                $code = '1046';
                $msg = '修改个人信息失败';
            }
            $this->returnApiMsg($code, $msg);

        } else {
            $code = '1047';
            $msg = '没有传入或无效字段';
            $this->returnApiMsg($code, $msg);
        }
    }

    /**
     * 我的推荐列表
     */
    public function reclist()
    {
        $page = addslashes(I('page'));
        if (!$page) {
            $page = 1;
        }
        $size = 10;//显示10条
        $limit = ($page - 1) * $size . ',' . $size;
        $nowTime = date("Y-m-d H:i:s", time());
        $result = array();
        $param = array(
            'field' => 'id,user_id,phrase,cate_id,award,total_copies,copies,start_time,end_time,read_number,exposure_number,share_number,type,title,thumbnail,flag',
            'where' => array(
                'user_id' => $this->userid,
            ),
            'order' => 'create_time DESC',
            'limit' => $limit,
        );
        $recommendList = D('Recommend')->getList($param);//查询推荐列表
        if (!empty($recommendList)) {
            foreach ($recommendList as $k => $v) {
                $recommendList[$k]['id'] = _passport_encrypt('gl', $v['id']);
                if (empty($v['thumbnail'])) {
                    $recommendList[$k]['thumbnail'] = C('DATA_IMG_URL') . C('DATA_IMG_HEAD_PIC');
                } else {
                    $recommendList[$k]['thumbnail'] = C('DATA_IMG_URL') . $v['thumbnail'];
                }
                $recommendList[$k]['status'] = $this->_check_status($v);
                /* if($v['flag'] == 2){
                    $recommendList[$k]['status'] = C('PAUSE_STATUS'); //'已暂停';
                }else if($v['start_time'] > $nowTime){
                    $recommendList[$k]['status'] = C('NOT_BEGIN_STATUS'); //'未开始';
                }else if($v['start_time'] <= $nowTime && $v['end_time'] >= $nowTime){
                    $recommendList[$k]['status'] = C('ON_GOING_STATUS'); //'推广中';
                }else{
                    $recommendList[$k]['status'] = C('FINISH_STATUS'); //'已结束';
                } */
            }
        }
        $param = array(
            'user_id' => $this->userid,
        );
        $recommendCount = D("Recommend")->getCount($param);//查询总数量
        $result['items'] = $recommendList;//赋值到列表
        $result['totalPages'] = ceil($recommendCount / $size);//计算页数
        $this->returnApiData($result);
    }

    /**
     * 我的分享列表
     */
    public function shareList()
    {
        $page = addslashes(I('page'));
        if (!$page) {
            $page = 1;
        }
        $size = 10;//显示10条
        $limit = ($page - 1) * $size . ',' . $size;
        $nowTime = date("Y-m-d H:i:s", time());
        $result = array();
        $sql = "SELECT id,user_id,phrase,cate_id,award,total_copies,copies,start_time,end_time,read_number,exposure_number,share_number,type,title,thumbnail,flag FROM `gl_recommend` WHERE user_id='" . $this->userid . "' ORDER BY create_time DESC LIMIT " . $limit;
        $recommendList = D('RecommendShare')->getQuery($sql);//查询推荐列表
        foreach ($recommendList as $k => $v) {
            $recommendList[$k]['id'] = _passport_encrypt('gl', $v['id']);
            $recommendList[$k]['thumbnail'] = C('DATA_IMG_URL') . $v['thumbnail'];//获取分类名称
            if (empty($v['head_pic'])) {
                $recommendList[$k]['head_pic'] = C('DATA_IMG_URL') . C('DATA_IMG_HEAD_PIC');
            } else {
                $recommendList[$k]['head_pic'] = C('DATA_IMG_URL') . $v['head_pic'];
            }
            $recommendList[$k]['status'] = $this->_check_status($v);
            /* if($v['flag'] == 2){
                $recommendList[$k]['status'] = C('PAUSE_STATUS'); //'已暂停';
            }else if($v['start_time'] > $nowTime){
                $recommendList[$k]['status'] = C('NOT_BEGIN_STATUS'); //'未开始';
            }else if($v['start_time'] <= $nowTime && $v['end_time'] >= $nowTime){
                $recommendList[$k]['status'] = C('ON_GOING_STATUS'); //'推广中';
            }else{
                $recommendList[$k]['status'] = C('FINISH_STATUS'); //'已结束';
            } */
        }
        $sql = "SELECT COUNT(*) AS num FROM `gl_recommend` WHERE user_id='" . $this->userid . "'";
        $recommendCount = D("RecommendShare")->getQuery($sql);//查询总数量
        $recommendCount = $recommendCount[0]['num'];
        $result['items'] = $recommendList;//赋值到列表
        $result['totalPages'] = ceil($recommendCount / $size);//计算页数
        $this->returnApiData($result);
    }

    /**
     * 参与分享列表
     */
    public function actorShareList()
    {
        $page = addslashes(I('page'));
        if (!$page) {
            $page = 1;
        }
        $size = 10;//显示10条
        $limit = ($page - 1) * $size . ',' . $size;
        $nowTime = date("Y-m-d H:i:s", time());
        $result = array();
        $sql = "SELECT SUM(a.read_number) as friend_read_number,SUM(a.read_number*a.coin) AS `total_coin`,b.id,b.user_id,b.phrase,b.cate_id,b.award,b.total_copies,b.copies,b.start_time,b.end_time,b.exposure_number,b.share_number,b.type,b.title,b.thumbnail,b.flag FROM `gl_recommend_share` AS a LEFT JOIN `gl_recommend` AS b ON (a.rec_id=b.id) WHERE a.user_id='" . $this->userid . "' GROUP BY b.id ORDER BY b.create_time DESC LIMIT " . $limit;
        $recommendList = D('RecommendShare')->getQuery($sql);//查询推荐列表
        foreach ($recommendList as $k => $v) {
            $recommendList[$k]['id'] = _passport_encrypt('gl', $v['id']);
            $recommendList[$k]['thumbnail'] = C('DATA_IMG_URL') . $v['thumbnail'];//获取分类名称
            if (empty($v['head_pic'])) {
                $recommendList[$k]['head_pic'] = C('DATA_IMG_URL') . C('DATA_IMG_HEAD_PIC');
            } else {
                $recommendList[$k]['head_pic'] = C('DATA_IMG_URL') . $v['head_pic'];
            }
            $recommendList[$k]['status'] = $this->_check_status($v);
            /* if($v['flag'] == 2){
                $recommendList[$k]['status'] = C('PAUSE_STATUS'); //'已暂停';
            }else if($v['start_time'] > $nowTime){
                $recommendList[$k]['status'] = C('NOT_BEGIN_STATUS'); //'未开始';
            }else if($v['start_time'] <= $nowTime && $v['end_time'] >= $nowTime){
                $recommendList[$k]['status'] = C('ON_GOING_STATUS'); //'推广中';
            }else{
                $recommendList[$k]['status'] = C('FINISH_STATUS'); //'已结束';
            } */
        }
        $sql = "SELECT COUNT(DISTINCT(rec_id)) AS num FROM `gl_recommend_share` WHERE user_id='" . $this->userid . "'";
        $recommendCount = D("RecommendShare")->getQuery($sql);//查询总数量
        $recommendCount = $recommendCount[0]['num'];
        $result['items'] = $recommendList;//赋值到列表
        $result['totalPages'] = ceil($recommendCount / $size);//计算页数
        $this->returnApiData($result);
    }

    /**
     * 密码修改
     */
    public function chgPwd()
    {
        $userid = $this->userid;
        $pwd = addslashes(I('pwd'));
        $newpwd = addslashes(I('newpwd'));
        if (!$pwd) {
            $this->returnApiMsg('1049', '旧密码不能为空');
        }
        if (!$newpwd) {
            $this->returnApiMsg('1050', '新密码不能为空');
        }
        $param = array(
            'where' => array(
                'id' => $userid,
                'password' => md5($pwd),
            ),
            'field' => 'id,mobile',
        );
        $resUser = D('User')->getUserInfo($param);
        if (empty($resUser)) {
            $this->returnApiMsg('1051', '旧密码填写不对');
        }
        $where = array(
            'id' => $userid,
        );
        $data = array(
            'password' => md5($newpwd),
        );
        $res = D('User')->updateUser($where, $data);
        if ($res) {
            $this->returnApiMsg('0', '密码修改成功');
        } else {
            if ($res !== false) {
                $this->returnApiMsg('1086', '原密码不能与新密码相同');
            } else {
                $this->returnApiMsg('1052', '密码修改失败');
            }

        }
    }

    /**
     * 反馈建议
     */
    public function feedback()
    {
        $userid = $this->userid;
        $content = addslashes(I('content'));
        if (!$content) {
            $this->returnApiMsg('1053', '反馈意见不能为空');
        }
        $basePath = APP_ROOT . '/Public';
        $path = '/data/feedback/' . date("Y-m", time()) . '/' . date("d", time()) . '/';
        $destinationFolder = $basePath . $path;
        if (!is_dir($destinationFolder)) {
            mkdir($destinationFolder, 0755, true);
        }
        if (!is_readable($destinationFolder)) {
            chmod($destinationFolder, 0755);
        }
        //上传图片
        $newData = array();
        if (isset($_FILES['uploadfile'])) {
            $img = '';
            if (UPLOAD_ERR_OK == $_FILES['uploadfile']['error']) {
                $tmpName = $_FILES['uploadfile']['tmp_name'];
                $arrUploadFileName = explode('.', $_FILES['uploadfile']['name']);
                $ufnCount = count($arrUploadFileName) - 1;
                $uploadFileName = $arrUploadFileName[$ufnCount];
                //如果没有后缀名，默认以jpg为后缀
                if (empty($uploadFileName)) {
                    $uploadFileName = 'jpg';
                }
                $mtRand = mt_rand(100, 999);
                $unUploadFileName = uniqid() . $mtRand . '.' . $uploadFileName;
                $uploadFile = $destinationFolder . $unUploadFileName;
                $isSave = move_uploaded_file($tmpName, $uploadFile);
                if ($isSave) {
                    $img = $path . $unUploadFileName;
                }
            }
            if ($img) {
                $newData['img'] = $img;//头像
            }
        } else {
            $this->returnApiMsg('1054', '反馈截图需要上传');
        }
        $newData['content'] = $content;
        $newData['user_id'] = $userid;
        $newData['cdate'] = date("Y-m-d H:i:s", time());
        $res = D('Feedback')->insertUser($newData);
        if ($res) {
            $this->returnApiMsg('0', '反馈提交成功');
        } else {
            $this->returnApiMsg('1055', '反馈提交失败');
        }
    }

    /**
     * 朋友分享
     */
    public function share()
    {
        $userId = $this->userid;//获取userid
        $param = array(
            'where' => array(
                'id' => $userId,
            ),
            'field' => 'id,mobile,invitedcode',
        );

        $res = D('User')->getUserInfo($param);
        if ($res) {
            $res['url'] = C('GL_HOST_URL') . '/index.php?m=home&c=share&a=invite&mobile=' . $res['mobile'];
            //$res['url'] = C('GL_HOST_URL') . '/home/share/invite/mobile/' . $res['mobile'];
            //$res['url'] = 'http://www.fapiao.com/fpt-app/share/share_duobao.jsp';
            $this->returnApiData($res);
        } else {
            $this->returnApiMsg('1045', '分享失败');
        }
    }

    /**
     * 充值
     */
    public function pay()
    {
        $userId = $this->userid;//获取userid
        $amount = I('amount');//充值金额
        if (!isset($amount) || empty($amount) || $amount < 0) {
            $this->returnApiMsg('1064', '请输入充值金额');
        }
        if (!is_numeric($amount)) {
            $this->returnApiMsg('1066', '充值金额有误');
        }
        $amount = sprintf("%.2f", $amount);//取后两位小数点
        $coin = $amount * 1000;//金币设置
        $way = I('way', 1);//充值方式 (1为支付宝，2为微信支付)
        if (!isset($way) || empty($way) || !in_array($way, array('1', '2'))) {
            $this->returnApiMsg('1065', '充值方式有误');
        }
        $resUserInfo = D('User')->getUserInfo(array('field' => '*', 'where' => array('id' => $userId)));
        if ($resUserInfo) {
            if ($resUserInfo['mobile']) {
                $mobile = $resUserInfo['mobile'];
            } else {
                $mobile = '';
            }
            if (empty($mobile)) {
                $this->returnApiMsg('1087', '您还不是注册用户，请移步到注册界面进行绑定');
            }
        } else {
            $this->returnApiMsg('1088', '用户不存在');
        }
        $nowDate = date("Y-m-d H:i:s", time());
        $payOrderInfo = D("PayOrder")->getInfo(array('field' => 'mobile,money,order_id,status', 'where' => array('user_id' => $userId, 'money' => $amount, 'channel' => $way, 'status' => 0)));
        $flag = false;//标记是否过期
        if ($payOrderInfo) {
            $pastTime = strtotime($payOrderInfo['cdate']) + 7000;//设置过期时间
            if (strtotime($payOrderInfo['cdate']) < time()) {
                D("PayOrder")->updateData(array('id' => $payOrderInfo['id']), array('status' => 3));//到了过期时间则作废
                $flag = true;
            }
        }
        if (empty($payOrderInfo) || $flag) {
            $order_id = date("YmdHis", time()) . getMicroSecondtime() . randString(6, 'NUMBER');
            $data = array(
                'user_id' => $userId,
                'mobile' => $mobile,
                'order_id' => $order_id,
                'channel' => $way,
                'coin' => $coin,
                'money' => $amount,
                'udate' => $nowDate,
                'cdate' => $nowDate,
                'creator' => $mobile,
                'status' => 0,
            );
            $res = D("PayOrder")->insertData($data);
            if ($res) {
                $payOrderInfo = D("PayOrder")->getInfo(array('field' => 'mobile,money,order_id,status', 'where' => array('user_id' => $userId, 'money' => $amount, 'channel' => $way, 'status' => 0)));
            }
        }
        if ($payOrderInfo) {
            $result = array();
            if (1 == $way) {//调起支付宝支付
                $result['str'] = $this->_alipay($payOrderInfo);
            } else if (2 == $way) {//调起微信支付
                $result['str'] = $this->_wechat($payOrderInfo);
            }
            $this->returnApiData($result);
        } else {
            $this->returnApiMsg('1063', '充值失败');
        }
    }

    /**
     * 调起微信支付
     */
    private function _wechat($payOrderInfo)
    {
        //$ip = get_client_ip();
        require_once(APP_ROOT . "/Apps/Home/Library/wxpay/WxPayApi.php");
        $nonce_str = \Home\Library\wxpay\WxPayApi::getNonceStr(32);//随机生成字符串
        $prepay_id = $this->_wechat_prepay_id($payOrderInfo);//获得微信prepay_id信息
        //这里返回客户端信息
        $info = array();
        $info['appid'] = \Home\Library\wxpay\WxPayConfig::APPID;//公众号APPID
        $info['partnerid'] = \Home\Library\wxpay\WxPayConfig::MCHID;//微信支付商户号
        $info['package'] = \Home\Library\wxpay\WxPayConfig::PACKAGE;//
        $info['noncestr'] = $nonce_str;
        $info['timestamp'] = time();
        $info['prepayid'] = $prepay_id ? $prepay_id : '';
        $info['sign'] = \Home\Library\wxpay\WxPayApi::_MakeSign($prepay_id);
        return json_encode($info);
    }

    /**
     * 获得微信prepay_id信息
     * @param array $payOrderInfo
     * @param array $wxpay_config
     */
    private function _wechat_prepay_id($payOrderInfo)
    {
        require_once(APP_ROOT . "/Apps/Home/Library/wxpay/WxPayApi.php");
        $input = new \Home\Library\wxpay\WxPayUnifiedOrder();
        $input->setBody($payOrderInfo['mobile'] . "的充值");//商品描述
        $input->setAttach($payOrderInfo['mobile'] . "的充值");//附加数据
        $input->setOut_trade_no($payOrderInfo['order_id']);//订单号
        $input->setTotal_fee(floatval($payOrderInfo['money']));//订单总金额
        $input->setTime_start(date('YmdHis', time()));//交易起始时间
        $input->setTime_expire(date('YmdHis', time() + (3600 * 24)));//交易结束时间
        //$input->setGoods_tag("金锁APP-" . $payOrderInfo['mobile']."账户-充值");//
        $input->setNotify_url(\Home\Library\wxpay\WxPayConfig::NOTIFY_URL);//回调地址
        $input->setTrade_type(\Home\Library\wxpay\WxPayConfig::TRADE_TYPE);//支付类型
        $order = \Home\Library\wxpay\WxPayApi::unifiedOrder($input);//统一下单
        return $order['prepay_id'];
    }

    /**
     * 调起支付宝支付
     * @param array $payOrderInfo
     * @return string
     */
    private function _alipay($payOrderInfo)
    {
        $alipay_config = C('alipay_config');
        //构造业务请求参数的集合(订单信息)
        $content = array();
        $content['body'] = $payOrderInfo['mobile'] . "的充值";
        $content['subject'] = "金锁APP-" . $payOrderInfo['mobile'] . "账户-充值";//商品的标题/交易标题/订单标题/订单关键字等
        $content['out_trade_no'] = $payOrderInfo['order_id'];//商户网站唯一订单号
        $content['timeout_express'] = $alipay_config['alipay_config_biz']['timeout_express'];//该笔订单允许的最晚付款时间 1d
        $content['total_amount'] = floatval($payOrderInfo['money']);//订单总金额(必须定义成浮点型)
        $content['seller_id'] = $alipay_config['seller_id'];//收款人账号  tracy.li@trueland.net  fwrnqp4000@sandbox.com
        $content['product_code'] = $alipay_config['alipay_config_biz']['product_code'];//销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
        //$content['store_id'] = 'BJ_001';//商户门店编号
        $con = json_encode($content);//$content是biz_content的值,将之转化成字符串
        //pr($content);
        //公共参数
        require_once(APP_ROOT . "/Apps/Home/Library/alipay/AopClient.php");
        $param = array();
        $client = new \Home\Library\alipay\AopClient();//实例化支付宝sdk里面的AopClient类,下单时需要的操作,都在这个类里面
        $param['app_id'] = $alipay_config['app_id'];//支付宝分配给开发者的应用ID   2017051807272756 2016080400168640
        $param['method'] = $alipay_config['method'];//接口名称
        $param['charset'] = $alipay_config['charset'];//请求使用的编码格式
        $param['sign_type'] = $alipay_config['sign_type'];//商户生成签名字符串所使用的签名算法类型 RSA RSA2
        $param['timestamp'] = date('Y-m-d H:i:s', time());//发送请求的时间
        $param['version'] = $alipay_config['version'];//调用的接口版本，固定为：1.0
        $param['notify_url'] = $alipay_config['notify_url'];//支付宝服务器主动通知地址
        $param['biz_content'] = $con;//业务请求参数的集合,长度不限,json格式
        $paramStr = $client->getSignContent($param);
        $sign = $client->alonersaSign($paramStr, $alipay_config['rsaPrivateKey'], $alipay_config['sign_type']);//RSA  RSA2
        $param['sign'] = $sign;
        $str = $client->getSignContentUrlencode($param);
        return $str;
    }

    /**
     * 单页面
     */
    public function spage()
    {
        $name = I('name');
        $res['url'] = C('GL_HOST_URL') . '/index.php?m=home&c=app&a=sp&name=' . $name;
        $this->returnApiData($res);
    }

    /**
     * 是否绑定支付宝
     */
    public function isBindAlipay()
    {
        $userInfo = D("User")->getUserInfo(array("where" => array("id" => $this->userid)));
        if ($userInfo) {
            if (empty($userInfo['alipay_account'])) {
                $this->returnApiMsg('1064', '支付宝账号还未绑定');
            } else {
                $this->returnApiMsg('0', '支付宝已经绑定');
            }
        } else {
            $this->returnApiMsg('1088', '用户不存在');
        }
    }

    /**
     * 添加提现记录
     */
    public function addExchange()
    {
        $pwd = I('pwd');//登录密码
        $mobile = I('mobile');//手机号，可填
        $money = I('money');//金额
        $way = I('way');//方式
        $param = array();
        if (!$pwd) {
            $this->returnApiMsg('1039', '请填写密码');
        }
        if ($pwd) {
            $param = array(
                'where' => array(
                    'id' => $this->userid,
                    'password' => md5($pwd),
                    'status' => 0,//状态是未冻结
                )
            );
            $userInfo = D('User')->getUserInfo($param);
            if (!$userInfo) {
                $this->returnApiMsg('1069', '密码填写有误');
            }
        }
        if ($mobile) {
            $checkMobile = $this->_check_mobile($mobile);//检查手机号
            if (!$checkMobile) {
                $this->returnApiMsg('1075', '手机号填写有误');
            }
        }
        if (!in_array($money, array(15, 30, 50, 100))) {
            $this->returnApiMsg('1068', '选择提现金额');
        }
        $coin = $money * 1000;
        if (!$way) {
            $way = 0;
        }
        if (!in_array($way, array(1, 2, 3, 4))) {
            $this->returnApiMsg('1076', '选择提现方式');
        }
        $pcTotalCoin = D('Recommend')->getPushlishCoin($this->userid);//获得发布时正在使用的金币
        $modelUser = D("User");
        $userInfo = $modelUser->getUserInfo(array('id,mobile,wechat_account,wechat_name,alipay_account,alipay_name,coin', 'where' => array('id' => $this->userid, 'status' => 0)));
        $exchange_account = '';
        $exchange_name = '';
        if ($userInfo) {
            if ($userInfo['coin'] < ($pcTotalCoin + $coin)) {
                $this->returnApiMsg('1077', '金币不够无法提取');
            }
            if ($userInfo['coin'] < $coin) {
                $this->returnApiMsg('1077', '金币不够无法提取');
            }
            if ($way == 1) {
                if ($userInfo['alipay_account']) {
                    $exchange_account = $userInfo['alipay_account'];
                } else {
                    $this->returnApiMsg('1078', '支付宝账号未绑定');
                }
                if ($userInfo['alipay_name']) {
                    $exchange_name = $userInfo['alipay_name'];
                } else {
                    $this->returnApiMsg('1097', '支付宝真实姓名未绑定');
                }
            } else if ($way == 2) {
                if ($userInfo['wechat_account']) {
                    $exchange_account = $userInfo['wechat_account'];
                } else {
                    $this->returnApiMsg('1079', '微信账号未绑定');
                }
                if ($userInfo['wechat_name']) {
                    $exchange_name = $userInfo['wechat_account'];
                } else {
                    $this->returnApiMsg('1098', '微信姓名未绑定');
                }
            } else {
                $exchange_account = $userInfo['mobile'];
            }
        } else {
            $this->returnApiMsg('1080', '账户不存在');
        }
        $nowDate = date("Y-m-d H:i:s", time());
        $flag = true;
        $modelUser->startTrans();//任务事务开启
        $updateUserFlag = $modelUser->updateUser(array("id" => $this->userid, "coin" => array("egt", $coin)), array("coin" => array("exp", "`coin`-" . $coin), "use_coin" => array("exp", "`use_coin`+" . $coin)));
        if (!$updateUserFlag) {
            $flag = false;
        }
        $param = array(
            'user_id' => $this->userid,
            'mobile' => $userInfo['mobile'],
            'exchange_date' => $nowDate,
            'exchange_channel' => $way,
            'exchange_gold' => $coin,
            'exchange_money' => $money,
            'exchange_account' => $exchange_account,
            'exchange_name' => $exchange_name,
            'exchange_id' => date("YmdHis", time()) . getMicroSecondtime() . randString(4, 'NUMBER'),
            'create_date' => $nowDate,
            'creator' => $userInfo['mobile'],
            'update_date' => $nowDate,
            'updater' => '',
            'is_del' => 0,
            'status' => 0,
        );
        $recordFlag = D('UserExchangeRecord')->insertData($param);
        if (!$recordFlag) {
            $flag = false;
        }
        if ($flag) {
            $modelUser->commit();//上传结果成功，事务提交
            $this->returnApiMsg('0', '提取成功');
        } else {
            $modelUser->rollback();//上传结果失败，事务回滚
            $this->returnApiMsg('1081', '提取失败');
        }
    }

    /**
     * 提现列表
     */
    public function exchangeLog()
    {
        $page = I('page');
        if (!$page) {
            $page = 1;
        }
        $size = 10;//显示10条
        $limit = ($page - 1) * $size . ',' . $size;

        $param = array(
            'field' => '*',
            'where' => array(
                'user_id' => $this->userid,
            ),
            'limit' => $limit,
            'order' => 'id DESC'
        );
        $res = D('userExchangeRecord')->getList($param);
        $resCount = D('userExchangeRecord')->getCount($param['where']);
        $result = array();
        $result['items'] = $res;
        $result['totalPages'] = ceil($resCount / $size);
        if ($res) {
            $this->returnApiData($result);
        } else {
            $this->returnApiMsg('1059', '没有数据');
        }
    }

    /**
     * 添加邀请人
     */
    public function addInviter()
    {
        $invitedCode = addslashes(I('invitedcode'));//邀请码也就是邀请人
        if (!$invitedCode) {
            $this->returnApiMsg('1070', '请填写邀请码');
        }
        $res = D('User')->getUserInfo(array('field' => 'id,invitedcode,inviter', 'where' => array('invitedcode' => $invitedCode, 'status' => 0)));//获取邀请人的信息
        if ($res) {
            $nowDate = date("Y-m-d H:i:s", time());
            $curRes = D('User')->getUserInfo(array('field' => 'id,mobile,invitedcode,inviter', 'where' => array('id' => $this->userid, 'status' => 0)));//获取被邀请人的信息
            if ($curRes && ($curRes['mobile'] != '')) {
                if ($curRes['inviter'] == '') {
                    if ($curRes['invitedcode'] == trim($invitedCode)) {
                        $this->returnApiMsg('1089', '邀请人不能填写自己');
                    }
                    if (empty($curRes['inviter'])) {
                        $inviteCoin = getSystemConfig("01", "02");//邀请好友获得的金币
                        $result = D("User")->updateUser(array("id" => $this->userid, "status" => 0), array("inviter" => $invitedCode, "udate" => $nowDate));//被邀请人加金币
                        if ($result) {
                            $param = array(
                                'user_id' => $res['id'],//邀请人id
                                'invte' => $res['invitedcode'],//邀请者
                                'by_user_id' => $curRes['id'],//被邀请人id
                                'invtee' => $curRes['invitedcode'],//被邀请者
                                'udate' => $nowDate,//修改时间
                                'cdate' => $nowDate,//创建时间
                                'flag' => 0,//0为正常，1为领取奖励
                            );
                            D('UserInvite')->insertData($param);
                            //D("User")->updateUser(array("id"=>$res['id'], "status"=>0), array("total_coin"=>array("exp", "`total_coin`+".$inviteCoin), "coin"=>array("exp", "`coin`+".$inviteCoin), "today_coin"=>array("exp", "`today_coin`+".$inviteCoin), "udate"=>$nowDate));//邀请人加金币
                            D('User')->increaseCoin($curRes['id'], $inviteCoin, C('INCOME_TYPE'), '您已成功绑定邀请码，给您赠送' . $inviteCoin . '积分');//赠送积分
                            D('User')->increaseCoin($res['id'], $inviteCoin, C('INCOME_TYPE'), '您的邀请码，被人绑定了，给你赠送' . $inviteCoin . '积分');//赠送积分
                            D("MessageReceive")->insertMessage(C('PERSON_MESSAGE'), $res['id'], '您已成功绑定邀请码，给您赠送' . $inviteCoin . '积分', '请在收益记录中查看', $curRes['id']);//插入消息
                            D("MessageReceive")->insertMessage(C('PERSON_MESSAGE'), $res['id'], '您的邀请码，被人绑定了，给你赠送' . $inviteCoin . '积分', '请在收益记录中查看', $res['id']);//插入消息
                            //$this->_send_message($res['id'], "添加邀请", $curRes['mobile']."设置你为邀请人");
                            $this->returnApiMsg('0', '邀请成功');
                        }
                        $this->returnApiMsg('1119', '已邀请过');
                    } else {
                        $this->returnApiMsg('1071', '邀请失败');
                    }
                } else {
                    $this->returnApiMsg('1072', '您已填写过邀请了');
                }
            } else {
                $this->returnApiMsg('1073', '游客不能邀请');
            }
        } else {
            $this->returnApiMsg('1074', '邀请人不存在');
        }
    }

    /**
     * 收益记录
     */
    public function earnings()
    {
    	//获取版本号2.5
    	$userInfo = SU(md5($this->userid));
    	$versionCode = $userInfo['version_code'];
        if ($versionCode >= 2.5) {
            $type = I("type", 1);// 1为累计收益、2为今日收益、 3为昨日收益、 4为上周收益、 5为上月收益
            $param = array(
                'field' => "SUM(`coin`) AS `coin`, LEFT(`cdate`, 10) AS `cdate`",
                'where' => array(
                    'user_id' => $this->userid,
                    'type' => '收入',
                ),
                'group' => " LEFT(`cdate`, 10) DESC",
            );
            //$sql = "SELECT `id`,SUM(`coin`) AS `coin`,LEFT(`cdate`,10) AS `cdate` FROM `gl_user_consume` WHERE `user_id` = '" . $this->userid . "' AND `type` = '收入'";
            //echo "<pre>";
            $sum = 0;
            $userConsumeMax = 0;
            switch ($type) {
                case 1:
                case '累计收益':
                    $param['where']['LEFT(`cdate`, 10)&LEFT(`cdate`, 10)'] = array(array("egt", date("Y-m-d", strtotime("-1 month"))), array("elt", date("Y-m-d", strtotime("now"))), "_multi" => true);
                    $tempUserConsumeList = D('UserConsume')->getUserList($param);
                    $paramSum = array(
                        'field' => "SUM(`coin`) as `coin`",
                        'where' => array(
                            'user_id' => $this->userid,
                            'type' => '收入',
                        ),
                    );
                    $tempUserConsumeSumInfo = D('UserConsume')->getUserList($paramSum);
                    $sum += $tempUserConsumeSumInfo[0]['coin'];
                    $userConsumeList = array();
                    $userConsumeMaxList = array();
                    if ($tempUserConsumeList) {
                        foreach ($tempUserConsumeList as $k => $v) {
                            $userConsumeList[$v['cdate']] = $v;
                            $userConsumeMaxList[] = $v['coin'];
                        }
                    }
                    $userConsumeMax = max($userConsumeMaxList);
                    unset($tempUserConsumeList);
                    $start = date("Y-m-d", strtotime("-1 month"));
                    $end = date("Y-m-d", strtotime("now"));
                    $userConsumeArr = array();
                    for ($i = 1; $i <= 34; $i++) {
                        if (isset($userConsumeList[$start])) {
                            $userConsumeArr[] = array(
                                'coin' => (int)$userConsumeList[$start]['coin'],
                                'cdate' => $userConsumeList[$start]['cdate'],
                            );
                        } else {
                            $userConsumeArr[] = array(
                                'coin' => 0,
                                'cdate' => $start,
                            );
                        }
                        if ($start == $end) {
                            break;
                        }
                        $start = date("Y-m-d", strtotime("-1 month + " . $i . " day"));
                    }
                    krsort($userConsumeArr);
                    $userConsumeArr = array_values($userConsumeArr);
                    break;
                case 2:
                case '今日收益':
                    $param['where']['LEFT(`cdate`, 10)'] = date("Y-m-d", strtotime("now"));
                    $tempUserConsumeList = D('UserConsume')->getUserList($param);
                    $userConsumeArr = array();
                    if (!empty($tempUserConsumeList)) {
                        $userConsumeArr[] = array(
                            'coin' => (int)$tempUserConsumeList[0]['coin'],
                            'cdate' => $tempUserConsumeList[0]['cdate'],
                        );
                        $sum += (int)$tempUserConsumeList[0]['coin'];
                        $userConsumeMax = (int)$tempUserConsumeList[0]['coin'];
                    } else {
                        $userConsumeArr[] = array(
                            'coin' => 0,
                            'cdate' => date("Y-m-d", strtotime("now")),
                        );
                    }
                    break;
                case 3:
                case '昨日收益':
                    $param['where']['LEFT(`cdate`, 10)'] = date("Y-m-d", strtotime("-1 day"));
                    $tempUserConsumeList = D('UserConsume')->getUserList($param);
                    $userConsumeArr = array();
                    if (!empty($tempUserConsumeList)) {
                        $userConsumeArr[] = array(
                            'coin' => (int)$tempUserConsumeList[0]['coin'],
                            'cdate' => $tempUserConsumeList[0]['cdate'],
                        );
                        $sum += (int)$tempUserConsumeList[0]['coin'];
                        $userConsumeMax = (int)$tempUserConsumeList[0]['coin'];
                    } else {
                        $userConsumeArr[] = array(
                            'coin' => 0,
                            'cdate' => date("Y-m-d", strtotime("-1 day")),
                        );
                    }
                    break;
                case 4:
                case '上周收益':
                    $param['where']['LEFT(`cdate`, 10)&LEFT(`cdate`, 10)'] = array(array("egt", date("Y-m-d", strtotime("last week Monday"))), array("elt", date("Y-m-d", strtotime("last week Sunday"))), "_multi" => true);
                    $tempUserConsumeList = D('UserConsume')->getUserList($param);
                    $userConsumeList = array();
                    $userConsumeMaxList = array();
                    if ($tempUserConsumeList) {
                        foreach ($tempUserConsumeList as $k => $v) {
                            $userConsumeList[$v['cdate']] = $v;
                            $sum += (int)$v['coin'];
                            $userConsumeMaxList[] = $v['coin'];
                        }
                    }
                    $userConsumeMax = max($userConsumeMaxList);
                    unset($tempUserConsumeList);
                    //echo "<pre>";
                    $start = date("Y-m-d", strtotime("last week Monday"));
                    $end = date("Y-m-d", strtotime("last week Sunday"));
                    $userConsumeArr = array();
                    for ($i = 1; $i <= 7; $i++) {
                        if (isset($userConsumeList[$start])) {
                            $userConsumeArr[] = array(
                                'coin' => (int)$userConsumeList[$start]['coin'],
                                'cdate' => $userConsumeList[$start]['cdate'],
                            );
                        } else {
                            $userConsumeArr[] = array(
                                'coin' => 0,
                                'cdate' => $start,
                            );
                        }
                        if ($start == $end) {
                            break;
                        }
                        $start = date("Y-m-d", strtotime("last week Monday + " . $i . " day"));
                    }
                    krsort($userConsumeArr);
                    $userConsumeArr = array_values($userConsumeArr);
                    break;
                case 5:
                case '上月收益':
                    $param['where']['LEFT(`cdate`, 10)&LEFT(`cdate`, 10)'] = array(array("egt", date("Y-m-01", strtotime("last month"))), array("elt", date("Y-m-t", strtotime("last month"))), "_multi" => true);
                    $tempUserConsumeList = D('UserConsume')->getUserList($param);
                    $userConsumeList = array();
                    $userConsumeMaxList = array();
                    if ($tempUserConsumeList) {
                        foreach ($tempUserConsumeList as $k => $v) {
                            $userConsumeList[$v['cdate']] = $v;
                            $sum += (int)$v['coin'];
                            $userConsumeMaxList[] = $v['coin'];
                        }
                    }
                    $userConsumeMax = max($userConsumeMaxList);
                    //print_r($userConsumeList);
                    unset($tempUserConsumeList);
                    //echo "<pre>";
                    $start = date("Y-m-01", strtotime("last month"));
                    $end = date("Y-m-t", strtotime("last month"));
                    $userConsumeArr = array();
                    for ($i = 2; $i <= 31; $i++) {
                        if (isset($userConsumeList[$start])) {
                            $userConsumeArr[] = array(
                                'coin' => (int)$userConsumeList[$start]['coin'],
                                'cdate' => $userConsumeList[$start]['cdate'],
                            );
                        } else {
                            $userConsumeArr[] = array(
                                'coin' => 0,
                                'cdate' => $start,
                            );
                        }
                        if ($start == $end) {
                            break;
                        }
                        if ($i < 10) {
                            $istr = '0' . $i;
                        } else {
                            $istr = $i;
                        }
                        $start = date("Y-m-" . $istr, strtotime("last month"));
                    }
                    krsort($userConsumeArr);
                    $userConsumeArr = array_values($userConsumeArr);
                    break;
                default:
                    //累计收益
                    $param['where']['LEFT(`cdate`, 10)&LEFT(`cdate`, 10)'] = array(array("egt", date("Y-m-d", strtotime("-1 month"))), array("elt", date("Y-m-d", strtotime("now"))), "_multi" => true);
                    $tempUserConsumeList = D('UserConsume')->getUserList($param);
                    $paramSum = array(
                        'field' => "SUM(`coin`) as `coin`",
                        'where' => array(
                            'user_id' => $this->userid,
                            'type' => '收入',
                        ),
                    );
                    $tempUserConsumeSumInfo = D('UserConsume')->getUserList($paramSum);
                    $sum += $tempUserConsumeSumInfo[0]['coin'];
                    $userConsumeList = array();
                    $userConsumeMaxList = array();
                    if ($tempUserConsumeList) {
                        foreach ($tempUserConsumeList as $k => $v) {
                            $userConsumeList[$v['cdate']] = $v;
                            $userConsumeMaxList[] = $v['coin'];
                        }
                    }
                    $userConsumeMax = max($userConsumeMaxList);
                    unset($tempUserConsumeList);
                    //echo "<pre>";
                    $start = date("Y-m-d", strtotime("-1 month"));
                    $end = date("Y-m-d", strtotime("now"));
                    $userConsumeArr = array();
                    for ($i = 1; $i <= 34; $i++) {
                        if (isset($userConsumeList[$start])) {
                            $userConsumeArr[] = array(
                                'coin' => (int)$userConsumeList[$start]['coin'],
                                'cdate' => $userConsumeList[$start]['cdate'],
                            );
                        } else {
                            $userConsumeArr[] = array(
                                'coin' => 0,
                                'cdate' => $start,
                            );
                        }
                        if ($start == $end) {
                            break;
                        }
                        $start = date("Y-m-d", strtotime("-1 month + " . $i . " day"));
                    }
                    krsort($userConsumeArr);
                    $userConsumeArr = array_values($userConsumeArr);
                    break;
            }
            $result = array();
            $result['items'] = $userConsumeArr;
            $result['sum'] = $sum;
            $result['max'] = (int)$userConsumeMax;
            //print_r($result);
            $this->returnApiData($result);
        } else {
            $type = I("type", 1);// 1为累计收益、2为今日收益、 3为昨日收益、 4为上周收益、 5为上月收益
            $page = I("page", 1);
            if (is_numeric($page) < 1) {
                $page = 1;
            }
            $size = 20;
            $limit = ($page - 1) * $size;
            $param = array(
                'field' => "id,coin,intro,cdate",
                'where' => array(
                    'user_id' => $this->userid,
                    'type' => '收入',
                ),
                'order' => "id desc",
                'limit' => $limit . "," . $size,
            );
            switch ($type) {
                case 1:
                    break;
                case 2:
                    $param['where']['cdate'] = array("egt", date("Y-m-d 00:00:00", time()));
                    break;
                case 3:
                    $param['where']['cdate&cdate'] = array(array("egt", date("Y-m-d 00:00:00", strtotime("-1 day"))), array("elt", date("Y-m-d 23:59:59", strtotime("-1 day"))), "_multi" => true);
                    break;
                case 4:
                    $lastWeekDay = date('w');
                    $param['where']['cdate&cdate'] = array(array("egt", date("Y-m-d 00:00:00", strtotime("last week"))), array("elt", date("Y-m-d 23:59:59", strtotime("last week + 6 days"))), "_multi" => true);
                    break;
                case 5:
                    $param['where']['cdate&cdate'] = array(array("egt", date("Y-m-01 00:00:00", strtotime("last month"))), array("lt", date("Y-m-01 00:00:00", strtotime("now"))), "_multi" => true);
                    break;
                default:
                    break;
            }
            $userConsumeList = D('UserConsume')->getUserList($param);
            $userConsumeCount = D('UserConsume')->getCount($param['where']);
            $paramUserConsume = array(
                'field' => "sum(`coin`) as `sum_coin`",
                'where' => $param['where'],
            );
            //print_r($paramUserConsume);
            $userConsumeInfo = D('UserConsume')->getUserInfo($paramUserConsume);
            $result = array();
            $result['items'] = $userConsumeList;
            $result['totalPages'] = ceil($userConsumeCount / $size);
            if ($userConsumeInfo) {
                $result['sum'] = $userConsumeInfo['sum_coin'];
            } else {
                $result['sum'] = '0';
            }
            $this->returnApiData($result);
        }
    }

    /**
     * 收支明细
     */
    public function balance()
    {
        $type = I("type", 0);
        $page = I("page", 1);
        if (is_numeric($page) < 1) {
            $page = 1;
        }
        $size = 20;
        $limit = ($page - 1) * $size;
        $where = array(
            "user_id" => $this->userid
        );
        switch ($type) {
            case 1:
            case '收入':
                $where["type"] = '收入';
                break;
            case 2:
            case '支出':
                $where["type"] = '支出';
                break;
            case 3:
            case '充值':
                $where["type"] = '充值';
                break;
            case 4:
            case '提现':
                $where["type"] = '提现';
                break;
        }
        $param = array(
            "field" => "id,type,coin,surplus_coin,intro,cdate",
            "where" => $where,
            "order" => "id desc",
            "limit" => $limit . "," . $size,
        );
        $userConsumeList = D('UserConsume')->getUserList($param);
        $userConsumeCount = D('UserConsume')->getCount($param['where']);
        $result = array();
        $result['items'] = $userConsumeList;
        $result['totalPages'] = ceil($userConsumeCount / $size);
        $this->returnApiData($result);
    }

    /**
     * 绑定手机
     */
    public function bindMobile()
    {
        $mobile = I("mobile");//手机号
        $verify = I("verify");//验证码
        if (!$mobile) {
            $this->returnApiMsg("1036", "请填写手机号");
        }
        if (!$this->_check_mobile($mobile)) {
            $this->returnApiMsg("1037", "手机号填写错误");
        }
        if (!$verify) {
            $this->returnApiMsg("1038", "请填写验证码");

        }
        $cacheVerify = S('mobileVerify_' . $mobile);
        if (md5($verify) != $cacheVerify) {
            $this->returnApiMsg("1041", "验证码错误");
        }
        $result = array();
        $result['mobile'] = $mobile;
        $this->returnApiData($result);
    }

    /**
     * 绑定支付宝
     */
    public function bindAlipay()
    {
        $account = I("account");
        $name = I("name");
        if (!$account) {
            $this->returnApiMsg("1095", "请填写支付宝账号");
        }
        if (!$name) {
            $this->returnApiMsg("1096", "请填写支付宝真实姓名");
        }
        if ($this->_check_email($account) || $this->_check_mobile($account)) {
            $userInfo = D("User")->getUserInfo(array("where" => array("id" => $this->userid)));
            if ($userInfo) {
                $updateUser = D("User")->updateUser(array("id" => $this->userid), array("alipay_account" => $account, "alipay_name" => $name, "udate" => date("Y-m-d H:i:s", time())));
                if ($updateUser) {
                    $result = array();
                    $result['account'] = $account;
                    $result['name'] = $name;
                    $this->returnApiData($result);
                } else {
                    $this->returnApiMsg("1069", "支付宝账号填写有误");
                }
            } else {
                $this->returnApiMsg("1088", "用户名不存在");
            }
        } else {
            $this->returnApiMsg("1093", "支付宝账号填写不正确");
        }
    }

    /**
     * 绑定微信号
     */
    public function bindWechat()
    {
        $account = I("account");
        $name = I("name");
        if (!$account) {
            $this->returnApiMsg("1109", "请填写微信账号");
        }
        if (!$name) {
            $this->returnApiMsg("1110", "请填写微信真实姓名");
        }
        if ($this->_check_wechat($account)) {//$this->_check_email($account) || $this->_check_mobile($account)
            $userInfo = D("User")->getUserInfo(array("where" => array("id" => $this->userid)));
            if ($userInfo) {
                $updateUser = D("User")->updateUser(array("id" => $this->userid), array("wechat_account" => $account, "wechat_name" => $name, "udate" => date("Y-m-d H:i:s", time())));
                if ($updateUser) {
                    $result = array();
                    $result['account'] = $account;
                    $result['name'] = $name;
                    $this->returnApiData($result);
                } else {
                    $this->returnApiMsg("1111", "微信账号填写有误");
                }
            } else {
                $this->returnApiMsg("1088", "用户名不存在");
            }
        } else {
            $this->returnApiMsg("1112", "微信账号填写不正确");
        }
    }

    /**
     * 版本升级
     */
    public function version()
    {
        $versionInfo = D("Version")->getInfo(array("order" => "id DESC"));
        $result = array();
        if (empty($versionInfo['proportion'])) {
            $result["status"] = 1;
        } else {
            $id = $this->userid;
            $userInfo = D("User")->getUserInfo(array('field' => "uid", 'where' => array("id" => $id)));
            if ($userInfo['uid'] % $versionInfo['proportion'] == 0) {
                $result["status"] = 1;
            } else {
                $result["status"] = 0;
            }
        }
        $this->returnApiData($result);
    }

    /**
     * 收益排名
     */
    public function rank()
    {
        $way = strtolower(I("way"));
        $field = "total_revenue";
        if ($way == 'last_week') {
            $field = "last_week";
        } else if ($way == 'this_week') {
            $field = "week_revenue";
        }
        $result = array();
        $result['my'] = $this->_calcRank($this->userid, $field);
        $this->returnApiData($result);
    }

    /**
     * 上周排行
     */
    public function lastWeekRank()
    {
        $sql = "SELECT (@rowNum:=@rowNum+1) AS rank, a.last_week AS coin, b.nickname FROM `gl_user_revenue_rank` as a left join `gl_user` as b on (a.user_id=b.id), (SELECT (@rowNum :=0) ) c ORDER BY a.last_week DESC LIMIT 10";
        $rankData = D('User')->getQuery($sql);
        $result = array();
        $result['items'] = $rankData;
        $result['my'] = $this->_calcRank($this->userid, "last_week");
        $this->returnApiData($result);
    }

    /**
     * 本周排行
     */
    public function weekRank()
    {
        $sql = "SELECT (@rowNum:=@rowNum+1) AS rank, a.week_revenue AS coin, b.nickname FROM `gl_user_revenue_rank` as a left join `gl_user` as b on (a.user_id=b.id), (SELECT (@rowNum :=0) ) c ORDER BY a.week_revenue DESC LIMIT 10";
        $rankData = D('User')->getQuery($sql);
        $result = array();
        $result['items'] = $rankData;
        $result['my'] = $this->_calcRank($this->userid, "week_revenue");
        $this->returnApiData($result);
    }

    /**
     * 总排行
     */
    public function totalRank()
    {
        $sql = "SELECT (@rowNum:=@rowNum+1) AS rank,a.total_revenue AS coin, b.nickname FROM `gl_user_revenue_rank` as a left join `gl_user` as b on (a.user_id=b.id), (SELECT (@rowNum :=0) ) c ORDER BY a.total_revenue DESC LIMIT 10";
        $rankData = D('User')->getQuery($sql);
        $result = array();
        $result['items'] = $rankData;
        $result['my'] = $this->_calcRank($this->userid, "total_revenue");
        $this->returnApiData($result);
    }

    /**
     * 消息列表
     */
    public function message()
    {
        $type = I('type', 1);//消息类型（1为个人消息，2为系统消息）
        $page = I("page", 1);//分页
        $size = 10;
        $limit = ($page - 1) * $size . "," . $size;
        $param = array();
        if (1 == $type) {
            $param['where'] = array(
                'a.info_type' => $type,
                'b.receiver_account' => $this->userid,
            );
            $param['table'] = 'gl_message as a';
            $param['join'] = 'LEFT JOIN gl_message_receive as b ON a.id=b.message_id';
            $param['order'] = 'a.id DESC';
            $param['field'] = 'a.id,a.info_type as type,a.info_title,a.info_content,a.create_date,'
                . 'b.receiver_account as receiver,b.send_date,b.status';
            $param['limit'] = $limit;
            //查询数据
            $messageList = D("Message")->getJoinQuery($param);
            //查询总数
            $messageCount = D("Message")->getJoinQueryCount($param);
            foreach ($messageList as $k => $v) {
                $messageList[$k]['id'] = _passport_encrypt('gl', $v['id']);//解密id;
                $messageList[$k]['type'] = (int)($v['type']);
                $messageList[$k]['status'] = (int)($v['status']);
            }
        } else if (2 == $type) {
            $messageList = array();
            $messageCount = 0;
            $userInfo = D('User')->getUserInfo(array('where' => array('id' => $this->userid)));
            $this->_calcMessageCount($userInfo['cdate'], $type, $limit);
            $param['where'] = array(
                'a.info_type' => $type,
                'b.receiver_account' => $this->userid,
            );
            $param['table'] = 'gl_message as a';
            $param['join'] = 'LEFT JOIN gl_message_receive as b ON a.id=b.message_id';
            $param['order'] = 'a.id DESC';
            $param['field'] = 'a.id,a.info_type as type,a.info_title,a.info_content,a.create_date,'
                . 'b.receiver_account as receiver,b.send_date,b.status';
            $param['limit'] = $limit;
            //查询数据
            $messageList = D("Message")->getJoinQuery($param);
            //查询总数
            $messageCount = D("Message")->getJoinQueryCount($param);
            foreach ($messageList as $k => $v) {
                $messageList[$k]['id'] = _passport_encrypt('gl', $v['id']);//解密id;
                $messageList[$k]['type'] = (int)($v['type']);
                $messageList[$k]['status'] = (int)($v['status']);
            }
        }

        $result = array();
        $result['items'] = $messageList;
        $result['totalPages'] = ceil($messageCount / $size);
        $this->returnApiData($result);
    }

    /**
     * 消息详情
     */
    public function messageDetail()
    {
        $id = I("id");
        $id = _passport_decrypt('gl', $id);
        //$userInfo = SU(md5($this->userid));
        $param['where'] = array(
            'a.id' => $id,
            'b.receiver_account' => $this->userid,
        );
        $param['table'] = 'gl_message as a';
        $param['join'] = 'LEFT JOIN gl_message_receive as b ON a.id=b.message_id';
        $param['field'] = 'a.id,a.info_type as type,a.info_title,a.info_content,a.create_date,'
            . 'b.receiver_account as receiver,b.send_date,b.status';
        //查询数据
        $messageInfo = D("Message")->getJoinQueryInfo($param);
        $messageInfo["id"] = _passport_encrypt('gl', $messageInfo["id"]);//解密id;
        $messageInfo['type'] = (int)($messageInfo['type']);
        $messageInfo['status'] = (int)($messageInfo['status']);
        $this->returnApiData($messageInfo);
    }

    /**
     * 标记已读
     */
    public function markRead()
    {
        $id = I("id");
        $id = _passport_decrypt('gl', $id);
        //$userInfo = SU(md5($this->userid));
        $where = array("message_id" => $id, "receiver_account" => $this->userid);
        $data = array("status" => 1);
        $res = D("MessageReceive")->updateData($where, $data);
        if (false !== $res) {
            $this->returnApiMsg('0', '标记已读');
        } else {
            $this->returnApiMsg('1057', '操作失败');
        }
    }

    /**
     * 我要举报
     */
    public function report()
    {
        $part = I('part');//举报的栏目
        $title = I('title', '');//举报标题
        $link = I('link', '');//举报链接
        $account = I('account', '');//发布者账号
        $type = I('type');//类型
        $content = I('content');//内容
        $nowDate = date("Y-m-d H:i:s", time());
        if (!in_array(strtolower($part), array('news', 'comment', 'recommend', 'mission'))) {
            $this->returnApiMsg('1114', '不存在的栏目');
        }
        $link = htmlspecialchars_decode($link);
        if (!empty($link)) {
            $info = array();
            if ('recommend' == strtolower($part)) {
                preg_match('/[\?&]id=([-=_\w]+)/', $link, $matches);
                $id = $matches[1];
                $id = _passport_decrypt('gl', $id);
                if (!empty($id)) {
                    $info = D('Recommend')->getInfo(array('field' => 'id,user_id', 'where' => array('id' => $id)));
                }
            } else if ('mission' == strtolower($part)) {
                /* preg_match('/[\?&]id=([-=_\w]+)/', $link, $matches);
                $id = $matches[1];
                $id = _passport_decrypt('gl', $id);
                if (!empty($id)) {
                    $info = D('Mission')->getInfo(array('field' => 'id,user_id', 'where' => array('id' => $id)));
                } */
            	$id = _passport_decrypt('gl', $link);
            	if (!empty($id)) {
            		$info = D('Mission')->getInfo(array('field' => 'id,user_id', 'where' => array('id' => $id)));
            	}
            	$link = C('GL_HOST_URL') . '/index.php?m=home&c=mission&a=detail&id=' . $link;
            }
            if (isset($info['user_id'])) {
                $userInfo = D('User')->getUserInfo(array('field' => 'id,mobile', 'where' => array('id' => $info['user_id'])));
                $account = $userInfo['mobile'];
            }
        }
        $param = array(
            'part' => $part,
            'title' => $title,
            'link' => $link,
            'account' => $account,
            'type' => $type,
            'content' => $content,
            'reporter' => $this->userid,
            'cdate' => $nowDate,
        );
        $ret = D("Report")->insertData($param);
        if ($ret) {
            $this->returnApiMsg('0', '举报成功');
        } else {
            $this->returnApiMsg('1113', '举报失败');
        }

    }
}