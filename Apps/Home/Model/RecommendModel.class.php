<?php
namespace Home\Model;
use Think\Model;
class RecommendModel extends Model {
	protected $trueTableName = 'gl_recommend';//要加上完整的表名
	
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
    
    public function insertRecommend($data){
    	$result = M($this->trueTableName)->add($data);
    	return $result;
    }
    
    public function updateRecommend($where, $data){
    	$result = M($this->trueTableName)->where($where)->save($data);
    	return $result;
    }
    
    public function delRecommend($where){
    	$result = M($this->trueTableName)->where($where)->delete();
    	//echo M($this->trueTableName)->_sql();
    	return $result;
    }
    
    public function updateFieldInc($where, $field, $num=1){
    	$result = M($this->trueTableName)->where($where)->setInc($field, $num);
    	return $result;
    }
    
    public function updateFieldDec($where, $field, $num=1){
    	$result = M($this->trueTableName)->where($where)->setDec($field, $num);
    	return $result;
    }
    
    public function getRecommendForUserList($param){
    	$result = M($this->trueTableName)->alias('a')->join($param['join'])->field($param['field'])->where($param['where'])->order($param['order'])->limit($param['limit'])->select();
    	return $result;
    }
    
    public function getRecommendForUserCount($param){
    	$result = M($this->trueTableName)->alias('a')->join($param['join'])->field($param['field'])->where($param['where'])->count();
    	return $result;
    }
    
    public function getRecommendForUserInfo($param){
    	$result = M($this->trueTableName)->alias('a')->join($param['join'])->field($param['field'])->where($param['where'])->find();
    	return $result;
    }
    
    /**
     * 获取文章标题和图片
     * @param string $url
     */
    public function grabWeixin($url){
    	$result = array();
    	$resContent = request_curl($url);//curl请求
    	import("Org.Util.simple_html_dom");//加载解析html页面的类库
    	$domObj = new \simple_html_dom();//new对象
    	$domObj->load($resContent);//把内容放到对象里
    	$result['title'] = $domObj->find('title', 0)->innertext;//取标题
    	$str = $this->deleteHtml($resContent);//去掉空格 和 换行
    	preg_match_all('/varmsg_cdn_url=\"(.*)\";varmsg_link=/', $str, $msgcdnurl);//正则匹配url
    	$imgPath = $msgcdnurl[1][0];//得到url地址
    	$result['img_path'] = $this->requestImg($imgPath);//把网络地址换成本地地址
    	$domObj->clear();// 使用完插件以后，销毁
    	return $result;
    }
    
    /**
     * 发布完的总金额数
     * @param string $userId
     * @return number
     */
    public function getPushlishCoin($userId){
    	$nowDate = date("Y-m-d H:i:s", time());
    	$missionList = D('Mission')->getList(array('field'=>'award, copies', 'where'=>array('user_id'=>$userId, 'end_time'=>array("gt", $nowDate), 'flag'=>array("in", "0,2"))));
    	$sum = 0;
    	if(!empty($missionList)){
    		foreach($missionList as $k=>$v){
    			$sum += ($v['award'] * $v['copies']);
    		}
    	}
    	
    	$recommendList = D('Recommend')->getList(array('field'=>'award, copies', 'where'=>array('user_id'=>$userId, 'end_time'=>array("gt", $nowDate) , 'flag'=>array("in", "0,2"))));
    	if(!empty($recommendList)){
    		foreach($recommendList as $k=>$v){
    			$sum += ($v['award'] * $v['copies']);
    		}
    	}
    	
    	return $sum;
    }
    
    /**
     * 去掉空格 和 换行
     * @author hainer.zhou
     * @date 2016-4-27
     * */
    public function deleteHtml($str) {
    	// 去掉字符串中的换行和空格
    	$str = trim($str); //清除字符串两边的空格
    	//$str = strip_tags($str,""); //利用php自带的函数清除html格式
    	$str = preg_replace("/\t/","",$str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
    	$str = preg_replace("/\r\n/","",$str);
    	$str = preg_replace("/\r/","",$str);
    	$str = preg_replace("/\n/","",$str);
    	$str = preg_replace("/ /","",$str);
    	$str = preg_replace("/  /","",$str);  //匹配html中的空格
    	$str = trim($str);
    	return $str;
    }
    
    /**
     * 把网上的图片下载到本地
     * @author hainer.zhou
     * @date 2016-4-27
     * */
    public function requestImg($url, $ext = 'png') {
    	//$url = 'http://mp.weixin.qq.com/rr?timestamp=1461720185&src=3&ver=1&signature=i7*34ULdX-MctiJEEvxNn--a*p-U4O-WUGc8LAU-ADBLouFcaS*MCXLdoknc-DotqKDNBGjLFApErYZjRjM53hmhiHcqrRqqjaFBnSLaNkY=';
    	$date = date('Y-m-d');
    	$destination_folder = APP_ROOT . '/Public/data/wximg/' . $date . '/';
    	if( ! is_dir($destination_folder)){
    		mkdir($destination_folder, 0755, true);
    	}
    	if( ! is_readable($destination_folder) ){
    		chmod($destination_folder, 0755);
    	}
    	$pic_name = uniqid() . '.' . $ext;
    	$newfname = $destination_folder . $pic_name;//文件PATH
    	$file = @fopen($url, 'rb');
    	if($file){
    		$newf = @fopen( $newfname, 'wb');
    		if($newf) {
    			while(!feof($file)) {
    				fwrite($newf, fread($file,1024*8), 1024*8);
    			}
    		}
    		if($file) {
    			fclose($file);
    		}
    		if($newf) {
    			fclose($newf);
    		}
    	}
    	if (is_file($newfname)) {
    		return '/data/wximg/' . $date . '/'.$pic_name;
    	} else {
    		return '';
    	}
    }
}