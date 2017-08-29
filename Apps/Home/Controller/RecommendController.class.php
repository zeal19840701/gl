<?php
namespace Home\Controller;
use Think\Controller;
class RecommendController extends AuthController {
    
	/* public function index(){
    	echo 11;
    } */
	
	/**
	 * 推荐分类
	 */
	public function cate(){
		$result = array();
		$cateList = D('RecommendCate')->getCateList();//查询推荐分类
		if($cateList){
			foreach($cateList as $k=>$v){
				unset($v['id']);
				$result[] = $v;
			}
		}
		$this->returnApiData ( $result );
	}
	
	/**
	 * 发布推荐
	 */
    public function publish(){
    	set_time_limit(120);//设置时间
    	$publishData = I('publishData');//传入数据
    	$publishData = json_decode(htmlspecialchars_decode($publishData), true);//把json转成数组形式
    	//print_r($publishData);exit;
    	$newpublishData = array();
    	$newpublishData['phrase'] = $publishData['phrase'];
    	$newpublishData['link'] = trim($publishData['link']);
    	if(!$newpublishData['link']){
    		$code = 1004;
    		$msg = '内容链接不能为空';
    		$this->returnApiMsg ($code, $msg );
    	}
    	$regex = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';
    	if(!preg_match($regex, $newpublishData['link'])){
    		$code = 1005;
    		$msg = '内容链接不是正确的url地址';
    		$this->returnApiMsg ($code, $msg );
    	}
    	if((substr(strtolower($newpublishData['link']), 0, 24) != 'http://mp.weixin.qq.com/') && substr(strtolower($newpublishData['link']), 0, 25) != 'https://mp.weixin.qq.com/'){
    		$code = 1006;
    		$msg = '内容链接不是微信地址';
    		$this->returnApiMsg ($code, $msg );
    	}
    	if(!is_numeric($publishData['award'])){
    		$this->returnApiMsg ('1103', '奖励不是数字' );
    	}
    	if(!is_numeric($publishData['copies'])){
    		$this->returnApiMsg ('1104', '份数不是数字' );
    	}
    	$userInfo = D('User')->getUserInfo(array('where'=>array('id'=>$this->userid)));
    	$recommendCoin = D('Recommend')->getPushlishCoin($this->userid);
    	$publishTotalCoin = $publishData['award'] * $publishData['copies'];
    	if(($userInfo['coin']-$recommendCoin) < $publishTotalCoin){
    		$this->returnApiMsg ('1115', '余额不足，无法发布' );
    	}
    	//$missionTotalCoin = D('Recommend')->getRecommendCoin($this->userid);//获得推荐总金币数
    	//@todo 暂时注释
    	/* $totalCoin = (int)$publishData['award'] * (int)$publishData['copies'];
    	$userInfo = D("User")->getInfo(array("id"=>$this->userid));
    	if($userInfo){
    		if($userInfo['coin'] < $totalCoin){
    			$this->returnApiMsg ('1105', '金币不足，无法发布' );
    		}
    	} */
    	$newpublishData['cate_id'] = trim($publishData['category']);
    	$newpublishData['award'] = $publishData['award'];
    	$newpublishData['total_copies'] = $publishData['copies'];
    	$newpublishData['copies'] = $publishData['copies'];
    	$newpublishData['start_time'] = $publishData['start_time'];
    	$newpublishData['end_time'] = date("Y-m-d 23:59:59", strtotime($publishData['end_time']));
    	
    	$newpublishData['type'] = $publishData['type'];
    	$newpublishData['addition_type'] = $publishData['addition_type'];
    	$newpublishData['addition_content'] = $publishData['addition_content'];
    	$newpublishData['addition_style'] = $publishData['addition_style'];
    	$newpublishData['user_id'] = $this->userid;
    	$wxData = D('Recommend')->grabWeixin($newpublishData['link']);//获取微信标题和图片
    	$nowTime = date("Y-m-d H:i:s", time());
    	$newpublishData['title'] = $wxData['title'];
    	$newpublishData['thumbnail'] = $wxData['img_path'];
    	$newpublishData['update_time'] = $nowTime;
    	$newpublishData['create_time'] = $nowTime;
    	$code = '0';
    	$msg = '发布成功';
    	switch ($publishData['type']){
    		case 1:
    			$res = D('Recommend')->insertRecommend($newpublishData);
    			if(!$res){
    				$code = '1003';
    				$msg = '发布失败';
    			}else{
    				//@todo 暂时注释
    				/* $res = D("User")->updateUser(array("id"=>$this->userid, "coin"=>array("egt", $totalCoin)), array("coin"=>array("exp", "`coin`-". $totalCoin)));
    				if($res){
    					D("UserConsume")->insertData(array("user_id"=>$this->userid, "coin"=>$totalCoin, "surplus_coin"=>"", "type"=>"支出", "intro"=>"", "cdate"=>$nowTime));
    				} */
    				
    			}
    			break;
    		case 2:
    			$newsObj = new \Home\Common\NewsApi();
    			$wechatInfo = $newsObj->getWechatInfo($newpublishData['link']);
    			if(!$wxData['title']){
    				$newpublishData['title'] = $wechatInfo['title'];
    			}
    			$newpublishData['content'] = $wechatInfo['content'];
    			$newpublishData['source'] = $wechatInfo['source'];
    			$newpublishData['publish_time'] = $wechatInfo['publishTime'];
    			$newpublishData['public_number'] = $wechatInfo['public_number'];
    			$newpublishData['release_person'] = $wechatInfo['release_person'];
    			$newpublishData['original'] = $wechatInfo['Original'];
    			$newpublishData['article_type'] = $wechatInfo['article_type'];
    			$newpublishData['function_introduction'] = $wechatInfo['function_introduction'];
    			$res = D('Recommend')->insertRecommend($newpublishData);
    			if(!$res){
    				$code = '1003';
    				$msg = '发布失败';
    			}
    			break;
    		default:
    			$code = '1003';
    			$msg = '发布失败';
    			break;
    	}
    	if($code){
    		@unlink(APP_ROOT . $wxData['img_path']);
    	}
    	$this->returnApiMsg ($code, $msg );
    }
    
