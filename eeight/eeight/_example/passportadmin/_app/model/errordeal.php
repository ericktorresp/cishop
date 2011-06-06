<?php
/**
 * 文件 : /_app/model/errordeal.php
 * 功能 : 数据模型 - 错误处理
 * 
 * @author	   louis
 * @version    v1.0
 * @package    passportadmin
 * @since      2010-08-24
 * 
 */

class model_errordeal extends basemodel
{
    
	/**
	 * id
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 表名
	 *
	 * @var string
	 */
	private  $_table = "error_sync_prize";
	
	/**
	 * 频道id
	 *
	 * @var int
	 */
	public $channel;
	
	/**
	 * 父id
	 *
	 * @var int
	 */
	public $parentId;
	
	/**
	 * 上级用记名
	 *
	 * @var string
	 */
	public $parentName;
	
	/**
	 * 子id
	 *
	 * @var int
	 */
	public $childId;
	
	/**
	 * 下级用户名
	 *
	 * @var string
	 */
	public $childName;
	
	/**
	 * 记录状态
	 *
	 * @var int
	 */
	public $status;
	
	/**
	 * 操作管理员名称
	 *
	 * @var string
	 */
	public $adminName;
	
	/**
	 * 操作管理员id
	 *
	 * @var int
	 */
	public $adminId;
	
	
	/**
	 * 获取奖组同步失败列表
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-08-24
	 * 
	 * @return 		array
	 */
	public function getErrorSyncPrizeList(){
		$aResult = array();
		$sSql = "SELECT * FROM `$this->_table` WHERE `status` != 2";
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
	/**
	 * 写入错误日志
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-08-24
	 * 
	 * @return 		mix				-1	// 数据错误
	 */
	public function insertErrorInfo(){
		// 基础数据检查
		if (!is_numeric($this->parentId) || $this->parentId <= 0 || !is_numeric($this->childId) || $this->childId <= 0 || 
			!is_numeric($this->channel) || empty($this->parentName) || empty($this->childName)){
				return -1;
			}
		// 写入错误日志表
		$aError = array();
		$aError['parent_id'] 	= $this->parentId;
		$aError['parent_name'] 	= $this->parentName;
		$aError['child_id']	 	= $this->childId;
		$aError['child_name']	= $this->childName;
		$aError['channel']	 	= $this->channel;
		$aError['created']	 	= date("Y-m-d H:i:s", time());
		return $this->oDB->insert($this->_table, $aError);
	}
	
	
	
	/**
	 * 根据id获取开户同步失败的信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-08-24
	 * 
	 * @return 		array
	 */
	public function getOne(){
		$aResult = array();
		if (!is_numeric($this->Id) || $this->Id <= 0){
			return $aResult;
		}
		$sSql = "SELECT * FROM `{$this->_table}` WHERE `id` = {$this->Id}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult;
	}
	
	
	/**
	 * 检查用户是否存在
	 * 
	 * @author 		louis
	 * @version 	v1.0	
	 * @since 		2010-08-24
	 * 
	 * @return 		array
	 *
	 */
	public function checkUser(){
		// 数据检查
		$aResult = array();
		if ( !is_numeric($this->parentId) || $this->parentId <= 0 || !is_numeric($this->childId) || $this->childId <= 0 ){
			return $aResult;
		}
		// 检查上级用户
		$sSql = "SELECT `isdeleted` FROM `usertree` WHERE `userid` = {$this->parentId}";
		$aParent = $this->oDB->getOne($sSql);
		
		// 检查下级用户
		$sSql = "SELECT `isdeleted` FROM `usertree` WHERE `userid` = {$this->childId}";
		$aChild = $this->oDB->getOne($sSql);
		
		// 组合最终结果
		if ( empty($aParent) || empty($aChild) )							$aResult['status'] = 0; // 失败状态
		if ( $aParent['isdeleted'] != 0 || $aChild['isdeleted'] != 0 )		$aResult['delete'] = 1; // 将记录置为已处理
		return $aResult;		
	}
	
	
	
	/**
	 * 修改记录状态
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-08-24
	 * 
	 * @return 		mix
	 * 
	 */
	public function setError(){
		// 数据检查
		if ( !is_numeric($this->Id) || $this->Id <= 0 || !is_numeric($this->status) || $this->status < 0 || $this->status > 2 ){
			return false;
		}
		$aData = array();
		$aData['status'] = $this->status;
		$aNum = $this->oDB->update( $this->_table, $aData, "id = {$this->Id} AND status != {$this->status}" );
		if ($aNum === 1){
			return true;
		}else if ($aNum === 0 && $this->oDB->errno() === 0){
			return true;
		} else {
			return false;
		}
	}
	
	
	
	
	/**
	 * 处理同步出错的记录
	 * 
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-08-24
	 *
	 * @return 		mix				-1	// 数据有误
	 * 								-2	// 已经被执行过，直接更改状态即可
	 * 								-3 	// 未通过数据检查
	 * 
	 */
	public function dealError(){
		// 数据检查
		if (!is_numeric($this->Id) || $this->Id <= 0){
			return -3;
		}
		$aResult = $this->getOne();
		// 检查上级用户是否已经手工设置了下级用户的奖金，或者程序重复执行了操作
		$mResult = $this->_verify(intval($aResult['channel']), intval($aResult['child_id']));
		if ($mResult === -1){ // 数据有误
			return -1;
		}
		if ($mResult === false){ // 已经被执行过
			return -2;
		}
		if ( $mResult === true ){ // 开始操作
			// 调用失败频道api完成操作
			$aTranfer = array();
			$aTranfer['iUserId'] = $aResult['child_id']; // 新开用户id
        	$aTranfer['iPid'] = $aResult['parent_id']; // 操作者id
			$oChannelApi = new channelapi( $aResult['channel'], 'syncPrizeGroup', TRUE );
            $oChannelApi->setTimeOut(15);            // 整个转账过程的超时时间, 可能需要微调
            $oChannelApi->sendRequest( $aTranfer );  // 发送转账请求给调度器
            $mAnswers = $oChannelApi->getDatas();    // 获取转账 API 返回的结果
            if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
            {
            	return false;
            } 
            else 
            {
            	return true;
            }
		}
	}
	
	
	
	/**
	 * 检查待操作的用户是否已被完成了同步
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-08-24
	 * 
	 * @return 		-1			// 数据有误
	 * 				false		// 已经执行过
	 * 				true		// 未执行
	 * 
	 */
	private function _verify($iChannelId, $iUserId){
		$sSql = "SELECT * FROM `userchannel` WHERE `userid` = {$iUserId} AND `channelid` = {$iChannelId}";
		$aResult = $this->oDB->getAll($sSql);
		if (count($aResult) > 1){ // 数据有误
			return -1;
		}
		if (count($aResult) == 1){ // 已经执行过
			return false;
		}
		if (count($aResult) == 0){ // 未执行
			return true;
		}
	}
	
	
	
	
	/**
	 * 写入操作管理员信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-08-24
	 * 
	 * @return 		boolean
	 */
	public function setAdmin(){
		// 数据检查
		if (empty($this->adminName) || !is_numeric($this->adminId) || $this->adminId <= 0 || !is_numeric($this->Id) || 
			$this->Id <= 0){
			return false;
		}
		$aData = array();
		$aData['admin_name'] = $this->adminName;
		$aData['admin_id'] = $this->adminId;
		$aNum = $this->oDB->update($this->_table, $aData, "id = {$this->Id}");
		if ($aNum === 1){
			return true;
		}else if ($aNum === 0 && $this->oDB->errno() === 0){
			return true;
		} else {
			return false;
		}
	}
}
?>