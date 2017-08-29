<?php
namespace Home\Controller;
use Think\Controller;
class MissionController extends AuthController {
	
    public function madd(){
    	set_time_limit(120);
    	$missionData = I('missionData');//传入数据
    	/* $this->_logs('missionData');
    	$this->_logs($missionData); */
    	$missionData = json_decode(htmlspecialchars_decode($missionData), true);//把json转成数组形式
    	$this->_logs('missionData');
    	$this->_logs($missionData);
    	//pr($missionData);
    	$userInfo = D('User')->getUserInfo(array('where'=>array('id'=>$this->userid)));
    	$missionCoin = D('Recommend')->getPushlishCoin($this->userid);//获得发布总金额
    	$publishTotalCoin = $missionData['award'] * $missionData['copies'];
    	if(($userInfo['coin']-$missionCoin) < $publishTotalCoin){
    		$this->returnApiMsg ('1115', '余额不足，无法发布' );
    	}
    	$nowTime = date("Y-m-d H:i:s", time());
    	$model = D('Mission');
    	$modelStep = D('MissionStep');
    	$modelStepImg = D('MissionStepImg');
    	$model->startTrans();//任务事务开启
    	//$modelStep->startTrans();//步骤事务开启
    	//$modelStepImg->startTrans();//图片事务开启
    	$missionFlag = true;
    	$newMissionData = array();
    	$newMissionData['user_id'] = $this->userid;
    	$newMissionData['title'] = $missionData['title'];
    	$newMissionData['intro'] = $missionData['intro'];
    	$newMissionData['award'] = $missionData['award'];
    	$newMissionData['total_copies'] = $missionData['copies'];
    	$newMissionData['copies'] = $missionData['copies'];
    	$newMissionData['start_time'] = $missionData['start_time'];
    	$newMissionData['end_time'] = date("Y-m-d 23:59:59", strtotime($missionData['end_time']));
    	$newMissionData['city'] = $missionData['city'];
    	if(isset($missionData['equipment']) && in_array($missionData['equipment'], array("Android", "IOS"))){
    		$newMissionData['equipment'] = $missionData['equipment'];
    	}
    	$newMissionData['total_number'] = $missionData['total_number'];
    	$newMissionData['day_number'] = $missionData['day_number'];
    	
    	$newMissionData['attend_num'] = 0;
    	$newMissionData['not_audit_num'] = 0;
    	if(isset($missionData['is_equipment']) && in_array($missionData['is_equipment'], array("无", "IP", "MAC", "IMEI", "IDFA"))){
    		$newMissionData['is_equipment'] = $missionData['is_equipment'];
    	}
    	$newMissionData['update_time'] = $nowTime;
    	$newMissionData['create_time'] = $nowTime;
    	
    	$missionId = $model->insertData($newMissionData);
    	if(!$missionId){
    		$missionFlag = false;//报错就设置false
    	}
    	$basePath = APP_ROOT . '/Public';
    	$path = '/data/uploads/'.date("Y-m-d", time()).'/';
    	$destinationFolder = $basePath . $path;
    	if( ! is_dir($destinationFolder)){
    		mkdir($destinationFolder, 0755, true);
    	}
    	if( ! is_readable($destinationFolder) ){
    		chmod($destinationFolder, 0755);
    	}
    	$result = array();
    	$imgs = array();
    	$step = array();
    	$isSave = false;
    	$j = 1;
    	while(true){
    		$this->_logs(array("进入上传文件", $_FILES));
    		if(isset($_FILES['uploadfile'.$j])){
    			/* $this->_logs('uploadfile'.$j);
    			$this->_logs($_FILES['uploadfile'.$j]); */
    			$maxFileCount = count($_FILES['uploadfile'.$j]['error']);
    			$this->_logs(array("数量maxFileCount", $maxFileCount));
    			for($i=0;$i<$maxFileCount;$i++){
    				if(UPLOAD_ERR_OK == $_FILES['uploadfile'.$j]['error'][$i]){
    					$tmpName = $_FILES['uploadfile'.$j]['tmp_name'][$i];
    					$uploadFileName = $_FILES['uploadfile'.$j]['name'][$i];
    					$unUploadFileName = uniqid() . basename($uploadFileName);
    					$uploadFile =  $destinationFolder . $unUploadFileName;
    					$isSave = move_uploaded_file($tmpName, $uploadFile);
    					if($isSave){
    						$imgs[$j][] = $path . $unUploadFileName;
    					}
    				}
    			}
    		}else{
    			break;
    		}
    		$j++;
    	}
    	$this->_logs(array("数量imgs", $imgs));
    	$stepj=1;
    	$arrMissionStepIds = array();//步骤id集合
    	while(true){
    		$step = I('step'.$stepj);
    		if(isset($step) && !empty($step)){
    			$data = array(
    					'user_id' => $this->userid,
    					'mid' => $missionId,
    					'intro' => $step,
    					'step' => $stepj,
    					'create_time' => $nowTime,
    			);
    			$missionStepId = $modelStep->insertData($data);
    			$arrMissionStepIds[$stepj] = $missionStepId;
    			if(!$missionStepId){
    				$missionFlag = false;//报错就设置false
    			}
    		}else{
    			break;
    		}
    		$stepj++;
    	}
    	
    	if(!empty($imgs)){
    		/* $this->_logs('imgs');
    		$this->_logs($imgs); */
    		foreach($imgs as $key=>$value){
    			foreach($value as $kk=>$vv){
    				$data = array(
    						'user_id' => $this->userid,
    						'mid'=>$missionId,
    						'msid'=>$arrMissionStepIds[$key],//新加步骤id字段
    						'img_path'=>$vv,
    						'step' => $key,
    						'create_time'=>$nowTime,
    				);
    				$missionStepImgId = $modelStepImg->insertData($data);
    				if(!$missionStepImgId){
    					$missionFlag = false;//报错就设置false
    				}
    			}
    		}
    		/* $code = '0';
    		$msg = '文件上传成功!';
    		$this->returnApiMsg ($code, $msg ); */
    	}else{
    		/* $code = '1010';
    		$msg = '文件上传失败!';
    		$this->returnApiMsg ($code, $msg ); */
    	}
    	
    	if($missionFlag){
    		$model->commit();//任务事务提交
    		//$modelStep->commit();//步骤事务提交
    		//$modelStepImg->commit();//图片事务提交
    		$code = '0';
    		$msg = '任务发布成功!';
    		$this->returnApiMsg ($code, $msg );
    	}else{
    		$model->rollback();//任务事务回滚
    		//$modelStep->rollback();//步骤事务回滚
    		//$modelStepImg->rollback();//图片事务回滚
    		$code = '1010';
    		$msg = '任务发布失败!';
    		$this->returnApiMsg ($code, $msg );
    	}
    }
    
    /**
     * 任务编辑
     */
    public function medit(){
    	set_time_limit(120);
    	$id = addslashes(I('id'));//任务id
    	$this->_logs(array('任务编辑未解析id：', $id));
    	$id = _passport_decrypt('gl', $id);
    	$this->_logs(array('任务编辑解析后id：', $id));
    	$missionData = I('missionData');//传入数据
    	$this->_logs(array('missionData原来数据：', $missionData));
    	$missionData = json_decode(htmlspecialchars_decode($missionData), true);//把json转成数组形式
    	$this->_logs(array('missionData解析数据：', $missionData));
    	if(!$id && !is_numeric($id)){
    		$this->returnApiMsg ('1062', '任务id不能为空' );
    	}
    	//pr($missionData);
    	$nowTime = date("Y-m-d H:i:s", time());
    	$model = D('Mission');
    	$missionInfo = $model->getInfo(array("where"=>array('id' => $id)));
    	if(empty($missionInfo)){
    		$this->returnApiMsg ( '1031', '任务不存在' );
    	}
    	$status = $this->_check_status($missionInfo);
    	if($status == 2){
    		$this->returnApiMsg ( '1121', '任务进行中，无法编辑' );
    	}
    	$userInfo = D('User')->getUserInfo(array('where'=>array('id'=>$this->userid)));
    	$missionCoin = D('Recommend')->getPushlishCoin($this->userid);//获得发布总金额
    	$publishTotalCoin = $missionData['award'] * $missionData['copies'];
    	$digt = $publishTotalCoin - ($missionInfo['award'] * $missionInfo['copies']);
    	if($userInfo['coin'] < ($missionCoin + $digt)){
    		$this->returnApiMsg ('1115', '余额不足，无法发布' );
    	}
    	
    	$modelStep = D('MissionStep');
    	$modelStepImg = D('MissionStepImg');
    	$model->startTrans();//任务事务开启
    	//$modelStep->startTrans();//步骤事务开启
    	//$modelStepImg->startTrans();//图片事务开启
    	$missionFlag = true;
    	$newMissionData = array();
    	//$newMissionData['user_id'] = $this->userid;
    	$newMissionData['title'] = $missionData['title'];
    	$newMissionData['intro'] = $missionData['intro'];
    	$newMissionData['award'] = $missionData['award'];
    	$newMissionData['total_copies'] = $missionData['copies'];
    	$newMissionData['copies'] = $missionData['copies'];
    	$newMissionData['start_time'] = $missionData['start_time'];
    	$newMissionData['end_time'] = date("Y-m-d 23:59:59", strtotime($missionData['end_time']));
    	$newMissionData['city'] = $missionData['city'];
    	$newMissionData['equipment'] = $missionData['equipment'];
    	$newMissionData['total_number'] = $missionData['total_number'];
    	$newMissionData['day_number'] = $missionData['day_number'];
    	 
    	//$newMissionData['attend_num'] = 0;
    	//$newMissionData['not_audit_num'] = 0;
    	$newMissionData['is_equipment'] = $missionData['is_equipment'];
    	$newMissionData['update_time'] = $nowTime;
    	//$newMissionData['create_time'] = $nowTime;
    	$this->_logs(array('任务数据newMissionData：', $newMissionData));
    	//$missionInfo  = $model->getInfo(array("where"=>array('id'=>$id, 'user_id'=>$this->userid)));
    	//$missionId = $missionInfo['id'];
    	$missionId = $model->updateData(array('id'=>$id, 'user_id'=>$this->userid), $newMissionData);
    	$this->_logs(array('missionId的数据：', $missionId));
    	if(!$missionId){
    		$missionFlag = false;//报错就设置false
    	}
    	$this->_logs(array('APP_ROOT：', APP_ROOT));
    	$basePath = APP_ROOT . '/Public';
    	$path = '/data/uploads/'.date("Y-m-d", time()).'/';
    	$destinationFolder = $basePath . $path;
    	$this->_logs(array('destinationFolder：', $destinationFolder));
    	if( ! is_dir($destinationFolder)){
    		mkdir($destinationFolder, 0755, true);
    	}
    	$this->_logs(array('mkdir'));
    	if( ! is_readable($destinationFolder) ){
    		chmod($destinationFolder, 0755);
    	}
    	$result = array();
    	$imgs = array();
    	$step = array();
    	$isSave = false;
    	
    	$stepj=1;
    	$arrMissionStepIds = array();//步骤id集合
    	$this->_logs(array("进入编辑页arrMissionStepIds:", $this->userid));
    	while(true){
    		$this->_logs(array("进入true:", $this->userid));
    		$step = I('step'.$stepj);
    		$this->_logs(array("进入step:" . $step, $this->userid));
    		$this->_logs(array("step:", $step));
    		if(isset($step) && !empty($step)){
    			$data = array(
    					'user_id' => $this->userid,
    					'mid' => $id,
    					'intro' => $step,
    					'update_time'=>$nowTime,
    					'step' => $stepj,
    					'create_time' => $nowTime,
    			);
    			$missionStepId = $modelStep->insertData($data);
    			/* $stepMissionWhere = array(
    				'user_id' => $this->userid,
    				'mid' => $missionId,
    				'step' => $stepj,
    			); */
    			/* $getStepMission = $modelStep->getInfo(array("where"=>array($stepMissionWhere)));
    			if(empty($getStepMission)){
    				$missionStepId = $modelStep->insertData(array('user_id' => $this->userid,'mid' => $missionId,'intro' => $step,'update_time'=>$nowTime,'step' => $stepj,'create_time' => $nowTime));
    			}else{
    				$missionStepId = $modelStep->updateData($stepMissionWhere, $data);
    			} */
    			$this->_logs(array("missionStepId的step:", $missionStepId));
    			$arrMissionStepIds[$stepj] = $missionStepId;
    			$this->_logs(array("arrMissionStepIds:", $arrMissionStepIds));
    			if(!$missionStepId){
    				$missionFlag = false;//报错就设置false
    			}
    		}else{
    			break;
    		}
    		$stepj++;
    	}
    	
    	$j = 1;
    	while(true){
    		$this->_logs(array("检查_FILES", $_FILES['uploadfile1']));
    		if(isset( $_FILES['uploadfile'.$j])){
    			$this->_logs(array("文件上传FILES：", $_FILES));
    			$maxFileCount = count($_FILES['uploadfile'.$j]['error']);
    			for($i=0;$i<$maxFileCount;$i++){
    				if(UPLOAD_ERR_OK == $_FILES['uploadfile'.$j]['error'][$i]){
    					$tmpName = $_FILES['uploadfile'.$j]['tmp_name'][$i];
    					$uploadFileName = $_FILES['uploadfile'.$j]['name'][$i];
    					$unUploadFileName = uniqid() . basename($uploadFileName);
    					$uploadFile =  $destinationFolder . $unUploadFileName;
    					$isSave = move_uploaded_file($tmpName, $uploadFile);
    					$this->_logs(array("isSave:", $isSave));
    					if($isSave){
    						$imgs[$j][] = $path . $unUploadFileName;
    					}
    				}
    			}
    		}else{
    			break;
    		}
    		$j++;
    	}
    	$this->_logs(array("图片的数据imgs:", $imgs));
    	 
    	if(!empty($imgs)){
    		foreach($imgs as $key=>$value){
    			foreach($value as $kk=>$vv){
    				$data = array(
    						'user_id' => $this->userid,
    						'mid'=>$id,
    						'msid'=>$arrMissionStepIds[$key],//新加步骤id字段
    						'img_path'=>$vv,
    						'step' => $key,
    						'create_time'=>$nowTime,
    				);
    				$missionStepImgId = $modelStepImg->insertData($data);
    				if(!$missionStepImgId){
    					$missionFlag = false;//报错就设置false
    				}
    			}
    		}
    	}
    	 
    	if($missionFlag){
    		$model->commit();//任务事务提交
    		//$modelStep->commit();//步骤事务提交
    		//$modelStepImg->commit();//图片事务提交
    		$code = '0';
    		$msg = '修改任务成功!';
    		$this->returnApiMsg ($code, $msg );
    	}else{
    		$model->rollback();//任务事务回滚
    		//$modelStep->rollback();//步骤事务回滚
    		//$modelStepImg->rollback();//图片事务回滚
    		$code = '1010';
    		$msg = '修改任务失败!';
    		$this->returnApiMsg ($code, $msg );
    	}
    }
    