    /**
     * 扣金币
     */
    protected function _deductCoin(){
    	
    }
    
    /**
     * 修改推荐
     */
    public function updatePublish(){
    	set_time_limit(120);//设置时间
    	$id = I('id');//传入id
    	$id = _passport_decrypt('gl', $id);
    	if(!$id){
    		$code = '1061';
    		$msg = '推荐id不能为空';
    		$this->returnApiMsg ($code, $msg );
    	}
    	$publishData = I('publishData');//传入数据
    	$publishData = json_decode(htmlspecialchars_decode($publishData), true);//把json转成数组形式
    	//print_r($publishData);exit;
    	$newpublishData = array();
    	$newpublishData['phrase'] = $publishData['phrase'];
    	$newpublishData['link'] = trim($publishData['link']);
    	if(!$newpublishData['link']){
    		$code = 1004;
    		$msg = '内容链接不能为空';
    		$this->returnApiMsg ($code, $msg );
    	}
    	$regex = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';
    	if(!preg_match($regex, $newpublishData['link'])){
    		$code = 1005;
    		$msg = '内容链接不是正确的url地址';
    		$this->returnApiMsg ($code, $msg );
    	}
    	if((substr(strtolower($newpublishData['link']), 0, 24) != 'http://mp.weixin.qq.com/') && substr(strtolower($newpublishData['link']), 0, 25) != 'https://mp.weixin.qq.com/'){
    		$code = 1006;
    		$msg = '内容链接不是微信地址';
    		$this->returnApiMsg ($code, $msg );
    	}
    	//D('Recommend')->checkRecommend($this->userid);//检查推荐是否过期，过期就修改状态
    	$recommendInfo = D('Recommend')->getInfo(array('where'=>array('id'=>$this->userid)));
    	/* $oldRecommendTotalCoin = $recommendInfo['award'] * $recommendInfo['copies'];//原来发布总金币
    	$newReccomendTotalCoin = $publishData['award'] * $publishData['copies'];//新提交的总金币
    	$surplusRecommendTotalCoin = $newReccomendTotalCoin-$oldRecommendTotalCoin;//剩余的总金币 */
    	$status = $this->_check_status($recommendInfo);
    	if($status==C('ON_GOING_STATUS')){
    		$this->returnApiMsg ('1117', '推荐进行中，无法编辑' );
    	}
    	/* $userInfo = D('User')->getInfo(array('where'=>array('id'=>$this->userid)));
    	$publishTotalCoin = $publishData['award'] * $publishData['copies'];
    	if($userInfo['coin'] > $surplusRecommendTotalCoin){
    		$this->returnApiMsg ('1116', '余额不足，无法编辑' );
    	} */
    	$userInfo = D('User')->getUserInfo(array('where'=>array('id'=>$this->userid)));
    	$recommendCoin = D('Recommend')->getPushlishCoin($this->userid);
    	$publishTotalCoin = $publishData['award'] * $publishData['copies'];
        $digt = $publishTotalCoin - ($recommendInfo['award'] * $recommendInfo['copies']);
        if($userInfo['coin'] < ($recommendCoin + $digt) ){
    	//if(($userInfo['coin']-$recommendCoin) < $publishTotalCoin){
    		$this->returnApiMsg ('1116', '余额不足，无法编辑' );
    	}
    	//$newpublishData['user_id'] = 'username';
    	$newpublishData['cate_id'] = trim($publishData['category']);
    	$newpublishData['award'] = $publishData['award'];
    	$newpublishData['total_copies'] = $publishData['copies'];
    	$newpublishData['copies'] = $publishData['copies'];
    	 
    	$newpublishData['start_time'] = $publishData['start_time'];
    	$newpublishData['end_time'] = date("Y-m-d 23:59:59", strtotime($publishData['end_time']));
    	 
    	$newpublishData['type'] = $publishData['type'];
    	$newpublishData['addition_type'] = $publishData['addition_type'];
    	$newpublishData['addition_content'] = $publishData['addition_content'];
    	$newpublishData['addition_style'] = $publishData['addition_style'];
    	//$newpublishData['user_id'] = $this->userid;
    	$wxData = D('Recommend')->grabWeixin($newpublishData['link']);//获取微信标题和图片
    	$nowTime = date("Y-m-d H:i:s", time());
    	$newpublishData['title'] = $wxData['title'];
    	$newpublishData['thumbnail'] = $wxData['img_path'];
    	$newpublishData['update_time'] = $nowTime;
    	$code = '0';
    	$msg = '发布成功';
    	switch ($publishData['type']){
    		case 1:
    			$res = D('Recommend')->updateRecommend(array('id'=>$id, 'user_id'=>$this->userid), $newpublishData);
    			if(!$res){
    				$code = '1003';
    				$msg = '发布失败';
    			}
    			break;
    		case 2:
    			$newsObj = new \Home\Common\NewsApi();
    			$wechatInfo = $newsObj->getWechatInfo($newpublishData['link']);
    			if(!$wxData['title']){
    				$newpublishData['title'] = $wechatInfo['title'];
    			}
    			$newpublishData['content'] = $wechatInfo['content'];
    			$newpublishData['source'] = $wechatInfo['source'];
    			$newpublishData['publish_time'] = $wechatInfo['publishTime'];
    			$newpublishData['public_number'] = $wechatInfo['public_number'];
    			$newpublishData['release_person'] = $wechatInfo['release_person'];
    			$newpublishData['original'] = $wechatInfo['Original'];
    			$newpublishData['article_type'] = $wechatInfo['article_type'];
    			$newpublishData['function_introduction'] = $wechatInfo['function_introduction'];
    			$res = D('Recommend')->updateRecommend(array('id'=>$id, 'user_id'=>$this->userid), $newpublishData);
    			if(!$res){
    				$code = '1003';
    				$msg = '发布失败';
    			}
    			break;
    		default:
    			$code = '1003';
    			$msg = '发布失败';
    			break;
    	}
    	if($code){
    		@unlink(APP_ROOT . $wxData['img_path']);
    	}
    	$this->returnApiMsg ($code, $msg );
    }
    
