<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * 收益管理
 */
class EarningsitemController extends BaseController
{
    public function index(){
    	$key = I("key");
    	$where = "";
    	$model = D('UserConsume');
    	if($key !== ""){
    		$where .= " and (b.id like '%" .$key. "%' or b.mobile like '%". $key ."%')";
    		
    	}
    	$count = $model->userEarningsItemCount($where);// 查询满足要求的总记录数
    	$Page = new \Extend\Page($count,15, array('key'=>$key));// 实例化分页类 传入总记录数和每页显示的记录数(25)
    	$show = $Page->show();// 分页显示输出
    	$pages = $model->userEarningsItem($where, ' a.`cdate` DESC', $Page->firstRow.','.$Page->listRows);
    	$this->assign('model', $pages);
    	$this->assign('page',$show);
    	$this->assign('key', $key);
    	$this->display();
    }
}
