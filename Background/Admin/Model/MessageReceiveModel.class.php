<?php
namespace Admin\Model;
use Think\Model;
class MessageReceiveModel extends Model {
	protected $trueTableName = 'gl_message_receive';//要加上完整的表名
	protected $connection = 'DB_GOLD_LOCK';
	
    public function getInfo($param){
    	$res = M($this->trueTableName, '', $this->connection)->field($param['field'])->where($param['where'])->find();
    	return $res;
    }
    
    public function getList($param){
    	$result = M($this->trueTableName, '', $this->connection)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	//echo M($this->trueTableName, '', $this->connection)->_sql();
    	return $result;
    }
    
    public function getCount($param){
    	$result = M($this->trueTableName, '', $this->connection)->where($param)->count();
    	return $result;
    }
    
    public function getJoinQuery($param){
    	$result = M($this->trueTableName, '', $this->connection)->table($param['table'])->join($param['join'])->field($param['field'])->order($param['order'])->where($param['where'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function getJoinQueryInfo($param){
    	$result = M($this->trueTableName, '', $this->connection)->table($param['table'])->join($param['join'])->field($param['field'])->where($param['where'])->find();
    	//echo M($this->trueTableName, '', $this->connection)->_sql();
    	return $result;
    }
    
    public function getJoinQueryCount($param){
    	$result = M($this->trueTableName, '', $this->connection)->table($param['table'])->join($param['join'])->order($param['order'])->where($param['where'])->count();
    	//echo M($this->trueTableName, '', $this->connection)->_sql();
    	return $result;
    }
    
    public function insertData($data){
    	$res = M($this->trueTableName, '', $this->connection)->add($data);
    	return $res;
    }
    
    public function updateData($where, $data){
    	$res = M($this->trueTableName, '', $this->connection)->where($where)->save($data);
    	//echo M($this->trueTableName, '', $this->connection)->getLastSql();
    	return $res;
    }
    
    public function updateFieldInc($where, $field, $num=1){
    	$result = M($this->trueTableName, '', $this->connection)->where($where)->setInc($field, $num);
    	return $result;
    }
    
    public function updateFieldDec($where, $field, $num=1){
    	$result = M($this->trueTableName, '', $this->connection)->where($where)->setDec($field, $num);
    	return $result;
    }
    
    public function getQuery($sql){
    	$result = M($this->trueTableName, '', $this->connection)->query($sql);
    	return $result;
    }
    
    /**
     * 插入消息
     * @param int $type 消息类型(1:个人,2:系统)
     * @param string $sender 发送者
     * @param string $title 标题
     * @param string $content 内容
     * @param string $receiver 接受者
     */
	public function insertMessage($type, $sender, $title, $content, $receiver, $messageId=''){
    	$nowDate = date("Y-m-d H:i:s");
    	if(empty($messageId)){
    		$param = array(
    				'info_type'=>$type,
    				'sender'=>$sender,
    				'info_title'=>$title,
    				'info_content'=>$content,
    				'create_date'=>$nowDate,
    				'is_del'=>0,
    		);
    		$messageId = D("Message")->insertData($param);
    	}
    	if($messageId){
    		if(1 == $type){
    			//$userInfo = SU(md5($receiver));
    			$userInfo = D('User')->getInfo(array('field'=>'id,jg_id,mobile', 'where'=>array('id'=>$receiver)));
    			//write_log(array('insertMessage:', $userInfo, $receiver));
    			$messageData = array();
    			if($userInfo['jg_id']){
    				//向极光推送通知
    				if($userInfo['jg_id']){
    					$client = new \Admin\Common\JpushApi();
    					$messageData = $client->push_notification_person($title, $content, $userInfo['jg_id'], $userInfo['id']);
    				}
    			}
    			//write_log(array('messageData:', $messageData));
    			$param = array(
    				'message_id' => $messageId,
    				'receiver_account' => $receiver,
    				'sendno' => isset($messageData['sendno'])?$messageData['sendno']:'',
    				'msg_id' => isset($messageData['msg_id'])?$messageData['msg_id']:'',
    				'send_date' => $nowDate,
    				'status' => 0,
    			);
    			$this->insertData($param);
    		}else if(2 == $type){
    			//向极光推送通知
    			$client = new \Admin\Common\JpushApi();
    			$messageData = $client->push_notification($content);
    		}
    	}
    }
}