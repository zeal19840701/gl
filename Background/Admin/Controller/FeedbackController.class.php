<?php
namespace Admin\Controller;
use Admin\Controller;

class FeedbackController extends BaseController{

	public function index($key="")
    {
    	$where = array();
    	$model = D('Feedback');
        if($key !== ""){
        	$where['content'] = array('like',"%$key%");
        }
        $count = $model->getCount($where);// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15, array('key'=>$key));// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $pages = $model->getList(array('field'=>'*', 'where'=>$where, 'order'=>'id DESC', 'limit'=>$Page->firstRow.','.$Page->listRows));
        $this->assign('model', $pages);
        $this->assign('page',$show);
        $this->assign('key', $key);
        $this->display();     
    }

    
    /**
     * 更新单页信息
     * @param  [type] $id [单页ID]
     * @return [type]     [description]
     */
    public function reply($id)
    {
    	$id = intval($id);
        //默认显示添加表单
    	$param = array(
    		'where' => array('id'=>$id),
    	);
    	$result = D('Feedback')->getInfo($param);
        if (!IS_POST) {
            $this->assign('page',$result);
            $this->display();
        }
        if (IS_POST) {
            $where = array();
            $where['id'] = I("post.id");
            $param = array();
            $param['answer'] = I("post.answer");
            $param['updater'] = session('username');
            $param['udate'] = date("Y-m-d H:i:s", time());
            $param['status'] = 1;
            $resultUpdate = D("Feedback")->updateData($where, $param);
            if($resultUpdate){
            	D('MessageReceive')->insertMessage(1, 'SYSTEM', "您的提问：" . $result['content'] . "，已经得到反馈", "反馈内容：" . I("post.answer"), $result['user_id']);
            	$this->success("更新成功", U('feedback/index'));
            }else{
            	$this->error("更新失败");
            }
        }
    }
    
    public function close(){
    	$id = I('get.id');
    	$model = D('Feedback');
    	$result = $model->updateData(array('id'=>$id), array('status'=>1));
    	if($result){
    		$this->success("关闭成功", U('feedback/index'));
    	}else{
    		$this->error("关闭失败");
    	}
    }
}