    /**
     * 进行中任务编辑
     */
    public function onGoingMissionEdit(){
    	set_time_limit(120);
    	$id = addslashes(I('id'));//任务id
    	$this->_logs(array('任务编辑未解析id：', $id));
    	$id = _passport_decrypt('gl', $id);
    	$this->_logs(array('任务编辑解析后id：', $id));
    	$missionData = I('missionData');//传入数据
    	$this->_logs(array('missionData原来数据：', $missionData));
    	$missionData = json_decode(htmlspecialchars_decode($missionData), true);//把json转成数组形式
    	$this->_logs(array('missionData解析数据：', $missionData));
    	if(!$id && !is_numeric($id)){
    		$this->returnApiMsg ('1062', '任务id不能为空' );
    	}
    	//pr($missionData);
    	$nowTime = date("Y-m-d H:i:s", time());
    	$model = D('Mission');
    	$missionInfo = $model->getInfo(array("where"=>array('id' => $id)));
    	if(empty($missionInfo)){
    		$this->returnApiMsg ( '1031', '任务不存在' );
    	}
    	$status = $this->_check_status($missionInfo);
    	if($status == 1){
    		$this->returnApiMsg ( '1121', '任务未开始，无法编辑' );
    	}
    	$userInfo = D('User')->getUserInfo(array('where'=>array('id'=>$this->userid)));
    	$missionCoin = D('Recommend')->getPushlishCoin($this->userid);//获得发布总金额
    	$publishTotalCoin = $missionData['award'] * $missionData['copies'];
    	$digt = $publishTotalCoin - ($missionInfo['award'] * $missionInfo['copies']);
    	if($userInfo['coin'] < ($missionCoin + $digt)){
    		$this->returnApiMsg ('1115', '余额不足，无法发布' );
    	}
    	 
    	$modelStep = D('MissionStep');
    	$modelStepImg = D('MissionStepImg');
    	$model->startTrans();//任务事务开启
    	//$modelStep->startTrans();//步骤事务开启
    	//$modelStepImg->startTrans();//图片事务开启
    	$missionFlag = true;
    	$model->updateData(array('id'=>$id, 'user_id'=>$this->userid), array('flag'=>1));
    	$modelStep->updateData(array('mid'=>$id, 'user_id'=>$this->userid), array('flag'=>1));
    	$modelStepImg->updateData(array('mid'=>$id, 'user_id'=>$this->userid), array('flag'=>1));
    	
    	$newMissionData = array();
    	$newMissionData['user_id'] = $this->userid;
    	$newMissionData['title'] = $missionData['title'];
    	$newMissionData['intro'] = $missionData['intro'];
    	$newMissionData['award'] = $missionData['award'];
    	$newMissionData['total_copies'] = $missionData['copies'];
    	$newMissionData['copies'] = $missionData['copies'];
    	$newMissionData['start_time'] = $missionData['start_time'];
    	$newMissionData['end_time'] = date("Y-m-d 23:59:59", strtotime($missionData['end_time']));
    	$newMissionData['city'] = $missionData['city'];
    	$newMissionData['equipment'] = $missionData['equipment'];
    	$newMissionData['total_number'] = $missionData['total_number'];
    	$newMissionData['day_number'] = $missionData['day_number'];
    	
    	$newMissionData['attend_num'] = 0;
    	$newMissionData['not_audit_num'] = 0;
    	$newMissionData['is_equipment'] = $missionData['is_equipment'];
    	$newMissionData['update_time'] = $nowTime;
    	$newMissionData['create_time'] = $nowTime;
    	$this->_logs(array('任务数据newMissionData：', $newMissionData));
    	//$missionInfo  = $model->getInfo(array("where"=>array('id'=>$id, 'user_id'=>$this->userid)));
    	//$missionId = $missionInfo['id'];
    	//$missionId = $model->updateData(array('id'=>$id, 'user_id'=>$this->userid), $newMissionData);
    	$missionId = $model->insertData($newMissionData);
    	$this->_logs(array('missionId的数据：', $missionId));
    	if(!$missionId){
    		$missionFlag = false;//报错就设置false
    	}
    	$this->_logs(array('APP_ROOT：', APP_ROOT));
    	$basePath = APP_ROOT . '/Public';
    	$path = '/data/uploads/'.date("Y-m-d", time()).'/';
    	$destinationFolder = $basePath . $path;
    	$this->_logs(array('destinationFolder：', $destinationFolder));
    	if( ! is_dir($destinationFolder)){
    		mkdir($destinationFolder, 0755, true);
    	}
    	$this->_logs(array('mkdir'));
    	if( ! is_readable($destinationFolder) ){
    		chmod($destinationFolder, 0755);
    	}
    	$result = array();
    	$imgs = array();
    	$step = array();
    	$isSave = false;
    	 
    	$stepj=1;
    	$arrMissionStepIds = array();//步骤id集合
    	$this->_logs(array("进入编辑页arrMissionStepIds:", $this->userid));
    	while(true){
    		$this->_logs(array("进入true:", $this->userid));
    		$step = I('step'.$stepj);
    		$this->_logs(array("进入step:" . $step, $this->userid));
    		$this->_logs(array("step:", $step));
    		if(isset($step) && !empty($step)){
    			$data = array(
    					'user_id' => $this->userid,
    					'mid' => $missionId,
    					'intro' => $step,
    					'update_time'=>$nowTime,
    					'step' => $stepj,
    					'create_time' => $nowTime,
    			);
    			$missionStepId = $modelStep->insertData($data);
    			/* $stepMissionWhere = array(
    			 'user_id' => $this->userid,
    			 'mid' => $missionId,
    			 'step' => $stepj,
    			 ); */
    			/* $getStepMission = $modelStep->getInfo(array("where"=>array($stepMissionWhere)));
    			 if(empty($getStepMission)){
    			 $missionStepId = $modelStep->insertData(array('user_id' => $this->userid,'mid' => $missionId,'intro' => $step,'update_time'=>$nowTime,'step' => $stepj,'create_time' => $nowTime));
    			 }else{
    			 $missionStepId = $modelStep->updateData($stepMissionWhere, $data);
    			 } */
    			$this->_logs(array("missionStepId的step:", $missionStepId));
    			$arrMissionStepIds[$stepj] = $missionStepId;
    			$this->_logs(array("arrMissionStepIds:", $arrMissionStepIds));
    			if(!$missionStepId){
    				$missionFlag = false;//报错就设置false
    			}
    		}else{
    			break;
    		}
    		$stepj++;
    	}
    	 
    	$j = 1;
    	while(true){
    		$this->_logs(array("检查_FILES", $_FILES['uploadfile1']));
    		if(isset( $_FILES['uploadfile'.$j])){
    			$this->_logs(array("文件上传FILES：", $_FILES));
    			$maxFileCount = count($_FILES['uploadfile'.$j]['error']);
    			for($i=0;$i<$maxFileCount;$i++){
    				if(UPLOAD_ERR_OK == $_FILES['uploadfile'.$j]['error'][$i]){
    					$tmpName = $_FILES['uploadfile'.$j]['tmp_name'][$i];
    					$uploadFileName = $_FILES['uploadfile'.$j]['name'][$i];
    					$unUploadFileName = uniqid() . basename($uploadFileName);
    					$uploadFile =  $destinationFolder . $unUploadFileName;
    					$isSave = move_uploaded_file($tmpName, $uploadFile);
    					$this->_logs(array("isSave:", $isSave));
    					if($isSave){
    						$imgs[$j][] = $path . $unUploadFileName;
    					}
    				}
    			}
    		}else{
    			break;
    		}
    		$j++;
    	}
    	$this->_logs(array("图片的数据imgs:", $imgs));
    	
    	if(!empty($imgs)){
    		foreach($imgs as $key=>$value){
    			foreach($value as $kk=>$vv){
    				$data = array(
    						'user_id' => $this->userid,
    						'mid'=>$missionId,
    						'msid'=>$arrMissionStepIds[$key],//新加步骤id字段
    						'img_path'=>$vv,
    						'step' => $key,
    						'create_time'=>$nowTime,
    				);
    				$missionStepImgId = $modelStepImg->insertData($data);
    				if(!$missionStepImgId){
    					$missionFlag = false;//报错就设置false
    				}
    			}
    		}
    	}
    	
    	if($missionFlag){
    		$model->commit();//任务事务提交
    		//$modelStep->commit();//步骤事务提交
    		//$modelStepImg->commit();//图片事务提交
    		$code = '0';
    		$msg = '修改任务成功!';
    		$this->returnApiMsg ($code, $msg );
    	}else{
    		$model->rollback();//任务事务回滚
    		//$modelStep->rollback();//步骤事务回滚
    		//$modelStepImg->rollback();//图片事务回滚
    		$code = '1010';
    		$msg = '修改任务失败!';
    		$this->returnApiMsg ($code, $msg );
    	}
    	
    }
    
