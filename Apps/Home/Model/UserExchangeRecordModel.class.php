<?php
namespace Home\Model;
use Think\Model;
class UserExchangeRecordModel extends Model {
	protected $trueTableName = 'gl_user_exchange_record';//要加上完整的表名
	
    public function getInfo($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->find();
    	//echo M($this->trueTableName)->_sql();
    	return $res;
    }
    
    public function getList($param){
    	$res = M($this->trueTableName)->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	//echo M($this->trueTableName)->_sql();
    	return $res;
    }
    
    public function insertData($data){
    	$res = M($this->trueTableName)->add($data);
    	//echo M($this->trueTableName)->_sql();
    	return $res;
    }
    
    public function updateData($where, $data){
    	$res = M($this->trueTableName)->where($where)->save($data);
    	return $res;
    }
    
    public function getQuery($sql){
    	$result = M($this->trueTableName)->query($sql);
    	return $result;
    }
    
    public function getCount($param){
    	$result = M($this->trueTableName)->where($param)->count();
    	return $result;
    }
    
    /**
     * 修改兑换成功状态
     * @param int $id
     * @return array:
     */
    public function updateIDS($ids){
    
    	$arrID = split (",", $ids);
    
    	foreach ($arrID as $strID){
    		$this->updateID($strID);
    	}
    }
    
    private function updateID($id){
    	$where = array(
    		'exchange_id' => array('in', $id),
    		'is_del' => 0,
    		'status' => array('neq', '4'),
    	);
    	$data = array(
    		'status' => '4',
    		'update_date' => date("Y-m-d H:i:s", time()),
    	);
    	$res = $this->updateData($where, $data);
    	if(!$res){
    		return false;
    	}
    	$result = $this->getList(array('field'=>'id,user_id,mobile,exchange_gold', 'where'=>array('exchange_id'=>array('in', $id))));
    	if($result){
    		$nowDate = date("Y-m-d H:i:s", time());
    		
    		foreach($result as $row){
    			//添加记录人时候就先扣了
    			//$resUserUpdate = D("User")->updateUser(array("id"=>$row['user_id'], "coin"=>array("egt", $row['exchange_gold'])), array("use_coin"=>array("exp", "`use_coin`+" . $row['exchange_gold']), "coin"=>array("exp", "`coin`-" . $row['exchange_gold'])));
    			if($resUserUpdate){
					$userInfo = D("User")->getInfo(array("field"=>"id,coin", "where"=>array("id"=>$r['id'])));
    				D("UserExchangeRecord")->updateData(array("id"=>$row['id']), array("status"=>4));//原来成功状态是2，现在为4
					/* $msgInfo = array(
						'info_type' => 1,//系统信息
						'receiver' => $row['mobile'],
						'info_title' => "提现到账",
						"info_content" => "你的兑换已经到帐，请在我的->收益余额->提现->提现记录中查看兑换详情。",
						"create_date" => $nowDate,
						"creator" => "SYSTEM",
						"update_date" => $nowDate,
						"is_del" => 0,
					);
					D("Message")->insertData($msgInfo); */
					//$surplus_coin = (int)($userInfo['coin']) + (int)($row['exchange_gold']);
					//D("UserConsume")->insertData(array("user_id"=>$row['user_id'], "coin"=>$row['exchange_gold'], "surplus_coin" => $surplus_coin, "type"=>"提现", "intro"=>"提现成功了", "cdate" => date("Y-m-d H:i:s", time())));
					D('MessageReceive')->insertMessage(1, 'SYSTEM', '提现到账', '你的兑换已经到帐，请在我的->收益余额->提现->提现记录中查看兑换详情。', $userInfo['id']);
    				D("UserConsume")->UserConsume($row['user_id'], $row['exchange_gold'], $userInfo['coin'], "提现", "提现成功了");
    			}
    		}
    	}
    	
    	/* $this->db->set("status", '2');
    	$this->db->set("updateDtTm","now()",false);;
    	$this->db->where("exchangeID in ($id)");
    	$this->db->where("isDel = 0");
    	$this->db->where("status != 2");
    	$query = $this->db->update('TBL_UserChangeRecord');
    
    	// 如果更新0件数据直接返回
    	if($this->db->affected_rows() <= 0){
    		return false;
    	} */
    
    	// 检索兑换情报的用户ID
    	/* $sql = "SELECT accountID FROM TBL_UserChangeRecord WHERE exchangeID IN ($id)";
    	$query = $this->db->query($sql);
    	if ($query->num_rows() > 0)
    	{
    		foreach ($query->result() as $row){
    			$info = new stdClass();
    			$info->infoType = 2;
    			$info->receiver = $row->accountID;
    			$info->infoTitle = "兑换到帐";
    			$info->infoContent = "你的兑换已经到帐，请在金库->累计赚取->支出中查看兑换详情。";
    			$username = 'SYSTEM';
    			$info->creator = $username;
    			$this->insertInfoMgmt($info);
    		}
    	} */
    }
    
