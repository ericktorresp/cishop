<?php

require_once "db.php";
/**
 * 获取指定支付接口信息
 *
 * @param int $id			分账户接口id
 * @return array
 */
function getCardInfo($id)
{
    $sql = "SELECT * FROM deposit_acc_set WHERE aid='$id' LIMIT 1";
    $result = $GLOBALS['db']->getOne($sql);

    return $result;
}

/**
 * 获取指定支付接口信息（列表）
 *
 * @param array $ids		分账户id数组
 * @return array
 */
// 待调试
function getCardInfos($ids)
{
    $sql = "SELECT * FROM deposit_acc_set WHERE aid IN (".implode(",", $ids).")";
    $result = $GLOBALS['db']->getAll($sql);

    return array_spec_key($result, 'aid');
}


/**
 * 写入虚拟机的错误码
 *
 * @param int $vm_id		// 虚拟机id
 * @param int $errno		// 错误码
 * @return array
 */
function updateErrno($vm_id, $errno)
{
    // 数据检查
    if (!is_numeric($errno) || intval($vm_id) <= 0){
        die("params are illegal!\n");
    }
    $sql = "UPDATE vmtables SET errno='$errno' WHERE vm_id='$vm_id' LIMIT 1";

    return $GLOBALS['db']->getOne($sql);
}


/**
 * 获取指定虚拟机列表
 *
 * @param int $vm_id			// 虚拟机id
 * @param int $is_running		// 运行状态
 * @return array
 */
function getVmInfo($vm_id, $is_running = 1)
{
    $sql = "SELECT * FROM vmtables WHERE vm_id=".intval($vm_id);
    if ($is_running !== NULL) {
        $sql .= " AND is_running = $is_running";
    }
    if ($result = $GLOBALS['db']->getOne($sql)) {
        if ($cardInfo = getCardInfo($result['card_id'])) {
            $result['card_email']   = $cardInfo['acc_mail'];
            $result['nickname']     = $cardInfo['acc_name'];
            $result['accept_name']  = $cardInfo['acc_ident'];
            $result['card_num']     = $cardInfo['acc_bankacc'];
            $result['login_name']   = $cardInfo['acc_mail'];
            $result['area']         = $cardInfo['area'];
            $result['branch_id']    = $cardInfo['branch_id'];
        }
    }

    return $result;
}


/**
 * 检查记录是否已经存在
 *
 * @param string $key			// 验证串
 * @param strint $sAcceptCard	// 收款账号
 * @return boolean			// true不存在，false已存在
 */
// icbc_transfers表
function isExistTransfer($key, $sAcceptCard)
{
	if (empty($key) || empty($sAcceptCard))		return false;
	$aResult = array();
    $sql = "SELECT * FROM ccb_transfers WHERE encode_key='$key' AND `accept_card` = '{$sAcceptCard}'";
    $aResult = $GLOBALS['db']->getOne($sql);
    return empty($aResult) ? true : false;
}
	
	

/**
 * 批量获取虚拟机信息
 *
 * @param int $is_running		// 运行状态
 * @return array
 */
function getVmInfos($is_running = 1, $bankid)
{
    $sql = "SELECT * FROM vmtables WHERE 1";
    if ($is_running !== NULL) {
        $sql .= " AND is_running = $is_running";
    }
    $sql .= " AND `bank_id` = {$bankid}";
    $result = $GLOBALS['db']->getAll($sql);
    if (empty($result))		return $result;
    $cardInfos = getCardInfos(array_keys(array_spec_key($result, 'card_id')));
    foreach ($result as $k => $v) {
        $result[$k]['card_num']     = $cardInfos[$v['card_id']]['acc_bankacc'];
        $result[$k]['card_email']   = $cardInfos[$v['card_id']]['acc_mail'];
        $result[$k]['acc_name']     = $cardInfos[$v['card_id']]['acc_name'];
        $result[$k]['area']         = $cardInfos[$v['card_id']]['area'];
        $result[$k]['branch_id']    = $cardInfos[$v['card_id']]['branch_id'];
    }
    return $result;
}