    /**
     * 删除任务
     */
    public function mdel(){
    	$id = addslashes(I('id'));//任务id
    	$this->_logs(array('删除任务之前的id', $id));
    	$id = _passport_decrypt('gl', $id);
    	$this->_logs(array('删除任务之后的id', $id));
    	if(!$id){
    		$this->returnApiMsg ('1062', '任务id不能为空' );
    	}
    	$model = D('Mission');
    	$modelStep = D('MissionStep');
    	$modelStepImg = D('MissionStepImg');
    	$getMissionInfo = $model->getInfo(array('where'=>array('id'=>$id, 'user_id'=>$this->userid)));
    	$this->_logs(array('删除任务信息是否存在:', $getMissionInfo));
    	if(!$getMissionInfo){
    		$this->returnApiMsg ('1031', '任务不存在' );
    	}
    	$res = $model->deleteData(array('user_id'=>$this->userid, 'id'=>$getMissionInfo['id']));//删除主表记录
    	$this->_logs(array('删除任务是否成功:', $res));
    	if(false !== $res){
    		D('MissionUser')->deleteData(array('mid'=>$id));
    		D('MissionUserStep')->deleteData(array('mid'=>$id));
    		D('MissionUserStepImg')->deleteData(array('mid'=>$id));
    		$stepImgList = $modelStepImg->getList(array('field'=>'id,user_id,mid,msid,img_path,step', 'where'=>array('user_id'=>$this->userid, 'mid'=>$getMissionInfo['id'])));
    		$modelStep->deleteData(array('user_id'=>$this->userid, 'mid'=>$getMissionInfo['id']));//删除步骤表记录
    		$modelStepImg->deleteData(array('user_id'=>$this->userid, 'mid'=>$getMissionInfo['id']));//删除步骤图片表记录
    		if($stepImgList){
    			$basePath = APP_ROOT . '/Public';
    			 foreach ($stepImgList as $k=>$v){
    			 	@unlink($basePath . $v['img_path']);
    			 }
    		}
    		$this->returnApiMsg ('0', '删除成功' );
    	}else{
    		$this->returnApiMsg ('1063', '删除失败' );
    	}
    	
    }
    
    /**
     * 暂停任务
     */
    public function mpause(){
    	$id = I('id');//传入id
    	$this->_logs(array('暂停任务之前的id', $id));
    	$id = _passport_decrypt('gl', $id);
    	$this->_logs(array('暂停任务之后的id', $id));
    	if(!$id){
    		$this->returnApiMsg ('1062', '任务id不能为空' );
    	}
    	$res = D('Mission')->updateData(array('id'=>$id, 'user_id'=>$this->userid), array('flag'=>2));
    	$this->_logs(array('暂停任务是否成功:', $res));
    	if(false !== $res){
    		$this->returnApiMsg ('0', '操作成功' );
    	}else{
    		$this->returnApiMsg ('1057', '操作失败' );
    	}
    }
    
    /**
     * 开启任务
     */
    public function mstart(){
    	$id = I('id');//传入id
    	$this->_logs(array('开始任务之前的id', $id));
    	$id = _passport_decrypt('gl', $id);
    	$this->_logs(array('开始任务之后的id', $id));
    	if(!$id){
    		$this->returnApiMsg ('1062', '任务id不能为空' );
    	}
    	$res = D('Mission')->updateData(array('id'=>$id, 'user_id'=>$this->userid), array('flag'=>0));
    	$this->_logs(array('开始任务是否成功:', $res));
    	if(false !== $res){
    		$this->returnApiMsg ('0', '操作成功' );
    	}else{
    		$this->returnApiMsg ('1057', '操作失败' );
    	}
    }
    
    public function mlist(){
    	$page = addslashes(I('page'));//当前页数
    	if(!$page){
    		$page = 1;
    	}
    	$size = 10;//显示10条
    	$limit = ($page-1) * $size . ',' . $size;
    	$nowTime = date("Y-m-d H:i:s", time());
    	$result = array();
    	$param = array(
    		'where' => array(
    			'a.start_time' => array('elt', $nowTime),
    			'a.end_time' => array('egt', $nowTime),
    			//'a.copies' => array('gt', 0),
    			'a.flag' => 0,
    			'b.coin' => array('gt', 0),
    		),
    		'table'=>'gl_mission as a',
    		'join'=>'LEFT JOIN gl_user as b ON a.user_id=b.id',
    		'order' => 'a.award DESC,a.copies DESC',
    		'field' => 'a.*,b.head_pic',
    		'limit' => $limit,
    	);
    	$resList = D('Mission')->getJoinQuery($param);
    	$resCount = D('Mission')->getCount(array(
    			'start_time' => array('elt', $nowTime),
    			'end_time' => array('egt', $nowTime),
    			//'copies' => array('gt', 0),
    			'flag' => 0,
    		));
    	foreach($resList as $k=>$v){
    		$resList[$k]['id'] = _passport_encrypt('gl', $v['id']);
    		if(empty($v['head_pic'])){
    			$resList[$k]['head_pic'] = C('DATA_IMG_URL') . C('DATA_IMG_HEAD_PIC');
    		}else{
    			$resList[$k]['head_pic'] = C('DATA_IMG_URL') . $v['head_pic'];
    		}
    		$resList[$k]['status'] = $this->_check_status($v);
    		$resList[$k]['end_time'] = substr($v['end_time'], 0, 10);
    	}
    	$result['items'] = $resList;
    	$result['totalPages'] = ceil($resCount/$size);
    	$this->returnApiData ( $result );
    }
    
    public function detail(){
    	$id = addslashes(I('id'));//id
    	$pure_id = _passport_decrypt('gl', $id);//解密id
    	if(!$id){
    		$this->returnApiMsg ( '1017', '任务ID不存在' );
    	}
    	if(!$pure_id){
    		$this->returnApiMsg ( '1017', '任务ID不存在' );
    	}
    	$nowTime = date("Y-m-d H:i:s", time());
    	$result = array();
    	//查询任务信息
    	$paramMission = array(
    		'where' => array(
    			'id' => $pure_id,
    		),
    	);
    	$missionInfo = D('Mission')->getInfo($paramMission);
    	if(empty($missionInfo)){
    		$this->returnApiMsg ( '1031', '任务不存在' );
    	}
    	$missionInfo['id'] = _passport_encrypt('gl', $missionInfo['id']);
    	$missionInfo['status'] = $this->_check_status($missionInfo);//检查状态
    	$missionInfo['flag'] = (int)$missionInfo['flag'];//是否暂停(0:开启，2：暂停)
    	$result['items'] = $missionInfo;
    	
    	//用户上传步骤是否完成进度
    	$paramMissionUser = array(
    		'where' => array(
    			'mid' => $pure_id,
  				'user_id' => $this->userid,
    			'status' => 0,
    		),
    		'field' => '*',
    		'order' => 'step ASC',
    	);
    	$missionUserInfo = D('MissionUser')->getInfo($paramMissionUser);//查询用户任务信息
    	$newMissionUserStepList = array();
    	$is_first_participate = 1;//默认是首次
    	$user_status = -1;//用户还未开未开始做任务
    	if(!empty($missionUserInfo)){
    		$is_first_participate = 0;//有记录就不是首次
    		if($missionUserInfo['status'] == 1){
    			$user_status = 1;//任务正常完成
    		}else if($missionUserInfo['status'] == 2){
    			$user_status = 2;//任务放弃
    		}else{
    			$user_status = 0;//任务还在进行中
    		}
    		$result['items']['mission_complete_time'] = strtotime($missionUserInfo['step_time']) + 172800 - time();//2天后的时间
    		$paramMissionUserStep = array(
    			'where' => array(
    				'muid' => $missionUserInfo['id'],//用户任务id
    				'user_id' => $this->userid,
    			),
    			'field' => '*',
    			'order' => 'step ASC',
    		);
    		$missionUserStepList = D('MissionUserStep')->getList($paramMissionUserStep);//查询
    		 
    		if(!empty($missionUserStepList)){
    			foreach($missionUserStepList as $k=>$v){
    				$newMissionUserStepList[$v['step']]['flag'] = $v['flag'];
    				$newMissionUserStepList[$v['step']]['reason'] = $v['reason'];
    			}
    		}
    	}
    	$result['items']['is_first_participate'] = $is_first_participate;//判断是不是首次参与
    	$result['items']['user_status'] = $user_status;//任务完成状态
    	//查询任务步骤列表
    	$paramMissionStep = array(
    		'where' => array(
    			'mid' => $pure_id,
    		),
    	);
    	$missionStepList = D('MissionStep')->getList($paramMissionStep);
    	$newMissionStepList = array();
    	if(!empty($missionStepList)){
    		foreach($missionStepList as $k=>$v){
    			$newMissionStepList[$v['step']] = $v;
    			$newMissionStepList[$v['step']]['id'] = _passport_encrypt('gl', $v['id']);
    			$newMissionStepList[$v['step']]['flag'] = isset($newMissionUserStepList[$v['step']]['flag'])?(int)$newMissionUserStepList[$v['step']]['flag']:-1;//任务状态，未参加为10
    			$newMissionStepList[$v['step']]['reason'] = isset($newMissionUserStepList[$v['step']]['reason'])?$newMissionUserStepList[$v['step']]['reason']:'';
    			if(3 == $newMissionStepList[$v['step']]['flag']){//判断是否重新上传,被拒绝
    				$result['items']['is_first_participate'] = 2;//是就给2状态
    			}
    		}
    		unset($missionStepList);
    	}
    	
    	//查询任务步骤列表的图片
    	$paramMissionStepImg = array(
    		'where' => array(
    			'mid' => $pure_id,
    		),
    		'field' => 'mid,img_path,step',
    	);
    	$missionStepImgList = D('MissionStepImg')->getList($paramMissionStepImg);
    	$newMissionStepImgList = array();
    	if(!empty($missionStepImgList)){
    		foreach($missionStepImgList as $k=>$v){
    			$newMissionStepImgList[$v['step']][] = C('DATA_IMG_URL') . $v['img_path'];
    		}
    		unset($missionStepImgList);
    	}
    	if(!empty($newMissionStepList)){
    		foreach($newMissionStepList as $k=>$v){
    			//$newMissionStepList[$k]['num'] = count($newMissionStepImgList[$k]);
    			$newMissionStepList[$k]['img'] = $newMissionStepImgList[$k];
    		}
    		$newMissionStepList = array_values($newMissionStepList);
    	}
    	$result['step'] = $newMissionStepList;
    	$this->returnApiData ( $result );
    }
    
