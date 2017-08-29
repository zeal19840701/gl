<?php
namespace Home\Controller;
use Think\Controller;
class NewsController extends CommonController {
    
	/* public function index(){
    	$type = addslashes(I('type'));//类型
    	$page = addslashes(I('page'));
    	if(!$type){
    		$type = 'RM';
    	}
    	if(!$page){
    		$page = 1;
    	}
    	 $newsObj = new \Home\Common\NewsApi();
    	 $newList = $newsObj->getList($type, $page);
    	 pr($newList);exit;
    	 $result = array();
    	 if($newList['code'] == 'ok'){
    	 foreach($newList['items'] as $k=>$v){
    	 $result['items'][$k] = $v;
    	 $result['items'][$k]['checkKey'] = _passport_encrypt('gl', $v['checkKey']);
    	 }
    	 $result['totalPages'] = $newList['totalPages'];//总页数
    	 }else{
    	 $result['items'] = array();
    	 $result['totalPages'] = 0;
    	 }
    } */
	
	/**
	 * 新闻列表
	 */
	public function newslist(){
		$type = addslashes(I('type'));//类型
		$page = addslashes(I('page'));
		if(!$type){
			$type = 'RM';
		}
		if(!$page){
			$page = 1;
		}
		$size = 10;//显示10条
		$limit = ($page-1) * $size . ',' . $size;
		$result = array();
		$param = array(
			'field' => 'id',
			'where' => array(
				'column_type' => $type,
			),
			'limit' => $limit,
			'order' => 'publish_time DESC'
		);
		$newsIdList = D("News")->getList($param);
		$newsList = array();
		if(!empty($newsIdList)){
			$ids = array();
			foreach($newsIdList as $k=>$v){
				$ids[] = $v['id'];
			}
			unset($newsIdList);
			$newsList = D("News")->getList(array('field'=>'id,title,publish_time,storage_time,release_person,source,qr_code,public_number,reading_number,spot_number,article_type,function_introduction,original,column_type,thumbnail,comment_num,flag', 'where'=>array('id'=>array('in', $ids))));
		}
		$newsCount = D("News")->getCount($param['where']);
		if(empty($newsList)){
			$result['items'] = array();
		}else{
			foreach ($newsList as $k=>$v){
				$newsList[$k]['id'] = _passport_encrypt('gl', $v['id']);
				$newsList[$k]['qr_code'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $v['qr_code']);
				$newsList[$k]['thumbnail'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $v['thumbnail']);
				//$newsList[$k]['content'] = str_replace("src=\"/data/imgs/", "src=\"http://cdn.weixin.71360.com/", $v['content']);
				unset($newsList[$k]['check_key']);
			}
			$result['items'] = $newsList;
		}
		if(empty($newsCount)){
			$result['totalPages'] = 0;
		}else{
			$result['totalPages'] = ceil($newsCount/$size);
		}
		$this->returnApiData ( $result );
    }
    
    /**
     * 新闻详情接口
     */
    public function newsdetail(){
    	$id = addslashes(I('id'));//类型
    	$pure_id = _passport_decrypt('gl', $id);//解密id
    	if(!$id){
    		$this->returnApiMsg ( '1001', '新闻详情ID不存在' );
    	}
    	if(!$pure_id){
    		$this->returnApiMsg ( '1001', '新闻详情ID不存在' );
    	}
    	$newsInfo = D('News')->getInfo(array('where'=>array('id'=>$pure_id)));
    	//pr($newsInfo);
    	if(!empty($newsInfo)){
    		$newsInfo['id'] = _passport_encrypt('gl', $newsInfo['id']);
    		$newsInfo['qr_code'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $newsInfo['qr_code']);
    		$newsInfo['thumbnail'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $newsInfo['thumbnail']);
    		$newsInfo['content'] = str_replace("src=\"/data/imgs/", "src=\"http://cdn.weixin.71360.com/", $newsInfo['content']);
    		unset($newsInfo['check_key']);
    		$this->returnApiData ( $newsInfo );
    	}else{
    		$this->returnApiMsg ( '1002', '新闻详情不存在' );
    	}
    }
    