/**
 * 获取建行抓取信息记录
 *
 * @param string $cardNum		// 银行卡号
 * @return array
 */
// icbc_transfers表
function getLastTransfer($cardNum)
{
    $sql = "SELECT * FROM ccb_transfers WHERE accept_card='$cardNum' ORDER BY pay_date DESC LIMIT 1";

    return $GLOBALS['db']->getOne($sql);
}


/**
 * 写入登录卡日志
 *
 * @param string $card_num			// 卡号
 * @param int $type					// 状态
 * @return int
 */
function logs($card_num, $type)
{
    $date = date('Y-m-d H:i:s');
    $sql = "INSERT INTO ccb_logs (`card_num`, `type`, `date`)".
        " VALUES('$card_num', '$type', '$date')";
    $GLOBALS['db']->query($sql);

    return $GLOBALS['db']->insert_id;
}


/**
 * 写入工行抓取转账信息
 *
 * @param 		array		$aData		// 抓取数据
 * @param 		int			$iBankId	// 银行id
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-11-17
 * 
 * @return int
 */
function AddTransfer($aData, $iBankId)
{
	// 数据检查
	if (empty($aData['pay_date']) || empty($aData['area']) || floatval($aData['amount']) <= 0 || floatval($aData['balance']) <= 0 || empty($aData['hidden_account']) || empty($aData['acc_name']) || empty($aData['summary']) || empty($aData['encode_key']) || empty($aData['accept_name']) || empty($aData['accept_card'])){
		return false;
	}
	// 充值手续费
	$iFee = paymentFee($aData['amount'], $iBankId);
	if ($iFee < 0){
		return false;
	}
	
    $sql = "INSERT INTO ccb_transfers (`pay_date`, `area`, `amount`, `balance`, `fee`, `full_account`, `hidden_account`, `acc_name`, `currency`, `summary`, `encode_key`, `nickname`, `accept_name`, `accept_card`,`create`)".
        " VALUES('{$aData['pay_date']}', '{$aData['area']}', {$aData['amount']}, {$aData['balance']}, {$iFee}, '{$aData['full_account']}', '{$aData['hidden_account']}', '{$aData['acc_name']}', '{$aData['currency']}', '{$aData['summary']}', '{$aData['encode_key']}', '{$aData['nickname']}', '{$aData['accept_name']}', '{$aData['accept_card']}','" . date("Y-m-d H:i:s") . "')";
    $GLOBALS['db']->query($sql);
    
    return $GLOBALS['db']->affected_rows;
}


/**
 * 获取配置信息
 *
 * @param string $mKeys			// 配置信息键名
 * @return array
 */
function getConfigs($mKeys)
{
	$aConfigs = array();
	if( empty($mKeys) )
	{
		return $aConfigs;
	}
	if( is_array($mKeys) )
	{
		$aConfig = $GLOBALS['db']->getAll("SELECT * FROM `config` WHERE `configkey` IN ('".join("','", $mKeys)."')");
	}
	else 
	{
		$aConfig = $GLOBALS['db']->getOne("SELECT * FROM `config` WHERE `configkey` ='" .$mKeys. "'" . ' LIMIT 1');
	}
	if( !empty($aConfig) )
	{
	    if( is_array($mKeys) )
	    {
			foreach( $aConfig as $value )
			{
				$aConfigs[$value["configkey"]] = $value["configvalue"];
			}
	    }
	    else 
	    {
	        $aConfigs = $aConfig['configvalue'];
	    }
	}
	return $aConfigs;
}






