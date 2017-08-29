<?php
namespace Home\Controller;
use Think\Controller;
class GrabController extends Controller {
    public function push(){
    	$acqModel = M('data_acquisition', '', 'DB_DATA_GRAB');
    	$newsModel = M('gl_news', '', 'DB_GOLD_LOCK_NEW');
    	$sql = "SELECT * FROM `data_acquisition` WHERE `flag` = 0 limit 0, 100";
    	$res = $acqModel->query($sql);
    	if(empty($res)){
    		exit('empty data!'. "\n");
    	}
    	$res = $this->_checkData($res);//检查数据是否存在
    	$nowDate = date("Y-m-d H:i:s", time());
    	foreach($res as $k=>$v){
    		$newsArr = array();
    		$newsArr = $v;
    		$newsArr['check_key'] = $v['checkkey'];
    		if(empty($v['publishtime'])){
    			$newsArr['publish_time'] = $v['storagetime']?$v['storagetime']:$nowDate;
    		}else{
    			$newsArr['publish_time'] = $v['publishtime'];
    		}
    		$newsArr['storage_time'] = $v['storagetime']?$v['storagetime']:$nowDate;
    		
    		unset($newsArr['flag']);
    		unset($newsArr['checkkey']);
    		unset($newsArr['publishtime']);
    		unset($newsArr['storagetime']);
    		unset($newsArr['comment_num']);
    		$rsNews = $newsModel->add($newsArr);
   			if($rsNews){
   				$acqModel->where(array('checkKey'=>$v['checkkey']))->save(array('flag'=>1));
   			}
   		}
    	exit('push finish!'. "\n");
    }
    

    /**
     * 检查记录是否存在。存在去掉
     * @param unknown $res
     * @return unknown
     */
    private function _checkData($res){
    	$acqModel = M('data_acquisition', '', 'DB_DATA_GRAB');
    	$newsModel = M('gl_news', '', 'DB_GOLD_LOCK_NEW');
    	if(!empty($res)){
    		$resStr = '';
    		foreach($res as $k=>$v){
    			$resStr .= "'" . $v['checkkey'] . "',";
    		}
    		if($resStr){
    			$resStr = substr($resStr, 0, -1);
    			$csql = "SELECT * FROM `gl_news` WHERE check_key IN (" . $resStr . ")";
    			//$newsRes = D('News')->getQuery($csql);
    			$newsRes = $newsModel->query($csql);
    			if(!empty($newsRes)){
    				$sum=0;
    				$newRes1 = array();
    				foreach($newsRes as $kk=>$vv){
    					$newRes1[$vv['check_key']] = $vv;
    				}
    				foreach($res as $key=>$val){
    					if($val['checkkey'] == $newRes1[$val['checkkey']]['check_key']){
    						$sum++;
    						unset($res[$key]);
    						//D('Acquisition')->updateAcquisition(array('checkKey'=>$val['checkkey']), array('flag'=>1));
    						$acqModel->where(array('checkKey'=>$val['checkkey']))->save(array('flag'=>1));
    					}
    				}
    				echo 'repetition data:'.$sum."\n";
    			}
    		}
    	}
    	return $res;
    }
    
    /* public function autopush(){
    	//set_time_limit(0);
    	ini_set('memory_limit','512M');
    	$acqModel = M('data_acquisition', '', 'DB_DATA_GRAB');
    	$newsModel = M('gl_news', '', 'DB_GOLD_LOCK');
    	$i = 0;
    	$sum = 0;
    	while(true){
    		$sql = "SELECT * FROM `data_acquisition` WHERE `flag` = 0 limit 0, 1000";
    		//$res = D('Acquisition')->getQuery($sql);
    		$res = $acqModel->query($sql);
    		if(empty($res)){
    			break;//跳出while循环
    		}
    		$res = $this->_checkData($res);//检查数据是否存在
    		$sum += count($res);
    		foreach($res as $k=>$v){
    			$newsArr = array();
    			$newsArr = $v;
    			$newsArr['check_key'] = $v['checkkey'];
    			$newsArr['publish_time'] = $v['publishtime'];
    			$newsArr['storage_time'] = $v['storagetime'];
    			unset($newsArr['flag']);
    			unset($newsArr['checkkey']);
    			unset($newsArr['publishtime']);
    			unset($newsArr['storagetime']);
    			unset($newsArr['comment_num']);
    			//$rsNews = D('News')->insertNews($newsArr);
    			$rsNews = $newsModel->add($newsArr);
    			if($rsNews){
    				//D('Acquisition')->updateAcquisition(array('checkKey'=>$v['checkkey']), array('flag'=>1));
    				$acqModel->where(array('checkKey'=>$v['checkkey']))->save(array('flag'=>1));
    			}
    		}
    		$i++;
    		echo $i;
    		if($i<5){
    			break;
    		}
    	}
    	if($i<1){
    		echo ('empty data!'. "\n");
    	}
    	echo ('sum:'. $sum .' autopush finish!' . "\n");
    	
    	//echo json_encode(array('status'=>true, 'msg'=>'推送成功'));
    } */
    
