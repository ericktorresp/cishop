<?php

require_once "db.php";
/*
select * from payport_acc_set where aid = 55\G;
*************************** 1. row ***************************
             aid: 55
        acc_name: 1@2.com
       acc_ident: 许军
         acc_key: mdeposit
      acc_siteid: 6222021001047824039
        acc_mail: 1@2.com
        acc_attr: 9
    acc_currency: 人民币
      srcbalance: 0.0000
       inbalance: 0.0000
      outbalance: 0.0000
         balance: 0.0000
      total_load: 0
      total_draw: 0
   balance_limit: 0.0000
  ads_payport_id: 7
ads_payport_name: mdeposit
acc_receive_host: 1
        reg_time: 2010-08-18 14:34:00
      valid_time: 0
       open_time: 2010-09-16 10:10:51
        isenable: 1
           utime: 2010-09-20 
*/
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
            $result['card_num'] = $cardInfo['acc_bankacc'];
            $result['card_email'] = $cardInfo['acc_mail'];
        }
    }

    return $result;
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
        $result[$k]['card_num'] = $cardInfos[$v['card_id']]['acc_bankacc'];
        $result[$k]['card_email'] = $cardInfos[$v['card_id']]['acc_mail'];
        $result[$k]['acc_name'] = $cardInfos[$v['card_id']]['acc_name'];
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
function ICBC_GetLastTransfer($cardNum)
{
    $sql = "SELECT * FROM icbc_transfers WHERE accept_card_num='$cardNum' ORDER BY pay_date DESC LIMIT 1";

    return $GLOBALS['db']->getOne($sql);
}


/**
 * 写入登录卡日志
 *
 * @param string $card_num			// 卡号
 * @param int $type					// 状态
 * @return int
 */
function ICBC_log($card_num, $type)
{
    $date = date('Y-m-d H:i:s');
    $sql = "INSERT INTO icbc_logs (`card_num`, `type`, `date`)".
        " VALUES('$card_num', '$type', '$date')";
    $GLOBALS['db']->query($sql);

    return $GLOBALS['db']->insert_id;
}


/**
 * 写入工行抓取转账信息
 *
 * @param string $name				// 汇款人姓名
 * @param string $card_num			// 汇款人卡号
 * @param string $area				// 汇款卡地址
 * @param float $amount				// 汇款金额
 * @param float $fee				// 汇款手续费
 * @param string $notes				// 汇款附言
 * @param string $accept_name		// 收款人姓名
 * @param string $accept_card_num	// 收款人卡号
 * @param string $accept_area		// 收款卡地址
 * @param datetime $pay_date		// 汇款时间
 * @param int $admin_id				// 操作管理员id
 * @param int $status				// 记录状态
 * @param datetime $date			// 写入日期
 * @return int
 */
function ICBC_AddTransfer($name, $card_num, $area, $amount, $fee, $notes, $accept_name, $accept_card_num, $accept_area, $pay_date, $admin_id, $status, $date)
{
    if (empty($name) || empty($amount) || empty($fee) || empty($accept_name) || empty($accept_card_num) || empty($pay_date)){
        return 0;
    }
    $sql = "INSERT IGNORE INTO icbc_transfers (name, card_num, area, amount, fee, notes, accept_name, accept_card_num, accept_area, pay_date, admin_id, status, date)".
        " VALUES('$name', '$card_num', '$area', '$amount', '$fee', '$notes', '$accept_name', '$accept_card_num', '$accept_area', '$pay_date', '$admin_id', '$status', '$date')";
    $GLOBALS['db']->query($sql);
    
    if ($GLOBALS['db']->insertId() > 0){
        return $GLOBALS['db']->affected_rows;
    }
    
    // 如果失败，检查存在的记录是否为自动录入，如果返回后停止，如果不是返回继续下一个执行
    $sSql = "SELECT COUNT(*) AS num FROM `icbc_transfers` WHERE `name` = '{$name}' AND `amount` = {$amount} AND `notes` = '{$notes}' AND `accept_name` = '{$accept_name}' AND `accept_card_num` = '{$accept_card_num}' AND `pay_date` = '{$pay_date}' AND `admin_id` = 0";
    
    $aResult = $GLOBALS['db']->query($sql);
    return $aResult['num'] > 0 ? 0 : -1;
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

?>