function parse_cmdline_argv_to_var()
{
    $para = array();
    for ($i = 0; $i < $GLOBALS['argc']; $i++)
    {
        if (substr($GLOBALS['argv'][$i], 0, 2) != '--')
        {
            continue;
        }
        $name = substr($GLOBALS['argv'][$i], 2, strpos($GLOBALS['argv'][$i], '=') - 2);
        $para[$name] = trim(substr($GLOBALS['argv'][$i], strpos($GLOBALS['argv'][$i], '=') + 1), '\'"');
    }

    return $para;
}

function array_spec_key($array, $key, $unset_key = false)
{
    if (empty($array) || !is_array($array))
    {
        return array();
    }

    $new_array = array();
    foreach ($array AS $value)
    {
        if (!isset($value[$key]))
        {
            continue;
        }
        $value_key = $value[$key];
        if ($unset_key === true)
        {
            unset($value[$key]);
        }
        $new_array[$value_key] = $value;
    }

    return $new_array;
}

function dump()
{
    static $count = 0;
    $argsNum = func_num_args();
    $args = func_get_args();
    if (extension_loaded('xdebug'))
    {
        echo "<pre><p><strong>**************BEGIN DEBUG($count)************** at ".xdebug_call_class()."::".xdebug_call_function()."() [".xdebug_call_file()." : <font color=red>".xdebug_call_line()."</font>]</strong></p>";
    }
    else
    {
        echo "<p>Debug info (no xdebug extension)</p>";
    }
    for ($i = 0; $i < $argsNum; ++$i)
    {
        if (is_array($args[$i]) && !empty($args[$i]))
        {
            print_r($args[$i]);
        }
        else
        {
            var_dump($args[$i]);
        }
    }
    echo "<p><strong>**************END DEBUG($count)**************</strong></p></pre>\n";

    $count++;
}

function logdump()
{
    static $count = 0;
    $argsNum = func_num_args();
    $args = func_get_args();
    $str = '';
    if (extension_loaded('xdebug'))
    {
        $str .= "**************BEGIN DEBUG($count)************** at ".xdebug_call_class()."::".xdebug_call_function()."() [".xdebug_call_file()." : <font color=red>".xdebug_call_line()."\n";
    }
    else
    {
        $str .= "Debug info (no xdebug extension)\n";
    }
    for ($i = 0; $i < $argsNum; ++$i)
    {
        if (is_string($args[$i]))
        {
            $str .= $args[$i];
        }
        else
        {
            $str .= var_export($args[$i], true)."\n";
        }
    }
    $str .= "**************END DEBUG($count)**************\n";

    file_put_contents("logdump.log", $str, FILE_APPEND);
    $count++;
}

function daddslashes( $sString, $force = 0 ) 
{
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if( !MAGIC_QUOTES_GPC || $force ) 
	{
		if( is_array($sString) ) 
		{
			foreach( $sString as $key => $val ) 
			{
				$sString[$key] = daddslashes( $val, $force );
			}
		} 
		else
		{
			$sString = addslashes( $sString );
		}
	}
	return $sString;
}


/**
 * 获取指定支付接口的充提参数
 * 
 * @param 		int			$id				// 支付接口id
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-11-24
 *
 */
function getLoadParam($id){
	if ($id <= 0)			return false;
	$sql = "SELECT `plat_load_percent`,`plat_load_min`,`plat_load_max` FROM `deposit_set` WHERE `id`='$id' AND `status` = 1";

    return $GLOBALS['db']->getOne($sql);
}


/**
 * 充值手续费计算
 *
 * @param float $iAmount			// 充值金额		
 * @param int	$iDepositId			// 支付接口id 
 * @return unknown
 */
function paymentFee($iAmount, $iDepositId){
	// 数据检查
	if (floatval($iAmount) <= 0 || $iDepositId <= 0)				return false;
	// 获取对应支付接口手续费参数
	$aResult = getLoadParam($iDepositId);
	
	if (empty($aResult))											return false;
    
    $iTempFee = $iAmount * floatval($aResult['plat_load_percent']);
    
    $iFee = 0.00;
    
    if (floatval($iTempFee) >= floatval($aResult['plat_load_max'])){
        $iFee = floatval($aResult['plat_load_max']);
    } else if (floatval($iTempFee) <= floatval($aResult['plat_load_min'])){
        $iFee = floatval($aResult['plat_load_min']);
    } else {
        $iFee = floatval($iTempFee);
    }
	
	// 四舍五入
	return number_format($iFee, 2, '.', '');
}