    /**
     * 将news表过期的新闻移动到news1表上
     */
    public function cpnews(){
    	$newsModel = M('gl_news', '', 'DB_GOLD_LOCK');
    	$news1Model = M('gl_news1', '', 'DB_GOLD_LOCK');
    	while(true){
    		$j = $i * 10;
    		$sql = "SELECT * FROM `gl_news` WHERE publish_time<='2017-01-31 23:59:59' LIMIT 20";
    		$res = $newsModel->query($sql);
    		if(empty($res)){
    			break;
    		}
    		$res = $this->_checkNews1($res);
    		$checkKeyArr = array();
    		$i=0;
    		foreach($res as &$v){
    			$checkKeyArr[] = $v['check_key'];
    			unset($v['id']);
    			$rsNews = $news1Model->add($v);
    			$i++;
    		}
    		echo "add news1 databases sum: " . $i . "\n";
    		if(!empty($checkKeyArr)){
    			echo "delete sum: " . count($checkKeyArr) . "\n";
    			$newsModel->where(array('check_key'=>array('in', $checkKeyArr)))->delete();
    		}
    		$i++;
    	}
    	echo "operate finish!\n";
    }
    
    /**
     * 检查数据
     * @param unknown $res
     */
    public function _checkNews1($res){
    	$newsModel = M('gl_news', '', 'DB_GOLD_LOCK');
    	$news1Model = M('gl_news1', '', 'DB_GOLD_LOCK');
    	if(!empty($res)){
    		$resStr = '';
    		foreach($res as $k=>$v){
    			$resStr .= "'" . $v['check_key'] . "',";
    		}
    		if($resStr){
    			$resStr = substr($resStr, 0, -1);
    			$csql = "SELECT * FROM `gl_news1` WHERE check_key IN (" . $resStr . ")";
    			$newsRes = $news1Model->query($csql);
    			 
    			if(!empty($newsRes)){
    				$sum=0;
    				$newRes1 = array();
    				foreach($newsRes as $kk=>$vv){
    					$newRes1[$vv['check_key']] = $vv;
    				}
    				foreach($res as $key=>$val){
    					if($val['check_key'] == $newRes1[$val['check_key']]['check_key']){
    						$sum++;
    						unset($res[$key]);
    						$newsModel->where(array('check_key'=>$val['check_key']))->delete();
    					}
    				}
    				echo 'repetition data: '.$sum."\n";
    			}
    		}
    	}
    	return $res;
    }
    
    
    /**
     * 44.200->114.80.18.34
     */
    public function cpnews1(){
    	$acqModel = M('gl_news', '', 'DB_GOLD_LOCK');
    	$newsModel = M('gl_news', '', 'DB_GOLD_LOCK_NEW');
    	while(true){
	    	$sql = "SELECT * FROM `gl_news` WHERE `flag1` = 0 limit 0, 60";
	    	$res = $acqModel->query($sql);
	    	if(empty($res)){
	    		echo 'empty data!'. "\n";
	    		break;
	    	}
	    	$res = $this->_checkData1($res);//检查数据是否存在
	    	$nowDate = date("Y-m-d H:i:s", time());
	    	foreach($res as $k=>$v){
	    		$newsArr = array();
	    		$newsArr = $v;
	    		$newsArr['check_key'] = $v['check_key'];
	    		if(empty($v['publish_time'])){
	    			$newsArr['publish_time'] = $v['storage_time']?$v['storage_time']:$nowDate;
	    		}else{
	    			$newsArr['publish_time'] = $v['publish_time'];
	    		}
	    		$newsArr['storage_time'] = $v['storage_time']?$v['storage_time']:$nowDate;
	    		unset($newsArr['id']);
	    		unset($newsArr['flag1']);
	    		//print_r($newsArr['id']);exit;
	    		$rsNews = $newsModel->add($newsArr);
	   			if($rsNews){
	   				$acqModel->where(array('check_key'=>$v['check_key']))->save(array('flag1'=>1));
	   			}
	   		}
    	}
    	exit('push finish!'. "\n");
    }
    
    /**
     * 检查记录是否存在。存在去掉
     * @param unknown $res
     * @return unknown
     */
    private function _checkData1($res){
    	$acqModel = M('gl_news', '', 'DB_GOLD_LOCK');
    	$newsModel = M('gl_news', '', 'DB_GOLD_LOCK_NEW');
    	if(!empty($res)){
    		$resStr = '';
    		foreach($res as $k=>$v){
    			$resStr .= "'" . $v['check_key'] . "',";
    		}
    		if($resStr){
    			$resStr = substr($resStr, 0, -1);
    			$csql = "SELECT check_key FROM `gl_news` WHERE check_key IN (" . $resStr . ")";
    			//$newsRes = D('News')->getQuery($csql);
    			$newsRes = $newsModel->query($csql);
    			
    			if(!empty($newsRes)){
    				$sum=0;
    				$newRes1 = array();
    				foreach($newsRes as $kk=>$vv){
    					$newRes1[$vv['check_key']] = $vv;
    				}
    				
    				foreach($res as $key=>$val){
    					if($val['check_key'] == $newRes1[$val['check_key']]['check_key']){
    						$sum++;
    						unset($res[$key]);
    						//D('Acquisition')->updateAcquisition(array('checkKey'=>$val['checkkey']), array('flag'=>1));
    						$acqModel->where(array('check_key'=>$val['check_key']))->save(array('flag1'=>1));
    					}
    				}
    				echo 'repetition data:'.$sum."\n";
    			}
    		}
    	}
    	return $res;
    }
}