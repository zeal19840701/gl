<?php
/**
 * 
 * 微信支付API异常类
 * @author widyhu
 *
 */
namespace Home\Library\wxpay;
class WxPayException extends \Think\Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