    /**
     * 开始参与
     */
    public function beginParticipate(){
    	$id = addslashes(I('id'));//任务id
    	if(!is_numeric($id)){
    		$id = _passport_decrypt('gl', $id);//解密id
    	}
    	$paramMission = array(
    		'where' => array(
    			'id' => $id,
    		),
    	);
    	$missionInfo = D('mission')->getInfo($paramMission);
    	$nowTime = date("Y-m-d H:i:s", time());
    	
    	if(empty($missionInfo)){
    		$data = array('code'=>'1031', 'msg' =>'任务不存在');
    		$this->returnApiData ( $data );
    	}
    	if($missionInfo['copies'] <= 0){
    		$data = array('code'=>'1120', 'msg' =>'该任务已被小金主们瓜分完啦，请选择其它任务参与');
    		$this->returnApiData ( $data );
    	}
    	if($missionInfo['user_id'] == $this->userid){
    		$data = array('code'=>'1033', 'msg' =>'不能参加自己发布的任务');
    		$this->returnApiData ( $data );
    	}
    	$status = $this->_check_status($missionInfo);
    	if($status == 1){
    		$data = array('code'=>'1090', 'msg' =>'任务还未开始');
    		$this->returnApiData ( $data );
    	}if($status == 3){
    		$data = array('code'=>'1091', 'msg' =>'任务暂停');
    		$this->returnApiData ( $data );
    	}else if($status == 4){
    		$data = array('code'=>'1092', 'msg' =>'任务已结束');
    		$this->returnApiData ( $data );
    	}
    	if($missionInfo['total_number'] != '不限'){
    		$missionCount = D('missionUser')->getCount(array('mid'=>$id, 'user_id'=>$this->userid, 'status'=>array("in", "0,1")));
    		if(($missionCount+1) <= $missionInfo['total_number']){
                //时间可以用and连接
                $nowDate = date("Y-m-d", time());//当前日期
                $missionDayCount = D('missionUser')->getCount(array('mid'=>$id, 'user_id'=>$this->userid, 'status'=>array("in", "0,1"), 'LEFT(create_time, 10)'=>$nowDate));
                if(($missionDayCount+1) > $missionInfo['day_number']){
                    $data = array('code'=>'1108', 'msg' =>'超过一天共可以体验次数');
                    $this->returnApiData ( $data );
                }
            }else{
                $data = array('code'=>'1107', 'msg' =>'超过总体验任务次数');
                $this->returnApiData ( $data );
            }
    		/*if(($missionCount+1) > $missionInfo['total_number']){
    			$data = array('code'=>'1107', 'msg' =>'超过总体验任务次数');
    			$this->returnApiData ( $data );
    		}else{
                //时间可以用and连接
                $nowDate = date("Y-m-d", time());//当前日期
                $missionDayCount = D('missionUser')->getCount(array('mid'=>$id, 'user_id'=>$this->userid, 'status'=>array("in", "0,1"), 'LEFT(create_time, 10)'=>$nowDate));
                if(($missionDayCount+1) > $missionInfo['day_number']){
                    $data = array('code'=>'1108', 'msg' =>'超过一天共可以体验次数');
                    $this->returnApiData ( $data );
                }
            }*/
    	}
    	if($missionInfo['city'] != '不限'){
    		$ipAddr = get_ip_lookup();//124.74.78.190
    		$ipAddr['province'] = trim($ipAddr['province'], '市省');
    		$missionInfo['city'] = trim($missionInfo['city'], '市省');
    		if(!in_array($ipAddr['province'], array($missionInfo['city']))){
    			$data = array('code'=>'1106', 'msg' =>'其他城市不能做该任务');
    			$this->returnApiData ( $data );
    		}
    	}
    	if($missionInfo['copies'] > 0){
    		$missionUserInfo = D('missionUser')->getInfo(array('field'=>'id,user_id,mid', 'where'=>array('mid'=>$id,'user_id'=>$this->userid, 'status'=>0)));
    		if(empty($missionUserInfo)){
    			$missionStepCount = D('missionStep')->getCount(array('mid'=>$id));//统计所有步数
    			$dataMissionUser = array(
    					'user_id' => $this->userid,
    					'mid' => $id,
    					'coin' => 0,
    					'current_step' => 0,
    					'total_step' => $missionStepCount,
    					'flag' => 0,
    					'step_time' => $nowTime,
    					'update_time' => $nowTime,
    					'create_time' => $nowTime,
    					'is_equipment' => '无',
    					'equipment_info' => '',
    			);
    			$missionUserId = D('missionUser')->insertData($dataMissionUser);//插入任务用户信息
    			//D('Mission')->updateFieldInc(array('id'=>$id), 'attend_num', 1);//参加的人数增长
    			//D('Mission')->updateFieldDec(array('id'=>$id), 'copies', 1);//减少份数
    			$attendNum = $this->_getMissionStatDistinctCount($id);
    			D('Mission')->updateData(array('id'=>$id), array('attend_num'=>$attendNum, 'copies'=>array('exp', "`copies`-1")));//统计参加的人数，减少可用份数
    		}
    		$data = array('code'=>'0', 'msg' =>'开始参与任务成功');
    		$this->returnApiData ( $data );
    	}else{
    		$data = array('code'=>'1120', 'msg' =>'该任务已被小金主们瓜分完啦，请选择其它任务参与');
    		$this->returnApiData ( $data );
    	}
    }
    
