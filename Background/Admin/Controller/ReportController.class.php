<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * 举报管理
 */
class ReportController extends BaseController
{
    /**
     * 举报列表
     * @return [type] [description]
     */
    public function index($key="")
    {
    	$where = array();
    	$model = D('Report');
        if($key !== ""){
        	$where['title'] = array('like',"%$key%");
        	$where['content'] = array('like',"%$key%");
        	$where['_logic'] = 'or';
        }
        $count = $model->getCount($where);// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15, array('key'=>$key));// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $pages = $model->getList(array('field'=>'*', 'where'=>$where, 'order'=>'id DESC', 'limit'=>$Page->firstRow.','.$Page->listRows));
        if(!empty($pages)){
        	$typeList = array(1=>'低俗色情', 2=>'涉嫌侵权', 3=>'信息敏感',4=>'营销诈骗',5=>'其它');
        	foreach($pages as $k=>$v){
        		 $tempArr = explode(',', $v['type']);;
        		 $tempStr = '';
        		 foreach ($tempArr as $vv){
        		 	$tempStr .= $typeList[$vv].',';
        		 }
        		 $pages[$k]['type_str'] = trim($tempStr, ','); 
        	}
        }
        $this->assign('model', $pages);
        $this->assign('page',$show);
        $this->assign('key', $key);
        $this->display();     
    }

    public function ignore(){
    	$id = intval(I('id'));
    	$model = D('Report');
    	$result = $model->updateData(array('id'=>$id), array('status'=>1));
    	if($result){
    		$reportInfo = $model->getInfo(array('id'=>$id));
    		D('MessageReceive')->insertMessage(1, 'SYSTEM', '举报没有通过', '很可惜，您的举报没有通过，感谢您支持！', $reportInfo['reporter']);
    		$this->success("忽略成功", U('report/index'));
    	}else{
    		$this->error("忽略失败");
    	}
    }
    
    public function pass(){
    	$id = intval(I('id'));
    	$model = D('Report');
    	$result = $model->updateData(array('id'=>$id), array('status'=>2));
    	if($result){
    		$reportInfo = $model->getInfo(array('where'=>array('id'=>$id)));
    		$nowDate = date("Y-m-d H:i:s");
    		$inviteCoin = getSystemConfig("03", "02");//获得积分
    		D("User")->increaseCoin($reportInfo['reporter'], $inviteCoin, '收入', '举报通过，奖励金币:'.$inviteCoin);
    		D('MessageReceive')->insertMessage(1, 'SYSTEM', '举报通过', '您的举报通过啦，奖励金币:'.$inviteCoin.'，感谢您支持！', $reportInfo['reporter']);
    		//D("User")->updateData(array("id"=>$reportInfo['reporter'], "status"=>0), array("total_coin"=>array("exp", "`total_coin`+".$inviteCoin), "coin"=>array("exp", "`coin`+".$inviteCoin), "today_coin"=>array("exp", "`today_coin`+".$inviteCoin), "udate"=>$nowDate));
    		$this->success("通过成功", U('report/index'));
    	}else{
    		$this->error("通过失败");
    	}
    }
    
    /**
     * 禁用账号
     */
    public function ban(){
    	$id = intval(I('id'));
    	$model = D('Report');
    	$param = array(
    		'id' => $id,
    	);
    	$reportInfo = $model->getInfo(array('where'=>array('id'=>$id)));
    	if($reportInfo){//account
    		D("User")->updateData(array("mobile"=>$reportInfo['account']), array("status"=>1));//禁用账号
    		$model->updateData(array('id'=>$id), array('is_ban'=>1));
    		D('MessageReceive')->insertMessage(1, 'SYSTEM', '禁用账号', '您已违规被禁用账号！', $reportInfo['account']);
    		$this->success("禁用账号成功", U('report/index'));
    	}else{
    		$this->error("禁用账号失败");
    	}
    }
    
    /**
     * 删除文章
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function is_del()
    {
    	$id = I('id');
        $model = D('Report');
        $param = array(
        	'id' => $id,
        );
        $reportInfo = $model->getInfo(array('where'=>array('id'=>$id)));
        if($reportInfo){
        	if('news' == $reportInfo['part']){//新闻
        		preg_match('/[\?&]id=([-=_\w]+)/', htmlspecialchars_decode($reportInfo['link']), $matches);
        		$newsId = _passport_decrypt('gl', $matches[1]);
        		if($newsId){
        			$res = D("News")->deleteNews(array("id"=>$newsId));
        			if($res){
        				$model->updateData(array('id'=>$id), array('is_del'=>1));
        				$this->success("删除新闻成功", U('report/index'));
        			}else{
        				$this->error("删除新闻失败");
        			}
        		}else{
        			$this->error("新闻ID不存在");
        		}
        	}else if('comment' == $reportInfo['part']){//评论
        		//@todo 待加
        		
        	}else if('recommend' == $reportInfo['part']){//推荐
        		preg_match('/[\?&]id=([-=_\w]+)/', htmlspecialchars_decode($reportInfo['link']), $matches);
        		$recommendId = _passport_decrypt('gl', $matches[1]);
        		if($recommendId){
        			$res = D("Recommend")->delRecommend(array("id"=>$recommendId));
        			if($res){
        				$model->updateData(array('id'=>$id), array('is_del'=>1));
        				D('MessageReceive')->insertMessage(1, 'SYSTEM', '您有推荐有删除', '您有推荐存在违规已被删除！', $reportInfo['account']);
        				$this->success("删除推荐成功", U('report/index'));
        			}else{
        				$this->error("删除推荐失败");
        			}
        		}else{
        			$this->error("推荐ID不存在");
        		}
        		
        	}else if('mission' == $reportInfo['part']){//任务
        		preg_match('/[\?&]id=([-=_\w]+)/', htmlspecialchars_decode($reportInfo['link']), $matches);
        		$missionId = _passport_decrypt('gl', $matches[1]);
        		if($missionId){
        			$res = D("Mission")->deleteData(array("id"=>$missionId));
        			if($res){
        				$model->updateData(array('id'=>$id), array('is_del'=>1));
        				D('MessageReceive')->insertMessage(1, 'SYSTEM', '您有任务有删除', '您有任务存在违规已被删除！', $reportInfo['account']);
        				$this->success("删除任务成功", U('report/index'));
        			}else{
        				$this->error("删除任务失败");
        			}
        		}else{
        			$this->error("任务ID不存在");
        		}
        	}else{
        		$this->error("还没有这个栏目");
        	}
        }else{
            $this->error("删除失败");
        }
    }
}
