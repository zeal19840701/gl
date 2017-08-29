<?php
namespace Admin\Common;
use \JPush\Client as JPush;
//use JPush\Exceptions\APIConnectionException;
//use JPush\Exceptions\APIRequestException;
class JpushApi{
	protected $app_key = '';
	protected $master_secret = '';
	public function __construct(){
		$config = C('JPUSH_CONFIG');
		$this->app_key = $config['app_key'];
		$this->master_secret = $config['master_secret'];
	}
	
	/**
	 * 推送系统信息
	 */
	public function push_notification($message){
		$client = new JPush($this->app_key, $this->master_secret);
		$result = $client->push()
		->setPlatform(array("android"))
		->addAllAudience()
		->setNotificationAlert($message)
		->send();
		if(200 == $result['http_code']){
			return array('status' => 'success', 'data' => $result['body']);
		}else{
			return array('status' => 'failure', 'data' => array());
		}
	}
	
	/**
	 * 推送个人信息
	 */
	public function push_notification_person($title, $message, $registration_id="", $mobile){
		$client = new JPush($this->app_key, $this->master_secret);
		$result = $client->push()
		->setPlatform(array('android'))
		// 一般情况下，关于 audience 的设置只需要调用 addAlias、addTag、addTagAnd  或 addRegistrationId
		// 这四个方法中的某一个即可，这里仅作为示例，当然全部调用也可以，多项 audience 调用表示其结果的交集
		// 即是说一般情况下，下面三个方法和没有列出的 addTagAnd 一共四个，只适用一个便可满足大多数的场景需求

		 ->addAlias($mobile)
		//->addTag(array('tag1', 'tag2'))
		->addRegistrationId($registration_id)
		//->setNotificationAlert('Hi, JPush')
		->androidNotification($message, array(
				'title' => $title,
				// 'builder_id' => 2,
				'extras' => array(
						'key' => 'value',
						'jiguang'
				),
		))
		/* ->message('message content', array(
				'title' => $message,
				// 'content_type' => 'text',
				'extras' => array(
						'key' => 'value',
						'jiguang'
				),
		)) */
		->options(array(
				// sendno: 表示推送序号，纯粹用来作为 API 调用标识，
				// API 返回时被原样返回，以方便 API 调用方匹配请求与返回
				// 这里设置为 100 仅作为示例

				// 'sendno' => 100,

				// time_to_live: 表示离线消息保留时长(秒)，
				// 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
				// 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
				// 这里设置为 1 仅作为示例

				// 'time_to_live' => 1,

				// apns_production: 表示APNs是否生产环境，
				// True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境

				'apns_production' => false,

				// big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
				// 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
				// 这里设置为 1 仅作为示例

				// 'big_push_duration' => 1
		))
		->send();
		//print_r($result);
		if(200 == $result['http_code']){
			return array('status' => 'success', 'data' => $result['body']);
		}else{
			return array('status' => 'failure', 'data' => array());
		}
	}
	
	public function push_message($message, $registration_id=""){
		$client = new JPush($this->app_key, $this->master_secret);
		$result = $client->push()
		->setPlatform(array('android'))
		// 一般情况下，关于 audience 的设置只需要调用 addAlias、addTag、addTagAnd  或 addRegistrationId
		// 这四个方法中的某一个即可，这里仅作为示例，当然全部调用也可以，多项 audience 调用表示其结果的交集
		// 即是说一般情况下，下面三个方法和没有列出的 addTagAnd 一共四个，只适用一个便可满足大多数的场景需求

		// ->addAlias('alias')
		//->addTag(array('tag1', 'tag2'))
		->addRegistrationId($registration_id)
		//->setNotificationAlert('Hi, JPush')
		/* ->androidNotification('Hello Android', array(
				'title' => 'hello jpush1',
				// 'builder_id' => 2,
				'extras' => array(
						'key' => 'value',
						'jiguang'
				),
		)) */
		->message('message content', array(
				'title' => $message,
				// 'content_type' => 'text',
				'extras' => array(
						'key' => 'value',
						'jiguang'
				),
		))
		->options(array(
				// sendno: 表示推送序号，纯粹用来作为 API 调用标识，
				// API 返回时被原样返回，以方便 API 调用方匹配请求与返回
				// 这里设置为 100 仅作为示例

				// 'sendno' => 100,

				// time_to_live: 表示离线消息保留时长(秒)，
				// 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
				// 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
				// 这里设置为 1 仅作为示例

				// 'time_to_live' => 1,

				// apns_production: 表示APNs是否生产环境，
				// True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境

				'apns_production' => false,

				// big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
				// 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
				// 这里设置为 1 仅作为示例

				// 'big_push_duration' => 1
		))
		->send();
		//print_r($result);
		if(200 == $result['http_code']){
			return array('status' => 'success', 'data' => $result['body']);
		}else{
			return array('status' => 'failure', 'data' => array());
		}
	}
	
	public function send($receive, $content, $platform="android", $m_type, $m_txt, $m_time){
		$appkey = $this->app_key; //AppKey
		$secret = $this->master_secret; //Secret
		
		$postUrl = "https://api.jpush.cn/v3/push";
		
		$base64 = base64_encode("$appkey:$secret");
		$header = array("Authorization:Basic $base64", "Content-Type:application/json");
		$data = array();
		$data['platform'] = $platform;          //目标用户终端手机的平台类型android,ios,winphone
		$data['audience'] = $receive;      //目标用户
		
		$data['notification'] = array(
				//统一的模式--标准模式
				"alert" => $content,
				//安卓自定义
				"android" => array(
						"alert" => $content,
						"title" => "",
						"builder_id" => 1,
						"extras" => array("type" => $m_type, "txt" => $m_txt)
				),
				//ios的自定义
				"ios" => array(
						"alert" => $content,
						"badge" => "1",
						"sound" => "default",
						"extras" => array("type" => $m_type, "txt" => $m_txt)
				)
		);
		
		//苹果自定义---为了弹出值方便调测
		$data['message'] = array(
				"msg_content" => $content,
				"extras" => array("type" => $m_type, "txt" => $m_txt)
		);
		
		//附加选项
		$data['options'] = array(
				"sendno" => time(),
				"time_to_live" => $m_time, //保存离线时间的秒数默认为一天
				"apns_production" => false, //布尔类型   指定 APNS 通知发送环境：0开发环境，1生产环境。或者传递false和true
		);
		$param = json_encode($data);
		//    $postUrl = $this->url;
		$curlPost = $param;
		$ch = curl_init();                                      //初始化curl
		curl_setopt($ch, CURLOPT_URL, $postUrl);                 //抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);           // 增加 HTTP Header（头）里的字段
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$return_data = curl_exec($ch);                                 //运行curl
		curl_close($ch);
		return $return_data;
	}
}