    /**
     * 结果上传
     */
    public function uploadResult(){
    	set_time_limit(120);
    	$id = addslashes(I('id'));//任务id
    	$step = addslashes(I('step'));//步骤
    	$mac = addslashes(I('mac'));//mac地址
    	$imei = addslashes(I('imei'));//步骤
    	if(!is_numeric($id)){
    		$id = _passport_decrypt('gl', $id);//解密id
    	}
    	$ip = get_client_ip();
    	if(!$id){
    		$this->returnApiMsg ( '1017', '任务ID不存在' );
    	}
    	/* if(!trim($step)){
    		$this->returnApiMsg ( '1120', '该任务已被小金主们瓜分完啦，请选择其它任务参与' );
    	} */
    	$paramMission = array(
    		'where' => array(
    			'id' => $id,
    		),
    	);
    	$missionInfo = D('mission')->getInfo($paramMission);
    	if(empty($missionInfo)){
    		$this->returnApiMsg ( '1031', '任务不存在' );
    	}
    	/* if($missionInfo['copies'] <= 0){
    		$this->returnApiMsg ( '1120', '该任务已被小金主们瓜分完啦，请选择其它任务参与' );
    	} */
    	/* if($missionInfo['user_id'] == $this->userid){
    		$this->returnApiMsg ( '1033', '不能参加自己发布的任务' );
    	} */
    	/* $status = $this->_check_status($missionInfo);
    	if($status == 1){
    		$this->returnApiMsg ( '1090', '任务还未开始' );
    	}if($status == 3){
    		$this->returnApiMsg ( '1091', '任务暂停' );
    	}else if($status == 4){
    		$this->returnApiMsg ( '1092', '任务已结束' );
    	} */
    	/* if($missionInfo['total_number'] != '不限'){
    		$missionCount = D('missionUser')->getCount(array('mid'=>$id, 'user_id'=>$this->userid, 'status'=>array("in", "0,1")));
    		if(($missionCount) > $missionInfo['total_number']){
    			$this->returnApiMsg ( '1107', '超过总体验任务次数' );
    			//时间可以用and连接
    			$missionDayCount = D('missionUser')->getCount(array('mid'=>$id, 'user_id'=>$this->userid, 'status'=>array("in", "0,1"), 'create_time'=>array(array("egt", date("Y-m-d 00:00:00", time()) ), array("elt", date("Y-m-d 23:59:59", time()) ), 'and')));
    			if(($missionDayCount) > $missionInfo['day_number']){
    				$this->returnApiMsg ( '1108', '超过一天共可以体验次数' );
    			}
    		}
    	} */
    	/* if($missionInfo['city'] != '不限'){
    		$ipAddr = get_ip_lookup($ip);//124.74.78.190
    		$ipAddr['province'] = trim($ipAddr['province'], '市省');
    		$missionInfo['city'] = trim($missionInfo['city'], '市省');
    		if(!in_array($ipAddr['province'], array($missionInfo['city']))){
    			$this->returnApiMsg ( '1106', '其他城市不能做该任务' );
    		}
    	} */
    	$missionStepCount = D('missionStep')->getCount(array('mid'=>$id));
    	$nowTime = date("Y-m-d H:i:s", time());
    	
    	$model = D('MissionUser');
    	$modelStep = D('MissionUserStep');
    	$modelStepImg = D('MissionUserStepImg');
    	/* $stepInfo = $modelStep->getInfo(array("where"=>array("user_id"=>$this->userid, "mid"=>$id, "step"=>$step,"flag"=>array("in", "0,1"), "status"=>0)));
    	if($stepInfo){
    		if($stepInfo['flag'] == 0 || $stepInfo['flag'] == 1){
    			$this->returnApiMsg ( '1101', '任务等待审核' );
    		}
    	} */
    	$paramMissionUser = array(
    		'where' => array(
    			'mid' => $id,
    			'user_id' => $this->userid,
    			'status' => 0,
    		),
    	);
    	$missionUserInfo = $model->getInfo($paramMissionUser);//查询用户任务是否提交过
    	$missionUserId = $missionUserInfo['id'];
    	$model->startTrans();//任务事务开启
    	$missionFlag = true;
    	//如果没有提交就创建一条新的用户任务记录
    	if(empty($missionUserInfo)){
    		$dataMissionUser = array(
    			'user_id' => $this->userid,
    			'mid' => $id,
    			'coin' => 0,
    			'current_step' => 0,
    			'total_step' => $missionStepCount,
    			'flag' => 0,
    			'step_time' => $nowTime,
    			'update_time' => $nowTime,
    			'create_time' => $nowTime,
    		);
    		switch ($missionInfo['is_equipment']){
    			case '无':
    				$dataMissionUser['is_equipment'] = '无';
    				$dataMissionUser['equipment_info'] = '';
    				break;
    			case 'IP':
    				$dataMissionUser['is_equipment'] = 'IP';
    				$dataMissionUser['equipment_info'] = $ip;
    				break;
    			case 'MAC':
    				$dataMissionUser['is_equipment'] = 'MAC';
    				$dataMissionUser['equipment_info'] = $mac;
    				break;
    			case 'IMEI':
    				$dataMissionUser['is_equipment'] = 'IMEI';
    				$dataMissionUser['equipment_info'] = $imei;;
    				break;
    			case 'IDFA':
    				$dataMissionUser['is_equipment'] = 'IDFA';
    				$dataMissionUser['equipment_info'] = $imei;;
    				break;
    		}
    		$missionUserId = $model->insertData($dataMissionUser);//插入任务用户信息
    		if(!$missionUserId){
    			$missionFlag = false;
    		}
    		/* if($missionUserId){
    			$paramMissionUser = array(
    				'id' => $missionUserId,
    			);
    			//$missionUserInfo = $model->getInfo($paramMissionUser);//查询用户任务是否提交过
    		}else{
    			$missionFlag = false;
    		} */
    		//D('Mission')->updateFieldInc(array('id'=>$id), 'attend_num', 1);//参加的人数增长
    		D('Mission')->updateFieldDec(array('id'=>$id), 'copies', 1);//减少份数
    	}else{
    		$dataMissionUser = array(
    			'update_time' => $nowTime,
    		);
    		switch ($missionInfo['is_equipment']){
    			case '无':
    				$dataMissionUser['is_equipment'] = '无';
    				$dataMissionUser['equipment_info'] = '';
    				break;
    			case 'IP':
    				$dataMissionUser['is_equipment'] = 'IP';
    				$dataMissionUser['equipment_info'] = $ip;
    				break;
    			case 'MAC':
    				$dataMissionUser['is_equipment'] = 'MAC';
    				$dataMissionUser['equipment_info'] = $mac;
    				break;
    			case 'IMEI':
    				$dataMissionUser['is_equipment'] = 'IMEI';
    				$dataMissionUser['equipment_info'] = $imei;;
    				break;
    			case 'IDFA':
    				$dataMissionUser['is_equipment'] = 'IDFA';
    				$dataMissionUser['equipment_info'] = $imei;;
    				break;
    		}
    		$missionUserId = $model->updateData(array('id'=>$missionUserInfo['id']), $dataMissionUser);//插入任务用户信息
    		if(!$missionUserId){
    			$missionFlag = false;
    		}
    	}
    	$paramMissionUserStep = array(
    		'where' => array(
    			'user_id' => $this->userid,
    			'muid' => $missionUserId,
    			'step' => $step,
    		),
    	);
    	$missionUserStepInfo = $modelStep->getInfo($paramMissionUserStep);
    	$missionUserStepId = $missionUserStepInfo['id'];
    	if(empty($missionUserStepInfo)){
    		$missionUserInfo = $model->getInfo($paramMissionUser);//查询用户任务是否提交过
    		$missionUserId = $missionUserInfo['id'];
    		//插入用户步骤表
    		$dataMissionUserStep = array(
    				'user_id' => $this->userid,
    				'muid' => $missionUserId,
    				'mid' => $id,
    				'step' => $step,
    				'flag' => 1,
    				'create_time' => $nowTime,
    		);
    		$missionUserStepId = $modelStep->insertData($dataMissionUserStep);//插入任务用户信息
    		if(!$missionUserStepId){
    			$missionFlag = false;
    		}
    	}else{
    		//修改未通过的状态
    		$this->_logs(array('id'=>$missionUserStepInfo['id']), array('flag'=>1));
    		$modelStep->updateData(array('id'=>$missionUserStepInfo['id']), array('flag'=>1));//插入任务用户信息
    	}
    	$basePath = APP_ROOT . '/Public';
    	$path = '/data/uploads/'.date("Y-m", time()). '/' . date("d", time()). '/';
    	$destinationFolder = $basePath . $path;
    	if( ! is_dir($destinationFolder)){
    		mkdir($destinationFolder, 0755, true);
    	}
    	if( ! is_readable($destinationFolder) ){
    		chmod($destinationFolder, 0755);
    	}
    	//上传图片
    	if(isset($_FILES['uploadfile'])){
    		$maxFileCount = count($_FILES['uploadfile']['error']);
    		for($i=0;$i<$maxFileCount;$i++){
    			if(UPLOAD_ERR_OK == $_FILES['uploadfile']['error'][$i]){
    				$tmpName = $_FILES['uploadfile']['tmp_name'][$i];
    				$arrUploadFileName = explode('.', $_FILES['uploadfile']['name'][$i]);
    				$ufnCount = count($arrUploadFileName)-1;
    				$uploadFileName = $arrUploadFileName[$ufnCount];
    				//如果没有后缀名，默认以jpg为后缀
    				if(empty($uploadFileName)){
    					$uploadFileName = 'jpg';
    				}
    				$mtRand = mt_rand(100, 999);
    				$unUploadFileName = uniqid() . $mtRand . '.' . $uploadFileName;
    				$uploadFile =  $destinationFolder . $unUploadFileName;
    				$isSave = move_uploaded_file($tmpName, $uploadFile);
    				if($isSave){
    					$imgs[] = $path . $unUploadFileName;
    				}
    			}
    		}
    	}
    	if(!empty($imgs)){
    		/* $this->_logs('imgs');
    		 $this->_logs($imgs); */
    		//删除数据
    		//$modelStepImg->deleteData(array( 'user_id' => $this->userid, 'mid'=>$id , 'muid' => $missionUserId, 'step' => $step ));
    		foreach($imgs as $valImg){
				$data = array(
    				'user_id' => $this->userid,
    				'muid'=>$missionUserId,
					'musid'=>$missionUserStepId,
					'mid'=>$id,
    				'img_path'=>$valImg,
    				'step' => $step,
    				'create_time'=>$nowTime,
    			);
    			$missionStepImgId = $modelStepImg->insertData($data);
    			if(!$missionStepImgId){
    				$missionFlag = false;//报错就设置false
    			}
    		}
    	}
    	
    	if($missionFlag){
    		$model->commit();//上传结果成功，事务提交
    		/* $sql = "SELECT COUNT(*) AS num FROM `gl_mission_user_step` WHERE `mid`=" . $id . " AND `flag` IN (0,1,3)";
    		$userStep = D('MissionUserStep')->getQuery($sql); */
    		$getMissionStepStatCount = $this->_getMissionStepStatCount($id);
    		$getMissionStatCount = $this->_getMissionStatCount($id);
    		$surplus = $missionInfo['total_copies'] - $getMissionStatCount;
    		$attendNum = $this->_getMissionStatDistinctCount($id);
    		D('Mission')->updateData(array('id'=>$id), array('attend_num'=>$attendNum, 'not_audit_num'=>$getMissionStepStatCount, 'copies'=>$surplus));
    		$this->returnApiMsg ('0', '上传结果成功' );
    	}else{
    		$model->rollback();//上传结果失败，事务回滚
    		$this->returnApiMsg ('1032', '上传结果失败' );
    	}
    }
    
	/**
	 * 统计未完成步骤
	 * @param unknown $id
	 */
    protected function _getMissionStepStatCount($id){
    	$count = D('MissionUserStep')->getCount(array('mid'=>$id, 'flag'=>array('in', '0,1')));
    	return $count;
    }
    
    /**
     * 统计用户参加的总次数
     * @param string $id
     * @return number
     */
    protected function _getMissionStatCount($id){
    	$count = D('MissionUser')->getCount(array('mid'=>$id, 'status'=>array('in', '0,1')));
    	return $count;
    }
    
    /**
     * 统计用户参加的总数
     * @param string $id
     * @return number
     */
    protected function _getMissionStatDistinctCount($id){
    	$sql = "SELECT COUNT(DISTINCT(`user_id`)) AS cc FROM `gl_mission_user` WHERE `mid`=" . $id ." AND `status` IN (0,1) LIMIT 1";
    	$muInfo = D('MissionUser')->getQuery($sql);
    	return $muInfo[0]['cc'];
    }
    
    /**
     * 步骤详情
     */
    public function stepDetail(){
    	$id = addslashes(I('id'));//任务id
    	$step = I('step_id');//step
    	if(!is_numeric($id)){
    		$id = _passport_decrypt('gl', $id);//解密id
    	}
    	if(!$id){
    		$this->returnApiMsg ( '1099', '任务步骤ID不存在' );
    	}
    	//获取任务步骤
    	/* $missionStepInfo = D("MissionStep")->getInfo(array("field"=>"id,mid,step", "where"=>array("id"=>$id)));//查询对应任务步骤记录
    	if(empty($missionStepInfo)){
    		$this->returnApiMsg ( '1100', '任务步骤不存在' );
    	} */
    	$result = array();
    	$result['step_img'] = array();
    	$result['user_step_img'] = array();
    	//获取任务步骤的图片，他说不需要给这个字段，直接留空就好
    	/* $missionImgList = D("MissionStepImg")->getList(array("field"=>"mid,msid,img_path,step", "where"=>array("mid"=>$missionStepInfo['mid'], "step"=>$missionStepInfo['step'])));
    	if($missionImgList){
    		foreach($missionImgList as $k=>$v){
    			$result['step_img'][] = $v['img_path'];
    		}
    	} */
    	//$this->_logs(array('stepDetail:'.$this->userid, $missionStepInfo));
    	//$missionUserImgList = D("MissionUserStepImg")->getList(array("field"=>"id,user_id,muid,musid,mid,img_path,step,status", "where"=>array("user_id"=>$this->userid, "mid"=>$missionStepInfo['mid'], "step"=>$missionStepInfo['step'])));
    	$muInfo = D("MissionUser")->getInfo(array("field"=>"id", "where"=>array("user_id"=>$this->userid, "mid"=>$id, "status"=>0)));
    	$musInfo = D("MissionUserStep")->getInfo(array("field"=>"id,muid", "where"=>array("user_id"=>$this->userid, "mid"=>$id, "step"=>$step, "muid"=>$muInfo["id"])));
    	$missionUserImgList = D("MissionUserStepImg")->getList(array("field"=>"id,user_id,muid,musid,mid,img_path,step,status", "where"=>array("user_id"=>$this->userid, "musid"=>$musInfo["id"], "muid"=>$musInfo["muid"], "mid"=>$id, "step"=>$step)));//获取任务步骤图片
    	$this->_logs(array('missionUserImgList:'.$this->userid, $missionUserImgList));
    	if($missionUserImgList){
    		foreach($missionUserImgList as $k=>$v){
    			$result['user_step_img'][] = C('DATA_IMG_URL') . $v['img_path'];
    		}
    	}
    	$this->_logs(array('result:'.$this->userid, $result));
    	$this->returnApiData ( $result );
    }
    
