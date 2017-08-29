<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
    	$version = D('Version')->getInfo(array('order'=>'id DESC'));
    	
    	$recommendList = D('WebsiteRecommend')->getList(array('limit'=>'0,3'));
    	
    	$this->assign('title', '金锁-人人都是金主！');
    	$this->assign('version', $version);
    	$this->assign('recommend_list', $recommendList);
        $this->display();
    }

    public function newsList(){
    	$p = I('get.p', 1);
    	$size = 12;
    	$limit = ($p-1)*$size . ',' . $size;
    	$param = array(
    		'field' => '*',
    		'where' => array(
    			
    		),
    		'order' => 'id DESC',
    		'limit' => $limit,
    			
    	);
    	$newsList = D('WebsiteNews')->getList($param);
    	$newsCount = D('WebsiteNews')->getCount($param);
    	$page = new \Home\Common\Page($newsCount,$size);// 实例化分页类 传入总记录数和每页显示的记录数(25)
    	$show = $page->show();// 分页显示输出
    	
    	$this->assign('title', '新闻列表');
    	$this->assign('news_list', $newsList);
    	$this->assign('page', $show);
        $this->display();
    }

    public function newsDetail(){
    	$id = I('get.id');
    	$param = array(
    		'field'=>'*',
    		'where'=>array(
    			'id'=>$id
    		),
    	);
    	$apiWebsiteUrl = C('API_WEBSITE_URL');
    	$newsInfo = D('WebsiteNews')->getInfo($param);
    	$newsInfo['content'] = str_replace("src=\"/Public/upload/image/", "src=\"" . $apiWebsiteUrl . "/Public/upload/image/", htmlspecialchars_decode($newsInfo['content']));
    	$this->assign('title', '新闻详情-'.$newsInfo['title']);
    	$this->assign('news_info', $newsInfo);
        $this->display();
    }
}