    /**
     * 进行中推荐编辑
     */
    public function onGoingRecommendEdit(){
    	set_time_limit(120);//设置时间
    	$id = I('id');//传入id
    	$id = _passport_decrypt('gl', $id);
    	if(!$id){
    		$code = '1061';
    		$msg = '推荐id不能为空';
    		$this->returnApiMsg ($code, $msg );
    	}
    	$publishData = I('publishData');//传入数据
    	$publishData = json_decode(htmlspecialchars_decode($publishData), true);//把json转成数组形式
    	//print_r($publishData);exit;
    	$newpublishData = array();
    	$newpublishData['phrase'] = $publishData['phrase'];
    	$newpublishData['link'] = trim($publishData['link']);
    	if(!$newpublishData['link']){
    		$code = 1004;
    		$msg = '内容链接不能为空';
    		$this->returnApiMsg ($code, $msg );
    	}
    	$regex = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';
    	if(!preg_match($regex, $newpublishData['link'])){
    		$code = 1005;
    		$msg = '内容链接不是正确的url地址';
    		$this->returnApiMsg ($code, $msg );
    	}
    	if((substr(strtolower($newpublishData['link']), 0, 24) != 'http://mp.weixin.qq.com/') && substr(strtolower($newpublishData['link']), 0, 25) != 'https://mp.weixin.qq.com/'){
    		$code = 1006;
    		$msg = '内容链接不是微信地址';
    		$this->returnApiMsg ($code, $msg );
    	}
    	//D('Recommend')->checkRecommend($this->userid);//检查推荐是否过期，过期就修改状态
    	$recommendInfo = D('Recommend')->getInfo(array('where'=>array('id'=>$this->userid)));
    	/* $oldRecommendTotalCoin = $recommendInfo['award'] * $recommendInfo['copies'];//原来发布总金币
    	 $newReccomendTotalCoin = $publishData['award'] * $publishData['copies'];//新提交的总金币
    	 $surplusRecommendTotalCoin = $newReccomendTotalCoin-$oldRecommendTotalCoin;//剩余的总金币 */
    	$status = $this->_check_status($recommendInfo);
    	if($status == 1){
    		$this->returnApiMsg ('1117', '推荐未开始，无法编辑' );
    	}
    	/* $userInfo = D('User')->getInfo(array('where'=>array('id'=>$this->userid)));
    	 $publishTotalCoin = $publishData['award'] * $publishData['copies'];
    	 if($userInfo['coin'] > $surplusRecommendTotalCoin){
    	 $this->returnApiMsg ('1116', '余额不足，无法编辑' );
    	 } */
    	$userInfo = D('User')->getUserInfo(array('where'=>array('id'=>$this->userid)));
    	$recommendCoin = D('Recommend')->getPushlishCoin($this->userid);
    	$publishTotalCoin = $publishData['award'] * $publishData['copies'];
    	$digt = $publishTotalCoin - ($recommendInfo['award'] * $recommendInfo['copies']);
    	if($userInfo['coin'] < ($recommendCoin + $digt) ){
    		//if(($userInfo['coin']-$recommendCoin) < $publishTotalCoin){
    		$this->returnApiMsg ('1116', '余额不足，无法编辑' );
    	}
    	D('Recommend')->updateRecommend(array('id'=>$id, 'user_id'=>$this->userid), array('flag'=>1));
    	$newpublishData['user_id'] = $this->userid;
    	$newpublishData['cate_id'] = trim($publishData['category']);
    	$newpublishData['award'] = $publishData['award'];
    	$newpublishData['total_copies'] = $publishData['copies'];
    	$newpublishData['copies'] = $publishData['copies'];
    	
    	$newpublishData['start_time'] = $publishData['start_time'];
    	$newpublishData['end_time'] = date("Y-m-d 23:59:59", strtotime($publishData['end_time']));
    	
    	$newpublishData['type'] = $publishData['type'];
    	$newpublishData['addition_type'] = $publishData['addition_type'];
    	$newpublishData['addition_content'] = $publishData['addition_content'];
    	$newpublishData['addition_style'] = $publishData['addition_style'];
    	
    	$wxData = D('Recommend')->grabWeixin($newpublishData['link']);//获取微信标题和图片
    	$nowTime = date("Y-m-d H:i:s", time());
    	$newpublishData['title'] = $wxData['title'];
    	$newpublishData['thumbnail'] = $wxData['img_path'];
    	$newpublishData['update_time'] = $nowTime;
    	$newpublishData['create_time'] = $nowTime;
    	$code = '0';
    	$msg = '发布成功';
    	switch ($publishData['type']){
    		case 1:
    			$res = D('Recommend')->updateRecommend(array('id'=>$id, 'user_id'=>$this->userid), $newpublishData);
    			if(!$res){
    				$code = '1003';
    				$msg = '发布失败';
    			}
    			break;
    		case 2:
    			$newsObj = new \Home\Common\NewsApi();
    			$wechatInfo = $newsObj->getWechatInfo($newpublishData['link']);
    			if(!$wxData['title']){
    				$newpublishData['title'] = $wechatInfo['title'];
    			}
    			$newpublishData['content'] = $wechatInfo['content'];
    			$newpublishData['source'] = $wechatInfo['source'];
    			$newpublishData['publish_time'] = $wechatInfo['publishTime'];
    			$newpublishData['public_number'] = $wechatInfo['public_number'];
    			$newpublishData['release_person'] = $wechatInfo['release_person'];
    			$newpublishData['original'] = $wechatInfo['Original'];
    			$newpublishData['article_type'] = $wechatInfo['article_type'];
    			$newpublishData['function_introduction'] = $wechatInfo['function_introduction'];
    			//$res = D('Recommend')->updateRecommend(array('id'=>$id, 'user_id'=>$this->userid), $newpublishData);
    			$res = D('Recommend')->insertRecommend($newpublishData);
    			if(!$res){
    				$code = '1003';
    				$msg = '发布失败';
    			}
    			break;
    		default:
    			$code = '1003';
    			$msg = '发布失败';
    			break;
    	}
    	if($code){
    		@unlink(APP_ROOT . $wxData['img_path']);
    	}
    	$this->returnApiMsg ($code, $msg );
    	
    }
    