    /**
     * 重新上传
     */
    public function reUpload(){
    	set_time_limit(120);
    	$id = addslashes(I('id'));//这个的任务id
    	$step = I('step');//步骤
    	if(!is_numeric($id)){
    		$id = _passport_decrypt('gl', $id);//解密id
    	}
    	if(!$id){
    		$this->returnApiMsg ( '1099', '任务步骤ID不存在' );
    	}
    	$missionStepInfo = D("MissionStep")->getInfo(array("field"=>"id,mid,step", "where"=>array("mid"=>$id,'step'=>$step)));//查询对应任务步骤记录
    	if(empty($missionStepInfo)){
    		$this->returnApiMsg ( '1100', '任务步骤不存在' );
    	}
    	$missionInfo = D("Mission")->getInfo(array("where"=>array("id"=>$id)));
    	if(empty($missionInfo)){
    		$this->returnApiMsg ( '1031', '任务不存在' );
    	}
    	/* if($missionInfo['copies'] <= 0){
    		$this->returnApiMsg ( '1120', '该任务已被小金主们瓜分完啦，请选择其它任务参与' );
    	} */
    	/* if($missionInfo['user_id'] == $this->userid){
    		$this->returnApiMsg ( '1033', '不能参加自己发布的任务' );
    	} */
    	/* $status = $this->_check_status($missionInfo);
    	if($status == 1){
    		$this->returnApiMsg ( '1090', '任务还未开始' );
    	}if($status == 3){
    		$this->returnApiMsg ( '1091', '任务暂停' );
    	}else if($status == 4){
    		$this->returnApiMsg ( '1092', '任务已结束' );
    	} */
    	/* if($missionInfo['total_number'] != '不限'){
    		$missionCount = D('missionUser')->getCount(array('mid'=>$id, 'user_id'=>$this->userid, 'status'=>array("in", "0,1")));
    		if(($missionCount) > $missionInfo['total_number']){
    			$this->returnApiMsg ( '1107', '超过总体验任务次数' );
    			//时间可以用and连接
    			$missionDayCount = D('missionUser')->getCount(array('mid'=>$id, 'user_id'=>$this->userid, 'status'=>array("in", "0,1"), 'create_time'=>array(array("egt", date("Y-m-d 00:00:00", time()) ), array("elt", date("Y-m-d 23:59:59", time()) ), 'and')));
    			if(($missionDayCount) > $missionInfo['day_number']){
    				$this->returnApiMsg ( '1108', '超过一天共可以体验次数' );
    			}
    		}
    	} */
    	/* if($missionInfo['city'] != '不限'){
    		$ipAddr = get_ip_lookup($ip);//124.74.78.190
    		$ipAddr['province'] = trim($ipAddr['province'], '市省');
    		$missionInfo['city'] = trim($missionInfo['city'], '市省');
    		if(!in_array($ipAddr['province'], array($missionInfo['city']))){
    			$this->returnApiMsg ( '1106', '其他城市不能做该任务' );
    		}
    	} */
    	$missionUserStepInfo = D("MissionUserStep")->getInfo(array("where"=>array("user_id"=>$this->userid, "mid"=>$missionStepInfo['mid'], "step"=>$missionStepInfo['step'])));
    	if(empty($missionUserStepInfo)){
    		$this->_logs(array('查询用户步骤', $missionUserStepInfo));
    		$this->returnApiMsg ( '1102', '该用户任务步骤不存在' );
    	}
    	$missionFlag = true;
    	D("MissionUserStep")->startTrans();//任务事务开启
    	//$missionUserStepInfo = D('MissionUserStep')->getInfo(array('where'=>array("id"=>$missionUserStepInfo['id'])));
    	$this->_logs(array('MissionUserStep0', $missionUserStepInfo, array('where'=>array("id"=>$missionUserStepInfo['id']))));
    	if(!empty($missionUserStepInfo)){
    		//改成status为1
    		$updateUserStepStatus = D("MissionUserStep")->updateData(array("id"=>$missionUserStepInfo['id']), array("flag"=>1, "status"=>1));
    		if(false === $updateUserStepStatus){//步骤表执行语句不成功才进入
    			$missionFlag = false;
    		}
    	}
    	$paramWhere = array(
    		'where'=>array(
    			"user_id"=>$this->userid,
    			"mid"=>$missionStepInfo['mid'],
    			"step"=>$missionStepInfo['step'],
    		),
    	);
    	$missionUserStepImgInfo = D('MissionUserStepImg')->getInfo($paramWhere);
    	$this->_logs(array('查询用户步骤图片', $missionUserStepImgInfo, $paramWhere));
    	if(!empty($missionUserStepImgInfo)){
    		$updateStepImgStatus = D("MissionUserStepImg")->updateData($paramWhere['where'], array("status"=>1));
    		if(false === $updateStepImgStatus){//步骤图片表执行语句不成功才进入
    			$missionFlag = false;
    		}
    	}
    	$basePath = APP_ROOT . '/Public';
    	$path = '/data/uploads/'.date("Y-m", time()). '/' . date("d", time()). '/';
    	$destinationFolder = $basePath . $path;
    	if( ! is_dir($destinationFolder)){
    		mkdir($destinationFolder, 0755, true);
    	}
    	if( ! is_readable($destinationFolder) ){
    		chmod($destinationFolder, 0755);
    	}
    	//上传图片
    	$imgs = array();
    	if(isset($_FILES['uploadfile'])){
    		$maxFileCount = count($_FILES['uploadfile']['error']);
    		for($i=0;$i<$maxFileCount;$i++){
    			if(UPLOAD_ERR_OK == $_FILES['uploadfile']['error'][$i]){
    				$tmpName = $_FILES['uploadfile']['tmp_name'][$i];
    				$arrUploadFileName = explode('.', $_FILES['uploadfile']['name'][$i]);
    				$ufnCount = count($arrUploadFileName)-1;
    				$uploadFileName = $arrUploadFileName[$ufnCount];
    				//如果没有后缀名，默认以jpg为后缀
    				if(empty($uploadFileName)){
    					$uploadFileName = 'jpg';
    				}
    				$mtRand = mt_rand(100, 999);
    				$unUploadFileName = uniqid() . $mtRand . '.' . $uploadFileName;
    				$uploadFile =  $destinationFolder . $unUploadFileName;
    				$isSave = move_uploaded_file($tmpName, $uploadFile);
    				if($isSave){
    					$imgs[] = $path . $unUploadFileName;
    				}
    			}
    		}
    	}
    	$this->_logs(array('imgs数组', $imgs));
    	$nowDate = date("Y-m-d H:i:s", time());
    	if(!empty($imgs)){
    		foreach($imgs as $valImg){
    			$data = array(
    					'user_id' => $this->userid,
    					'muid'=>$missionUserStepInfo['muid'],
    					'musid'=>$missionUserStepInfo['id'],
    					'mid'=>$missionUserStepInfo['mid'],
    					'img_path'=>$valImg,
    					'step' => $missionUserStepInfo['step'],
    					'create_time'=>$nowDate,
    					'status'=>0,
    			);
    			$missionStepImgId = D("MissionUserStepImg")->insertData($data);
    			$this->_logs(array('missionStepImgId', $missionStepImgId));
    			$this->_logs(array('missionFlag5', $missionFlag));
    			if(false === $missionStepImgId){
    				$missionFlag = false;//报错就设置false
    			}
    		}
    	}
    	if($missionFlag){
    		D("MissionUserStep")->commit();//上传结果成功，事务提交
    		/* $sql = "SELECT COUNT(*) AS num FROM `gl_mission_user_step` WHERE `mid`=" . $id . " AND `flag` IN (0,1,3)";
    		$userStep = D('MissionUserStep')->getQuery($sql); */
    		$getMissionStepStatCount = $this->_getMissionStepStatCount($id);
    		$getMissionStatCount = $this->_getMissionStatCount($id);
    		$surplus = $missionInfo['total_copies'] - $getMissionStatCount;
    		$attendNum = $this->_getMissionStatDistinctCount($id);
    		D('Mission')->updateData(array('id'=>$id), array('attend_num'=>$attendNum, 'not_audit_num'=>$getMissionStepStatCount, 'copies'=>$surplus));
    		$this->returnApiMsg ('0', '重新上传成功' );
    	}else{
    		D("MissionUserStep")->rollback();//上传结果失败，事务回滚
    		$this->returnApiMsg ('1032', '重新上传失败' );
    	}
    }
    
    /**
     * 参与的任务列表
     */
    public function attendMission(){
    	$page = addslashes(I('page'));//当前页数
    	if(!$page){
    		$page = 1;
    	}
    	$size = 10;//显示10条
    	$limit = ($page-1) * $size . ',' . $size;
    	$nowTime = date("Y-m-d H:i:s", time());
    	$result = array();
    	//progress
    	$sql = "SELECT a.mid AS id,a.coin,a.current_step,a.total_step,a.flag,a.create_time,b.title,c.head_pic FROM `gl_mission_user` AS a LEFT JOIN `gl_mission` AS b ON (a.mid=b.id) LEFT JOIN `gl_user` AS c ON (b.user_id=c.id) WHERE a.user_id='{$this->userid}' AND b.flag IN (0,2) ORDER BY a.create_time DESC limit {$limit}";
    	$missionUserList = D('MissionUser')->getQuery($sql);
    	if(!empty($missionUserList)){
    		foreach($missionUserList as $k=>&$v){
	    		$v['id'] = _passport_encrypt('gl', $v['id']);
	    		$v['progress'] = $v['current_step'] . '/' . $v['total_step'];
	    		$v['create_time'] = substr($v['create_time'], 0, 16);
	    		$v['head_pic'] = C('DATA_IMG_URL') . $v['head_pic'];
	    		
	    	}
    	}
    	$paramMissionUser = array(
    		'user_id' => $this->userid,
    	);
    	$missionUserCount = D('MissionUser')->getCount($paramMissionUser);
    	$result['items'] = $missionUserList;
    	$result['totalPages'] = ceil($missionUserCount/$size);
    	//pr($missionUserList);
    	$this->returnApiData ( $result );
    }
    
