<?php
return array(
	//'配置项'=>'配置值'
	//任务和推荐共用,1为未开始、2为任务中、3为已暂停、4为已结束
	//2任务中不能编辑
	//1、4不能暂停
	//4不能编辑
	//2不能删除
	'NOT_BEGIN_STATUS' => 1,//任务未开始
	'ON_GOING_STATUS' => 2,//任务进行中
	'PAUSE_STATUS' => 3,//任务暂停
	'FINISH_STATUS' => 4,//任务已结束
	
	//收支类型
	'INCOME_TYPE' => '收入',
	'EXPEND_TYPE' => '支出',
	'RECHARGE_TYPE' => '充值',
	'EXTRACT_TYPE' => '提现',
	
	'PERSON_MESSAGE' => 1,//个人消息
	'SYSTEM_MESSAGE' => 2,//系统消息
	
	//有米服务器密钥
	'YOUMI_DEV_SERVER_SECRET' => 'b56fe04c41690e5e',//4d3f4b8a2c6f2fe4
);