<?php
return array(
	//'配置项'=>'配置值'
	'NEWS_URL' => 'http://114.80.18.66/GrabAlgorithmWeb/',//新闻url
	//'GL_HOST_URL' => 'http://js.mytcloud.com',//域名地址
	//'DATA_IMG_URL' => 'http://js.mytcloud.com',//图片地址
	'DATA_IMG_URL' => 'http://114.80.18.34',//图片地址
	'GL_HOST_URL' => 'http://114.80.18.34',//域名地址
	'DATA_IMG_HEAD_PIC'=> '/images/default.jpg',
	//'__SUITUI_STATIC__'=> '/Public/Apps',
	'LOAD_EXT_CONFIG' => 'database, constant',//加载constant.php配置，多个文件用逗号隔开
	'TMPL_PARSE_STRING' => array(
		'__SUITUI_STATIC__' =>  '/Apps',),//__ROOT__.'/Background/'.MODULE_NAME.'/View' .
	
	//极光推送配置
	'JPUSH_CONFIG' => array(
		'app_key' => 'fae05a2b934e82a997c52404',
		'master_secret' => 'e0cdf0bb20fe51dfa299072b',
	),
		
	//**********支付宝配置从这里开始**********
	'alipay_config' => array(
		'app_id' =>'2017051807272756',//商户密钥  2017051807272756 2016080400168640
		//'rsaPrivateKey' =>'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCKEO8n+mN5IXMdGVXoCqRLcskQpd3nlxYVoBBneduDkE1tQgwBi44793IT2z2/2Zpx8GYLDF5OmQZjPi39GktbDA1aICecpO75ZnWc7R1ZmzD+Axv4dcvNnNRiPgun/9zeuHOOPr/IK564tv+dv5BzQ0OjFL2+XGSovxpzy775bkY7D+eBVVU1b+98s1GUc8xoBV5XCGotoJMlGRQksJTJsCvs0ahIxpda4ZXaq4QW1jXLCAGRuuHAzBTZqjXN6m7RKjeT9CAgYiCvp3Yk0Gfh2aM46nJh21RRy1hgiXz7q3FFVT7Jy4nNzS3wWHtWqsKpXkkO4QMpm0wvDPYOthaRAgMBAAECggEAJ14iw1R7DqwCBdcWjr6SEE1flZamCoYjLvFgHBE8DWyVF0VQ5RPk76Zj6dOG7PBgQILapeXV8jViA+RT+qqHuCNcn0kx4lGJLqYPfTqDNuywcI10aUk1XqCMUqL8cd/cG8mecX3k82+0p1jO1C0uRB925sZgQCpaLV+obEI5ZyeleTyJj8jkyD30g8+1fMR6akdAddfRSDQgwnFzKhhMWLxyYec++0aiPbThK03ibjznVm9ucYEXEQc0i0mjpbO4NoDY3YwcHMdttYIVlMU1kKzkErAh1rIPoRYRbi6T2qdur2FLZ866I05GVPlAY4548PNfncZtKmNLEfsgmi03zQKBgQDTpSxsNU+nQRVALOMwfj7affQ8YFR7Ji8+K8ZZb2Msuu9INSGZbPS7P5/IxZHpR08fGVidtdb4v1z9heLM2uPsoSsrcoE9TxIWEo3j/ZwpM2rreHHDjhZDvZ2BQKiJBNAojUBNuifXZ0MOgY6Tsa4HRTQXP0ZaaH67ewQGtbIoMwKBgQCnADkQdwSr89nYe0csML4yRvsGhcZ0GrnNVCWmbPoivQahoIN0WaBUWtWJZ7FXrp4DdJ3UQ1qwhYF7RWRgzqYJ7nGqN0WuyfnhJYVetso8DXxyr3e6/p9tm/NkSwSVpBhE28FWXdHkUhEpkSAAGbiuMWeE07ogoFYfqoLZ7nNSKwKBgB3TpVMKoFMd5PTKXqoy4IDCR21K1h0U4IuOd8Ga5QskvRwcAMQyirro2Ife8BVEK25ikA6J1eXwchZ4i+H65ywt+nuBA520SwQ5US3US/GygVr1+nJoz9J0IoJYrmfA+eT9IxbRKVQ+BUFCGOnPnIsBwbmU/UE4gMPDLmoTqSvpAoGBAIujbROuYTKiuMIB+rYhn7eMkHOYwiLtAfWFaeHp95G6euQEwkY0dxJxzWGSWcBWWvvKs/n7w8YcK/N2R4OG52EghtxsURDhA9bVt9pFf36NTETdIYEzTP2qy+5Np+y0BDL3iYb89fczA/S8y4Qp/blxx/xRya1kQ/9AZR9y+ex3AoGBAMxwEmWlyaw8BDNeGcezaYTVQTis4Aoqaoosep/NLJ1blTor2KMVNdLXSSum4YmUrbqp65tfyYN6hRZu0cvC6pkl1lEuz2Gaop1bCD7HAPHFjU3G4yWWiH6Dk+PyZZxGOazUfGqFF0kJsi0FoSNW9d7xlE7QX4nUrhrmrGKJJ3n6',//私钥 沙箱配置
		'rsaPrivateKey' => 'MIICXwIBAAKBgQDIxA/rNzBNVpXUoUrLWbC1Xd1ElXx+QpgItf/xGkK6xQeBXxDsqQ3synMXbL/jOEsrEo6x8dOVkRQKtsgxwXVyrVRbW2WFcN1uAtiWMMgzUugq+AhQ34XiN6ow62gaAwmxmmpCueXpFu1nfo0VOrsXJCfOfUwJW7joHdyUuGLQ2QIDAQABAoGBAI8gx8CG7daU/ehCvNHQNVx3eIGmmMRisYS0S60VbbE9OiaPIb+2ngjMI9T1YK+auGwSpMxTfOZKMaGZwYtHQnSlphyNxxSmcMMC2v8oyu2Fw5gsIbsiDGJvkh2m5ly7nLiLQXTwKq37dD4yCkcBlVFrFB11WtykGCIYuA2mYpdJAkEA6KRx2qXayEONDYYph+vOU2c7d+CI4lh5ZrPPWzRoSBTdSLMwbl0sMqsp19QCdx/FQF8K25VEEWiu61SBvOXgWwJBANzsTY3Vx0YQ6Yp0jIckLncQpRKJ6DU+TgBPkKsEY4JWTcWLuAWDBxco0bWJQYN3GMWx5uYakbQlTFZMjo7pGdsCQQDYHyTSHfAgyXh5TuC4L50uqCF73TDtLYoimfqRXR9fj1p/VlAwxwfvTkmCAqgDqJUjlufMVF+22IxffNNF/DwvAkEAovL8xRggcpK433HV0TwjtZimWQU1LEh3Wg1VxH5pM1Ka7JGAuzgI+9EU1RSXKPOoZvEwQRrpy3kTVDgFkm1mrwJBAIN9SjGWVdwGIOLGPTm2qR2su7dCZPgSLl3F/6rK/3V9Wfbb/74DW4rAhlbhJypE7WbeW9RVKPgUloRDhDczhDA=',//正式
		//'alipayrsaPublicKey'=>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAihDvJ/pjeSFzHRlV6AqkS3LJEKXd55cWFaAQZ3nbg5BNbUIMAYuOO/dyE9s9v9macfBmCwxeTpkGYz4t/RpLWwwNWiAnnKTu+WZ1nO0dWZsw/gMb+HXLzZzUYj4Lp//c3rhzjj6/yCueuLb/nb+Qc0NDoxS9vlxkqL8ac8u++W5GOw/ngVVVNW/vfLNRlHPMaAVeVwhqLaCTJRkUJLCUybAr7NGoSMaXWuGV2quEFtY1ywgBkbrhwMwU2ao1zepu0So3k/QgIGIgr6d2JNBn4dmjOOpyYdtUUctYYIl8+6txRVU+ycuJzc0t8Fh7VqrCqV5JDuEDKZtMLwz2DrYWkQIDAQAB',//公钥(自己的程序里面用不到) 沙箱配置
		'alipayrsaPublicKey'=> 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB',//正式
		'partner'=>'2088801888628335',//(商家的参数,新版本的好像用不到) 2088421540577515
		'notify_url' =>'http://114.80.18.34/home/app/notify_url',//回调地址(支付宝支付成功后回调修改订单状态的地址)
		'return_url' =>'http://114.80.18.34/home/app/return_url',//同步回调地址(支付宝支付成功后回调修改订单状态的地址)
		'payment_type' =>1,//(固定值)
		'seller_id' =>'tracy.li@trueland.net',//收款商家账号 tracy.li@trueland.net  fwrnqp4000@sandbox.com
		'charset'    => strtolower('utf-8'),//编码
		'sign_type' => 'RSA',//签名方式 RSA RSA2
		//'timestamp' =>date('Y-m-d H:i:s', time()),
		'version'   =>"1.0",//固定值
		'url'       => 'https://openapi.alipay.com/gateway.do',//固定值 https://openapi.alipay.com/gateway.do https://openapi.alipaydev.com/gateway.do
		'method'    => 'alipay.trade.app.pay',//固定值
		
		'alipay_config_biz' => array(
			//'body' => 'ceshi充值',
			//'subject' => '',//商品的标题/交易标题/订单标题/订单关键字等
			//'out_trade_no' => '',//商户网站唯一订单号
			'timeout_express' => '1d',//该笔订单允许的最晚付款时间
			//'total_amount' => '',//订单总金额(必须定义成浮点型)
			//'seller_id' => 'tracy.li@trueland.net',//收款人账号  tracy.li@trueland.net  fwrnqp4000@sandbox.com
			'product_code' => 'QUICK_MSECURITY_PAY',//销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
			//'store_id' => 'BJ_001',//商户门店编号
					
		
		),
	),
	
	//**********支付宝配置从这里结束**********
	
		
	//**********微信配置从这里结束**********
	/* 'wxpay_config' => array(
		'appid' => 'wx426b3015555a46be',//公众号APPID wx71592a5777a8cfeb
		'mch_id' => '1900009851',//微信支付商户号 1399644302
		'notify_url' => 'http://114.80.18.34/home/app/wxnotify_url',//回调地址(支付宝支付成功后回调修改订单状态的地址)
		'trade_type' => strtoupper('app'),//支付类型(app)
		'sign_type' => strtoupper('md5'),//加密方式(md5, sha256)
		'package' => 'Sign=WXpay',//固定写法
		'key' => '8934e7d15453e97507ef794cf7b0519d',//安全密钥 D5e1e2b22214533Ac0779b5e20acb71d
	), */
	//**********微信配置从这里结束**********
);