    /**
     * 删除推荐
     */
    public function delPublish(){
    	$id = I('id');//传入id
    	$id = _passport_decrypt('gl', $id);
    	if(!$id){
    		$this->returnApiMsg ('1061', '推荐id不能为空' );
    	}
    	$res = D('Recommend')->delRecommend(array('id'=>$id, 'user_id'=>$this->userid));
    	if(false !== $res){
    		D('RecommendShare')->delRecommend(array('rec_id'=>$id));//分享的记录要删除
    		$this->returnApiMsg ('0', '删除成功' );
    	}else{
    		$this->returnApiMsg ('1060', '删除失败' );
    	}
    }
    
    /**
     * 暂停推荐
     */
    public function pausePublish(){
    	$id = I('id');//传入id
    	$id = _passport_decrypt('gl', $id);
    	if(!$id){
    		$this->returnApiMsg ('1061', '推荐id不能为空' );
    	}
    	$res = D('Recommend')->updateRecommend(array('id'=>$id, 'user_id'=>$this->userid), array('flag'=>2));
    	if(false !== $res){
    		$this->returnApiMsg ('0', '操作成功' );
    	}else{
    		$this->returnApiMsg ('1057', '操作失败' );
    	}
    }
    
    /**
     * 开启推荐
     */
    public function startPublish(){
    	$id = I('id');//传入id
    	$id = _passport_decrypt('gl', $id);
    	if(!$id){
    		$this->returnApiMsg ('1061', '推荐id不能为空' );
    	}
    	$res = D('Recommend')->updateRecommend(array('id'=>$id, 'user_id'=>$this->userid), array('flag'=>0));
    	if(false !== $res){
    		$this->returnApiMsg ('0', '操作成功' );
    	}else{
    		$this->returnApiMsg ('1057', '操作失败' );
    	}
    }
    