/**
 * 更新数据库中的cookie值
 *
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-11-26
 * 
 * @return 		boolean
 * 
 */
function updateCookie($sNextCookie, $iVmId){
	if (empty($sNextCookie) || intval($iVmId) <= 0)		return false;
	
	$sSql = "UPDATE `vmtables` SET `cookie` = '{$sNextCookie}' WHERE `vm_id` = {$iVmId}";
	$GLOBALS['db']->query($sSql);
	
	if ($GLOBALS['db']->errno() > 0){
		return false;
	} else {
		return true;
	}
}




/**
 * 更新此次抓取的截止页码
 * 
 * @param 		int		$iVmId		// 虚拟机id
 * @param 		int		$iLastPage	// 抓取的最后一页页码
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-12-06
 * 
 * @return 		boolean
 *
 */
function updateLastPage($iVmId, $iLastPage){
	// 数据检查
	if (intval($iVmId) <= 0 || intval($iLastPage) <= 0){
		return false;
	}
	
	$sSql = "UPDATE `vmtables` SET `last_page` = $iLastPage WHERE `vm_id` = $iVmId";
	$GLOBALS['db']->query($sSql);
	if ($GLOBALS['db']->errno() > 0){
		return false;
	} else {
		return true;
	}
}




/**
 * 更新银行卡号验证串
 * 
 * @param 		int		$iVmId			// 虚拟机id
 * @param 		int		$sCardInfoKey	// 银行卡验证串
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-12-06
 * 
 * @return 		boolean
 * 
 */
function updateCardInfoKey($iVmId, $sCardInfoKey){
	// 数据检查
	if(intval($iVmId) <= 0 || empty($sCardInfoKey)){
		return false;
	}
	
	$sSql = "UPDATE `vmtables` SET `card_info_key` = '{$sCardInfoKey}' WHERE `vm_id` = $iVmId";
	$GLOBALS['db']->query($sSql);
	if ($GLOBALS['db']->errno() > 0 || $GLOBALS['db']->affectedRows() <= 0){
		return false;
	} else {
		return true;
	}
}




/**
 * 更新抓取页面的md5串
 *
 * @param int 		$iVmId				// 虚拟机id
 * @param string 	$sContentKey		// 抓取页面的md5串
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-12-06
 * 
 * return 		boolean
 * 
 */
function updateContentKey($iVmId, $sContentKey){
	// 数据检查
	if(intval($iVmId) <= 0 || empty($sContentKey)){
		return false;
	}
	
	$sSql = "UPDATE `vmtables` SET `get_content_key` = '{$sContentKey}' WHERE `vm_id` = $iVmId";
	$GLOBALS['db']->query($sSql);
	if ($GLOBALS['db']->errno() > 0 || $GLOBALS['db']->affectedRows() <= 0){
		return false;
	} else {
		return true;
	}
}




/**
 * 更新虚拟机抓取的最后一页页码
 * 
 * @param      int      $iVmId      // 虚拟机id
 * @param      int      $iPage      // 页码，默认为1
 * 
 * @author      louis
 * @version     v1.0
 * @since       2010-01-11
 * 
 */
function updatePage($iVmId, $iPage = 1){
    if (intval($iVmId) <= 0 || intval($iPage) <= 0){
        return false;
    }
    
    $sSql = "UPDATE `vmtables` SET `last_page` = '{$iPage}' WHERE `vm_id` = $iVmId";
    $GLOBALS['db']->query($sSql);
	if ($GLOBALS['db']->errno() > 0){
		return false;
	} else {
		return true;
	}
}