    /**
     * 发布的任务列表
     */
    public function publishMission(){
    	$page = addslashes(I('page'));//当前页数
    	if(!$page){
    		$page = 1;
    	}
    	$size = 10;//显示10条
    	$limit = ($page-1) * $size . ',' . $size;
    	$nowTime = date("Y-m-d H:i:s", time());
    	$result = array();
    	$paramMission = array(
    		'where' => array(
    			'user_id' => $this->userid,
    			'flag'=>array('in', '0,2')
    		),
    		'order' => 'id DESC',
    		'field' => 'id,user_id,title,copies,start_time,end_time,create_time,attend_num,not_audit_num,flag',
    		'limit' => $limit,
    	);
    	$missionList = D('Mission')->getList($paramMission);
    	$userInfo = D('User')->getUserInfo(array('field'=>'id,head_pic', 'where'=>array('id'=>$this->userid)));
    	foreach($missionList as $k=>&$v){
    		$v['id'] = _passport_encrypt('gl', $v['id']);
    		$v['create_time'] = substr($v['create_time'], 0, 16);
    		//$v['not_audit_num'] = $v['attend_num'] - $v['audit_num'];
    		$v['status'] = $this->_check_status($v);
    		$v['head_pic'] = C('DATA_IMG_URL') . $userInfo['head_pic'];
    		/* if($v['flag'] == 2){
    			$v['status'] = C('PAUSE_STATUS'); //'已暂停';
    		}else if(strtotime($nowTime) < strtotime($v['start_time'])){
    			$v['status'] = C('NOT_BEGIN_STATUS'); //'未开始';
    		}else if(strtotime($nowTime) >= strtotime($v['start_time']) && strtotime($nowTime) <= strtotime($v['end_time'])){
    			$v['status'] = C('ON_GOING_STATUS'); //'进行中';
    		}else{
    			$v['status'] = C('FINISH_STATUS'); //'已结束';
    		} */
    	}
    	$missionCount = D('Mission')->getCount($paramMission['where']);
    	$result['items'] = $missionList;
    	$result['totalPages'] = ceil($missionCount/$size);
    	//pr($missionList);
    	$this->returnApiData ( $result );
    }
    
    /**
     * 发布任务详情
     */
    public function publishDetail(){
    	$id = addslashes(I('id'));//任务id
    	$id = _passport_decrypt('gl', $id);//解密id
    	if(!$id){
    		$this->returnApiMsg ( '1017', '任务ID不存在' );
    	}
    	$page = addslashes(I('page'));//当前页数
    	if(!$page){
    		$page = 1;
    	}
    	$size = 10;//显示10条
    	$limit = ($page-1) * $size . ',' . $size;
    	$result = array();
    	$model = D('Mission');
    	$nowTime = date("Y-m-d H:i:s", time());
    	$paramMission = array(
    		'where'=>array(
    			'id' => $id,
    			'user_id' => $this->userid,
    		),
    		'field' => 'id,user_id,title,start_time,end_time,create_time,attend_num,not_audit_num,flag',
    	);
    	$missionInfo = $model->getInfo($paramMission);
    	if(!empty($missionInfo)){
    		$missionInfo['id'] = _passport_encrypt('gl', $missionInfo['id']);
    		$missionInfo['create_time'] = substr($missionInfo['create_time'], 0, 16);
    		$missionInfo['status'] = $this->_check_status($missionInfo);
    		/* if($missionInfo['flag'] == 2){
    			$missionInfo['status'] = C('PAUSE_STATUS'); //'已暂停';
    		}else if(strtotime($nowTime) < strtotime($missionInfo['start_time'])){
    			$missionInfo['status'] = C('NOT_BEGIN_STATUS'); //'未开始';
    		}else if(strtotime($nowTime) >= strtotime($missionInfo['start_time']) && strtotime($nowTime) <= strtotime($missionInfo['end_time'])){
    			$missionInfo['status'] = C('ON_GOING_STATUS'); //'进行中';
    		}else{
    			$missionInfo['status'] = C('FINISH_STATUS'); //'已结束';
    		} */
    		//$missionInfo['not_audit_num'] = $missionInfo['attend_num'] - $missionInfo['audit_num'];
    	}else{
    		$this->returnApiMsg ('1031', '任务不存在' );
    	}
    	$result['mission'] = $missionInfo;//任务信息
    	
    	//$sql = "SELECT a.*, b.nickname FROM `gl_mission_user` AS a LEFT JOIN `gl_user` AS b ON (a.user_id=b.id) WHERE `mid`='{$id}' AND `flag`=0 ORDER BY `id` DESC LIMIT {$limit}";
    	$sql = "SELECT a.id,a.user_id,a.mid,a.current_step,a.total_step,a.flag,b.id AS musid,b.step,b.reason,b.id AS step_id,b.flag AS step_flag,b.create_time,c.`nickname` FROM `gl_mission_user` AS a LEFT JOIN `gl_mission_user_step` AS b ON (a.`id`=b.`muid`) LEFT JOIN `gl_user` AS c ON (a.`user_id`=c.`id`)   WHERE a.`mid`='{$id}' AND b.`flag` in (0,1) LIMIT {$limit}";
    	$missionUserList = D('MissionUser')->getQuery($sql);
    	
    	if(!empty($missionUserList)){
    		$strMissionUser = '';
    		foreach ($missionUserList as $k=>$v){
    			$missionUserList[$k]['id'] = _passport_encrypt('gl', $v['id']);
    			$strMissionUser .= " (musid=" . $v['musid'] . " AND step=" . $v['step'] . ") OR";
    			
    			$missionUserList[$k]['step_id'] = _passport_encrypt('gl', $v['step_id']);
    			$missionUserList[$k]['progress'] = $v['current_step'] . '/' . $v['total_step'];
    			$missionUserList[$k]['create_time'] = substr($v['create_time'], 0, 16);
    		}
    		
    		if($strMissionUser){
    			$strMissionUser = substr($strMissionUser, 0, -2);
    			$sql = "SELECT * FROM `gl_mission_user_step_img` WHERE " . $strMissionUser;
    			$missionUserStepImgList = D('MissionUserStepImg')->getQuery($sql);
    			//pr($missionUserStepImgList);
    			$newMissionUserStepImgList = array();
    			if($missionUserStepImgList){
    				foreach($missionUserStepImgList as $k=>$v){
    					$newMissionUserStepImgList[$v['musid']][] = C("DATA_IMG_URL") . $v['img_path'];
    				}
    				//pr($newMissionUserStepImgList);
    				foreach($missionUserList as $k=>$v){
    					$missionUserList[$k]['img'] = $newMissionUserStepImgList[$v['musid']]?$newMissionUserStepImgList[$v['musid']]:array();
    				}
    				if(!isset($missionUserList[$k]['img'])){
    					$missionUserList[$k]['img'] = array();
    				}
    			}
    		}
    		if(!isset($missionUserList[0]['img'])){
    			$missionUserList[0]['img'] = array();
    		}
    		
    	}
    	
    	$result['items'] = $missionUserList;
    	$sql = "SELECT count(*) AS cc FROM `gl_mission_user` AS a LEFT JOIN `gl_mission_user_step` AS b ON (a.`id`=b.`muid`) WHERE a.`mid`='{$id}' AND b.`flag`=0";//获得总数量
    	$missionUserStepCount = D('MissionUserStep')->getQuery($sql);
    	$missionUserStepCount = $missionUserStepCount['cc'];
    	$result['totalPages'] = ceil($missionUserStepCount/$size);
    	$this->returnApiData ( $result );
    }
    
