<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * 新闻管理
 */
class GrabController extends BaseController
{
    /**
     * 分类列表
     * @return [type] [description]
     */
    public function index()
    {
    	$param = array();
    	$key = I("key");//传入key值
    	$where['flag'] = 0;//1表示插入过数据。0表示没有
        if($key === ""){
        	$param['where'] = $where;
        }else{
            $where['title'] = array('like',"%$key%");
            // $where['name'] = array('like',"%$key%");
            //$where['_logic'] = 'or';
            $param['where'] = $where;
        }
        $model = D('Acquisition');
        $total = $model->getCount($param['where']);
        $page = new \Admin\Common\Page($total,15, array('key'=>$key));
        $param['limit'] = $page->firstRow.','.$page->listRows;
        $newsList = $model->getList($param);
        foreach($newsList as $k=>$v){
        	$newsList[$k]['content'] = strip_tags($v['content']);
        	$newsList[$k]['original'] = $v['original']==1?'原创':'非原创';
        }
        $show = $page->show();
        $this->assign('model', $newsList);
        $this->assign('page', $show);
        $this->assign('key', $key);
        $this->display();
    }
    
    public function push(){
    	$mypush = I('mypush');
    	$mystr = '';
    	if(is_array($mypush)){
    		foreach($mypush as $v){
    			$mystr .= "'".addslashes($v)."',";
    		}
    		$mystr = substr($mystr, 0, -1);
    	}
    	if(!empty($mystr)){
    		$sql = "SELECT * FROM `data_acquisition` WHERE `checkKey` IN (" . ($mystr) . ") AND `flag` = 0";
    		$res = D('Acquisition')->getQuery($sql);
    		$res = $this->_checkData($res);//检查数据是否存在	
    		foreach($res as $k=>$v){
    			$newsArr = array();
    			$newsArr = $v;
    			$newsArr['check_key'] = $v['checkkey'];
    			$newsArr['publish_time'] = $v['publishtime'];
    			$newsArr['storage_time'] = $v['storagetime'];
    			unset($newsArr['flag']);
    			unset($newsArr['checkkey']);
    			unset($newsArr['publishtime']);
    			unset($newsArr['storagetime']);
    			unset($newsArr['comment_num']);
    			$rsNews = D('News')->insertNews($newsArr);
    			if($rsNews){
    				D('Acquisition')->updateAcquisition(array('checkKey'=>$v['checkkey']), array('flag'=>1));
    			}
    		}
    		echo json_encode(array('status'=>true, 'msg'=>'推送成功'));
    	}else{
    		echo json_encode(array('status'=>false, 'msg'=>'推送失败'));
    	}
    	
    }
    
    public function autopush(){
    	set_time_limit(0);
    	ini_set('memory_limit','1024M');
    	while(true){
    		$sql = "SELECT * FROM `data_acquisition` WHERE `flag` = 0 limit 0, 100";
    		$res = D('Acquisition')->getQuery($sql);
    		$res = $this->_checkData($res);//检查数据是否存在
    		if(empty($res)){
    			break;//跳出while循环
    		}
    		foreach($res as $k=>$v){
    			$newsArr = array();
    			$newsArr = $v;
    			$newsArr['check_key'] = $v['checkkey'];
    			$newsArr['publish_time'] = $v['publishtime'];
    			$newsArr['storage_time'] = $v['storagetime'];
    			unset($newsArr['flag']);
    			unset($newsArr['checkkey']);
    			unset($newsArr['publishtime']);
    			unset($newsArr['storagetime']);
    			unset($newsArr['comment_num']);
    			$rsNews = D('News')->insertNews($newsArr);
    			if($rsNews){
    				D('Acquisition')->updateAcquisition(array('checkKey'=>$v['checkkey']), array('flag'=>1));
    			}
    		}
    	}
    	echo json_encode(array('status'=>true, 'msg'=>'推送成功'));
    }
    
    /**
     * 检查记录是否存在。存在去掉
     * @param unknown $res
     * @return unknown
     */
    private function _checkData($res){
    	if(!empty($res)){
    		$resStr = '';
    		foreach($res as $k=>$v){
    			$resStr .= "'" . $v['checkkey'] . "',";
    		}
    		if($resStr){
    			$resStr = substr($resStr, 0, -1);
    			$csql = "SELECT * FROM `gl_news` WHERE check_key IN (" . $resStr . ")";
    			$newsRes = D('News')->getQuery($csql);
    			if(!empty($newsRes)){
    				$newRes1 = array();
    				foreach($newsRes as $kk=>$vv){
    					$newRes1[$vv['check_key']] = $vv;
    				}
    				foreach($res as $key=>$val){
    					if($val['checkkey'] == $newRes1[$val['checkkey']]['check_key']){
    						unset($res[$key]);
    						D('Acquisition')->updateAcquisition(array('checkKey'=>$val['checkkey']), array('flag'=>1));
    					}
    				}
    			}
    		}
    	}
    	return $res;
    }

    /**
     * 添加分类
     */
    public function add()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $model = M('category')->select();
            $cate = getSortedCategory($model);

            $this->assign('cate',$cate);
            $this->display();
        }
        if (IS_POST) {
            //如果用户提交数据
            $model = D("Category");
            if (!$model->create()) {
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($model->getError());
                exit();
            } else {

                if ($model->add()) {
                    $this->success("分类添加成功", U('category/index'));
                } else {
                    $this->error("分类添加失败");
                }
            }
        }
    }
    /**
     * 更新分类信息
     * @param  [type] $id [分类ID]
     * @return [type]     [description]
     */
    public function update()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $model = M('category')->find(I('id',"addslashes"));
          
            $this->assign('cate',getSortedCategory(M('category')->select()));
            $this->assign('model',$model);
            $this->display();
        }
        if (IS_POST) {
            $model = D("Category");
            if (!$model->create()) {
                $this->error($model->getError());
            }else{
             //   dd(I());die;
                if ($model->save()) {
                    $this->success("分类更新成功", U('category/index'));
                } else {
                    $this->error("分类更新失败");
                }        
            }
        }
    }
    /**
     * 删除分类
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete($id)
    {
    		$id = intval($id);
        $model = M('category');
        //查询属于这个分类的文章
        $posts = M('post')->where("cate_id= %d",$id)->select();
        if($posts){
            $this->error("禁止删除含有文章的分类");
        }
        //禁止删除含有子分类的分类
        $hasChild = $model->where("pid= %d",$id)->select();
        if($hasChild){
            $this->error("禁止删除含有子分类的分类");
        }
        //验证通过
        $result = $model->delete($id);
        if($result){
            $this->success("分类删除成功", U('category/index'));
        }else{
            $this->error("分类删除失败");
        }
    }
}
