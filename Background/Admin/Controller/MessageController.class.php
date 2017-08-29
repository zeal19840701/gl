<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * 消息管理
 */
class MessageController extends BaseController
{
    /**
     * 用户列表
     * @return [type] [description]
     */
    public function index($type="", $key="")
    {
        if($key === ""){
        	$where = array();
        }else{
            /* $where['info_title'] = array('like',"%$key%");
            $where['info_content'] = array('like',"%$key%");
            
            $where['_logic'] = 'or'; */
            $where['_string'] = "(info_title like '%$key%') OR (info_content like '%$key%')";
        }
        if(!empty($type)){
        	$where['info_type'] = $type;
        }
        $model = D('Message')->getList(array("where"=>$where));
        $count  = D('Message')->getCount($where);// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15, array('key'=>$key));// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $member = D('Message')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
        $this->assign('message', $member);
        $this->assign('page',$show);
        $this->assign('type',$type);
        $this->assign('key',$key);
        $this->display();     
    }

    /**
     * 添加
     */
    public function add()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $this->display();
        }
        if (IS_POST) {
            //如果用户提交数据
            $info = I("POST.");
            $model = D("Message");
            $param = array();
            $param['info_type'] = $info['info_type'];
            $param['sender'] = 'SYSTEM';
            $receviverAccount = '';
            if(empty($info['receiver'])){
            	$param['receiver'] = '';
            }else{
            	$userInfo = D('User')->getInfo(array('field'=>'id,mobile', 'where'=>array('mobile'=>$info['receiver'])));
            	if($userInfo['id']){
            		$param['receiver'] = $info['receiver'];
            		$receviverAccount = $userInfo['id'];
            	}
            }
            $param['info_title'] = $info['info_title'];
            $param['info_content'] = $info['info_content'];
            $param['link'] = $info['link'];
            $param['is_push'] = $info['is_push'];
            $param['create_date'] = date("Y-m-d H:i:s");
            $param['is_del'] = 0;
            $messageId = $model->insertData($param);
            //write_log(array('messageId:', $messageId, $receviverAccount, $param['is_push']));
            if ($messageId) {
            	if($param['is_push']){
            		D('MessageReceive')->insertMessage($param['info_type'], $param['sender'], $param['info_title'], $param['info_content'], $receviverAccount, $messageId);
            	}
               $this->success("添加消息成功", U('message/index'));
            } else {
               $this->error("添加消息失败");
            }
        }
    }
    
    /**
     * 更新
     */
    public function update()
    {
        //默认显示添加表单
        if (!IS_POST) {
        	$id = I('id',"addslashes");
            $model = D('Message')->getInfo(array('where'=>array('id'=>$id)));
            $this->assign('model',$model);
            $this->display();
        }
        if (IS_POST) {
        	//如果用户提交数据
        	$info = I("POST.");
        	$model = D("Message");
        	$param = array();
        	$param['info_type'] = $info['info_type'];
        	$param['link'] = $info['link'];
        	$param['sender'] = 'SYSTEM';
        	$receviverAccount = '';
        	if(empty($info['receiver'])){
        		$param['receiver'] = '';
        	}else{
        		$userInfo = D('User')->getInfo(array('field'=>'id,mobile', 'where'=>array('mobile'=>$info['receiver'])));
        		if($userInfo['id']){
        			$param['receiver'] = $info['receiver'];
        			$receviverAccount = $userInfo['id'];
        		}
        	}
        	$param['info_title'] = $info['info_title'];
        	$param['info_content'] = $info['info_content'];
        	$param['is_push'] = $info['is_push'];
        	$param['create_date'] = date("Y-m-d H:i:s");
        	$param['is_del'] = 0;
        	$messageId = $model->insertData($param);
        	if ($messageId) {
        		if($param['is_push']){
        			D('MessageReceive')->insertMessage($param['info_type'], $param['sender'], $param['info_title'], $param['info_content'], $receviverAccount, $messageId);
        		}
        		$this->success("编辑消息成功", U('message/index'));
        	} else {
        		$this->error("编辑消息失败");
        	}
        }
    }
    /**
     * 删除
     */
    public function delete($id)
    {
    	$id = I('get.id', "addslashes");
        $model = D('Message');
        //更新字段
        $data['id']=$id;
        if($model->deleteData($data)){
            $this->success("删除消息成功", U('message/index'));
        }else{
            $this->error("删除消息失败");
        }
    }
}
