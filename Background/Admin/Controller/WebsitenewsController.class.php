<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * 新闻管理
 */
class WebsitenewsController extends BaseController
{
    /**
     * 分类列表
     * @return [type] [description]
     */
    public function Index()
    {
    	$where = array();
    	$model = D('WebsiteNews');
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
    		$imgDir = '';
    		//print_r($_FILES["img"]);exit;
    		//如果用户提交数据
    		/* if ((($_FILES["img"]["type"] == "image/gif")
    				|| ($_FILES["img"]["type"] == "image/jpeg")
    				|| ($_FILES["img"]["type"] == "image/pjpeg"))
    				&& ($_FILES["img"]["size"] < 2097152))
    		{ */
    			if ($_FILES["img"]["error"] > 0)
    			{
    				$this->error("Return Code:" . $_FILES["file"]["error"]);
    			}
    			else
    			{
    				/* echo "Upload: " . $_FILES["img"]["name"] . "<br />";
    				echo "Type: " . $_FILES["img"]["type"] . "<br />";
    				echo "Size: " . ($_FILES["img"]["size"] / 1024) . " Kb<br />";
    				echo "Temp file: " . $_FILES["img"]["tmp_name"] . "<br />"; */
    				$rootDir = APP_ROOT . "/Public/web/";
    				$dir = "upload/" . date("Y") . "/" . date("m")."/";
    				if(!is_dir($rootDir . $dir)) {
    					mkdir($rootDir . $dir, 0755, true);
    				}
    				$fileName = date("dHis"). $_FILES["img"]["name"];
    				$newDirName = $rootDir . $dir . $fileName;
    				move_uploaded_file($_FILES["img"]["tmp_name"], $newDirName);
    				$imgDir = $dir . $fileName;
    				//echo "Stored in: " . $newDirName;
    			}
    		//}
    		/* else
    		{
    			$this->error("Invalid file");
    		} */
    		$param = array();
    		$param['title'] = I("post.title");
    		$param['intro'] = I("post.intro");
    		$param['img'] = $imgDir;
    		$param['content'] = I("post.content");
    		$param['source'] = I("post.source");
    		$param['release_time'] = date("Y-m-d H:i:s", time());
    		$param['create_time'] = date("Y-m-d H:i:s", time());
    		$model = D("WebsiteNews");
    		$result = $model->insertData($param);
    		if($result){
    			$this->success("添加成功", U('Websitenews/index'));
    		}else{
    			$this->error("添加失败");
    		}
    	}
    }
    /**
     * 更新
     * @param  [type] $id [单页ID]
     * @return [type]     [description]
     */
    public function update()
    {
    	$id = I("id");
    	//默认显示添加表单
    	if (!IS_POST) {
    		$param = array(
    			'where' => array('id'=>$id),
    		);
    		$result = D('WebsiteNews')->getInfo($param);
    		$this->assign('page', $result);
    		$this->assign('id', $result['id']);
    		$this->display();
    	}
    	if (IS_POST) {
    		$imgDir = '';
    		//print_r($_FILES["img"]);exit;
    		//如果用户提交数据
    		/* if ((($_FILES["img"]["type"] == "image/gif")
    		 || ($_FILES["img"]["type"] == "image/jpeg")
    		 || ($_FILES["img"]["type"] == "image/pjpeg"))
    		&& ($_FILES["img"]["size"] < 2097152))
    		{ */
    		if ($_FILES["img"]["error"] > 0)
    		{
    			//$this->error("Return Code:" . $_FILES["file"]["error"]);
    		}
    		else
    		{
    			/* echo "Upload: " . $_FILES["img"]["name"] . "<br />";
    			 echo "Type: " . $_FILES["img"]["type"] . "<br />";
    			 echo "Size: " . ($_FILES["img"]["size"] / 1024) . " Kb<br />";
    			 echo "Temp file: " . $_FILES["img"]["tmp_name"] . "<br />"; */
    			$rootDir = APP_ROOT . "/Public/web/";
    			$dir = "upload/" . date("Y") . "/" . date("m")."/";
    			if(!is_dir($rootDir . $dir)) {
    				mkdir($rootDir . $dir, 0755, true);
    			}
    			$fileName = date("dHis"). $_FILES["img"]["name"];
    			$newDirName = $rootDir . $dir . $fileName;
    			move_uploaded_file($_FILES["img"]["tmp_name"], $newDirName);
    			$imgDir = $dir . $fileName;
    			//echo "Stored in: " . $newDirName;
    		}
    		//}
    		/* else
    		 {
    		 $this->error("Invalid file");
    		 } */
    		
    		$where = array();
    		$where['id'] = $id;
    		$param = array();
    		
    		$param['title'] = I("post.title");
    		$param['intro'] = I("post.intro");
    		$param['content'] = I("post.content");
    		if(!empty($imgDir)){
    			$param['img'] = $imgDir;
    		}
    		$param['source'] = I("post.source");
    		$param['release_time'] = date("Y-m-d H:i:s", time());
    		$result = D("WebsiteNews")->updateData($where, $param);
    		if($result){
    			$this->success("更新成功", U('Websitenews/index'));
    		}else{
    			$this->error("更新失败");
    		}
    	}
    }
    /**
     * 删除
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete()
    {
    	$id = I('id');
    	$model = D('WebsiteNews');
    	$param = array(
    		'id' => $id,
    	);
    	$result = $model->deleteData($param);
    	if($result){
    		$this->success("删除成功", U('Websitenews/index'));
    	}else{
    		$this->error("删除失败");
    	}
    }
}