    /**
     * 修改兑换失败状态
     * @param int $id
     * @return array:
     */
    public function updateplsIDS($ids){
    	$where = array(
    			'exchange_id' => array('in', $id),
    			'is_del' => 0,
    			'status' => 3,
    	);
    	$data = array(
    			'status' => '5',
    			'update_date' => date("Y-m-d H:i:s", time()),
    	);
    	$res = $this->updateData($where, $data);
    	if(!$res){
    		return false;
    	}
    	return true;
    
    	/* $this->db->set("status", '1');
    	$this->db->set("updateDtTm","now()",false);;
    	$this->db->where("exchangeID in ($ids)");
    	$this->db->where("isDel = 0");
    	$query = $this->db->update('TBL_UserChangeRecord');
    	if ($this->db->affected_rows() <= 0) {
    		return false;
    	}
    	return true; */
    }
    
    /* public function insertInfoMgmt($info)
    {
    	// 1 系统
    	// 2 个人
    	$this->db->set('infoType', $info->infoType);
    
    	// 2 个人场合有用
    	$this->db->set('receiver', $info->receiver);
    	// 标题
    	$this->db->set('infoTitle', $info->infoTitle);
    	// 内容
    	$this->db->set('infoContent', $info->infoContent);
    	// 是否删除
    	$this->db->set('isDel', '0');
    	$this->db->set('createDtTm', 'now()',false);
    	$this->db->set('creator', $info->creator);
    	$this->db->set('updateDtTm', 'now()',false);
    	$this->db->set('updater', $info->creator);
    	$this->db->insert('tbl_infomgmt');
    
    
    	// 推送到那个用户账号
    	$this->db->set('accountID', $info->receiver);
    	$messagestr = $info->receiver . " : 你的兑换已经到帐，请注意查收。";
    	// 推送消息
    	$this->db->set('message', $messagestr);
    	// 推送标记 0 未推送
    	// 推送标记 1 已推送
    	$this->db->set('pushflg', '0');
    	// 发送终了时间
    	$this->db->set('pushEndTm', 'NULL', false);
    	//是否删除=未删除
    	$this->db->set('isDel','0');
    	$this->db->set('createDtTm', 'now()',false);
    	$this->db->set('creator', $info->creator);
    	$this->db->set('updateDtTm', 'now()',false);
    	$this->db->set('updater', $info->creator);
    	$this->db->insert('tbl_pushmsg');
    
    
    	// 调用url
    	$ch = curl_init();
    	$CFG =& load_class('Config', 'core');
    	$jinsuo_url = $CFG->item('jinsuo_url');
    	$url =  $jinsuo_url . 'admin/schedulepush';
    
    	$opts = array(
    			'http'=>array(
    					'method'=>"GET",
    					'timeout'=>3,
    			)
    	);
    
    	$context = stream_context_create($opts);
    	file_get_contents($url, false, $context);
    } */
}