    /**
     * 任务审核
     */
    public function audit(){
    	$id = addslashes(I('id'));//任务步骤id
    	$flag = addslashes(I('flag'));//是否审核通过
    	$reason = addslashes(I('reason'));//是否审核通过
    	$this->_logs(C("DATA_IMG_URL") . "/index.php?m=home&c=mission&a=audit&id=" . $id . "&flag=" . $flag . "&userid=" . $this->userid."&token=".$this->token);
    	$id = _passport_decrypt('gl', $id);//解密id
    	if(!$id){
    		$this->returnApiMsg ( '1034', '用户步骤不存在' );
    	}
    	$paramMissionUserStep = array(
    		'where' => array(
    			'id' => $id,
    		),
    	);
    	$nowDate = date("Y-m-d H:i:s", time());
    	$model = D('MissionUserStep');
    	$MissionUserStepInfo = $model->getInfo($paramMissionUserStep);
    	$this->_logs(array('任务审核MissionUserStepInfo', $MissionUserStepInfo));
    	$this->_logs(array('任务审核MissionUserStepInfo：mid', $MissionUserStepInfo['mid']));
    	$missionInfo = D('Mission')->getInfo(array('where'=>array('id'=>$MissionUserStepInfo['mid'])));
    	$this->_logs(array('任务审核missionInfo', $missionInfo));
    	$isFinishMission = 0;//是否完成任务初始化
    	if(!empty($MissionUserStepInfo)){
    		$model->startTrans();//任务事务开启
    		$missionFlag = true;
    		$paramUpdateData = array();
    		if($flag){
    			$paramUpdateData['flag'] = 2;//通过
    			//审核通过
    			$updateUserStepId = $model->updateData(array('id'=>$id), $paramUpdateData);
    			if(!$updateUserStepId){
    				$missionFlag = false;
    			}
    			//修改当前的任务步数
    			$updateUserId = D('MissionUser')->updateData(array('id'=>$MissionUserStepInfo['muid']), array('current_step'=>$MissionUserStepInfo['step'], 'step_time'=>$nowDate));
    			if(!$updateUserId){
    				$missionFlag = false;
    			}
    			//查询任务用户信息
    			$userInfo = D('MissionUser')->getInfo(array('where'=>array('id'=>$MissionUserStepInfo['muid'])));
    			//判断是否当前是否完成
    			if($MissionUserStepInfo['step'] == $userInfo['total_step']){
    				$isFinishMission = 1;//任务完成
    				//任务完成，计算收益
    				$missionInfo = D('Mission')->getInfo(array('where'=>array('id'=>$MissionUserStepInfo['mid'])));
    				$this->_logs(array('step==total_step,missionInfo', $missionInfo));
    				//判断用户完成状态和添加得到金币数
    				$updateUserId = D('MissionUser')->updateData(array('id'=>$MissionUserStepInfo['muid']), array('flag'=>2, 'coin'=>$missionInfo['award'], "status"=>1));//正常完成任务状态为1
    				if(!$updateUserId){
    					$missionFlag = false;
    				}
    				
    				//增加相应的分数，完成任务者
    				/* $updateId = D('User')->updateUser(array('id'=>$MissionUserStepInfo['user_id']), array('total_coin'=>array('exp', 'total_coin+'.$missionInfo['award']), 'coin'=>array('exp', 'coin+'.$missionInfo['award']), 'today_coin'=>array('exp', 'today_coin+'.$missionInfo['award'])));
    				if(!$updateId){
    					$missionFlag = false;
    				}
    				//排行数据
    				$ret = D('UserRevenueRank')->getUserRevenueRankInfo(array('where'=>array('user_id'=>$MissionUserStepInfo['user_id'])));
    				if($ret){
    					//用户更新排行的数据
    					$userRevenueRank = D('UserRevenueRank')->updateUserRevenueRank(array('where'=>array('user_id'=>$MissionUserStepInfo['user_id'])), array('week_revenue'=>array('exp', '`week_revenue`+'.$missionInfo['award']), 'total_revenue'=>array('exp', '`total_revenue`+'.$missionInfo['award']), 'udate'=>$nowDate));
    					if(!$userRevenueRank){
    						$missionFlag = false;
    					}
    				}else{
    					$userRevenueRank = D('UserRevenueRank')->insertUserRevenueRank(array('user_id'=>$MissionUserStepInfo['user_id'], 'week_revenue'=>$missionInfo['award'], 'total_revenue'=>$missionInfo['award'], 'udate'=>$nowDate, 'cdate'=>$nowDate));
    					if(!$userRevenueRank){
    						$missionFlag = false;
    					}
    				}
    				//做任务
    				$dataUserConsume = array(
    						'user_id' => $MissionUserStepInfo['user_id'],
    						'coin'=>$missionInfo['award'],
    						'type'=>'收入',
    						'intro'=>'做任务获得',
    						'cdate'=>$nowDate,
    				);
    				$userConsume = D('UserConsume')->insertData($dataUserConsume);
    				if(!$userConsume){
    					$missionFlag = false;
    				} */
    				//减去相应分数，任务发布的者，应该在发布任务时去掉
    				/* $updateId = D('User')->updateUser(array('id'=>$missionInfo['user_id']), array('coin'=>array('exp', 'coin-'.$missionInfo['award'])));
    				 if(!$updateId){
    				 $missionFlag = false;
    				 } */
    			}
    			
    		}else{
    			$paramUpdateData['flag'] = 3;//拒绝
    			$paramUpdateData['reason'] = $reason;
    			//审核不通过
    			$updateUserStepId = $model->updateData(array('id'=>$id), $paramUpdateData);
    			if(!$updateUserStepId){
    				$missionFlag = false;
    			}
    			$userStepInfo = $model->getInfo(array("field"=>"id,mid,flag,status", "where"=>array("id"=>$id, "status"=>1)));
    			if(!empty($userStepInfo)){
    				$updateUserStepStatus = $model->updateData(array('id'=>$id), array("status"=>0));
    				if(!$updateUserStepStatus){
    					$missionFlag = false;
    				}
    			}
    		}
    		if($missionFlag){
    			$model->commit();//任务事务提交
    			//查询未审核的数量
    			$not_audit_num = D('MissionUserStep')->getCount(array('mid'=>$MissionUserStepInfo['mid'], 'flag'=>1));//只有审核中的人才能进行审核
    			D('Mission')->updateData(array('id'=>$MissionUserStepInfo['mid']), array('not_audit_num' => $not_audit_num));//修改审核的数量
    			$userInfo = D('MissionUser')->getInfo(array('where'=>array('id'=>$MissionUserStepInfo['muid'])));
    			if($flag){
    				D('MessageReceive')->insertMessage(1, $missionInfo['user_id'], '审核通过', '标题为' . $missionInfo['title'] . '得任务审核通过啦', $userInfo['user_id']);
    			}else{
    				if((1 == $userInfo['total_step']) || (0 == $userInfo['current_step'] && 1 != $userInfo['total_step'])){
    					//现在审核不立即剩余份数+1，同样是48小时，48小时之内重新上传有效，48小时未上传剩余份数+1
    					//D('Mission')->updateFieldInc(array('id'=>$userInfo['mid']), 'copies', 1);
    					
    				}
    				D('MessageReceive')->insertMessage(1, $missionInfo['user_id'], '拒绝通过', '标题为' . $missionInfo['title'] . '得任务被拒绝通过，请重新上传哦', $userInfo['user_id']);
    			}
    			if($flag && !$isFinishMission){
    				$stepCoin = floor($missionInfo['award']/$userInfo['total_step']);
    				$this->_logs(array('stepCoin', $stepCoin, $missionInfo['award'], $userInfo['total_step']));
    				//获得积分
    				D('User')->increaseCoin($userInfo['user_id'], $stepCoin, C('INCOME_TYPE'), '完成任务:'.$missionInfo['title'].'获得积分');
    				//减去积分
    				D('User')->decreaseCoin($missionInfo['user_id'], $stepCoin, C('EXPEND_TYPE'), '参与者已完成任务积分已消耗');
    				D('MessageReceive')->insertMessage(1, $missionInfo['user_id'], '任务部分完成', '标题为' . $missionInfo['title'] . '得步骤'.$userInfo['current_step'].'完成啦', $userInfo['user_id']);
    			}
    			if($isFinishMission){
    				//获得积分
    				$stepCoin = $missionInfo['award'] - ($userInfo['total_step']-1)*(floor($missionInfo['award']/$userInfo['total_step']));
    				D('User')->increaseCoin($userInfo['user_id'], $stepCoin, C('INCOME_TYPE'), '完成任务:'.$missionInfo['title'].'获得积分');
    				//减去积分
    				D('User')->decreaseCoin($missionInfo['user_id'], $stepCoin, C('EXPEND_TYPE'), '参与者已完成任务积分已消耗');
    				D('MessageReceive')->insertMessage(1, $missionInfo['user_id'], '任务完成', '标题为' . $missionInfo['title'] . '得任务完成啦', $userInfo['user_id']);
    			}
    			$this->returnApiMsg ('0', '审核成功' );
    		}else{
    			$model->rollback();//任务事务回滚
    			$this->returnApiMsg ('1035', '审核失败' );
    		}
    	}else{
    		$this->returnApiMsg ( '1034', '用户步骤不存在' );
    	}
    }
    
    /**
     * 放弃上传
     */
    public function giveUpUpload(){
    	$id = I("id");//任务id
    	if(empty($id)){
    		$this->returnApiMsg ('1062', '任务id不能为空' );
    	}
    	if(!is_numeric($id)){
    		$id = _passport_decrypt('gl', $id);//解密id
    	}
    	$sql = "SELECT a.id,a.user_id,a.muid,a.mid,a.step,a.flag,b.current_step,b.total_step,b.step_time,c.start_time,c.end_time FROM `gl_mission_user_step` AS a LEFT JOIN `gl_mission_user` AS b ON (a.muid=b.id) LEFT JOIN `gl_mission` AS c ON (b.mid=c.id) WHERE a.`mid`=" . $id . " AND a.`user_id`=" . $this->userid . " AND UNIX_TIMESTAMP(b.step_time)+172800<UNIX_TIMESTAMP(NOW()) AND a.flag=3 AND c.id IS NOT NULL";
    	$res = D('MissionUser')->getQuery($sql);
    	if($res){
    		foreach ($res as $k=>$v){
    			$musInfo = D('MissionUserStep')->getInfo(array("where"=>array("user_id"=>$this->userid, "mid"=>$v['mid'], "flag"=>3)));
    			if(!empty($musInfo)){
    				$modMissionUserInfo = D("MissionUser")->getInfo(array("where"=>array("user_id"=>$this->userid, "mid"=>$v['mid'], "status"=>0)));
    				if(!empty($modMissionUserInfo)){
    					if(($modMissionUserInfo['current_step']==0 && $modMissionUserInfo['total_step']==1) || ($modMissionUserInfo['current_step']!=0 && $modMissionUserInfo['total_step']!=1)){
    						D("Mission")->updateData(array("id"=>$v['mid']), array("copies"=>array("exp", "`copies`+1")));//暂时注释
    					}
    				}
    				D("MissionUser")->updateData(array("user_id"=>$this->userid, "mid"=>$v['mid'], "status"=>0), array("status"=>2));
    				D("MissionUserStep")->updateData(array("user_id"=>$this->userid, "mid"=>$v['mid'], "status"=>0), array("status"=>2));
    				D("MissionUserStepImg")->updateData(array("user_id"=>$this->userid, "mid"=>$v['mid'], "status"=>0), array("status"=>2));
    			}
    		}
    	}
    	/* $missionUserInfo = D("MissionUser")->getInfo(array("where"=>array("user_id"=>$this->userid, "mid"=>$id, "status"=>0)));
    	if(!empty($missionUserInfo)){
    		$nextTime = strtotime($missionUserInfo['step_time']) + 172800;
    		if(($nextTime<=time()) && ($missionUserInfo['current_step'] < $missionUserInfo['total_step'])){
    			D("MissionUser")->updateData(array("user_id"=>$this->userid, "mid"=>$id, "status"=>0), array("status"=>2));
    			D("MissionUserStep")->updateData(array("user_id"=>$this->userid, "mid"=>$id, "status"=>0), array("status"=>2));
    			D("MissionUserStepImg")->updateData(array("user_id"=>$this->userid, "mid"=>$id, "status"=>0), array("status"=>2));
    			$modMissionUserInfo = D("MissionUser")->getInfo(array("where"=>array("user_id"=>$this->userid, "mid"=>$id, "status"=>2)));
    			if(!empty($modMissionUserInfo)){
    				if(($modMissionUserInfo['current_step']==0 && $modMissionUserInfo['total_step']==1) || ($modMissionUserInfo['current_step']!=0 && $modMissionUserInfo['total_step']!=1)){
                        $musInfo = D('MissionUserStep')->getInfo(array("where"=>array("user_id"=>$this->userid, "mid"=>$id, "flag"=>3)));
                        if(empty($musInfo)){
                            D("Mission")->updateData(array("id"=>$id), array("copies"=>array("exp", "`copies`+1")));//暂时注释
                        }
    				}
    			}
    			//D("Mission")->updateData(array("id"=>$id), array("copies"=>array("exp", "`copies`+1")));//暂时注释
    			//$this->returnApiMsg ('0', '自动放弃上传' );
    		}
    		//$this->returnApiMsg ('1118', '自动放弃失败' );
    	}else{
    		//$this->returnApiMsg ('1031', '任务不存在' );
    	} */
    	$this->returnApiMsg ('0', '自动放弃上传' );
    }
    
    /**
     * 删除用户任务图片
     */
    public function delImage(){
    	$mid = I("mid");//任务id
        if(!is_numeric($mid)){
            $mid = _passport_decrypt('gl', $mid);//解密id
        }
    	//$stepId = I("step_id");//步骤id
    	//$stepId = _passport_decrypt('gl', $stepId);//解密id
    	$urlImg = I("url_img");//图片地址
    	$urlImg = "/" . trim($urlImg, C("GL_HOST_URL"));
        write_log(array('delImage->userid:'. $this->userid, $mid, $urlImg));
    	$res = D("MissionUserStepImg")->deleteData(array("mid"=>$mid, "img_path"=>htmlspecialchars_decode($urlImg), "user_id"=>$this->userid));
    	if(false !== $res){
    		$this->returnApiMsg ('0', '删除成功' );
    	}else{
    		$this->returnApiMsg ('1060', '删除失败' );
    	}
    }
    
    /**
     * 删除任务图片
     */
    public function delMissionImage(){
    	$mid = I("mid");//任务id
        if(!is_numeric($mid)){
            $mid = _passport_decrypt('gl', $mid);//解密id
        }
    	//$stepId = I("step_id");//步骤id
    	//$stepId = _passport_decrypt('gl', $stepId);//解密id
    	$urlImg = I("url_img");//图片地址
    	$urlImg = "/" . trim($urlImg, C("GL_HOST_URL"));
    	$this->_logs(array("删除任务图片", array("mid"=>$mid, "img_path"=>htmlspecialchars_decode($urlImg), "user_id"=>$this->userid)));
    	$res = D("MissionStepImg")->deleteData(array("mid"=>$mid, "img_path"=>htmlspecialchars_decode($urlImg), "user_id"=>$this->userid));
    	if(false !== $res){
    		$this->returnApiMsg ('0', '删除成功' );
    	}else{
    		$this->returnApiMsg ('1060', '删除失败' );
    	}
    }
}