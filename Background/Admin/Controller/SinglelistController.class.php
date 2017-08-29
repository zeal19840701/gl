<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * 单页列表管理
 */
class SinglelistController extends BaseController
{
    /**
     * 单页列表
     * @return [type] [description]
     */
    public function index($key="")
    {
    	$where = array();
    	$model = D('SingleList');
        if($key !== ""){
        	$where['title'] = array('like',"%$key%");
        	$where['name'] = array('like',"%$key%");
        	$where['_logic'] = 'or';
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
     * 添加单页
     */
    public function add()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $this->display();
        }
        if (IS_POST) {
            //如果用户提交数据
        	$param = array();
            $param['title'] = I("post.title");
            $param['ename'] = I("post.name");
            $param['content'] = I("post.content");
            $param['udate'] = date("Y-m-d H:i:s", time());
            $param['cdate'] = date("Y-m-d H:i:s", time());
            $model = D("SingleList");
            $result = $model->insertData($param);
            if($result){
            	$this->success("添加成功", U('single/index'));
            }else{
            	$this->error("添加失败");
            }
        }
    }
    /**
     * 更新单页信息
     * @param  [type] $id [单页ID]
     * @return [type]     [description]
     */
    public function update($id)
    {
    	$id = intval($id);
        //默认显示添加表单
        if (!IS_POST) {
        	$param = array(
        		'where' => array('id'=>$id),
        	);
        	$result = D('SingleList')->getInfo($param);
            $this->assign('page',$result);
            $this->display();
        }
        if (IS_POST) {
            $where = array();
            $where['id'] = I("post.id");
            $param = array();
            $param['title'] = I("post.title");
            $param['ename'] = I("post.name");
            $param['content'] = I("post.content");
            $param['udate'] = date("Y-m-d H:i:s", time());
            $result = D("SingleList")->updateData($where, $param);
            if($result){
            	$this->success("更新成功", U('single/index'));
            }else{
            	$this->error("更新失败");
            }
        }
    }
    /**
     * 删除单页
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete($id)
    {
    	$id = intval($id);
        $model = D('SingleList');
        $param = array(
        	'id' => $id,
        );
        $result = $model->deleteData($param);
        if($result){
            $this->success("删除成功", U('single/index'));
        }else{
            $this->error("删除失败");
        }
    }
}
