<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * 用户管理
 */
class UserController extends BaseController
{
    /**
     * 用户列表
     * @return [type] [description]
     */
    public function index($key="")
    {
        if($key === ""){
            $model = D("User");  
        }else{
            $where['mobile'] = array('like',"%$key%");
            $where['nickname'] = array('like',"%$key%");
            $where['_logic'] = 'or';
            $model = D("User")->where($where);
        }
        
        $count  = $model->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15, array('key'=>$key));// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $member = $model->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
        $this->assign('user', $member);
        $this->assign('page',$show);
        $this->assign('key', $key);
        $this->display();     
    }
    
    public function isStatus(){
    	$flag = I("get.flag");
    	$id = I("get.id");
    	if(!$flag){
    		$status = 0;
    	}else{
    		$status = 1;
    	}
    	$res = D("User")->updateData(array("id"=>$id), array("status"=>$status));
    	if($res){
    		$this->success("操作成功", U('admin/user/index'));
    	}else{
    		$this->error("操作失败");
    	}
    }
}
