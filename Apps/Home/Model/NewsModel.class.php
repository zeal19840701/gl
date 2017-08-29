<?php
namespace Home\Model;
use Think\Model;
class NewsModel extends Model {
	protected $trueTableName = 'gl_news';//要加上完整的表名
	
	public function getInfo($param){
		$result = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
		return $result;
	}
	
    public function getList($param){
    	$result = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function getCount($param){
    	$result = M($this->trueTableName)->where($param)->count();
    	return $result;
    }
    
    public function insertData($data){
    	$res = M($this->trueTableName)->add($data);
    	return $res;
    }
    
    public function updateData($where, $data){
    	$res = M($this->trueTableName)->where($where)->save($data);
    	return $res;
    }
    
    /**
     * 添加猜你喜欢
     * @param string $newsId
     * @param string $userId
     * @param string $column_type
     * @return int $res
     */
    public function addGuessLike($newsId, $userId, $column_type){
    	if(empty($userId)){
    		$guessLikeInfo = D('NewsGuessLike')->getInfo(array('field'=>'id,news_id,user_id', 'where'=>array('news_id'=>$newsId, 'user_id'=>$userId)));
    		if(empty($guessLikeInfo)){
    			$param = array(
    					'news_id' => $newsId,
    					'user_id' => $userId,
    					'num' => 0,
    					'news_type' => $column_type,
    					'update_time' => time(),
    					'create_time' => time(),
    			);
    			$res = D('NewsGuessLike')->insertData($param);
    		}else{
    			$res = D('NewsGuessLike')->updateData(array('news_id'=>$newsId, 'user_id'=>$userId), array('num'=>array('exp', '`num`+1'), 'update_time'=>time()));
    		}
    		return $res;
    	}
    	return false;
    }
    
    /**
     * 查询猜你喜欢
     * @param unknown $userId
     */
    public function guessLike($userId){
    	$likeInfo = S('news_guess_like_userid_' . $userId);
    	if(empty($likeInfo)){
    		$res = D('NewsGuessLike')->getList(array('field'=>'news_id,user_id,num,news_type', 'where'=>array('user_id'=>$userId), 'order'=>'num DESC', 'limit'=>'0,5'));
    		if(!empty($res) && count($res)>=5){
    			$where = array();
    			foreach($res as $k=>$v){
    				$where[] = $v['news_id'];
    			}
    			$likeInfo = $this->getList(array('field'=>'id,title,thumbnail,column_type,public_number,comment_num,storage_time', 'order'=>'id DESC', 'where'=>array('id', array('in', $where))));
    			foreach($likeInfo as $k=>$v){
    				$likeInfo[$k]['id'] = _passport_encrypt('gl', $v['id']);
    				$likeInfo[$k]['thumbnail'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $v['thumbnail']);
    			}
    		}else{
    			$params = array(
    					'field' => 'id,title,thumbnail,column_type,public_number,comment_num,storage_time',//id,title,thumbnail,column_type,public_number,comment_num,storage_time
    					'order'=>'id DESC',
    					'limit' => 1000
    			);
    			//pr($params);
    			$newsInfo = D('News')->getList($params);
    			$randArr=array();
    			for($i=0;$i<5;$i++){
    				$randArr[] = mt_rand(0,999);
    			}
    			$likeInfo = array();
    			foreach($randArr as $v){
    				$likeInfo[] = $newsInfo[$v];
    			}
    			foreach($likeInfo as $k=>$v){
    				$likeInfo[$k]['id'] = _passport_encrypt('gl', $v['id']);
    				$likeInfo[$k]['thumbnail'] = str_replace("/data/imgs/", "http://cdn.weixin.71360.com/", $v['thumbnail']);
    			}
    		}
    		S('news_guess_like_userid_' . $userId, $likeInfo, 3600);
    	}
    	return $likeInfo;
    }
}