    /**
     * 推荐列表
     */
    public function reclist(){
    	$type = addslashes(I('type'));//分类
    	$page = addslashes(I('page'));
    	/* if(!$type){
    		$type = 'CY';
    	} */
    	if(!$page){
    		$page = 1;
    	}
    	$size = 10;//显示10条
    	$limit = ($page-1) * $size . ',' . $size;
    	$nowTime = date("Y-m-d H:i:s", time());
    	$result = array();
    	$where = array();
    	//$whereTotal = array();
    	if(!empty($type)){
    		$where['a.cate_id'] = $type;//按分类
    		//$whereTotal['cate_id'] = $type;//按分类
    	}
    	$where['a.start_time'] = array('elt', $nowTime);//开始时间
    	$where['a.end_time'] = array('egt', $nowTime);//结束时间
    	$where['a.copies'] = array('gt', 0);
    	$where['a.flag'] = 0;
    	$where['b.coin'] = array('gt', 0);
    	
    	/* $whereTotal['start_time'] = array('elt', $nowTime);//开始时间
    	$whereTotal['end_time'] = array('egt', $nowTime);//结束时间
    	$whereTotal['copies'] = array('gt', 0);
    	$whereTotal['flag'] = 0;
    	$whereTotal['b.coin'] = array('gt', 0); */
    	
    	//$where['a.user_id'] = $this->userid;
    	//$whereTotal['user_id'] = $this->userid;
    	$param = array(
    		'where' => $where,
    		'join' => 'LEFT JOIN `gl_user` AS b ON a.user_id=b.id',
    		'field' => 'a.id,a.user_id,a.phrase,a.cate_id,a.award,a.total_copies,a.copies,a.start_time,a.end_time,a.read_number,a.exposure_number,a.type,a.title,a.thumbnail,b.nickname,b.head_pic',
    		'limit' => $limit,
    		'order' => 'a.create_time DESC'
    	);
    	$recommendList = D('Recommend')->getRecommendForUserList($param);//查询推荐列表
    	$recommendCate = D('RecommendCate')->getList();//查询推荐分类
    	foreach ($recommendList as $k=>$v){
    		$cate_id = $v['cate_id'];
    		if($cate_id){
    			$recommendList[$k]['cate_name'] = $recommendCate[$cate_id]['cate_name'];//获取分类名称
    		}
    		$recommendList[$k]['id'] = _passport_encrypt('gl', $v['id']);
    		$recommendList[$k]['thumbnail'] = C('DATA_IMG_URL') . $v['thumbnail'];//获取分类名称
    		if(empty($v['head_pic'])){
    			$recommendList[$k]['head_pic'] = C('DATA_IMG_URL') . C('DATA_IMG_HEAD_PIC');
    		}else{
    			$recommendList[$k]['head_pic'] = C('DATA_IMG_URL') . $v['head_pic'];
    		}
    	}
    	$recommendCount = D("Recommend")->getRecommendForUserCount($param);//查询总数量
    	$result['items'] = $recommendList;//赋值到列表
    	$result['totalPages'] = ceil($recommendCount/$size);//计算页数
    	$this->returnApiData ( $result );
    }
    