    /**
     * 新闻详情页面
     */
    public function detail(){
    	set_time_limit(60);
    	$id = addslashes(I('id'));//类型
    	$pure_id = _passport_decrypt('gl', $id);// 给id解密
    	$userId = I('userid', '', 'addslashes');//用户uid
    	$mobileWidth = I('mobile_width');
    	$mobileHeight = I('mobile_height');
    	if(!$id){
    		echo "新闻详情ID不存在";
    		exit();
    	}
    	if(!$pure_id){
    		echo "新闻详情ID不存在";
    		exit();
    	}
    	$mobile = $_SERVER['HTTP_MOBILE'];
    	write_log(array('detail:', $userId, $_SERVER));
    	$newsInfo = D('News')->getInfo(array('where'=>array('id'=>$pure_id)));
    	if(!empty($newsInfo)){
    		//添加猜你喜欢
    		D('News')->addGuessLike($pure_id, $userId, $newsInfo['column_type']);
    		//正文
    		$newsInfo['id'] = _passport_encrypt('gl', $newsInfo['id']);
    		$newsInfo['qr_code'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $newsInfo['qr_code']);
    		$newsInfo['thumbnail'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $newsInfo['thumbnail']);
    		$newsInfo['content'] = str_replace("src=\"/data/imgs/", "src=\"http://cdn.weixin.71360.com/", $newsInfo['content']);
    		$newsInfo['content'] = preg_replace('/(<img\s+[^>]*?)style=\".*?\"/', '$1 ', $newsInfo['content']);
    		//$newsInfo['content'] = preg_replace('/(<img\s+[^>]*?)style=\".*?\"/', '<img $1 style="color: rgb(62, 62, 62); font-size: 16px; line-height: 25.6px; background-color: rgb(255, 255, 255); box-sizing: border-box !important; word-wrap: break-word !important; visibility: visible !important; width: auto !important; height: auto !important;"', $newsInfo['content']);
    		$newsInfo['content'] = preg_replace('/(<img\s+[^>]*?)/', '$1 style="color: rgb(62, 62, 62); font-size: 16px; line-height: 25.6px; background-color: rgb(255, 255, 255); box-sizing: border-box !important; word-wrap: break-word !important; visibility: visible !important; width: auto !important; height: auto !important;"', $newsInfo['content']);
    		//$newsInfo['content'] = preg_replace('/(<img\s+[^>]*?)class=\"__bg_gif\"/', '<img $1 ', $newsInfo['content']);
    		unset($newsInfo['check_key']);
    		/* $params = array(
    			'where' => array(
    				'column_type' => $newsInfo['column_type']?$newsInfo['column_type']:'RM',
    			),
    			'field' => 'id,title,thumbnail,column_type,public_number,comment_num,storage_time',
    			'limit' => 5
    			
    		);
    		$likeInfo = D('News')->getList($params);
    		foreach($likeInfo as $k=>$v){
    			$likeInfo[$k]['id'] = _passport_encrypt('gl', $v['id']);
    			$likeInfo[$k]['thumbnail'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $v['thumbnail']);
    		} */
    		$likeInfo = D('News')->guessLike($userId);//猜你喜欢
    		$this->assign("newsInfo", $newsInfo);
    		$this->assign("likeInfo", $likeInfo);
    		$this->assign("mobile", $mobile);
    		$this->assign("mobile_width", $mobileWidth);
    		$this->assign("mobile_height", $mobileHeight);
    		$this->display();
    	}else{
    		echo "新闻详情不存在";
    		exit();
    	}
    	
    }
    
    /**
     * 新闻分类
     */
    public function newscate(){
    	$param = array(
    		'field' => 'cate, cate_name, flag',
    		'order' => ' sort ASC',
    	);
    	
    	$newsCateList = D('NewsCategory')->getList($param);
    	$result = array();
    	foreach($newsCateList as $k=>$v){
    		switch ($v['flag']){
    			case 1:
    				$result['list'][] = $v;
    				break;
    			case 2:
    			default:
    				$result['collocate'][] = $v;
    				break;
    		}
    	}
    	//pr($result);exit;
    	$this->returnApiData ( $result );
    }
    
    public function test1(){
    	$type = addslashes(I('type'));//类型
    	$page = addslashes(I('page'));
    	if(!$type){
    		$type = 'RM';
    	}
    	if(!$page){
    		$page = 1;
    	}
    	$size = 10;//显示10条
    	$limit = ($page-1) * $size . ',' . $size;
    	$result = array();
    	$param = array(
    			'where' => array(
    					'column_type' => $type,
    			),
    			'order' => 'storage_time DESC'
    	);
    	$newsCount = D("News")->getCount($param['where']);
    	$page = new \Home\Common\Page($newsCount, $size);// 加载分页类
    	$param['limit'] = $page->firstRow.','.$page->listRows;
    	$newsList = D("News")->getList($param);
    	/* if(empty($newsList)){
    		$result = array();
    	}else{
    		foreach ($newsList as $k=>$v){
    			$newsList[$k]['id'] = _passport_encrypt('gl', $v['id']);
    			$newsList[$k]['qr_code'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $v['qr_code']);
    			$newsList[$k]['thumbnail'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $v['thumbnail']);
    			$newsList[$k]['content'] = str_replace("src=\"/data/imgs/", "src=\"http://cdn.weixin.71360.com/", $v['content']);
    			unset($newsList[$k]['check_key']);
    		}
    		$result = $newsList;
    	} */
    	
    	$show = $page->show();
    	
    	$param = array(
    			'field' => 'cate, cate_name, flag',
    			'order' => ' sort ASC',
    	);
    	$cateList = D('NewsCategory')->getList($param);
    	
    	$this->assign('result', $newsList);
    	$this->assign('cateList', $cateList);
    	$this->assign('page', $show);
    	$this->display();
    }
}