    /**
     * 详情页
     */
    public function detail(){
    	$id = addslashes(I('id'));//id
    	$this->_logs(array('详情页detail的id:', $id));
    	$pure_id = _passport_decrypt('gl', $id);//解密id
    	$this->_logs(array('详情页detail的id解码之后的:', $pure_id));
    	if(!$id){
    		$this->returnApiMsg ( '1007', '推荐详情ID不存在' );
    	}
    	if(!$pure_id){
    		$this->returnApiMsg ( '1007', '推荐详情ID不存在' );
    	}
    	$result = array();
    	$param = array(
    		'where' => array(
    			'a.id' => $pure_id
    		),
    		'join' => 'LEFT JOIN `gl_user` AS b ON a.user_id=b.id',
    		'field' => 'a.*,b.nickname,b.head_pic',
    	);
    	$recommendInfo = D('Recommend')->getRecommendForUserInfo($param);
    	if(empty($recommendInfo)){
    		$this->returnApiMsg ( '1007', '推荐详情ID不存在' );
    	}
    	$nowTime = date("Y-m-d H:i:s", time());
    	if($recommendInfo){
    		$cate_id = $recommendInfo['cate_id'];
    		if($cate_id){
    			$recommendCate = D('RecommendCate')->getCateList();//查询推荐分类
    			$recommendInfo['cate_name'] = $recommendCate[$cate_id]['cate_name'];//获取分类名称
    		}
    		$recommendInfo['status'] = $this->_check_status($recommendInfo);//推荐状态
    		$recommendInfo['id'] = _passport_encrypt('gl', $recommendInfo['id']);
    		$recommendInfo['end_time'] = substr($recommendInfo['end_time'], 0, 16);
    		$recommendInfo['thumbnail'] = C('DATA_IMG_URL') . $recommendInfo['thumbnail'];//获取分类名称
    		$recommendInfo['flag'] = (int)$recommendInfo['flag'];
    		if(empty($recommendInfo['head_pic'])){
    			$recommendInfo['head_pic'] = C('DATA_IMG_URL') . C('DATA_IMG_HEAD_PIC');
    		}else{
    			$recommendInfo['head_pic'] = C('DATA_IMG_URL') . $recommendInfo['head_pic'];
    		}
    		/* $distInfo = D('recommendShare')->getInfo(array('field'=>'id,dist', 'order'=>'id DESC', 'where'=>array('user_id'=>$this->userid, 'rec_id'=>$recommendInfo['id'])));
    		$distCount = (int)$distInfo['dist']; */
    		$recommendInfo['share_url'] = C('GL_HOST_URL') . '/index.php?m=home&c=share&a=index&id=' . $recommendInfo['id'] . '&userid=' . $this->userid.'&distid=';
    		$recommendInfo['dist_count'] = 0;
    		/* if($recommendInfo['flag'] == 2){
    			$recommendInfo['status'] = C('PAUSE_STATUS'); //'已暂停';
    		}else if($recommendInfo['start_time'] > $nowTime){
    			$recommendInfo['status'] = C('NOT_BEGIN_STATUS'); //'未开始';
    		}else if($recommendInfo['start_time'] <= $nowTime && $recommendInfo['end_time'] >= $nowTime){
    			$recommendInfo['status'] = C('ON_GOING_STATUS'); //'推广中';
    		}else{
    			$recommendInfo['status'] = C('FINISH_STATUS'); //'已结束';
    		} */
    	}
    	
    	$result['item'] = $recommendInfo;
    	//
    	$sql = "SELECT a.*,b.`nickname`,b.`head_pic` FROM `gl_recommend_share` AS a LEFT JOIN `gl_user` AS b ON (a.`user_id`=b.`id`) WHERE rec_id='" . $pure_id . "' GROUP BY user_id limit 10";
    	$recShareList = D('RecommendShare')->getQuery($sql);//查询分享的人员列表
    	if($recShareList){
    		foreach($recShareList as $k=>$v){
    			$recShareList[$k]['head_pic'] = C('DATA_IMG_URL') . $v['head_pic'];
    		}
    	}
    	$result['share'] = $recShareList;
    	$this->returnApiData ( $result );
    	//pr($result);
    }
    
    public function article(){
    	$id = addslashes(I('id'));//推荐id
    	$id = _passport_decrypt('gl', $id);//解密id
    	if(!$id){
    		echo "推荐ID不存在";
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
    	//判断有则进入
    	if(!empty($recommendInfo)){
    		$recommendInfo['addition_content'] = str_replace(array("\r\n", "\n", "\r"), "%0D%0A", $recommendInfo['addition_content']);
    		$recUrl = "/index.php?m=home&c=share&a=preview&content=" . $recommendInfo['addition_content'] . "&data=" . urlencode($recommendInfo['addition_style']);
    		//print_r($recommendInfo);
    		//echo $recommendInfo['type'];
    		if($recommendInfo['type'] == 1){
    			header("Location:" . $recommendInfo['link']);
    		}else if($recommendInfo['type'] == 2){
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
    
}